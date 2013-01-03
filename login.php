<?php

namespace Infrz\OAuth;

require_once('bootstrap.php');

use Infrz\OAuth\ResponseBuilder;
use Infrz\OAuth\ErrorCodes;
use Infrz\OAuth\DatabaseWrapper;

// set all needed GET-Variables to ${get-variable-name} if set
$username = isset($_POST['username']) ? $_POST['username'] : null;
$password = isset($_POST['password']) ? $_POST['password'] : null;

$response_builder = new ResponseBuilder();
$database_wrapper = new DatabaseWrapper();

if (!$username or !$password) {
    $response_builder->buildError('missing_credentials');
}
