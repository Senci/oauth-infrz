<?php

require_once('ResponseBuilder.php');
require_once('ErrorCodes.php');
require_once('vendor/autoload.php');

// set encoding to utf-8
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_http_input('UTF-8');
mb_language('uni');
mb_regex_encoding('UTF-8');
ob_start('mb_output_handler');
