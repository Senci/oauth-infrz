<?php

namespace Infrz\OAuth;

class ErrorCodes
{
    protected static $errors = array(
        'error_not_found' => array(
            'error' => 'error_not_found',
            'error_description' => 'There has been an error but the error_code was not found.',
            'http_status' => 500
        ),
        'invalid_param' => array(
            'error' => 'invalid_param',
            'error_description' => 'One or more required parameters are invalid.',
            'http_status' => 400
        ),
        'missing_param' => array(
            'error' => 'missing_param',
            'error_description' => 'One or more required parameters are missing.',
            'http_status' => 400
        ),
        'missing_credentials' => array(
            'error' => 'missing_credentials',
            'error_description' => 'Username or Password is missing.',
            'http_status' => 400
        ),
        'invalid_credentials' => array(
            'error' => 'invalid_credentials',
            'error_description' => 'Username or Password is missing.',
            'http_status' => 400
        ),
        'invalid_password' => array(
            'error' => 'invalid_password',
            'error_description' => 'Password is invalid, it must contain at least 8 characters.',
            'http_status' => 400
        )
    );

    public static function getErrors()
    {
        $errors = self::$errors;
        return $errors;
    }
}
