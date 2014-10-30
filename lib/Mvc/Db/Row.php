<?php
namespace Mvc\Db;

class Row
{
	protected $data;
	
	public function __construct(array $data)
	{
		$this->data = $data;
	}
	
	public function __get($var)
	{
		if(array_key_exists($var, $this->data)){
			return $this->data[$var];
		}
		throw new \Exception("Column $var does not exist in this row ".print_r($this->data, 1));
	}
	
	public function getData()
	{
		return $this->data;
	}
	
	public function toArray()
	{
		return $this->getData();
	}
	
}