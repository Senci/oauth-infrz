<?php
/**
 * @author   Senad Licina <senad@licina.eu>
 * @license  http://www.gnu.org/licenses/gpl.html GPLv3
 * @link     https://github.com/Senci/oauth-infrz/
 */

require_once('../Client/Client.php');
require_once('../vendor/autoload.php');

use Infrz\OAuth\Client;

// set encoding to UTF-8
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_http_input('UTF-8');
mb_language('uni');
mb_regex_encoding('UTF-8');
ob_start('mb_output_handler');

// start a session
session_start();

$logout = isset($_GET['logout']);
if ($logout) {
    unset($_SESSION['token']);
    unset($_SESSION['auth_token']);
    header('Location: /Demo');
}

$client_config_path = sprintf('%s/client.ini', getcwd());
$client = new Client($client_config_path);
$loader = new \Twig_Loader_Filesystem('View');
$twig = new \Twig_Environment($loader, array('/cache' => 'cache'));

// if code is set, exchange auth_code for auth_token
$code = isset($_GET['code']) ? $_GET['code'] : false;
if ($code) {
    try {
        $auth_token = $client->getAuthToken($code);
        $_SESSION['token'] = 'logged_in';
        $_SESSION['auth_token'] = $auth_token->token;
        header('Location: /Demo/');
    } catch (Exception $e) {
        $twig->render('error.html.twig', array('error' => $e->getMessage()));
    }
}

// if there is no session, make an authorization request site
$token = isset($_SESSION['token']) ? $_SESSION['token'] : false;
if (!$token) {
    $link_parts = explode('&', $client->getAuthorizationRequestUri($client->default_redirect_uri));
    $grant_link = $link_parts[0];
    $redirect_uri = explode('=', $link_parts[1])[1];
    $args = array('grant_link' => $grant_link, 'redirect_uri' =>$redirect_uri);
    exit($twig->render('auth_request.html.twig', $args));
}

// get user information and display it.
$auth_token = isset($_SESSION['auth_token']) ? $_SESSION['auth_token'] : false;
try {
    $user = (array)$client->getUser($auth_token);
    foreach ($user as $key => $value) {
        if (!$user[$key]) {
            unset($user[$key]);
        }
    }
    exit($twig->render('user.html.twig', array('user' => $user)));
} catch (Exception $e) {
    exit($twig->render('error.html.twig', array('error' => $e->getMessage())));
}
