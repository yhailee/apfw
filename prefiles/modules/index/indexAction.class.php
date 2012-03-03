<?php
/**
 * Index action
 *
 * @author andrew(at)w(dot)cn
 * @since __DATE__
 */

defined('SYS_ROOT') || die('Access deined !');

class indexAction extends publicAction {
  public function index() {
    $this->assign('title', 'Welcome');
    $this->assign('keywords', 'Welcome page');
    $this->assign('description', 'Welcome page, test content');
    $this->assign('content', 'Thanks for using Andrew Framework, this is a test content !');
    $this->display('index');
  }
}