<?php
/** Comlei Mvc Framework */

namespace Mvc\Db;

/** A database resultset. */
class Resultset extends \ArrayObject
{
	/**
	 * The corresponding DB table
	 * @var Mvc\Db\Table
	 */
	protected $table;
	
	/**
	 * The array of rows in the resultset.
	 * @var array
	 */
	protected $rows = [];
	
	/**
	 * Store Table element that generated the query. 
	 * @param Mvc\Db\Table $table
	 * @param array $array the array of rows in the resultset
	 */
	public function __construct(Table $table, $array = [])
	{
		$this->table = $table;
		parent::__construct($array);
	}
	
	/**
	 * Get the corresponding DB table
	 * @return Mvc\Db\Table
	 */
	public function getTable()
	{
		return $this->table;
	}
	
	/**
	 * Convert resultset and rows to array.
	 * @return array
	 */
	public function toArray()
	{
		$dataArray = [];
		foreach ($this->rows as $row) {
			$dataArray[] = $row->getData();
		}
		return $dataArray;
	}
	
	/**
	 * Add a row to the resultset.
	 * @param Mvc\Db\Row $row
	 */
	public function addRow($row)
	{
		$rowClass = $this->table->getRowClass();
		$rowObject = new $rowClass($this, $row);
		$this->rows[] = $rowObject;
		$this->append($rowObject);
	}
	
	/**
	 * Shifts the first value of the array off and returns it, shortening the
	 * array by one element and moving everything down. All numerical array
	 * keys will be modified to start counting from zero while literal keys
	 * won't be touched.
	 *
	 * @return mixed Returns the shifted value, or NULL if the array is empty.
	 */
	public function shift()
	{
		$value = array_shift($this->rows);
		$this->exchangeArray($this->rows);
		return $value;
	}
	
}