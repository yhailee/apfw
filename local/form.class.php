<?php

/**
 * Form class
 * base on jquery
 *
 * @version 0.01a
 * @author andrew(at)w(dot)cn
 * @since 07:25 2/22/2012
 */
defined('SYS_ROOT') || die('Access denied !');

class form {

	/**
	 * Form parameters
	 *
	 * @access private
	 */
	private $_id = NULL;
	private $_action = NULL;
	private $_elements = array();
	private $_readyScript = NULL;
	private $_submitScript = NULL;

	/**
	 * Element types
	 *
	 * @access private
	 */
	private $_elementTypes = array(
		'text',
		'button',
		'submit',
		'reset',
		'textarea',
		'select',
		'radio',
		'checkbox'
	);

	public function __construct($id = NULL) {
		if (!empty($id))
			$this->_id = $id;
	}

	/**
	 * Set id
	 *
	 * @author Andrew li
	 * @since 07:23 2/22/2012
	 *
	 * @access public
	 * @param string $id
	 * @return void
	 */
	public function setId($id) {
		$this->_id = $id;
	}

	/**
	 * set Action
	 *
	 * @author Andrew li
	 * @since 07:28 2/22/2012
	 *
	 * @access public
	 * @param string $url
	 * @return void
	 */
	public function setAction($url) {
		$this->_action = $url;
	}

	/**
	 * Add element
	 *
	 * @author Andrew li
	 * @since 07:28 2/22/2012
	 *
	 * @access public
	 * @param string $url
	 * @return void
	 */
	public function addElement($type, $name, $options) {
		if (!in_array($type, $this->_elementTypes))
			return FALSE;

		$this->_elements[] = array(
			'type' => $type,
			'name' => $name,
			'options' => $options
		);
	}

	/**
	 * ready
	 *
	 * @author Andrew li
	 * @since 07:40 2/22/2012
	 *
	 * @access public
	 * @param string $script
	 * @return void
	 */
	public function setReadyScript($script = NULL) {
		$this->_readyScript = $script;
	}

	/**
	 * Submit script
	 *
	 * @author Andrew li
	 * @since 07:45 2/22/2012
	 *
	 * @access public
	 * @param string $script
	 * @return void
	 */
	public function setSubmitScript($script = NULL) {
		$this->_submitScript = $script;
	}

	/**
	 * Make form and return html code
	 *
	 * @author Andrew li
	 * @since 07:47 2/22/2012
	 * @TODO build html
	 * @access public
	 * @return string
	 */
	public function make() {

	}

}