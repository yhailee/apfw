<?php

/**
 * Core action class
 *
 * @author andrew(at)w(dot)cn
 * @version 0.01a
 * @since 0:40 2012/1/17
 */
defined('SYS_ROOT') || die('Access denied !');

class action {

	protected $_config = NULL;
	protected $_tplvars = array();
	protected $_requestMethod = '';
	protected $_responseType = '';

	public function __construct() {
		$this->_config = $GLOBALS['config'];
		$mimes = require SYS_ROOT . 'mime.inc.php';
		header('Content-type:' . $mimes[$_SERVER['REQUEST_METHOD']] . '; charset=utf-8');
	}

	/**
	 * __call
	 *
	 * @access public
	 * @param $method
	 * @param $params
	 * @return void
	 */
	public function __call($method, $params = NULL) {
		unset($method, $params);
		die('Invalid method !');
	}

	/**
	 * Run
	 *
	 * @access public
	 * @return void
	 */
	public function run() {
		call_user_func(array($this, (strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') === 0 ? 'do' : 'show') . TRICK));
	}

	/**
	 * Set template var
	 *
	 * @access protected
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	protected final function assign($key, $value) {
		$key = (string) $key;
		$key = trim($key);
		if (!$key)
			return;
		$this->_tplvars[$key] = $value;
	}

	/**
	 * Fetch out put
	 *
	 * @access protected
	 * @param string $tpl
	 * @return void
	 */
	protected final function fetch($tpl = NULL) {
		if (!$tpl)
			$tpl = MODULE . '/' . ACTION . '/' . TRICK;
		else {
			$tmp = array_map('trim', explode('.', $tpl));
			$count = count($tmp);
			if ($count === 0)
				$tpl = MODULE . '/' . ACTION . '/' . TRICK;
			elseif ($count === 1)
				$tpl = MODULE . '/' . ACTION . '/' . $tmp[0];
			elseif ($count === 2)
				$tpl = MODULE . '/' . $tmp[0] . '/' . $tmp[1];
			else
				$tpl = $tmp[0] . '/' . $tmp[1] . '/' . $tmp[2];
		}
		extract($this->_tplvars);
		ob_start();
		require 'templates/' . $tpl . '.php';
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Display out put
	 *
	 * @access protected
	 * @param string $tpl
	 * @return void
	 * @output mixed
	 */
	protected final function display($tpl = NULL) {
		die($this->fetch($tpl));
	}

}
