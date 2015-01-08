<?php
/** Comlei Mvc Framework */

namespace Mvc\Db;

/** Represents a database record */
class Row
{
	/**
	 * The resultset to which this row belongs
	 * @var Mvc\Db\Resultset
	 */
	protected $resultset;
	
	/**
	 * The row data as an associative array
	 * @var array
	 */
	protected $data;
	
	/**
	 * Initialize object with provided data and parent Resultset object
	 * @param Resultset $resultset
	 * @param array $data
	 */
	public function __construct(Resultset $resultset, array $data)
	{
		$this->resultset = $resultset;
		$this->data = $data;
	}
	
	/**
	 * Return corresponding column value when accessing a property
	 * @param string $var
	 * @throws \Exception
	 * @return string
	 */
	public function __get($var)
	{
		if(array_key_exists($var, $this->data)){
			return $this->data[$var];
		}
		throw new \Exception("Column $var does not exist in this row");
	}
	
	/**
	 * Return the resultset to which this row belongs
	 * @return \Mvc\Db\Resultset
	 */
	public function getResultSet()
	{
		return $this->resultset;
	}
	
	/**
	 * Return row data array
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}
	
	/**
	 * Convert to array
	 * @return array
	 */
	public function toArray()
	{
		return $this->getData();
	}
	
}