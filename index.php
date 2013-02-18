<?php
/**
 * @author   Senad Licina <senad@licina.eu>
 * @license  http://www.gnu.org/licenses/gpl.html GPLv3
 * @link     https://github.com/Senci/oauth-infrz/
 */

namespace Infrz\OAuth;

require_once('Control/Autoloader.php'); // autoloader for this project
require_once('vendor/autoload.php'); // composer Autoloader

use Infrz\OAuth\Control\FrontController;

// set encoding to UTF-8
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_http_input('UTF-8');
mb_language('uni');
mb_regex_encoding('UTF-8');
ob_start('mb_output_handler');

// start a session
session_start();

// load config
$config_path = 'config.ini';
$config = parse_ini_file($config_path);
if (!isset($config['direct_redirect']) or !isset($config['auth_factory']) or !isset($config['auth_factory_config'])) {
    $config = false;
}

$front_controller = new FrontController($config);
$front_controller->run();
