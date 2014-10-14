<?php
namespace Mvc\Db;

class Resultset extends \ArrayObject
{
	protected $rows = array();
	
	public function toArray()
	{
		$dataArray = array();
		foreach($this->rows as $row){
			$dataArray[] = $row->getData();
		}
		return $dataArray;
	}
	
	public function addRow($row)
	{
		$this->rows[] = $row;
		$this->append($row);
	}
	
}