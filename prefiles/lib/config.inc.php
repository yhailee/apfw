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
			'passwd' => ''
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
	),
	/**
	 * @todo switch type (text/password/textarea/editor/button) except (action/enctype)
	 */
	'form' => array(
		'backend_login' => array(
			'action' => '',
			'user_name' => array(
				'type' => 'input-text',
				'width' => 300,
				'validation' => array(
					'rule' => '',
					'msg' => ''
				)
			),
			'user_password' => array(
				'type' => 'input-password',
				'width' => 300,
				'validation' => array(
					'rule' => '',
					'msg' => ''
				)
			),
			'captcha' => array(
				'type' => 'input-captcha',
				'width' => 200,
				'validation' => array(
					'rule' => '',
					'msg' => ''
				)
			),
			'submit' => array(
				'type' => 'button-submit',
				'value' => '提交'
			)
		)
	)
);