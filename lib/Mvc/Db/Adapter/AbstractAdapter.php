<?php
/** Comlei Mvc Framework */

namespace Mvc\Db\Adapter;

use Mvc\Config;

/** Abstract database adapter */
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
	 * (non-PHPdoc)
	 * @see \Mvc\Db\Adapter\AdapterInterface::query()
	 */
	public function query($sql, $bind = array())
	{
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