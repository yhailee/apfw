<?php

/**
 * Core action
 *
 * @author andrew(at)w(dot)cn
 * @since 0:40 01/17/12
 */
defined('SYS_ROOT') || die('Access denied !');

class action {

  protected $_config = NULL;
  protected $_tplvars = array();

  /**
   * __call
   *
   * @access public
   * @param $method
   * @param $params
   * @return void
   */
  public function __call($method, $params = NULL) {
    die('Invalid method !');
  }

  /**
   * Run
   *
   * @access public
   * @return void
   * @output mixed
   */
  public function run() {
    $this->_config = $GLOBALS['config'];
    call_user_func(array($this, METHOD));
  }

  /**
   * Set template var
   *
   * @access protected
   * @param string $key
   * @param mixed $value
   * @return void
   */
  protected function i($key, $value) {
    $key = (string) $key;
    $key = trim($key);

    if (!$key)
      return;

    $this->_tplvars[$key] = $value;
  }

  /**
   * Fetch out put
   *
   * @access public
   * @param string $tpl
   * @return void
   * @output mixed
   */
  public function f($tpl = NULL) {
    if (!$tpl)
      $tpl = MODULE . '/' . ACTION . '/' . METHOD;
    else {
      $tmp = array_map('trim', explode('.', $tpl));
      $count = count($tmp);
      if ($count === 0)
        ;
      elseif ($count === 1)
        $tpl = MODULE . '/' . ACTION . '/' . $tmp[0];
      elseif ($count === 2)
        $tpl = MODULE . '/' . $tmp[0] . '/' . $tmp[1];
      else
        $tpl = $tmp[0] . '/' . $tmp[1] . '/' . $tmp[2];
    }

    extract($this->_tplvars);
    require 'templates/' . $tpl . '.php';
  }

  /**
   * Display out put
   *
   * @access public
   * @param string $tpl
   * @return void
   * @output mixed
   */
  public function o($tpl = NULL) {
    die($this->f($tpl));
  }

}
