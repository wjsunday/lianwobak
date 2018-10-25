<?php

class Predis {
	public $redis = "";
	
	
	private static $_instance = null;
	private $call_error = 0;
	
	public static function getInstance() {
		if(empty(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	private function __construct() {
		$this->redis = new \Redis();
		$host = C('REDIS_HOST') ? C('REDIS_HOST') : '127.0.0.1';
		$port = C('REDIS_PORT') ? C('REDIS_PORT') : 6379;
		$timeOut = C('DATA_CACHE_TIMEOUT') ? C('DATA_CACHE_TIMEOUT') : false;
		$result = $this->redis->connect($host, $port, $timeOut);
	
		if($result === false) {
			throw new \Exception('redis connect error');
		}
	
	}
	
	public function set($key, $value, $time = 0) {
		if(!$key) {
			return '';
		}
		
		if(is_array($value)) {
			$value = json_encode($value);
		}
		if(!$time) {
			return $this->redis->set($key, $value);
		}
		
		return $this->redis->setex($key, $time, $value);
	}
	
	public function get($key) {
		if(!$key) {
			return '';
		}
		
		return $this->redis->get($key);
	}
	
	public function sAdd($key, $value) {
		return $this->redis->sAdd($key, $value);
	}
	
	public function sRem($key, $value) {
		return $this->redis->sRem($key, $value);
	}
	
	public function sMembers($key) {
		return $this->redis->sMembers($key);
	}
	
	/* public function __call($name, $arguments) {
		//app\common\lib\redis\Predis::getInstance()->sAdd('test', 'singwa');
		//echo $name.PHP_EOL;
		//print_r($arguments);
		return $this->redis->$name($arguments[0], $arguments[1]);
	} */
	
	public function __call($name, $arguments) {
		if (!$this->redis) {
			return false;
		}
		
		try {
			if ('scan' == $name) {
				$data = $this->redis->scan($arguments[0]);
			} else {
				$data =  call_user_func_array(array($this->redis, $name), $arguments);
			}
		} catch (Exception $e) {
			if ($this->call_error < 2) {
				$this->call_error++;
				return call_user_func_array(array($this->_redis, $name), $arguments);
			} else {
				$this->call_error = 0;
			}
			$data = false;
		}
		$this->call_error = 0;
		
		return $data;
	}
}