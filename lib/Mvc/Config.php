<?php
namespace Mvc;

class Config
{
	/**
	 * Configuration value(s)
	 * @var array|string
	 */
	protected $config;
	
	/**
	 * The name of the current node (empty for root node)
	 * @var string
	 */
	protected $name;
	
	/**
	 * Initialize object by setting configuration value(s) and node name
	 * @param array|string $config
	 * @param string $name
	 */
	public function __construct($config, $name = '')
	{
		$this->config = $config;
		$this->name = $name;
	}
	
	/**
	 * Return a value from config array
	 * @param unknown_type $var
	 * @throws \Exception
	 * @return Mvc\Config
	 */
	public function __get($var)
	{
		if(isset($this->config[$var])){
			$name = $this->name ? "$this->name/$var" : $var;
			return new Config($this->config[$var], $name);
		}
		throw new \Exception("Config key '$var' does not exist");
	}
	
	/**
	 * Get value. Throws exception if value is not a string.
	 * @throws Exception
	 * @return string
	 */
	public function __toString()
	{
		if(!is_array($this->config)){
			return (string) $this->config;
		}
		throw new \Exception("$this->name is not a string");
	}
	
	/**
	 * Checks for key existence in config array
	 * @param string $var
	 */
	public function __isset($var)
	{
		return isset($this->config[$var]);
	}
	
	public function toArray()
	{
		if(is_array($this->config)){
			return $this->config;
		}else{
			throw new \Exception("$this->name is not an array");
		}
	}
	
}