<?php

namespace Infrz\OAuth;

use Infrz\OAuth\ErrorCodes;

/**
 * ResponseGenerator generates Responses
 *
 * @author   Senad Licina <senad@licina.eu>
 * @license  http://www.gnu.org/licenses/gpl.html GPLv3
 * @link     https://github.com/Senci/oauth-infrz/
 */
class ResponseBuilder
{
    /**
     * Builds an error
     *
     * @param  string $error_code        The error that was triggered
     * @param  string $error_description Additional Information about the error that was produced
     * @return string JSON-Formatted Error-Response
     */
    public function buildError($error_code, $error_description = null)
    {
        $errors = ErrorCodes::getErrors();
        $error = $errors[$error_code];
        if ($error) {
            if ($error_description) {
                $error['error_description'] = $error_description;
            }

        } else {
            $error = $errors['error_not_found'];
        }
        http_response_code($error['http_status']);
        return json_encode($error);
    }
}