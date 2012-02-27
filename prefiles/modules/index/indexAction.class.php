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
    $this->i('title', 'Welcome');
    $this->i('keywords', 'Welcome page');
    $this->i('description', 'Welcome page, test content');
    $this->i('content', 'Thanks for using Andrew Framework, this is a test content !');
    $this->o('index');
  }
}