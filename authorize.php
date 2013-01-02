<?php

namespace Infrz\OAuth;

require_once('bootstrap.php');

use Infrz\OAuth\ResponseBuilder;
use Infrz\OAuth\ErrorCodes;

$response_type = $_GET['response_type'];
$client_id = $_GET['client_id'];
$redirect_uri = $_GET['redirect_uri'];
$scope = $_GET['scope'];
$state = $_GET['state'];

mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_http_input('UTF-8');
mb_language('uni');
mb_regex_encoding('UTF-8');
ob_start('mb_output_handler');

$response_builder = new ResponseBuilder();

if (!$response_type or !$client_id or !$redirect_uri) {
    $response_builder->buildError('missing_param');
} else {

}

if (!$scope) {
    $scope = array('username', 'first_name', 'last_name');
}

if (isAuthorized()) {
    $response_builder->buildAuthorize('Yo Mama', $scope);
} else {
    $response_builder->buildLogin('https://google.de');
}

function isAuthorized()
{
    return true;
}
