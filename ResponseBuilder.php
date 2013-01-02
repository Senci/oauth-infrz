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
    protected $loader;
    protected $twig;

    public function __construct()
    {
        $this->loader = new \Twig_Loader_Filesystem('view');
        $this->twig = new \Twig_Environment($this->loader, array('/cache' => 'cache'));
    }

    /**
     * Builds and returns an error.
     *
     * @param  string $error_code        The error that was triggered
     * @param  string $error_description Additional Information about the error that was produced
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

        exit($this->twig->render('error.html.twig', array('error' => $error)));
    }

    public function buildAuthorize($client, $scopes)
    {
        exit($this->twig->render('authorize.html.twig', array('client' => $client, 'scopes' => $scopes)));
    }

    public function buildLogin($redirect)
    {
        exit($this->twig->render('login.html.twig', array('redirect' => $redirect)));
    }
}
