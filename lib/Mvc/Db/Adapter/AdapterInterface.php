<?php
/** Comlei Mvc Framework */

namespace Mvc\Db\Adapter;

/** An interface for database adapters */
interface AdapterInterface
{
	/**
	 * Setup the database connection
	 */
	public function setDb();
	
	/**
	 * Prepare a SQL statement
	 * @param string $sql
	 */
	public function prepare($sql);
	
	/**
	 * Returns last insert ID
	 */
	public function insertedId();
	
	/**
	 * Returns number of rows affected by last query
	 */
	public function affectedRows();
	
	/**
	 * Prepares and executes an SQL statement with bound data.
	 *
	 * @param  string  $sql  The SQL statement with placeholders.
	 * @param  array   $bind An array of data to bind to the placeholders.
	 * @return Mvc\Db\StatementInterface
	 */
	public function query($sql, $bind = array());
	
}