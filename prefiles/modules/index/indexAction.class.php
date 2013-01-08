<?php

/**
 * Index action
 *
 * @author Andrew Lee<tinray1024@gmail.com>
 * @since __DATE__
 */
defined('SYS_ROOT') || die('Access deined');

class indexAction extends publicAction {

	public function showIndex() {
		$this->assign('title', 'Welcome');
		$this->assign('keywords', 'Welcome page');
		$this->assign('description', 'Welcome page, test content');
		$this->assign('content', 'Thanks for using A PHP Framework, this is a test content !');
		$this->display('index');
	}

}