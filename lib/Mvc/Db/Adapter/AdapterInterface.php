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
	
}