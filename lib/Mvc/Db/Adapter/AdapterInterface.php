<?php
namespace Mvc\Db\Adapter;

interface AdapterInterface
{
	/**
	 * Setup the database connection
	 */
	public function setDb();
	
	/**
	 * Prepares a SQL statement
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
	
}