<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/ObjectId.php';
require_once __DIR__ . '/../lib/PrtgChart.php';
require_once __DIR__ . '/../lib/GraphRequest.php';

use Colocation\GraphRequest;
use Colocation\ObjectId;
use Colocation\PrtgChart;

$failed = 0;

function check(bool $condition, string $message): void
{
    global $failed;
    if ($condition) {
        return;
    }

    $failed++;
    fwrite(STDERR, $message . PHP_EOL);
}

check(ObjectId::numeric(null) === null, 'null is unset');
check(ObjectId::numeric('') === null, 'blank is unset');
check(ObjectId::numeric('  ') === null, 'spaces are unset');
check(ObjectId::numeric('0') === null, 'zero is unset');
check(ObjectId::numeric('000') === null, 'zero padding is unset');
check(ObjectId::numeric('abc') === null, 'words are unset');
check(ObjectId::numeric('12a') === null, 'mixed text is unset');
check(ObjectId::numeric('-4') === null, 'a negative id is unset');
check(ObjectId::numeric('1.5') === null, 'a decimal id is unset');
check(ObjectId::numeric('123') === '123', 'a sensor id is kept');
check(ObjectId::numeric(' 456 ') === '456', 'surrounding spaces are ignored');
check(ObjectId::numeric('0007') === '7', 'leading zeros are dropped');
check(ObjectId::numeric(str_repeat('1', 15)) === str_repeat('1', 15), '15 digits are kept');
check(ObjectId::numeric(str_repeat('1', 16)) === null, '16 digits are rejected');

check(ObjectId::resolve('20', '10') === '20', 'the service id wins');
check(ObjectId::resolve('abc', '10') === '10', 'a bad service id falls through to the product');
check(ObjectId::resolve('', '10') === '10', 'a blank service id uses the product');
check(ObjectId::resolve(null, '0004') === '4', 'the product id is normalised');
check(ObjectId::resolve('nope', 'also-nope') === null, 'two bad ids resolve to nothing');
check(ObjectId::resolve(null, null) === null, 'no ids resolve to nothing');

check(PrtgChart::allowedGraphId('0') === '0', 'graph 0 is allowed');
check(PrtgChart::allowedGraphId('3') === '3', 'graph 3 is allowed');
check(PrtgChart::allowedGraphId('4') === null, 'graph 4 is rejected');
check(PrtgChart::allowedGraphId('00') === null, 'a padded graph id is rejected');
check(PrtgChart::allowedGraphId('0 ') === null, 'a graph id with a space is rejected');
check(PrtgChart::allowedGraphId('') === null, 'a blank graph id is rejected');

check(PrtgChart::isPng("\x89PNG\r\n\x1a\nrest"), 'a PNG signature is accepted');
check(!PrtgChart::isPng("\x89PNG\n"), 'a short signature is rejected');
check(!PrtgChart::isPng('<html>not a chart</html>'), 'HTML is rejected');
check(!PrtgChart::isPng(''), 'an empty body is rejected');

check(PrtgChart::dimension('', 800) === 800, 'a blank dimension uses the default');
check(PrtgChart::dimension('0', 800) === 800, 'a zero dimension uses the default');
check(PrtgChart::dimension('2001', 800) === 800, 'an oversized dimension uses the default');
check(PrtgChart::dimension('abc', 300) === 300, 'a word dimension uses the default');
check(PrtgChart::dimension(' 640 ', 800) === 640, 'a numeric dimension is kept');

$early = PrtgChart::fetch('not-an-id', '0');
check($early['ok'] === false && $early['body'] === '' && $early['status'] === 0, 'a bad object id does not fetch');
$earlyGraph = PrtgChart::fetch('15', '99');
check($earlyGraph['ok'] === false && $earlyGraph['body'] === '', 'a bad graph id does not fetch');

$server = $_SERVER;

$_SERVER['SCRIPT_NAME'] = '/clientarea.php';
check(
    GraphRequest::clientBaseUrl() === '/modules/servers/colocation/graph.php',
    'a root install points at the module graph proxy'
);

$_SERVER['SCRIPT_NAME'] = '/whmcs/clientarea.php';
check(
    GraphRequest::clientBaseUrl() === '/whmcs/modules/servers/colocation/graph.php',
    'a subdirectory install keeps the WHMCS path'
);

$root = sys_get_temp_dir() . '/colo-whmcs-' . bin2hex(random_bytes(4));
$module = $root . '/modules/servers/colocation';
mkdir($module, 0777, true);
file_put_contents($root . '/init.php', "<?php\n");
file_put_contents($root . '/configuration.php', "<?php\n");

$_SERVER['DOCUMENT_ROOT'] = $root;
$_SERVER['SCRIPT_NAME'] = '/modules/servers/colocation/graph.php';
unset($_SERVER['SCRIPT_FILENAME']);

$found = GraphRequest::locateInit('/tmp/colocation-missing-module');
check($found === realpath($root . '/init.php'), 'the proxy finds WHMCS from the public URL path');

unlink($root . '/configuration.php');
check(
    GraphRequest::locateInit('/tmp/colocation-missing-module') === null,
    'init.php without configuration.php is ignored'
);

removeTree($root);
$_SERVER = $server;

$overview = (string) file_get_contents(__DIR__ . '/../templates/overview.tpl');
$moduleSource = (string) file_get_contents(__DIR__ . '/../colocation.php');
$hooks = (string) file_get_contents(__DIR__ . '/../hooks.php');

check(!str_contains($overview, 'apitoken'), 'the overview does not contain apitoken');
check(!str_contains($overview, 'PRTG_API_TOKEN'), 'the overview does not contain the token setting');
check(!str_contains($overview, 'PRTG_URL'), 'the overview does not contain the PRTG address');
check(str_contains($overview, 'prtgGraphBase'), 'the overview uses the graph proxy');
check(str_contains($overview, 'No network graph is configured'), 'the overview has an empty state');

check(!str_contains($moduleSource, 'apitoken'), 'the module file does not contain apitoken');
check(!str_contains($moduleSource, 'PRTG_API_TOKEN'), 'the module file does not read the token');
check(!str_contains($moduleSource, 'PRTG_SENSOR_ID'), 'the module file does not read a global sensor id');
check(!str_contains($moduleSource, 'uniquefieldname'), 'the sample field names are gone');
check(!str_contains($moduleSource, 'AdminSingleSignOn'), 'the admin single sign-on function is gone');
check(!str_contains($moduleSource, 'SingleSignOnLabel'), 'the single sign-on labels are gone');

check(!str_contains($hooks, 'Provisioning Module'), 'the sample navbar hook is gone');
check(!str_contains($hooks, 'ClientEdit'), 'the sample client edit hook is gone');
check(!is_file(__DIR__ . '/../templates/manage.tpl'), 'the sample manage page is gone');

if ($failed > 0) {
    fwrite(STDERR, $failed . " failed\n");
    exit(1);
}

fwrite(STDOUT, "ok\n");

function removeTree(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }

    foreach (scandir($dir) ?: [] as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            removeTree($path);
            continue;
        }

        unlink($path);
    }

    rmdir($dir);
}
