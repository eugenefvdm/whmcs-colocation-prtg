<?php

/**
 * Client-area PRTG chart proxy.
 *
 * The browser requests this script with a WHMCS service id and a graph period.
 * The PRTG address and API token stay in the module .env file.
 */

ini_set('display_errors', '0');

require_once __DIR__ . '/lib/GraphRequest.php';

Colocation\GraphRequest::handle(__DIR__);
