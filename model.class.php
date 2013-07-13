<?php

/**
 * Core model
 *
 * @author Andrew Lee<tinray1024@gmail.com>
 * @version 0.01a
 * @since 0:40 2012/1/17
 */
defined('SYS_ROOT') || die('Access denied');

class model {

	protected $_config = NULL;

	public function __construct() {
		$this->_config = $GLOBALS['config'];
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
  }
