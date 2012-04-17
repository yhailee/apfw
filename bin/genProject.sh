#!/usr/bin/php
<?php
/**
 * Generate project by shell
 *
 * @author Andrew li<1024(at)w(dot)cn>
 * @version 0.01a
 * @since 14:22 2012/2/26
 */
isset($argv[1]) || die('No project dir' . PHP_EOL);
!is_dir($argv[1]) || !file_exists($argv[1] . '/index.php') || die('Project dir exists' . PHP_EOL);

define('SYS_ROOT', realpath('../') . '/');
define('DATE', date('H:i d/m/Y'));
$_SERVER['SERVER_ADDR'] = '127.0.0.1';
require '../function.func.php';
require '../local/builder.class.php';
builder::app($argv[1]);
echo 'Done' . PHP_EOL;