<?php

use Colocation\Env;
use Colocation\GraphRequest;
use Colocation\ObjectId;
use Colocation\ObjectIdStore;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/lib/GraphRequest.php';

Env::load();

function colocation_MetaData(): array
{
    return [
        'DisplayName' => 'Colocation',
        'APIVersion' => '1.1', // Use API Version 1.1
        'RequiresServer' => true, // Set true if module requires a server to work
        'DefaultNonSSLPort' => '1111', // Default Non-SSL Connection Port
        'DefaultSSLPort' => '1112', // Default SSL Connection Port
    ];
}

function colocation_ConfigOptions(): array
{
    return [
        'Graph Provider' => [
            'Type' => 'dropdown',
            'Options' => [
                'prtg' => 'PRTG',
            ],
        ],
        // Second option so the value stays on configoption2 for existing products.
        'Object ID' => [
            'Type' => 'text',
            'Size' => '16',
            'Description' => 'Default PRTG object id when a service has none of its own.',
        ],
    ];
}

function colocation_SuspendAccount(array $params): string
{
    try {
        // Call the service's suspend function, using the values provided by
        // WHMCS in `$params`.
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'colocation',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

function colocation_UnsuspendAccount(array $params): string
{
    try {
        // Call the service's unsuspend function, using the values provided by
        // WHMCS in `$params`.
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'colocation',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

function colocation_TerminateAccount(array $params): string
{
    try {
        // Call the service's terminate function, using the values provided by
        // WHMCS in `$params`.
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'colocation',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

function colocation_TestConnection(array $params): array
{
    try {
        // Call the service's connection test function.

        $success = true;
        $errorMsg = '';
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'colocation',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        $success = false;
        $errorMsg = $e->getMessage();
    }

    return [
        'success' => $success,
        'error' => $errorMsg,
    ];
}

/**
 * Additional actions an admin user can invoke.
 *
 * Define additional actions that an admin user can perform for an
 * instance of a product/service.
 *
 * @see colocation_buttonOneFunction()
 *
 * @return array
 */
function colocation_AdminCustomButtonArray()
{
    return array(
        "Button 1 Display Value" => "buttonOneFunction",
        "Button 2 Display Value" => "buttonTwoFunction",
    );
}

/**
 * Custom function for performing an additional action.
 *
 * You can define an unlimited number of custom functions in this way.
 *
 * Similar to all other module call functions, they should either return
 * 'success' or an error message to be displayed.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 * @see colocation_AdminCustomButtonArray()
 *
 * @return string "success" or an error message
 */
function colocation_buttonOneFunction(array $params): string
{
    try {
        // Call the service's function, using the values provided by WHMCS in
        // `$params`.
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'colocation',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

/**
 * Custom function for performing an additional action.
 *
 * You can define an unlimited number of custom functions in this way.
 *
 * Similar to all other module call functions, they should either return
 * 'success' or an error message to be displayed.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 * @see colocation_ClientAreaCustomButtonArray()
 *
 * @return string "success" or an error message
 */
function colocation_actionOneFunction(array $params): string
{
    try {
        // Call the service's function, using the values provided by WHMCS in
        // `$params`.
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'colocation',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

/**
 * Graph Object ID on the admin service page. Shows the stored per-service value only.
 *
 * @param array $params common module parameters
 *
 * @return array
 */
function colocation_AdminServicesTabFields(array $params)
{
    try {
        $serviceId = (int) ($params['serviceid'] ?? 0);
        if ($serviceId < 1) {
            return [
                'Graph Object ID' => 'Open a service to set the PRTG object id.',
            ];
        }

        if (!ObjectIdStore::ensure()) {
            return [
                'Graph Object ID' => 'The object id table could not be created. Check the database permissions.',
            ];
        }

        $stored = ObjectIdStore::find($serviceId) ?? '';
        $current = ObjectId::numeric($stored) ?? $stored;
        $safe = htmlspecialchars($current, ENT_QUOTES, 'UTF-8');

        return [
            'Graph Object ID' => '<input type="hidden" name="colocation_original_object_id" value="' . $safe . '" />'
                . '<input type="text" name="colocation_object_id" value="' . $safe . '" size="16" maxlength="15" />'
                . '<div class="text-muted">PRTG sensor or object id for this service. Digits only. Leave blank to use the product Object ID.</div>',
        ];
    } catch (Throwable $e) {
        logModuleCall(
            'colocation',
            __FUNCTION__,
            ['serviceid' => (int) ($params['serviceid'] ?? 0)],
            $e->getMessage(),
            $e->getTraceAsString()
        );
    }

    return [];
}

/**
 * Persist Graph Object ID when the admin service form is saved.
 * A save that does not include the field leaves the stored value alone.
 *
 * @param array $params common module parameters
 */
function colocation_AdminServicesTabFieldsSave(array $params)
{
    $serviceId = (int) ($params['serviceid'] ?? 0);
    if ($serviceId < 1) {
        return;
    }

    $original = $_POST['colocation_original_object_id'] ?? '';
    $submitted = $_POST['colocation_object_id'] ?? null;
    if (!is_string($original) || !is_string($submitted)) {
        return;
    }

    $original = trim($original);
    $submitted = trim($submitted);

    if ($submitted !== '' && ObjectId::numeric($submitted) === null) {
        logModuleCall(
            'colocation',
            __FUNCTION__,
            ['serviceid' => $serviceId],
            'Graph Object ID was not a positive integer and was left unchanged',
            ''
        );

        return;
    }

    $originalId = ObjectId::numeric($original) ?? '';
    $submittedId = ObjectId::numeric($submitted) ?? '';
    if ($originalId === $submittedId) {
        return;
    }

    try {
        $saved = $submittedId === ''
            ? ObjectIdStore::forget($serviceId)
            : ObjectIdStore::put($serviceId, $submittedId);

        if (!$saved) {
            logModuleCall(
                'colocation',
                __FUNCTION__,
                ['serviceid' => $serviceId],
                'Could not store the PRTG object id',
                ''
            );
        }
    } catch (Throwable $e) {
        logModuleCall(
            'colocation',
            __FUNCTION__,
            ['serviceid' => $serviceId],
            $e->getMessage(),
            $e->getTraceAsString()
        );
    }
}

/**
 * Client area overview. Graph images point at graph.php so the PRTG token
 * stays on the server. Replacing the overview hides the standard billing block.
 *
 * @param array $params common module parameters
 *
 * @return array
 */
function colocation_ClientArea(array $params): array
{
    try {
        $serviceId = (int) ($params['serviceid'] ?? 0);
        $productObjectId = isset($params['configoption2']) ? (string) $params['configoption2'] : '';
        $objectId = $serviceId > 0
            ? ObjectId::forService($serviceId, $productObjectId)
            : null;

        return [
            'tabOverviewReplacementTemplate' => 'templates/overview.tpl',
            'templateVariables' => [
                'prtgGraphConfigured' => $objectId !== null,
                'prtgGraphBase' => GraphRequest::clientBaseUrl(),
                'serviceId' => $serviceId,
            ],
        ];
    } catch (Throwable $e) {
        logModuleCall(
            'colocation',
            __FUNCTION__,
            ['serviceid' => (int) ($params['serviceid'] ?? 0)],
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return [
            'tabOverviewReplacementTemplate' => 'templates/error.tpl',
            'templateVariables' => [
                'usefulErrorHelper' => 'The network graphs could not be loaded.',
            ],
        ];
    }
}
