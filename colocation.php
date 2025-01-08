<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

// Require any libraries needed for the module to function.
// require_once __DIR__ . '/path/to/library/loader.php';
//
// Also, perform any initialization required by the service's library.

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

function colocation_MetaData(): array
{
    return [
        'DisplayName' => 'Colocation',
        'APIVersion' => '1.1', // Use API Version 1.1
        'RequiresServer' => true, // Set true if module requires a server to work
        'DefaultNonSSLPort' => '1111', // Default Non-SSL Connection Port
        'DefaultSSLPort' => '1112', // Default SSL Connection Port
        'ServiceSingleSignOnLabel' => 'Login to Panel as User',
        'AdminSingleSignOnLabel' => 'Login to Panel as Admin',
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
            'Object ID' => [
            'Type' => 'text',
            'Size' => '16',
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
 * Admin services tab additional fields.
 *
 * Define additional rows and fields to be displayed in the admin area service
 * information and management page within the clients profile.
 *
 * Supports an unlimited number of additional field labels and content of any
 * type to output.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 * @see colocation_AdminServicesTabFieldsSave()
 *
 * @return array
 */
function colocation_AdminServicesTabFields(array $params)
{
    try {
        // Call the service's function, using the values provided by WHMCS in
        // `$params`.
        $response =[];

        // Return an array based on the function's response.
        return array(
            'Graph Object ID' => '<input type="hidden" name="colocation_original_uniquefieldname" '
                . 'value="' . htmlspecialchars($response['textvalue']) . '" />'
                . '<input type="text" name="colocation_uniquefieldname"'
                . 'value="' . htmlspecialchars($response['textvalue']) . '" />',
        );
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'colocation',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        // In an error condition, simply return no additional fields to display.
    }

    return array();
}

/**
 * Execute actions upon save of an instance of a product/service.
 *
 * Use to perform any required actions upon the submission of the admin area
 * product management form.
 *
 * It can also be used in conjunction with the AdminServicesTabFields function
 * to handle values submitted in any custom fields which is demonstrated here.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 * @see colocation_AdminServicesTabFields()
 */
function colocation_AdminServicesTabFieldsSave(array $params)
{
    // Fetch form submission variables.
    $originalFieldValue = isset($_REQUEST['colocation_original_uniquefieldname'])
        ? $_REQUEST['colocation_original_uniquefieldname']
        : '';

    $newFieldValue = isset($_REQUEST['colocation_uniquefieldname'])
        ? $_REQUEST['colocation_uniquefieldname']
        : '';

    // Look for a change in value to avoid making unnecessary service calls.
    if ($originalFieldValue != $newFieldValue) {
        try {
//            echo $newFieldValue;
            // Call the service's function, using the values provided by WHMCS
            // in `$params`.
        } catch (Exception $e) {
            // Record the error in WHMCS's module log.
            logModuleCall(
                'colocation',
                __FUNCTION__,
                $params,
                $e->getMessage(),
                $e->getTraceAsString()
            );

            // Otherwise, error conditions are not supported in this operation.
        }
    }
}

///**
// * Perform single sign-onfor a given instance of a product/service.
// *
// * Called when single sign-on is requested for an instance of a product/service.
// *
// * When successful, returns a URL to which the user should be redirected.
// *
// * @param array $params common module parameters
// *
// * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
// *
// * @return array
// */
//function colocation_ServiceSingleSignOn(array $params)
//{
//    try {
//        // Call the service's single sign-on token retrieval function, using the
//        // values provided by WHMCS in `$params`.
//        $response = array();
//
//        return array(
//            'success' => true,
//            'redirectTo' => $response['redirectUrl'],
//        );
//    } catch (Exception $e) {
//        // Record the error in WHMCS's module log.
//        logModuleCall(
//            'colocation',
//            __FUNCTION__,
//            $params,
//            $e->getMessage(),
//            $e->getTraceAsString()
//        );
//
//        return array(
//            'success' => false,
//            'errorMsg' => $e->getMessage(),
//        );
//    }
//}

/**
 * Perform single sign-on for a server.
 *
 * Called when single sign-on is requested for a server assigned to the module.
 *
 * This differs from ServiceSingleSignOn in that it relates to a server
 * instance within the admin area, as opposed to a single client instance of a
 * product/service.
 *
 * When successful, returns a URL to which the user should be redirected to.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return array
 */
function colocation_AdminSingleSignOn(array $params)
{
    try {
        // Call the service's single sign-on admin token retrieval function,
        // using the values provided by WHMCS in `$params`.
        $response = array();

        return array(
            'success' => true,
            'redirectTo' => $response['redirectUrl'],
        );
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'colocation',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return array(
            'success' => false,
            'errorMsg' => $e->getMessage(),
        );
    }
}

/**
 * Client area output logic handling.
 *
 * This function is used to define module specific client area output. It should
 * return an array consisting of a template file and optional additional
 * template variables to make available to that template.
 *
 * The template file you return can be one of two types:
 *
 * * tabOverviewModuleOutputTemplate - The output of the template provided here
 *   will be displayed as part of the default product/service client area
 *   product overview page.
 *
 * * tabOverviewReplacementTemplate - Alternatively using this option allows you
 *   to entirely take control of the product/service overview page within the
 *   client area.
 *
 * Whichever option you choose, extra template variables are defined in the same
 * way. This demonstrates the use of the full replacement.
 *
 * Please Note: Using tabOverviewReplacementTemplate means you should display
 * the standard information such as pricing and billing details in your custom
 * template or they will not be visible to the end user.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return array
 */
function colocation_ClientArea(array $params): array
{
    // Determine the requested action and set service call parameters based on
    // the action.
    $requestedAction = isset($_REQUEST['customAction']) ? $_REQUEST['customAction'] : '';

    if ($requestedAction == 'manage') {
        $serviceAction = 'get_usage';
        $templateFile = 'templates/manage.tpl';
    } else {
        $serviceAction = 'get_stats';
        $templateFile = 'templates/overview.tpl';
    }

    try {
        // Call the service's function based on the request action, using the
        // values provided by WHMCS in `$params`.
        $response = [];

        // Create variables for the PRTG settings according to the .env file
        $prtgUrl = env('PRTG_URL');
        $prtgApiToken = env('PRTG_API_TOKEN');
        $prtgSensorId = env('PRTG_SENSOR_ID');
        $prtgGraphWidth = env('PRTG_GRAPH_WIDTH', 800);
        $prtgGraphHeight = env('PRTG_GRAPH_HEIGHT', 300);

        return [
            'tabOverviewReplacementTemplate' => $templateFile,
            'templateVariables' => [
                'prtgUrl' => $prtgUrl,
                'prtgApiToken' => $prtgApiToken,
                'prtgSensorId' => $prtgSensorId,
                'prtgGraphWidth' => $prtgGraphWidth,
                'prtgGraphHeight' => $prtgGraphHeight,
            ],
        ];
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'colocation',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        // In an error condition, display an error page.
        return array(
            'tabOverviewReplacementTemplate' => 'error.tpl',
            'templateVariables' => array(
                'usefulErrorHelper' => $e->getMessage(),
            ),
        );
    }
}
