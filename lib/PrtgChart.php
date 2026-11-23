<?php

declare(strict_types=1);

namespace Colocation;

class PrtgChart
{
    private const MAX_BYTES = 2000000;

    public static function allowedGraphId(string $value): ?string
    {
        if (!in_array($value, ['0', '1', '2', '3'], true)) {
            return null;
        }

        return $value;
    }

    public static function isPng(string $body): bool
    {
        return strncmp($body, "\x89PNG\r\n\x1a\n", 8) === 0;
    }

    public static function dimension(string $raw, int $default): int
    {
        $raw = trim($raw);
        if ($raw === '' || !ctype_digit($raw)) {
            return $default;
        }

        $value = (int) $raw;
        if ($value < 1 || $value > 2000) {
            return $default;
        }

        return $value;
    }

    /**
     * Fetch one PRTG chart. The result never includes the request URL.
     *
     * @return array{ok: bool, body: string, status: int}
     */
    public static function fetch(string $objectId, string $graphId): array
    {
        $failed = ['ok' => false, 'body' => '', 'status' => 0];

        $objectId = ObjectId::numeric($objectId) ?? '';
        $graphId = self::allowedGraphId($graphId) ?? '';
        if ($objectId === '' || $graphId === '') {
            return $failed;
        }

        $base = rtrim(Env::string('PRTG_URL'), '/');
        $token = Env::string('PRTG_API_TOKEN');
        if ($base === '' || $token === '' || !preg_match('#^https?://#i', $base)) {
            return $failed;
        }

        if (!function_exists('curl_init')) {
            return $failed;
        }

        $url = $base . '/chart.png?' . http_build_query([
            'type' => 'graph',
            'width' => self::dimension(Env::string('PRTG_GRAPH_WIDTH', '800'), 800),
            'height' => self::dimension(Env::string('PRTG_GRAPH_HEIGHT', '300'), 300),
            'graphid' => $graphId,
            'id' => $objectId,
            'apitoken' => $token,
        ], '', '&', PHP_QUERY_RFC3986);

        $handle = curl_init($url);
        if ($handle === false) {
            return $failed;
        }

        $body = '';
        $tooLarge = false;
        $maxBytes = self::MAX_BYTES;
        $options = [
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_MAXFILESIZE => $maxBytes,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'whmcs-colocation-prtg',
            CURLOPT_WRITEFUNCTION => static function ($handle, string $chunk) use (&$body, &$tooLarge, $maxBytes): int {
                if ($tooLarge || strlen($body) + strlen($chunk) > $maxBytes) {
                    $tooLarge = true;

                    return 0;
                }

                $body .= $chunk;

                return strlen($chunk);
            },
        ];

        if (defined('CURLOPT_PROTOCOLS') && defined('CURLPROTO_HTTPS')) {
            $protocols = CURLPROTO_HTTPS;
            if (defined('CURLPROTO_HTTP')) {
                $protocols |= CURLPROTO_HTTP;
            }
            $options[CURLOPT_PROTOCOLS] = $protocols;
        }

        curl_setopt_array($handle, $options);

        $ok = curl_exec($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);

        if ($ok !== true || $tooLarge || $status !== 200 || !self::isPng($body)) {
            return ['ok' => false, 'body' => '', 'status' => $status];
        }

        return ['ok' => true, 'body' => $body, 'status' => 200];
    }
}
