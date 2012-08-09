<?php

/**
 * Database access handler
 *
 * @author Andrew li<1024(at)w(dot)cn>
 * @version 0.01a
 * @since 16:19 2012/2/20
 */
defined('SYS_ROOT') || die('Access denied');

class db {

	/**
	 * DB connect configures
	 *
	 * @var array
	 * @access public
	 */
	public $default = array();
	public $masters = array();
	public $slaves = array();

	/**
	 * Connection type
	 *
	 * @var boolean
	 * @access public
	 */
	public $separate = FALSE;

	/**
	 * Result
	 *
	 * @var mixed
	 * @access public
	 */
	public $result = NULL;

	/**
	 * Logs
	 *
	 * @var array
	 * @access public
	 */
	public $logs = array();

	/**
	 * Connections
	 *
	 * @var object
	 * @access private
	 */
	private $_default = NULL;
	private $_master = NULL;
	private $_slave = NULL;

	/**
	 * Current connect
	 *
	 * @var string
	 * @access private
	 */
	private $_currentConnect = 'default';

	/**
	 * Table prefix
	 *
	 * @var string
	 * @access private
	 */
	private $_prefix = NULL;

	/**
	 * Execute result
	 *
	 * @var mixed
	 * @access private
	 */
	private $_executeResult = FALSE;

	/**
	 * Action
	 *
	 * @var string
	 * @access private
	 */
	private $_action = NULL;

	/**
	 * Consturctor
	 *
	 * @access public
	 * @return void
	 */
	public function __construct($config = NULL) {
		if (is_array($config)) {
			if (isset($config['separate']))
				$this->separate = $config['separate'];
			if (isset($config['default']) && is_array($config['default']))
				$this->default = $config['default'];
			if (isset($config['masters']) && is_array($config['masters']))
				$this->masters = $config['masters'];
			if (isset($config['slaves']) && is_array($config['slaves']))
				$this->slaves = $config['slaves'];
		}
	}

	/**
	 * Get rows
	 *
	 * @access public
	 * @param string $query
	 * @param array $data
	 * @return array
	 */
	public function rows($query, $data = array()) {
		$this->execute($query, $data);
		$this->result = array();

		if (is_resource($this->_executeResult)) {
			while ($row = mysql_fetch_assoc($this->_executeResult))
				$this->result[] = $row;
		}

		return $this->result;
	}

	/**
	 * Get one row
	 *
	 * @access public
	 * @param string $query
	 * @param array $data
	 * @param array
	 */
	public function row($query, $data = array()) {
		$this->execute($query, $data);

		$this->result = array();

		if (is_resource($this->_executeResult)) {
			while ($row = mysql_fetch_assoc($this->_executeResult)) {
				$this->result = $row;
				break;
			}
		}

		return $this->result;
	}

	/**
	 * Get one field
	 *
	 * @access public
	 * @param string $query
	 * @param array $data
	 * @return string
	 */
	public function field($query, $data = array()) {
		$this->execute($query, $data);

		$this->result = array();

		if (is_resource($this->_executeResult)) {
			while ($row = mysql_fetch_assoc($this->_executeResult)) {
				$this->result = $row;
				break;
			}
		}

		$this->result = array_shift($this->result);

		return $this->result;
	}

	/**
	 * Get insert id
	 *
	 * @access public
	 * @return integer
	 */
	public function insert($query, $data = array()) {
		$this->execute($query, $data);
		return mysql_insert_id($this->_{$this->_currentConnect});
	}

	/**
	 * Execute query
	 *
	 * @access public
	 * @param string $query
	 * @param array $data
	 * @return boolean
	 */
	public function execute($query, $data = array()) {
		$query = trim($query);

		if (empty($query))
			return FALSE;

		if ($this->separate)
			switch ($this->_action = strtoupper(substr($query, 0, strpos($query, ' ')))) {
				case 'INSERT':
				case 'UPDATE':
				case 'DELETE':
				case 'TRUNCATE':
				case 'DROP':
				case 'ALTER':
				case 'CREATE':
				case 'RENAME':
					$this->_currentConnect = 'master';
					break;
				default:
					$this->_currentConnect = 'slave';
			}

		if (!$this->_connect())
			return FALSE;
		$this->_executeResult = mysql_query($this->_parseQuery($query, $data), $this->_{$this->_currentConnect});
		if (mysql_errno($this->_{$this->_currentConnect})) {
			$this->logs[] = mysql_error($this->_{$this->_currentConnect});
		}
		return $this->_executeResult ? TRUE : FALSE;
	}

	/**
	 * Clear logs
	 *
	 * @access public
	 * @return void
	 */
	public function clearLogs() {
		$this->logs = array();
	}

	/**
	 * Connect
	 *
	 * @access private
	 * @return boolean
	 */
	private function _connect() {
		switch ($this->_currentConnect) {
			case 'master':
				if (is_resource($this->_master))
					return TRUE;

				if (count($this->masters) < 1)
					return FALSE;

				$config = $this->masters[array_rand($this->masters)];

				if (empty($config))
					return FALSE;

				break;
			case 'slave':
				if (is_resource($this->_slave))
					return TRUE;

				if (count($this->slaves) < 1)
					return FALSE;

				$config = $this->slaves[array_rand($this->slaves)];

				if (empty($config))
					return FALSE;

				break;
			default:
				if (is_resource($this->_default))
					return TRUE;

				if (count($this->default) < 1)
					return FALSE;

				$config = $this->default[array_rand($this->default)];

				if (empty($config))
					return FALSE;
		}
		if (empty($config['host']) || empty($config['user']) || empty($config['pwd']) || empty($config['db']))
			return FALSE;

		if (empty($config['charset']))
			$config['charset'] = 'utf8';

		$this->_{$this->_currentConnect} = mysql_connect($config['host'], $config['user'], $config['pwd']);
		if (!is_resource($this->_{$this->_currentConnect}))
			return FALSE;
		mysql_select_db($config['db'], $this->_{$this->_currentConnect});
		mysql_query('SET NAMES ' . $config['charset']);

		$this->_prefix = empty($config['prefix']) ? '' : $config['prefix'];

		return TRUE;
	}

	/**
	 * Parse query string
	 *
	 * @access private
	 * @param string $query
	 * @param array $data
	 * @return string
	 */
	private function _parseQuery($query, $data) {
		$keys = array();
		$values = array();
		if (is_array($data)) {
			$arr = array_keys($data);
			foreach ($arr as $a)
				$keys[] = ':' . $a;
			$values = array_values($data);
		}

		$keys[] = '@__';
		$values[] = $this->_prefix;

		foreach ($values as $k => $v)
			$values[$k] = mysql_real_escape_string($v);

		$query = str_replace($keys, $values, $query);
		$this->logs[] = $query;
		return $query;
	}

	/**
	 * Log
	 *
	 * @access private
	 * @param string $msg
	 * @param mixed $return
	 * @return mixed
	 */
	private function _log($msg, $return = TRUE) {
		$this->logs[] = $msg;
		return $return;
	}

}
