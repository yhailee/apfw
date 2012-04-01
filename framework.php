<?php

/**
 * Framework entrance
 *
 * @author Andrew li <1024(at)w(dot)cn>
 * @since 23:55 01/16/12
 */
defined('SYS_ROOT') || die('Access deined !');

/**
 * Define APP_ROOT
 */
$trace = debug_backtrace();
$trace = realpath(dirname($trace[0]['file'])) . DIRECTORY_SEPARATOR;
set_include_path($trace);
unset($trace);

/**
 * Define const
 */
define('DS', DIRECTORY_SEPARATOR);
define('TIME', $_SERVER['REQUEST_TIME']);
define('DATE', date('H:i d/m/Y'));
define('TPL', 'templates/');

require SYS_ROOT . 'function.func.php';

/**
 * Configure
 */
if (!file_exists('lib/config.inc.php'))
	(require SYS_ROOT . 'local/builder.class.php') && builder::app();

$config = (array) require 'lib/config.inc.php';

define('SYSTEM', empty($config['system']) ? 'ANDREW Framework' : $config['system']);

require 'local/router.class.php';
list($module, $action, $method) = router::parse();

define('MODULE', $module);
define('ACTION', $action);
define('METHOD', $method);

unset($module, $action, $method);

isset($config['modules']) && (in_array(MODULE, $config['modules']) ||
	isset($config['modules'][MODULE])) || die('Access denied !');

// auth request
if (!empty($config['modules'][MODULE]['username']) &&
	!empty($config['modules'][MODULE]['passwd']) && (
	empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW']) ||
	$config['modules'][MODULE]['username'] != $_SERVER['PHP_AUTH_USER'] ||
	$config['modules'][MODULE]['passwd'] != $_SERVER['PHP_AUTH_PW']
	)
)
	httpAuth();

// require core class
require SYS_ROOT . 'action.class.php';
require SYS_ROOT . 'model.class.php';

// require function/public
file_exists('lib/function.func.php') && require 'lib/function.func.php';
file_exists('lib/public.php') && require 'lib/public.php';

require 'modules/publicAction.class.php';
action(MODULE . '.' . ACTION)->run();