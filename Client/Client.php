<?php

/**
 * @author   Senad Licina <senad@licina.eu>
 * @license  http://www.gnu.org/licenses/gpl.html GPLv3
 * @link     https://github.com/Senci/oauth-infrz/
 */

namespace Infrz\OAuth\Client;

require_once('Model/AccessToken.php');
require_once('Model/User.php');

use Infrz\OAuth\Client\Model\AccessToken;
use Infrz\OAuth\Client\Model\User;

Class Client
{
    protected $client_id;
    protected $client_secret;
    protected $server_url;
    public $default_redirect_uri;

    public function __construct($config_path = 'config.ini')
    {
        $config = parse_ini_file($config_path);
        if (isset($config['client_id']) and isset($config['client_secret']) and
            isset($config['server_url']) and isset($config['default_redirect_uri'])) {
            $this->client_id = $config['client_id'];
            $this->client_secret = $config['client_secret'];
            $this->server_url = rtrim($config['server_url'], '/');
            $this->default_redirect_uri = $config['default_redirect_uri'];
        } else {
            throw new \Exception('Your config file is not set properly, please correct it and try again.');
        }
    }

    /**
     * Generates the uri of the authorization grant page (which your user has to visit) for your client.
     *
     * @param string $redirect_uri The uri to which the user gets redirected after a successful authorization grant.
     * @return string
     */
    public function getAuthorizationRequestUri($redirect_uri = '')
    {
        if (!$redirect_uri) {
            $redirect_uri = $this->default_redirect_uri;
        }
        return sprintf('%s/authorize?client_id=%s&redirect_uri=%s', $this->server_url, $this->client_id, urlencode($redirect_uri));
    }

    /**
     * Exchanges an auth_code for a valid access_token.
     *
     * @param string $code The auth code
     * @param string $redirect_uri
     * @param string $grant_type
     * @return AccessToken
     * @throws \Exception Throws an Exception when the server returns an error or the response object is not valid.
     */
    public function getAccessToken($code, $redirect_uri = '', $grant_type = 'authorization_code')
    {
        if (!$redirect_uri) {
            $redirect_uri = $this->default_redirect_uri;
        }
        $url = sprintf('%s/authorize/token', $this->server_url);
        $fields = array(
            'grant_type' => urlencode($grant_type),
            'client_id' => urlencode($this->client_id),
            'client_secret' => urlencode($this->client_secret),
            'code' => urlencode($code),
            'redirect_uri' => urlencode($redirect_uri)
        );
        $response = json_decode($this->requestPost($url, $fields));
        $this->notAnError($response);

        if (!(isset($response->access_token) and isset($response->refresh_token) and isset($response->scope) and isset($response->expires_at))) {
            throw new \Exception('The response object is not a valid access_token.');
        }

        $access_token = new AccessToken();
        $access_token->token = $response->access_token;
        $access_token->refresh_token = $response->refresh_token;
        $access_token->scope = $response->scope;
        $access_token->expires_at = $response->expires_at;

        return $access_token;
    }

    /**
     * Exchanges a refresh_token for a new access_token.
     *
     * @param string $refresh_token
     * @param string $redirect_uri
     * @return AccessToken
     */
    public function getAccessTokenByRefreshToken($refresh_token, $redirect_uri)
    {
        return $this->getAccessToken($refresh_token, $redirect_uri, 'refresh_token');
    }

    /**
     * Requests the server for user information and returns them as an User object.
     *
     * @param string $access_token
     * @return User
     * @throws \Exception Throws an Exception when the response is not a valid user object.
     */
    public function getUser($access_token)
    {
        $url = sprintf('%s/user?access_token=%s', $this->server_url, $access_token);
        $response = json_decode($this->requestGet($url));
        $this->notAnError($response);

        if (!isset($response->type) or $response->type != 'User') {
            throw new \Exception('The response object is not a valid User.');
        }

        $user = new User();
        foreach ($response->scope as $attribute) {
            $user->$attribute = $response->$attribute;
        }
        $user->scope = $response->scope;

        return $user;
    }

    protected function requestPost($url, $fields)
    {
        $fields_string = '';
        foreach ($fields as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }
        $fields_string = rtrim($fields_string, '&');

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    protected function requestGet($url)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    protected function notAnError($response)
    {
        if (isset($response->error)) {
            $err_msg = sprintf('[%s] %s: %s', $response->http_status, $response->error, $response->error_description);
            throw new \Exception($err_msg);
        }
    }
}
