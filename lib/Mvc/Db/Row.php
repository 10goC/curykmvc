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
		if(isset($this->data[$var])){
			return $this->data[$var];
		}
		throw new \Exception("Column $var does not exist in this row");
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