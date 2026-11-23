<?php

declare(strict_types=1);

namespace Colocation;

use Throwable;
use WHMCS\Database\Capsule;

require_once __DIR__ . '/Env.php';
require_once __DIR__ . '/ObjectId.php';
require_once __DIR__ . '/ObjectIdStore.php';
require_once __DIR__ . '/PrtgChart.php';

class GraphRequest
{
    public static function handle(string $moduleDir): void
    {
        ini_set('display_errors', '0');

        $init = self::locateInit($moduleDir);
        if ($init === null) {
            self::send(500);
        }

        try {
            require_once $init;
        } catch (Throwable $e) {
            self::send(500);
        }

        ini_set('display_errors', '0');

        if (!defined('WHMCS')) {
            self::send(500);
        }

        try {
            self::serve();
        } catch (Throwable $e) {
            self::send(502);
        }
    }

    public static function locateInit(string $moduleDir): ?string
    {
        $candidates = [];

        $documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? (string) $_SERVER['DOCUMENT_ROOT'] : '';
        $scriptName = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', (string) $_SERVER['SCRIPT_NAME']) : '';
        if ($documentRoot !== '' && str_starts_with($scriptName, '/')) {
            $scriptDir = dirname($scriptName);
            $candidates[] = rtrim($documentRoot, '/') . $scriptDir . '/../../../init.php';
        }

        if (!empty($_SERVER['SCRIPT_FILENAME'])) {
            $candidates[] = dirname((string) $_SERVER['SCRIPT_FILENAME']) . '/../../../init.php';
        }

        $candidates[] = rtrim($moduleDir, '/') . '/../../../init.php';

        foreach ($candidates as $candidate) {
            $init = realpath($candidate);
            if ($init === false || !is_file($init)) {
                continue;
            }

            if (!is_file(dirname($init) . '/configuration.php')) {
                continue;
            }

            return $init;
        }

        return null;
    }

    /**
     * URL of the graph proxy, relative to the current WHMCS web root.
     * Built from the public script path so a symlinked module still resolves.
     */
    public static function clientBaseUrl(): string
    {
        $scriptName = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', (string) $_SERVER['SCRIPT_NAME']) : '';
        $dir = $scriptName === '' ? '' : str_replace('\\', '/', dirname($scriptName));

        if ($dir === '/' || $dir === '.' || $dir === '') {
            $root = '';
        } else {
            $root = rtrim($dir, '/');
        }

        return $root . '/modules/servers/colocation/graph.php';
    }

    private static function serve(): void
    {
        $method = isset($_SERVER['REQUEST_METHOD']) ? (string) $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($method !== 'GET') {
            self::send(405);
        }

        $clientRaw = $_SESSION['uid'] ?? null;
        $clientId = 0;
        if (is_int($clientRaw) && $clientRaw > 0) {
            $clientId = $clientRaw;
        } elseif (is_string($clientRaw) && ctype_digit($clientRaw) && (string) (int) $clientRaw === $clientRaw) {
            $clientId = (int) $clientRaw;
        }

        if ($clientId < 1) {
            self::send(403);
        }

        $serviceRaw = $_GET['serviceid'] ?? '';
        if (!is_string($serviceRaw) || !ctype_digit($serviceRaw) || (string) (int) $serviceRaw !== $serviceRaw || (int) $serviceRaw < 1) {
            self::send(403);
        }

        $serviceId = (int) $serviceRaw;
        if (!class_exists(Capsule::class)) {
            self::send(500);
        }

        $row = Capsule::table('tblhosting')
            ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
            ->where('tblhosting.id', $serviceId)
            ->where('tblhosting.userid', $clientId)
            ->select('tblproducts.servertype', 'tblproducts.configoption2')
            ->first();

        if ($row === null || strtolower((string) $row->servertype) !== 'colocation') {
            self::send(403);
        }

        $graphRaw = $_GET['graphid'] ?? '';
        if (!is_string($graphRaw)) {
            self::send(400);
        }

        $graphId = PrtgChart::allowedGraphId($graphRaw);
        if ($graphId === null) {
            self::send(400);
        }

        $productObjectId = isset($row->configoption2) ? (string) $row->configoption2 : '';
        $objectId = ObjectId::forService($serviceId, $productObjectId);
        if ($objectId === null) {
            self::send(404);
        }

        $chart = PrtgChart::fetch($objectId, $graphId);
        if (!$chart['ok']) {
            if (function_exists('logModuleCall')) {
                logModuleCall(
                    'colocation',
                    'prtgGraph',
                    [
                        'serviceid' => $serviceId,
                        'graphid' => $graphId,
                    ],
                    'PRTG chart request failed',
                    'HTTP ' . $chart['status']
                );
            }

            self::send(502);
        }

        self::send(200, $chart['body']);
    }

    private static function send(int $status, string $body = ''): never
    {
        while (ob_get_level() > 0) {
            if (!ob_end_clean()) {
                break;
            }
        }

        http_response_code($status);
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');

        if ($status === 200) {
            header('Content-Type: image/png');
            header('Content-Length: ' . (string) strlen($body));
            echo $body;
        }

        exit;
    }
}
