<?php
/** Comlei Mvc Framework */

namespace Mvc\Db;

use Mvc\Controller;

/** A class that represents a database table */
class Table
{
	/**
	 * The table name
	 * @var string
	 */
	protected $table;
	
	/**
	 * The Controller
	 * @var Mvc\Controller
	 */
	protected $controller;
	
	/**
	 * Class name for the Resultset object
	 * @var string
	 */
	protected $resultsetClass = 'Mvc\Db\Resultset';
	
	/**
	 * Class name for the Row object
	 * @var string
	 */
	protected $rowClass = 'Mvc\Db\Row';
	
	/**
	 * Receives injected Controller and a table name
	 * @param Controller $controller
	 * @param string $table
	 * @throws \Exception
	 */
	public function __construct(Controller $controller, $table = null)
	{
		$this->controller = $controller;
		if ($table) {
			$this->table = $table;
		}
		if (!$this->table) {
			throw new \Exception('Table name not set for table '.get_class($this));
		}
	}
	
	/**
	 * Return controller object
	 * @return \Mvc\Controller
	 */
	public function getController()
	{
		return $this->controller;
	}
	
	/**
	 * Get the application database connection object
	 * @return Mvc\Db\Adapter\AdapterInterface
	 */
	public function getDb()
	{
		return $this->getController()->getApplication()->getDb();
	}
	
	/**
	 * Set the class name for the Row object
	 * @param string $rowClass
	 */
	public function setRowClass($rowClass)
	{
		$this->rowClass = $rowClass;
	}
	
	/**
	 * Get class name for the Row object
	 * @return string
	 */
	public function getRowClass()
	{
		return $this->rowClass;
	}
	
	/**
	 * Get table name
	 * @return string
	 */
	public function getName()
	{
		return $this->table;
	}
	
	/**
	 * Make a SELECT query to database and return a Resultset object
	 * @param string $select The SQL query to execute
	 * @param array $bind (Optional) An array of values to be bound into 
	 * the query. Question mark (?) placeholders must be present in $select
	 * @return Mvc\Db\Resultset
	 */
	public function fetch($select, $bind = []) {
		$result = $this->getDb()->query($select, $bind);
		if ($result) {
			$resultsetClass = $this->resultsetClass;
			$resultset = new $resultsetClass($this);
			while($row = $result->fetchRow()) {
				$resultset->addRow($row);
			}
			return $resultset;
		}
		return $result;
	}
	
	/**
	 * Insert a new row into the database.
	 * @param array $values An array of key / value 
	 * pairs representing column names and values
	 * @return int|NULL The inserted Id or null on failure
	 */
	public function insert(array $values)
	{
		$keys = array_keys($values);
		array_walk($keys, [$this, 'filterColumnName']);
		$placeholders = $this->getPlaceholders($keys);
		$columns = $keys ? '`' . implode('`, `', $keys) . '`' : '';
		$query = "INSERT INTO `$this->table` ($columns) VALUES ($placeholders)";
		$result = $this->getDb()->query($query, $values);
		if ($result) {
			return $this->getDb()->insertedId();
		}
		return null;
	}
	
	/**
	 * Update a record in the database.
	 * @param string|array $key The primary key column name
	 * @param string|array $id The primary key value
	 * @param string|array $values An array of key / value 
	 * pairs representing column names and values
	 * @return int|NULL The number of affected rows or null on failure.
	 */
	public function update($key, $id, $values)
	{
		if (is_string($values)) {
			$placeholders = $values;
		} else {
			foreach ($values as $column => $value) {
				if ($value === null) {
					$set[] = "`$column` = NULL";
					unset($values[$column]);
				} else {
					$set[] = "`$column` = ?";
				}
			}
			$placeholders = implode(', ', $set);
		}
		foreach ((array) $key as $k) {
			$where[] = "`$k` = ?";
		}
		$where = implode(" AND ", $where);
		$query = "UPDATE `$this->table` SET $placeholders WHERE $where";
		$bind = array_values($values);
		foreach ((array) $id as $i) {
			$bind[] = $i;
		}
		$result = $this->getDb()->query($query, $bind);
		if ($result) {
			return $this->getDb()->affectedRows();
		}
		return null;
	}
	
	/**
	 * Delete a record from the database
	 * @param string $key The primary key column name
	 * @param array|int|string $ids The primary key value(s)
	 * @return int|NULL The number of affected rows or null on failure.
	 */
	public function delete($key, $ids)
	{
		if (is_array($key)) {
			$bind = [];
			foreach ($ids as $id) {
				$bind = array_merge($bind, explode(',', $id));
				$wherePart = [];
				foreach ($key as $k) {
					$wherePart[] = "$k = ?";
				}
				$where[] = '('.implode(' AND ', $wherePart).')';
			}
			$where = implode(' OR ', $where);
			$query = "DELETE FROM $this->table WHERE $where";
		} else {
			$placeholders = $this->getPlaceholders($ids);
			$query = "DELETE FROM $this->table WHERE $key IN( $placeholders )";
			$bind = $ids;
		}
		$result = $this->getDb()->query($query, $bind);
		if ($result) {
			return $this->getDb()->affectedRows();
		}
		return null;
	}
	
	/**
	 * Remove back quotes (`) from column name
	 * @param string $column
	 * @return string
	 */
	public function filterColumnName(&$column)
	{
		return str_replace('`', '', $column);
	}
	
	/**
	 * Get a certain number of question mark placeholders 
	 * concatenated into a comma separated string
	 * @param array $columns an array which size should 
	 * match the number of desired placeholders
	 * @return string
	 */
	public function getPlaceholders($columns)
	{
		$placeholders = array_fill(0, count($columns), '?');
		return implode(', ', $placeholders);
	}
	
}