<?php

/**
 * Router
 *
 * @author andrew(at)w(dot)cn
 * @version 0.01a
 * @since 22:04 2012/2/26
 */
defined('SYS_ROOT') || die('Access denied');

class router {

	public static function parse() {
		$module = empty($_GET['module']) || !preg_match('/^[a-z]([a-z0-9_])*[a-z0-9]$/i', $_GET['module']) ? 'index' : $_GET['module'];
		$action = empty($_GET['action']) || !preg_match('/^[a-z]([a-z0-9_])*[a-z0-9]$/i', $_GET['action']) ? 'index' : $_GET['action'];
		$trick = empty($_GET['trick']) || !preg_match('/^[a-z]([a-z0-9_])*[a-z0-9]$/i', $_GET['trick']) ? 'index' : $_GET['trick'];
		$response = preg_match('/^.*?(html|json|xml|js|css)$/i', $trick) ? preg_replace('/^.*?(html|json|xml|js|css)$/i', '$1', $trick) : 'html';
		return array($module, $action, $trick, $response);
	}

}