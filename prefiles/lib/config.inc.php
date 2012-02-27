<?php
/**
 * Configure
 *
 * @author andrew(at)w(dot)cn
 * @since __DATE__
 */
defined('SYS_ROOT') || die('Access denied !');

return array(
    'modules' => array(
        'index' => array(
            'username' => '',
            'passwd'   => ''
        )
    ),
    'database' => array(
        'separate' => TRUE,
        'default' => array(
            array(
                'host' => '127.0.0.1',
                'db' => 'test',
                'user' => 'root',
                'pwd' => '',
                'charset' => 'utf8',
                'prefix' => ''
            )
        ),
        'masters' => array(
            array(
                'host' => '127.0.0.1',
                'db' => 'test',
                'user' => 'root',
                'pwd' => '',
                'charset' => 'utf8',
                'prefix' => ''
            )
        ),
        'slaves' => array(
            array(
                'host' => '127.0.0.1',
                'db' => 'test',
                'user' => 'root',
                'pwd' => '',
                'charset' => 'utf8',
                'prefix' => ''
            )
        )
    ),
    'redis' => array(
    )
);