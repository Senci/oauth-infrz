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
        'missing_param' => array(
            'error' => 'missing_param',
            'error_description' => 'One or more required parameters are missing.',
            'http_status' => 400
        )
    );

    public static function getErrors()
    {
        $errors = self::$errors;
        return $errors;
    }
}
