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

$response_builder = new ResponseBuilder();

if (!$response_type or !$client_id) {
    exit($response_builder->buildError('missing_param'));
}

print_r('Request is valid but nothing is implemented yet here. ;)');
