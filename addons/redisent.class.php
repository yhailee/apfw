<?php

/**
 * Redisent, a Redis interface for the modest
 * @author Justin Poliey <jdp34@njit.edu>
 * @copyright 2009 Justin Poliey <jdp34@njit.edu>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @package Redisent
 * @Modified by Andrew li on 12-2-28 下午4:56
 */

/**
 * Wraps native Redis errors in friendlier PHP exceptions
 */
class RedisException extends Exception {

}

/**
 * Redisent, a Redis interface for the modest among us
 */
class Redis {

	/**
	 * Socket connection to the Redis server
	 * @var resource
	 * @access private
	 */
	private $__sock;

	/**
	 * Data format separator
	 * @var string
	 * @access private
	 */
	private $_crlf;

	/**
	 * Host of the Redis server
	 * @var string
	 * @access public
	 */
	public $host;

	/**
	 * Port on which the Redis server is running
	 * @var integer
	 * @access public
	 */
	public $port;

	/**
	 * Creates a Redisent connection to the Redis server on host {@link $host} and port {@link $port}.
	 * @param string $host The hostname of the Redis server
	 * @param integer $port The port number of the Redis server
	 *
	 * @modified by Andrew for moving fundation to connect
	 */
	function __construct() {
		$this->_crlf = sprintf('%s%s', chr(13), chr(10));
	}

	function __destruct() {
		fclose($this->__sock);
	}

	function __call($name, $args) {

		/* Build the Redis unified protocol command */
		array_unshift($args, strtoupper($name));
		$result = NULL;
		if (isset($args[2]) && is_array($args[2])) {
			$sourceArgs = $args;
			$result = array();
			foreach ($sourceArgs[2] as $subArg) {
				$args = array(
					$sourceArgs[0],
					$sourceArgs[1],
					$subArg
				);
				$result[$subArg] = $this->_do($args);
			}
		} else {
			$result = $this->_do($args);
		}
		return $result;
	}

	public function connect($host, $port = 6379, $timeout = 1) {
		$this->host = $host;
		$this->port = $port;
		$this->__sock = fsockopen($this->host, $this->port, $errno, $errstr, $timeout);
		if (!$this->__sock) {
			throw new Exception("{$errno} - {$errstr}");
		}
	}

	private function _format($arg) {
		$arg = trim($arg);
		return sprintf('$%d%s%s', strlen($arg), $this->_crlf, $arg);
	}

	private function _do($args) {
		$command = sprintf('*%d%s%s%s', count($args), $this->_crlf, implode(array_map(array($this, '_format'), $args), $this->_crlf), $this->_crlf);

		/* Open a Redis connection and execute the command */
		for ($written = 0; $written < strlen($command); $written += $fwrite) {
			$fwrite = fwrite($this->__sock, substr($command, $written));
			if ($fwrite === FALSE) {
				throw new Exception('Failed to write entire command to stream');
			}
		}

		/* Parse the response based on the reply identifier */
		$reply = trim(fgets($this->__sock, 512));
		switch (substr($reply, 0, 1)) {
			/* Error reply */
			case '-':
				throw new RedisException(substr(trim($reply), 4));
				break;
			/* Inline reply */
			case '+':
				$response = substr(trim($reply), 1);
				break;
			/* Bulk reply */
			case '$':
				$response = null;
				if ($reply == '$-1') {
					break;
				}
				$read = 0;
				$size = substr($reply, 1);
				if ($size > 0) {
					do {
						$block_size = ($size - $read) > 1024 ? 1024 : ($size - $read);
						$response .= fread($this->__sock, $block_size);
						$read += $block_size;
					} while ($read < $size);
				}
				fread($this->__sock, 2); /* discard crlf */
				break;
			/* Multi-bulk reply */
			case '*':
				$count = substr($reply, 1);
				if ($count == '-1') {
					return null;
				}
				$response = array();
				for ($i = 0; $i < $count; $i++) {
					$bulk_head = trim(fgets($this->__sock, 512));
					$size = substr($bulk_head, 1);
					if ($size == '-1') {
						$response[] = null;
					} else {
						$read = 0;
						$block = "";
						do {
							$block_size = ($size - $read) > 1024 ? 1024 : ($size - $read);
							$block .= fread($this->__sock, $block_size);
							$read += $block_size;
						} while ($read < $size);
						fread($this->__sock, 2); /* discard crlf */
						$response[] = $block;
					}
				}
				break;
			/* Integer reply */
			case ':':
				$response = intval(substr(trim($reply), 1));
				break;
			default:
				throw new RedisException("invalid server response: {$reply}");
				break;
		}
		/* Party on */
		return $response;
	}

}
