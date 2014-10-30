<?php
namespace Mvc\Db\Adapter;

use Mvc\Config;
use Mvc\Db\Select;

abstract class AbstractAdapter implements AdapterInterface
{
	/**
	 * Configuration array
	 * @var Mvc\Config
	 */
	protected $config;
	
	/**
	 * The database connection object
	 * @var object
	 */
	protected $db;
	
	/**
	 * Prepares and executes an SQL statement with bound data.
	 *
	 * @param  string  $sql  The SQL statement with placeholders.
	 * @param  array   $bind An array of data to bind to the placeholders.
	 * @return Mvc\Db\StatementInterface
	 */
	public function query($sql, $bind = array())
	{
		if($sql instanceof Select){
			$sql = $sql->assemble();
		}
		
		// prepare and execute the statement with profiling
		if($stmt = $this->prepare($sql)){
			$stmt->execute((array) $bind);
		}
	
		// return the results embedded in the prepared statement object
		return $stmt;
	}
	
	/**
	 * Get configuration array
	 * @return \Mvc\Config
	 */
	public function getConfig()
	{
		return $this->config;
	}
	
	/**
	 * Retrieve database connection object
	 * @return object
	 */
	public function getDb(){
		if(!$this->db){
			$this->setDb();
		}
		return $this->db;
	}
	
	/**
	 * Assign injected config array object
	 * @param Config $config
	 */
	public function __construct(Config $config)
	{
		$this->config = $config;
	}
}