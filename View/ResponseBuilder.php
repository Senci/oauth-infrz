<?php

namespace Infrz\OAuth\View;

use Infrz\OAuth\Model\ErrorCodes;

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
        $this->loader = new \Twig_Loader_Filesystem('View');
        $this->twig = new \Twig_Environment($this->loader, array('/cache' => 'cache'));
    }

    /**
     * Builds an error.
     *
     * @param  string $error_code        The error that was triggered
     * @param  string $error_description Additional Information about the error that was produced
     */
    public function buildError($error_code, $error_description = null)
    {
        $error = $this->getError($error_code, $error_description);
        http_response_code($error['http_status']);

        exit($this->twig->render('error.html.twig', array('error' => $error)));
    }

    /**
     * Builds an authorize page.
     *
     * @param $client
     * @param $scopes
     */
    public function buildAuthorize($client, $scopes)
    {
        exit($this->twig->render('authorize.html.twig', array('client' => $client, 'scopes' => $scopes)));
    }

    /**
     * Builds a login page.
     *
     * @param $redirect
     * @param $error
     */
    public function buildLogin($redirect, $error_code = null)
    {
        $error = $error_code ? $this->getError($error_code) : null;

        exit($this->twig->render('login.html.twig', array('redirect' => $redirect, 'error' => $error)));
    }

    /**
     * Returns an Error by the error_code, alters the description if given.
     *
     * @param $error_code
     * @param $error_description
     * @return array The desired Error as array
     */
    protected function getError($error_code, $error_description = null)
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

        return $error;
    }
}
