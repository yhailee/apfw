<?php

/**
 * router
 *
 * @version 0.01a
 * @author andrew(at)w(dot)cn
 * @since 22:04 26/2/2012
 */
defined('SYS_ROOT') || die('Access denied !');

class router {

  public static function parse() {
    return array(empty($_GET['module']) ? 'index' : $_GET['module'],
        empty($_GET['action']) ? 'index' : $_GET['action'],
        empty($_GET['method']) ? 'index' : $_GET['method']
    );
  }

  function parse_bak() {
    if (!empty($config['siteUrl'])) {
      if (substr($config['siteUrl'], -1) == '/')
        $config['siteUrl'] = substr($config['siteUrl'], 0, -1);
      $protocol = strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, strpos($_SERVER['SERVER_PROTOCOL'], '/')));
      $port = $_SERVER['SERVER_PORT'] == 80 || $_SERVER['SERVER_PORT'] == 443 ? '' : $_SERVER['SERVER_PORT'];
      $uri = str_replace($config['siteUrl'], '', $protocol . '://' . $_SERVER['HTTP_HOST'] . $port . $_SERVER['REQUEST_URI']);
    } else
      $uri = $_SERVER['REQUEST_URI'];

// controller
    $uris = explode('/', preg_replace('/index\.php(\/|$)/i', '', $uri));

    unset($protocol, $port, $uri);

    if (!empty($uris[1]))
      define('MODULE', $uris[1]);
    else
      define('MODULE', 'index');

// action
    if (!empty($uris[2]))
      define('ACTION', $uris[2]);
    else
      define('ACTION', 'index');

    // parse request
    $keys = array();
    $values = array();
    foreach ($uris as $k => $v)
      if ($k > 2)
        if ($k % 2 && $v)
          $keys[] = $v;
        else
          $values[] = $v;

    if (count($keys) > count($values))
      $values[] = NULL;

    if ($keys)
      $_GET = array_combine($keys, $values);

// unset variables
    unset($uris, $k, $v, $keys, $values);
  }

}