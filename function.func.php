<?php

/**
 * Core functions
 *
 * @author Andrew Lee<tinray1024@gmail.com>
 * @version 0.01a
 * @since 0:40 2012/1/17
 */
defined('SYS_ROOT') || die('Access denied');

/**
 * Initialize database handle
 *
 * @return object
 */
function db($dbhandle = 'default') {
  static $db = array();
  if (!isset($db[$dbhandle])) {
    class_exists(__FUNCTION__) || require SYS_ROOT . 'local/db.class.php';
    $db[$dbhandle] = new db($GLOBALS['config'][$dbhandle == 'default' ? 'database' : $dbhandle]);
  }
  return $db[$dbhandle];
}

/**
 * Initialize restful client
 *
 * @return object
 */
function restclient() {
  static $restclient = NULL;
  if (NULL === $restclient && (class_exists(__FUNCTION__) || require SYS_ROOT . 'local/restclient.class.php'))
    $restclient = new restclient;
  return $restclient;
}

/**
 * Initialize form object
 *
 * @return object
 */
function form() {
  static $form = NULL;
  if (NULL === $form && (class_exists(__FUNCTION__) || require SYS_ROOT . 'local/form.class.php'))
    $form = new form;
  return $form;
}

/**
 * Generate page html
 *
 * @param string $url_tpl, <a href="/page/%d.html">%d</a>
 * @param integer $total
 * @param integer $offset
 * @param integer $start
 * @param integer $around
 * @return string
 */
function page($url_tpl, $total, $offset, $start = 0, $around = 4) {
  $url_tpl = trim($url_tpl);
  $total = max(0, (int) $total);
  $offset = max(0, (int) $offset);
  $start = max(0, (int) $start);
  $around = max(4, (int) $around);
  if (!$total || !$offset) {
    return '';
  }
  $cur_page = ceil(($start + 1) / $offset);
  $total_page = ceil($total / $offset);
  $min_page = 1;
  $max_page = $total_page;
  if ($cur_page > $min_page)
    $min_page = max(1, $cur_page - $around);
  if ($cur_page < $total_page)
    $max_page = min($total_page, $cur_page + $around);
  $pages = '';
  // generate pages
  for ($i = $min_page; $i <= $max_page; $i++)
    if ($i != $cur_page)
      $pages .= sprintf($url_tpl, $i, $i);
    else
      $pages .= '<strong>' . $i . '</strong>';
  // add first/last page
  if ($min_page != $cur_page)
    $pages = sprintf($url_tpl, 1, '<<') . $pages;
  if ($max_page != $cur_page)
    $pages .= sprintf($cur_page, $total_page, '>>');
  // compile with template
  static $page_tpl = NULL;
  if (NULL === $page_tpl)
    $page_tpl = require SYS_ROOT . 'templates/page.php';
  return sprintf($page_tpl, $total_page, $pages);
}

/**
 * Change object to array
 *
 * @return object
 */
function obj2arr($object) {
  if (!is_object($object) && !is_array($object)) {
    return $object;
  }

  $object = (array) $object;
  $arr = array();
  foreach ($object as $key => $value)
    $arr[$key] = obj2arr($value);

  return $arr;
}

/**
 * httpAuth
 *
 * @return void
 */
function httpAuth() {
  header('WWW-Authenticate: Basic realm="' . SYSTEM . '"');
  header('HTTP/1.0 401 Unauthorized');
  die('Unauthorized access !');
}

/**
 * Model
 *
 * @return object
 */
function model($name = NULL) {
  static $models = array();
  if (!$name)
    $name = MODULE;
  else
    $name = trim($name);
  if (isset($models[$name]))
    return $models[$name];
  require 'modules/' . $name . 'Model.class.php';
  return ($models[$name] = new $name . 'Model');
}

/**
 * Modules
 *
 * @format module.action
 * @return object
 */
function action($name = NULL, $constructArgs = array()) {
  static $actions = array();
  if (!$name)
    $name = MODULE . '.' . ACTION;
  $name = trim($name);
  if (isset($actions[$name]) && is_object($actions[$name]))
    return $actions[$name];
  if (FALSE === strpos($name, '.'))
    $name = MODULE . '.' . $name;
  $path = 'modules/' . str_replace('.', '/', $name) . 'Action.class.php';
  if (file_exists($path)) {
    require_once $path;
    $name = substr($name, strpos($name, '.') + 1);
    $action = $name . 'Action';
    if (class_exists($action))
      $actions[$name] = new $action($constructArgs);
  }
  isset($actions[$name]) && is_object($actions[$name]) || die('Invalid action[' . $name . ']!');
  return $actions[$name];
}

/**
 * Generate rand chars
 *
 * @param number $length
 * @param number $type
 *               1: upper
 *               2: lower
 *               3: upper+lower
 *               4: number
 *               5: upper+number
 *               6: lower+number
 *               7: upper+lower+number
 *               8: specialchar
 *               9: upper+specialchar
 *               10: lower+specialchar
 *               11: upper+lower+specialchar
 *               12: number+specialchar
 *               13: upper+number+specialchar
 *               14: lower+number+specialchar
 *               15: upper+lower+number+specialchar
 * @return string
 */
function randChars($length = 8, $type = 7) {
  $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $lower = 'abcdefghijklmnopqrstuvwxyz';
  $number = '0123456789';
  $specialchar = '~`!@#$^&*()-_=+[]{};:\'",<.>/?\\|';
  $src = '';
  switch ((int) $type) {
  case 1:
    $src = $upper;
    break;
  case 2:
    $src = $lower;
    break;
  case 3:
    $src = $upper . $lower;
    break;
  case 4:
    $src = $number;
    break;
  case 5:
    $src = $upper . $number;
    break;
  case 6:
    $src = $lower . $number;
    break;
  case 7:
    $src = $upper . $lower . $number;
    break;
  case 8:
    $src = $specialchar;
    break;
  case 9:
    $src = $upper . $specialchar;
    break;
  case 10:
    $src = $lower . $specialchar;
    break;
  case 11:
    $src = $upper . $lower . $specialchar;
    break;
  case 12:
    $src = $number . $specialchar;
    break;
  case 13:
    $src = $upper . $number . $specialchar;
    break;
  case 14:
    $src = $lower . $number . $specialchar;
    break;
  case 15:
    $src = $upper . $lower . $number . $specialchar;
    break;
    /* case 16:
      if (!function_exists('uuid_create'))
      return 'Please install php5-uuid extension';
      uuid_create(&$context);
      uuid_make($context, UUID_MAKE_V4);
      uuid_export($context, UUID_FMT_STR, &$uuid);
    return $uuid; */
  default:
    $src = $upper . $lower . $number;
  }
  $count = strlen($src);
  $string = '';
  for ($i = 0; $i < $length; $i++)
    $string .= $src{rand(0, $count - 1)};

  return $string;
}

/**
 * Out put json
 *
 * @param integer $status
 * @param string $method
 * @param mixed $data
 * @return void
 * @output json
 */
function outputJSON($status, $msg = '', $data = array()) {
  header('Content-type:application/json; charset=utf-8');
  $output = json_encode(array('status' => $status, 'msg' => $msg, 'data' => $data));
  if (preg_match('/\{%(\w+)%\}/is', $output)) {
    $lang_type = !empty($_COOKIE['_l_']) && in_array($_COOKIE['_l_'], $GLOBALS['config']['languages'])? $_COOKIE['_l_']:$GLOBALS['config']['languages'][0];
    $lang_path = 'lang/'.$lang_type. '/';
    $lang = json_decode(file_get_contents($lang_path.'public.json'), TRUE);
    $files = new DirectoryIterator($lang_path);
    if (is_array($files)) {
      foreach ($files as $file) {
        if (!$file->isFile()) continue;
        $filepath = $file->getPathName();
        if (FALSE !== strpos($filepath, 'public.json')) continue;
        $l = json_decode(file_get_contents($filepath), TRUE);
        if (is_array($l))
          $lang = array_merge($lang, $l);
      }
    }
    if (is_array($lang)) {
      foreach ($lang as $k => $l) {
        $output = str_replace('{%'.$k.'%}', $l, $output);
      }
    }
  }
  die($output);
}

/**
 * Make dirs
 *
 * @param string $dir
 * @param string $mode
 * @return boolean
 */
function makeDir($dir, $mode = 0755) {
  return empty($dir) || is_dir($dir) || makeDir(dirname($dir), $mode) && mkdir($dir, $mode);
}

/**
 * Remove dirs
 *
 * @param string $dir
 * @return boolean
 */
function removeDir($dir) {
  return is_file($dir) && FALSE !== @unlink($dir) || is_dir($dir) && FALSE !== array_map('removeDir', glob($dir . (substr($dir, -1) != '/' ? '/' : '') . '*')) && FALSE !== @rmdir($dir) || TRUE;
}

/**
 * Copy path
 *
 * @param string $path
 * @param string $mode
 * @return boolean
 */
function cp($path, $dest, $mode = 0755) {
  $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
  foreach ($iterator as $item) {
    $target = str_replace($path, $dest, $item);
    if (is_dir($item))
      makedir($target, $mode);
    else {
      makedir(dirname($target), $mode);
      copy($item, $target);
      file_put_contents($target, str_replace('__DATE__', DATE, file_get_contents($target)));
    }
    chmod($target, $mode);
  }
  return TRUE;
}

/**
 * Get client ip
 *
 * @return string
 */
function getClientIp() {
  if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown'))
    $ip = getenv('HTTP_CLIENT_IP');
  elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown'))
    $ip = getenv('HTTP_X_FORWARDED_FOR');
  elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown'))
    $ip = getenv('REMOTE_ADDR');
  elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown'))
    $ip = $_SERVER['REMOTE_ADDR'];
  return preg_match("/[\d\.]{7,15}/", $ip) ? $ip : 'unknown';
}

/**
 * Check ip range
 *
 * @param string $ip
 * @param array $ipRange, e.g. array('127.0.0.1-127.0.254.254', '192.168.1.17')
 * @return boolean
 */
function checkIpRange($ip, $ipRange) {
  if (empty($ip) || !ip2long($ip)) {
    return FALSE;
  } elseif (empty($ipRange) || !is_array($ipRange) || in_array('*', $ipRange)) {
    return TRUE;
  }
  $ipLong = 0;
  foreach ($ipRange as $ipAddr) {
    $ipAddr = str_replace(' ', '', $ipAddr);
    if (strpos($ipAddr, '-') && strpos($ipAddr, '-') == strrpos($ipAddr, '-')) {
      list($ipAddr1, $ipAddr2) = explode('-', $ipAddr);
      $ipAddr1 = ip2long($ipAddr1);
      $ipAddr2 = ip2long($ipAddr2);
      if ($ipLong === 0) {
        $ipLong = ip2long($ipLong);
      }
      if ($ipAddr1 <= $ipLong && $ipLong <= $ipAddr2) {
        return TRUE;
      }
    } elseif ($ip == $ipAddr) {
      return TRUE;
    }
  }
  return in_array(preg_replace('/^(\d+\.\d+\.\d+\.)\d+$/', '$1*', $ip), $ipRange)
    || in_array(preg_replace('/^(\d+\.\d+\.)\d+\.\d+$/', '$1*', $ip), $ipRange)
    || in_array(preg_replace('/^(\d+\.)\d+\.\d+\.\d+$/', '$1*', $ip), $ipRange);
}

/**
 * write log
 *
 * @param mixed $output
 * @param boolean $flush before write
 * @param return void
 */
function writeLog($output, $flush = FALSE) {
  $output = is_null($output) ? 'Null' : (empty($output) ? 'Empty' : print_r($output, TRUE));
  $output = date('Y-m-d H:i') . "\n$output\n------------------------------\n";
  if ($flush)
    file_put_contents('runtime/log.txt', $output);
  else
    file_put_contents('runtime/log.txt', $output, FILE_APPEND);
}

/**
 * Get ticket
 *
 * @desc require ticket table
 * @return integer
 */
function getTicket() {
  $salt = strval(randChars(3, 4));
  return strval(db()->insert("REPLACE INTO @__ticket SET salt = ':salt', stub = :stub", array('salt' => $salt, 'stub' => 1))) . $salt;
}

/**
 * Curl post compact 5.1.6
 *
 * @param string $url
 * @param array $contents
 * @param array $files
 * @return mixed
 */
function curlPost($url, $contents = array(), $files = array(), $writeLog = FALSE) {
  if (empty($url) || FALSE === stripos($url, 'http://') && FALSE === stripos($url, 'https://')) {
    return FALSE;
  }
  if (strcmp(PHP_VERSION, '5.1.6')) {
    require_once SYS_ROOT . 'local/restclient.class.php';
    $rest = new restclient();
    $rest->url = $url;
    foreach ($files as $name => $path) {
      $rest->params[$name] = '@' . realpath($path);
    }
    $rest->params = array_merge($contents, $rest->params);
    $rest->post();
    if ($writeLog)
      writeLog($rest->response);
    return $rest->response;
  }
  // form field separator
  $delimiter = '-------------' . uniqid();
  // file upload fields: name => array(type=>'mime/type',content=>'raw data')
  $fileFields = array();
  foreach ($files as $name => $file) {
    $fileFields[$name] = array(
      'name' => substr($file, strrpos($file, '/') + 1),
      'type' => mime_content_type($file),
      'content' => file_get_contents($file)
    );
  }
  // all other fields (not file upload): name => value
  $postFields = $contents;

  $data = '';

  // populate normal fields first (simpler)
  foreach ($postFields as $name => $content) {
    $data .= "--" . $delimiter . PHP_EOL;
    $data .= 'Content-Disposition: form-data; name="' . $name . '"';
    // note: double endline
    $data .= PHP_EOL . PHP_EOL;
    $data .= $content . PHP_EOL;
  }
  // populate file fields
  foreach ($fileFields as $name => $file) {
    $data .= "--" . $delimiter . PHP_EOL;
    // "filename" attribute is not essential; server-side scripts may use it
    $data .= 'Content-Disposition: form-data; name="' . $name . '"; filename="' . $file['name'] . '"' . PHP_EOL;
    // this is, again, informative only; good practice to include though
    $data .= 'Content-Type: ' . $file['type'] . PHP_EOL;
    // this endline must be here to indicate end of headers
    $data .= PHP_EOL;
    // the file itself (note: there's no encoding of any kind)
    $data .= $file['content'] . PHP_EOL;
  }
  // last delimiter
  $data .= "--" . $delimiter . "--" . PHP_EOL;

  $handle = curl_init($url);
  curl_setopt($handle, CURLOPT_POST, TRUE);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($handle, CURLOPT_HTTPHEADER, array(
    'Content-Type: multipart/form-data; boundary=' . $delimiter,
    'Content-Length: ' . strlen($data)
  ));
  curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
  $result = curl_exec($handle);
  if ($writeLog) {
    writeLog(print_r(curl_getinfo($handle), TRUE));
    writeLog($result);
  }
  curl_close($handle);
  unset($data);
  return $result;
}

/**
 * Get ext of file
 *
 * @param string $file_name
 * @return string
 */
function getExt($file) {
  if (!$file || !($file = trim($file)) || !strpos($file, '.'))
    return '';
  return strtolower(substr($file, strrpos($file, '.') + 1));
}

/**
 * Check if positive number
 *
 * @param mixed $mix
 * @return boolean
 */
function checkPositive($mix) {
  return is_numeric($mix) && $mix > 0;
}

/**
 * Get page offset and start row
 *
 * @param array $requestData
 * @return array($offset, $start)
 */
function getPageParams($requestData) {
  $offset = isset($requestData['offset']) && checkPositive($requestData['offset']) ? $requestData['offset'] : 20;
  $start = isset($requestData['page']) && checkPositive($requestData['page']) ? ($requestData['page'] - 1) * $offset : 0;
  return array($offset, $start);
}

/**
 * Minify html
 * 
 * @param string $html
 * @return string
 * @todo complete
 */
function minify($html) {
  //return $html;
  //require_once SYS_ROOT . 'addons/Minify_HTML.class.php';
  //$html = Minify_HTML::minify($html);
  $search = array(
    '/\>[^\S ]+/s', //strip whitespaces after tags, except space
    '/[^\S ]+\</s', //strip whitespaces before tags, except space
    '/(\s)+/s', // shorten multiple whitespace sequences
    '/<!\-\-.*?\-\->/is',
    '/\/>\s+</'
  );
  $replace = array(
    '>',
    '<',
    '\\1',
    '',
    '/><'
  );
  return preg_replace($search, $replace, $html);
}

/**
 * Import redis
 * 
 * @use for compact
 */
function redis() {
  static $redis;
  if (is_a($redis, 'Redis')) {
    return $redis;
  }
  if (!class_exists('Redis')) {
    require SYS_ROOT . 'addons/redisent.class.php';
  }
  $redis = new Redis();
  return $redis;
}

/**
 * Highlight string
 * 
 * @param string $string
 * @param boolean $with_style
 * @return string
 */
function highlightString($string, $with_style = FALSE) {
  $t = token_get_all(trim($string));
  $output = '<code><ol><li><span>';

  foreach ($t as $s) {
    if (is_array($s)) {
      $token = strtolower(substr(token_name($s[0]), 2));
      if ($token === "whitespace") {
        $token = "";
      }
      $string = $s[1];
    } else {
      $token = "";
      $string = $s;
    }

    $open = $close = "";
    if ($token) {
      $open = "<span class=\"" . $token . "\">";
      $close = "</span>";
    }

    $string = str_replace(array("\r\n", "\r"), "\n", $string);
    $string = htmlspecialchars($string);

    $string = preg_replace(
      "/(\\t+)/", "<span class=\"tab\">&nbsp;&nbsp;</span>", $string
    );

    $pizza = explode("\n", $string);

    foreach ($pizza as $i => $piece) {
      if ($i > 0) {
        $output .= "<br /></span></li>\n<li><span>";
      }
      if (!empty($piece)) {
        $output .= $open;
      }
      $output .= $piece;
      if (!empty($piece)) {
        $output .= $close;
      }
    }
  }
  $output .= '</span></li></ol></code>';
  if ($with_style) {
    $output = highlightStyle() . $output;
  }
  return $output;
}

/**
 * Highlight style
 * 
 * @return string
 */
function highlightStyle() {
  return <<<EOF
  <style type="text/css">code{background-color:#FFF;display:block;line-height:100%}code span.tab{font-size:8px}code span{white-space:pre;cursor:default}code ol{padding:0;list-style:none}code li{color:#ccc;font-size:12px;font-family:Verdana,monospace;padding:3px 0;padding-left:6px}code li:hover{background-color:ghostwhite}code li>span{color:#f0f;font-family:monospace}code li>span>span{color:#ccc}code span.inline_html{color:#369}code span.open_tag,code span.close_tag{color:red}code span.variable{color:#0ff}code span.string{color:#0a0}code span.constant_encapsed_string{color:#0f0}code span.comment{color:#808080}code span.file,code span.dir,code span.class_c{color:#8080ff}code span.is_equal,code span.is_greater_or_equal,code span.is_identical,code span.is_not_equal,code span.is_not_identical,code span.is_smaller_or_equal,code span.sl,code span.sl_equal,code span.sr,code span.sr_equal,code span.start_heredoc,code span.boolean_and,code span.boolean_or,code span.double_colon,code span.double_arrow{color:#f0f}code span.endfor,code span.endforeach,code span.endif,code span.endswitch,code span.endwhile,code span.break,code span.continue,code span.declare,code span.enddeclare,code span.do,code span.else,code span.elseif,code span.for,code span.as,code span.foreach,code span.goto,code span.if,code span.case,code span.default,code span.switch,code span.while,code span.function,code span.class,code span.extends,code span.new,code span.var,code span.catch,code span.throw,code span.try,code span.namespace,code span.use,code span.abstract,code span.clone,code span.const,code span.final,code span.implements,code span.interface,code span.private,code span.protected,code span.public,code span.and,code span.or,code span.xor,code span.instanceof,code span.global,code span.static,code span.array,code span.die,code span.echo,code span.empty,code span.exit,code span.eval,code span.include,code span.include_once,code span.isset,code span.list,code span.require,code span.require_once,code span.return,code span.print,code span.unset{color:orange}</style>
EOF;
}

/**
 * Async request
 * 
 * @param string $url
 * @return void
 */
function async_request($url) {
  $parts = parse_url($url);
  $fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80, $errno, $errstr, 30);
  $out = "GET " . $parts['path'] . "?".$parts['query']." HTTP/1.1\r\n";
  $out.= "Host: " . $parts['host'] . "\r\n";
  $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
  $out.= "Content-Length: 0\r\n";
  $out.= "Connection: Close\r\n\r\n";
  fwrite($fp, $out);
  fclose($fp);
}

/**
 * Send mail
 *
 * @param string $name, receiver name
 * @param string $address, receiver email address
 * @param string $subject, email subject
 * @param string $body, email content
 */
function sendmail($name, $address, $subject, $body) {
  if (empty($GLOBALS['config']) || !is_array($GLOBALS['config'])
    || empty($GLOBALS['config']['sendmail']) ) return FALSE;

  class_exists('PHPMailer') || require SYS_ROOT . 'addons/class.phpmailer.php';

  $mail = new PHPMailer();
  $mail->IsSMTP();
  $mail->SMTPDebug = $GLOBALS['config']['sendmail']['debug'];
  $mail->SMTPAuth = $GLOBALS['config']['sendmail']['auth'];
  $mail->CharSet = $GLOBALS['config']['sendmail']['charset'];
  $mail->Host = $GLOBALS['config']['sendmail']['host'];
  $mail->Port = $GLOBALS['config']['sendmail']['port'];
  $mail->Username = $GLOBALS['config']['sendmail']['username'];
  $mail->Password = $GLOBALS['config']['sendmail']['password'];
  $mail->SetFrom($GLOBALS['config']['sendmail']['from'], $GLOBALS['config']['site_name']);
  $mail->AddAddress($address, $name);
  $mail->Subject = $subject;
  $mail->AltBody = preg_replace('/<\/a\s*[^>]*>/', '', str_replace('<br>', "\n", $body));
  $mail->MsgHTML($body);

  return $mail->Send();
}

/**
 * Show message and redirect
 *
 * @access private
 * @param string $msg
 * @param integer $time
 * @param string $url
 * @return void
 * @output html
 */
function showMsg($msg, $time = 2, $url = 'javascript:history.back(-1)') {
  $url = htmlspecialchars($url);
  die('<html><head><title>提示</title></head><body><div style="margin:150px auto 0 auto;text-align:center;padding:10px;border:1px solid #DDD;width:300px;background:#F7F7F7"><p>'.$msg.'</p><a style="font-size:12px;text-decoration:none" href="'.$url.'" title="立即跳转">页面将在'.$time.'秒后跳转</a></div><script>setTimeout(function(){location="'.$url.'"}, '.($time*1000).');</script></body></html>');
}
