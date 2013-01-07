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

$front_controller = new FrontController(dirname(__FILE__));
$front_controller->run();
