<?php

/**
 * Framework entrance
 *
 * @author Andrew Lee<tinray1024@gmail.com>
 * @version $ID$ 
 */
defined('SYS_ROOT') || die('Access deined');

date_default_timezone_set('Asia/Shanghai');

// Define APP_ROOT
$trace = debug_backtrace();
$trace = realpath(dirname($trace[0]['file'])) . DIRECTORY_SEPARATOR;
set_include_path($trace);
unset($trace);

// Define const
define('DATE', date('H:i d/m/Y'));
define('TPL', 'templates/');

require SYS_ROOT . 'function.func.php';

// Configure
if (!file_exists('lib/config.inc.php'))
	(require SYS_ROOT . 'local/builder.class.php') && builder::app();

$config = (array) require 'lib/config.inc.php';

define('SYSTEM', empty($config['system']) ? 'A PHP Framework v0.01a' : $config['system']);

require 'local/router.class.php';
list($module, $action, $trick, $response) = router::parse();

define('MODULE', $module);
define('ACTION', $action);
define('TRICK', $trick);
define('RESPONSE', $response);

unset($module, $action, $trick, $response);

//@todo validate rest
if (!empty($config['modules'][MODULE]['type']) && $config['modules'][MODULE]['type'] == 1) {

}

isset($config['modules']) && (in_array(MODULE, $config['modules']) ||
	isset($config['modules'][MODULE])) || die('Access denied !');

// auth request
(!empty($config['modules'][MODULE]['username']) &&
	!empty($config['modules'][MODULE]['passwd']) && (
	empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW']) ||
	$config['modules'][MODULE]['username'] != $_SERVER['PHP_AUTH_USER'] ||
	$config['modules'][MODULE]['passwd'] != $_SERVER['PHP_AUTH_PW']
	)
) && httpAuth();

// require core class
require SYS_ROOT . 'action.class.php';
require SYS_ROOT . 'model.class.php';

// require function/public
file_exists('lib/function.func.php') && require 'lib/function.func.php';
file_exists('lib/public.php') && require 'lib/public.php';

require 'modules/publicAction.class.php';
action(MODULE . '.' . ACTION)->run();
