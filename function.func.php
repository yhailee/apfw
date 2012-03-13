<?php

/**
 * Core functions
 *
 * @author andrew(at)w(dot)cn
 * @since 0:40 01/17/12
 */
defined('SYS_ROOT') || die('Access denied !');

/**
 * Change object to array
 *
 * @return object
 */
function obj2arr($object) {
	if (!is_object($object) && !is_array($object)) {
		return $object;
	}

	$object = (array) $object;

	foreach ($object as $key => $value)
		$object[$key] = obj2arr($value);

	return $object;
}

/**
 * httpAuth
 *
 * @return void
 */
function httpAuth() {
	header('WWW-Authenticate: Basic realm="' . SYSTEM . '"');
	header('HTTP/1.0 401 Unauthorized');
	die('Unauthorized access !');
}

/**
 * Model
 *
 * @return object
 */
function model($name = NULL) {
	static $models = array();

	if (!$name)
		$name = MODULE;

	$name = trim($name);

	if (isset($models[$name]))
		return $models[$name];

	return ($models[$name] = new model($name));
}

/**
 * Modules
 *
 * @format module.action
 * @return object
 */
function action($name = NULL, $constructArgs = array()) {
	static $actions = array();

	if (!$name)
		$name = MODULE . '.' . ACTION;

	$name = trim($name);

	if (FALSE === strpos($name, '.'))
		$name = MODULE . '.' . $name;

	if (isset($actions[$name]))
		return $actions[$name];

	$path = 'modules/' . str_replace('.', '/', $name) . 'Action.class.php';

	if (file_exists($path) && is_readable($path)) {
		require $path;
		$name = substr($name, strpos($name, '.') + 1);
		$action = $name . 'Action';
		if (class_exists($action))
			$actions[$name] = new $action($constructArgs);
	}

	isset($actions[$name]) || die('Invalid action !');

	return $actions[$name];
}

/**
 * Generate password
 *
 * @param number $length
 * @param number $type
 *               1: upper
 *               2: lower
 *               3: upper+lower
 *               4: number
 *               5: upper+number
 *               6: lower+number
 *               7: upper+lower+number
 *               8: specialchar
 *               9: upper+specialchar
 *               10: lower+specialchar
 *               11: upper+lower+specialchar
 *               12: number+specialchar
 *               13: upper+number+specialchar
 *               14: lower+number+specialchar
 *               15: upper+lower+number+specialchar
 * @return string
 */
function genPwd($length = 8, $type = 7) {
	$upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$lower = 'abcdefghijklmnopqrstuvwxyz';
	$number = '0123456789';
	$specialchar = '~`!@#$^&*()-_=+[]{};:\'",<.>/?\\|';
	$src = '';
	switch ((int) $type) {
		case 1:
			$src = $upper;
			break;
		case 2:
			$src = $lower;
			break;
		case 3:
			$src = $upper . $lower;
			break;
		case 4:
			$src = $number;
			break;
		case 5:
			$src = $upper . $number;
			break;
		case 6:
			$src = $lower . $number;
			break;
		case 7:
			$src = $upper . $lower . $number;
			break;
		case 8:
			$src = $specialchar;
			break;
		case 9:
			$src = $upper . $specialchar;
			break;
		case 10:
			$src = $lower . $specialchar;
			break;
		case 11:
			$src = $upper . $lower . $specialchar;
			break;
		case 12:
			$src = $number . $specialchar;
			break;
		case 13:
			$src = $upper . $number . $specialchar;
			break;
		case 14:
			$src = $lower . $number . $specialchar;
			break;
		case 15:
			$src = $upper . $lower . $number;
			break;
		default:
			$src = $upper . $lower . $number;
	}
	$count = strlen($src);
	$password = '';
	for ($i = 0; $i < $length; $i++)
		$password .= $src{rand(0, $count - 1)};

	return $password;
}

/**
 * Out put json
 *
 * @param integer $status
 * @param string $method
 * @param mixed $data
 * @return void
 * @output json
 */
function outputJSON($status, $msg = '', $data = array()) {
	header('Content-type:application/json; charset=utf-8');
	echo json_encode(array('status' => $status, 'msg' => $msg, 'data' => $data));
}

/**
 * Make dirs
 *
 * @param string $dir
 * @param string $mode
 * @return boolean
 */
function makeDir($dir, $mode = 0755) {
	return empty($dir) || is_dir($dir) || makeDir(dirname($dir), $mode) && mkdir($dir, $mode);
}

/**
 * Copy path
 *
 * @param string $path
 * @param string $mode
 * @return boolean
 */
function cp($path, $dest, $mode = 0755) {
	$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
	foreach ($iterator as $item) {
		$target = str_replace($path, $dest, $item);
		if (is_dir($item))
			makedir($target, $mode);
		else {
			makedir(dirname($target), $mode);
			copy($item, $target);
			file_put_contents($target, str_replace('__DATE__', DATE, file_get_contents($target)));
		}
		chmod($target, $mode);
	}
	return TRUE;
}