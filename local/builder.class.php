<?php

/**
 * Builder
 *
 * @author Andrew Lee<tinray1024@gmail.com>
 * @version 0.01a
 * @since 10:32 2012/2/25
 */
defined('SYS_ROOT') || die('Access denied');

class builder {

	/**
	 * App
	 */
	public static function app($appPath = '.') {
		$ipFor777 = array('127.0.0.1', '10.0.0.', '192.168.');
		$isDev = FALSE;
		foreach ($ipFor777 as $ip) {
			if (0 === strpos($_SERVER['SERVER_ADDR'], $ip)) {
				$isDev = TRUE;
				break;
			}
		}

		cp(SYS_ROOT . 'prefiles', $appPath, $isDev ? 0777 : 0755);

		$index = $appPath . '/index.php';
		file_put_contents($index, sprintf(file_get_contents($index), SYS_ROOT));

		return TRUE;
	}

}