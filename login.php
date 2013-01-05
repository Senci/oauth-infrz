<?php

namespace Infrz\OAuth;

require_once('bootstrap.php');

use Infrz\OAuth\View\ResponseBuilder;
use Infrz\OAuth\Model\ErrorCodes;
use Infrz\OAuth\Model\DatabaseWrapper;

// set all needed GET-Variables to ${get-variable-name} if set
$username     = isset($_POST['username']) ? $_POST['username'] : false;
$password     = isset($_POST['password']) ? $_POST['password'] : false;
$login_action = isset($_POST['la'])       ? $_POST['la'] : false;

$response_builder = new ResponseBuilder();
$database_wrapper = new DatabaseWrapper();

if ($login_action and (!$username or !$password)) {
    $response_builder->buildLogin('', 'missing_credentials');
}

$response_builder->buildLogin('');
