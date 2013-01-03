<?php

namespace Infrz\OAuth;

require_once('bootstrap.php');

use Infrz\OAuth\ResponseBuilder;
use Infrz\OAuth\ErrorCodes;
use Infrz\OAuth\DatabaseWrapper;

if (isset($_GET['response_type'])) {
    $response_type = $_GET['response_type'];
}

// set all needed GET-Variables to ${get-variable-name} if set
$response_type = isset($_GET['response_type']) ? $_GET['response_type'] : null;
$client_id     = isset($_GET['client_id'])     ? $_GET['client_id'] : null;
$redirect_uri  = isset($_GET['redirect_uri'])  ? $_GET['redirect_uri'] : null;
$scope         = isset($_GET['scope'])         ? $_GET['scope'] : null;
$state         = isset($_GET['state'])         ? $_GET['state'] : null;

$response_builder = new ResponseBuilder();
$database_wrapper = new DatabaseWrapper();

if (!$response_type or !$client_id or !$redirect_uri) {
    $response_builder->buildError('missing_param');
} else {
}

// set scope to array or default value
if ($scope) {
    $scope = explode(',', $scope);
} else {
    $scope = array('username', 'first_name', 'last_name');
}

$client = $database_wrapper->getClientById($client_id);

if (!$client) {
    $response_builder->buildError('invalid_param', 'The given client_id is invalid.');
}
if ($client->redirect_uri != urldecode($redirect_uri)) {
    $response_builder->buildError('invalid_param', 'The given redirect_uri is invalid.');
}

if (isAuthorized()) {
    $response_builder->buildAuthorize($client->name, $scope);
} else {
    $response_builder->buildLogin('https://google.de');
}

function isAuthorized()
{
    return true;
}
