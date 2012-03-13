<?php

/**
 * Core model
 *
 * @author andrew(at)w(dot)cn
 * @since 0:40 01/17/12
 * @TODO testing every method
 */
defined('SYS_ROOT') || die('Access denied !');

/**
 * Model format
 *
 * array(
 *  'id' => 'uid',
 *  'name' => 'user.name',
 *  'password' => 'user.password',
 *  'nick' => 'user_profile.nick',
 * );
 */
class model {

	/**
	 * Model name
	 *
	 * @var string
	 * @access private
	 */
	private $_model = NULL;

	/**
	 * Default limit
	 *
	 * @var string
	 * @access private
	 */
	private $_limit = '0, 15';

	/**
	 * Maps
	 *
	 * @var array
	 * @access private
	 */
	private $_maps = array();

	/**
	 * DB object
	 *
	 * @var object
	 * @access private
	 */
	private static $db = NULL;

	/**
	 * Construct
	 *
	 * @access public
	 * @param string $name
	 * @return void
	 */
	public function __construct($name = NULL, $options = array()) {
		if (empty($name))
			return FALSE;

		if (!file_exists('models/' . $name . '.php'))
			return FALSE;

		$this->_model = $name;

		$this->_maps = require 'models/' . $name . '.php';

		if (is_array($options) && count($options) > 0)
			foreach ($options as $k => $v)
				if (isset($this->{'_' . $k}))
					$this->{'_' . $k} = $v;

		if (NULL === self::$db) {
			require_once SYS_ROOT . 'local/db.class.php';
			self::$db = new db($GLOBALS['config']['database']);
		}
	}

	/**
	 * __call
	 *
	 * @access public
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	public function __call($name, $arguments) {
		if (strlen($name) > 3 && (0 === strpos($name, 'set') || 0 === strpos($name, 'get'))) {
			$action = substr($name, 0, 3);
			$id = $arguments[0];
			$attr = strtolower(substr($name, 3));
			return call_user_func_array(array($this, $action . 'Attr'), array($id, $action == 'set' ? array($attr => $arguments[1]) : $attr));
		}
	}

	/**
	 * Get list
	 *
	 * @access public
	 * @param mixed $condition
	 * @param mixed $limit
	 * @param string $order
	 * @return array
	 */
	public function getList($condition = NULL, $limit = NULL, $order = NULL) {
		$tables = array($this->_model);
		$where = array();
		if (is_array($condition) && count($condition) > 0) {
			$data = array();
			foreach ($condition as $k => $c)
				if (isset($this->_maps[$k])) {
					$data[$this->_maps[$k]] = $c;
					if (0 === strpos($c, '~~')) {
						// ~~%,2,% => LIKE %,2,%
						$where[] = '@__' . $this->_maps[$k] . ' LIKE ":' . $this->_maps[$k] . '"';
						$data[$this->_maps[$k]] = substr($c, 2);
					} elseif (0 === strpos($c, '@>')) {
						// @>4 => >4, @>=4 => >=4
						$where[] = '@__' . $this->_maps[$k] . ' > ":' . $this->_maps[$k] . '"';
						$data[$this->_maps[$k]] = substr($c, 2);
					} elseif (0 === strpos($c, '@<')) {
						// @<3 => <3, @<=3 => <=3
						$where[] = '@__' . $this->_maps[$k] . ' < ":' . $this->_maps[$k] . '"';
						$data[$this->_maps[$k]] = substr($c, 2);
					} elseif (0 === stripos($c, 'ISIN')) {
						// ISIN1,2,3,4,5 => IN (1,2,3,4,5)
						$where[] = '@__' . $this->_maps[$k] . ' IN (:' . $this->_maps[$k] . ')';
						$data[$this->_maps[$k]] = substr($c, 4);
					} elseif (0 === strcasecmp($c, 'ISNULL'))
					// ISNULL => IS NULL
						$where[] = '@__' . $this->_maps[$k] . ' IS :' . $this->_maps[$k];
					elseif (0 === strcasecmp($c, 'SETNULL'))
					// SETNULL => = NULL
						$where[] = '@__' . $this->_maps[$k] . '=:' . $this->_maps[$k];
					else
						$where[] = '@__' . $this->_maps[$k] . '=":' . $this->_maps[$k] . '"';
					$table = substr($this->_maps[$k], 0, strpos($this->_maps[$k], '.'));
					if (!in_array($table, $tables))
						$tables[] = $table;
				}
		}

		if (NULL !== $limit) {
			if (is_string($limit)) {
				$limit = 'LIMIT ' . $limit;
			} elseif (is_array($limit) && 2 === count($limit)) {
				$limit = 'LIMIT ' . implode(', ', $limit);
			} else {
				$limit = $this->_limit;
			}
		} else {
			$limit = $this->_limit;
		}

		if (NULL !== $order && is_string($order)) {
			if (FALSE === stripos($order, 'RAND()')) {
				$orders = explode(',', $order);
				$order = '';
				foreach ($orders as $o)
					if (isset($this->_maps[$o])) {
						$order[] = '@__' . $this->_maps[$o];
						$table = substr($this->_maps[$o], 0, strpos($this->_maps[$o], '.'));
						if (!in_array($table, $tables))
							$tables[] = $table;
					}

				$order = implode(',', $order);
			}
			if ($order)
				$order = 'ORDER BY ' . $order;
		} else {
			$order = '';
		}

		foreach ($tables as $k => $t) {
			$tables[$k] = '@__' . $t;
			if ($t != $this->_model)
				$where[] = $tables[$k] . '.' . $this->_maps['id'] . '=@__' . $this->_model . '.' . $this->_maps['id'];
		}

		if (count($where) > 0)
			$where = ' WHERE ' . implode(' AND ', $where);
		else
			$where = '';

		if ($order)
			$order = ' ORDER ' . $order;

		if ($limit)
			$limit = ' LIMIT ' . $limit;

		// build query
		$query = 'SELECT @__' . $this->_model . '.' . $this->_maps['id'] . ' FROM ' . implode(',', $tables) . $where . $order . $limit;
		return self::$db->rows($query, $data);
	}

	/**
	 * Add data
	 *
	 * @access public
	 * @param array $data
	 * @return mixed
	 */
	public function add($data = array()) {
		if (!is_array($data) || count($data) < 1)
			return FALSE;

		$tables = array();
		foreach ($data as $k => $v) {
			if (isset($this->_maps[$k])) {
				$table = substr($this->_maps[$k], 0, strpos($this->_maps[$k], '.'));
				$tables[$table][$this->_maps[$k]] = $v;
			}
		}

		if (!array_key_exists($this->_model, $tables))
			return FALSE;

		// primary table
		$query = array();
		$data = array();
		foreach ($tables[$this->_model] as $k => $v) {
			$query[] = '@__' . $k . '=":' . $k . '"';
			$data[$k] = $v;
		}
		$query = 'INSERT INTO @__' . $this->_model . ' SET ' . implode(',', $query);
		$id = self::$db->insert($query, $data);

		if (!$id)
			return FALSE;

		// slave tables
		unset($tables[$this->_model]);
		foreach ($tables as $t => $table) {
			$query = array('@__' . $this->_maps['id'] . '=":id"');
			$data = array('id' => $id);
			foreach ($table as $k => $v) {
				$query[] = '@__' . $k . '=":' . $k . '"';
				$data[$k] = $v;
			}
			$query = 'INSERT INTO @__' . $t . ' SET ' . implode(',', $query);
			self::$db->insert($query, $data);
		}
		return $id;
	}

	/**
	 * Get attribute(s)
	 *
	 * @param mixed $id
	 * @param mixed $attrs
	 * @return mixed
	 */
	public function getAttr($id, $attrs = array()) {
		if (empty($id))
			return NULL;

		$isSingle = TRUE;

		if (is_string($attrs)) {
			if (empty($attrs))
				return NULL;
			$attrs = explode(',', $attrs);

			if (count($attrs) > 1)
				$isSingle = FALSE;
		} elseif (is_array($attrs)) {
			if (count($attrs) > 1)
				$isSingle = FALSE;
		} else {
			return NULL;
		}

		if (!is_array($attrs) || count($attrs) < 1)
			return NULL;

		foreach ($attrs as $f)
			if (!isset($this->_maps[$f]))
				return NULL;

		$tables = array();
		$fields = array();
		$where = array();
		$data = array('id' => $id);
		foreach ($attrs as $a) {
			$fields[] = '@__' . $this->_maps[$a];
			$table = '@__' . substr($this->_maps[$a], 0, strpos($this->_maps[$a], '.'));
			if (!in_array($a, $fields)) {
				$tables[] = $table;
				$where[] = $table . '.' . $this->_maps['id'] . '=":id"';
			}
		}
		$tables = array_unique($tables);
		$where = array_unique($where);
		$query = 'SELECT ' . implode(',', $fields) . ' FROM ' . implode(',', $tables) . ' WHERE ' . implode(' AND ', $where);
		return $isSingle ? self::$db->field($query, $data) : self::$db->row($query, $data);
	}

	/**
	 * Set attribute(s)
	 *
	 * @param mixed $id
	 * @param array $attrs
	 * @return boolean
	 */
	public function setAttr($id, $attrs = array()) {
		if (empty($id))
			return FALSE;

		if (!is_array($attrs) || count($attrs) < 1)
			return FALSE;

		$fields = array_keys($attrs);
		foreach ($fields as $f)
			if (!isset($this->_maps[$f]))
				return FALSE;

		$tables = array();
		$fields = array();
		//$data = array('id' => $id);
		$data = array();
		foreach ($attrs as $k => $a) {
			$table = '@__' . substr($this->_maps[$k], 0, strpos($this->_maps[$k], '.'));
			$fields[$table][] = '@__' . $this->_maps[$k] . '=":' . $k . '"';
			$data[$table][$k] = $a;
			if (!in_array($a, $fields)) {
				$tables[] = $table;
			}
		}

		foreach ($tables as $table) {
			$data[$table]['id'] = $id;
			$query = 'UPDATE ' . $table . ' SET ' . implode(',', $fields[$table]) . ' WHERE ' . $table . '.' . $this->_maps['id'] . '=":id"';
			self::$db->execute($query, $data[$table]);
		}

		return TRUE;
	}

	/**
	 * Delete
	 *
	 * @param mixed $condition, string/array
	 * @return boolean
	 */
	public function del($ids = NULL) {
		$where = '';
		if (!is_array($ids)) {
			$ids = trim($ids);
			$ids = explode(',', $ids);
		}
		if (is_array($ids) && count($ids) > 0)
			$where = $this->_maps['id'] . ' IN ("' . implode('","', $ids) . '")';

		$tables = array();
		foreach ($this->_maps as $m) {
			$table = substr($m, 0, strpos($m, '.'));
			if (!in_array($table, $tables))
				$tables[] = $table;
		}

		foreach ($tables as $t)
			self::$db->execute('DELETE FROM @__' . $t . ' WHERE ' . $where);

		return TRUE;
	}

}
