<?php

/**
 * Core action class
 *
 * @author Andrew Lee<tinray1024@gmail.com>
 * @version $ID$
 */
defined('SYS_ROOT') || die('Access denied !');

class action {

  protected $_config = NULL;
  protected $_tplvars = array();
  protected $_requestMethod = '';
  protected $_responseType = '';

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

  /**
   * Run
   *
   * @access public
   * @return void
   */
  public function run() {
    call_user_func(array($this, (!empty($_SERVER['REQUEST_METHOD']) && strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') === 0 ? 'do' : 'show') . TRICK));
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
    $backtrace = debug_backtrace();
    $class = strtolower(substr(get_class($backtrace[1]['object']), 0, -6));
    $function = strtolower(substr($backtrace[2]['function'],(0 === strpos($backtrace[2]['function'], 'do')? 2: 
      (0 === strpos($backtrace[2]['function'], 'show')? 4: 0))));
    if (!$tpl)
      $tpl = MODULE . '/' . $class . '/' . $function;
    else {
      $tmp = array_map('trim', explode('.', $tpl));
      $count = count($tmp);
      if ($count === 0)
        $tpl = MODULE . '/' . $class . '/' . $function;
      elseif ($count === 1)
        $tpl = MODULE . '/' . $class . '/' . $tmp[0];
      elseif ($count === 2)
        $tpl = MODULE . '/' . $tmp[0] . '/' . $tmp[1];
      else
        $tpl = $tmp[0] . '/' . $tmp[1] . '/' . $tmp[2];
    }
    extract($this->_tplvars);
    ob_start();
    require 'templates/' . $tpl . '.phtml';
    $content = ob_get_contents();
    ob_end_clean();
    // code
    if (FALSE !== ($codePos = stripos($content, '[code]')) && stripos($content, '[/code]')) {
      $content = preg_replace_callback('/(.*?)\[code\](.*?)\[\/code\](.*)/is', $this->_code_replacer($matchs), $content);
      $content = substr($content, 0, $codePos) . highlightStyle() . substr($content, $codePos);
    }

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

  /**
   * Code replacer
   * 
   * @access private
   * @param array $matchs
   * @return string
   */
  private function _code_replacer($matchs) {
    return $matchs[1] . highlightString($matchs[2]) . $matchs[3];
  }

}
