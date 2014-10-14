<?php
namespace Mvc\Db;

use Mvc\Controller;

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
	protected $db;
	protected $resultsetClass = 'Mvc\Db\Resultset';
	protected $rowClass = 'Mvc\Db\Row';
	
	public function __construct(Controller $controller)
	{
		$this->controller = $controller;
		if(!$this->table){
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
	
	public function getDb()
	{
		return $this->getController()->getApplication()->getDb();
	}
	
	public function fetch($select, $bind = array()){
		$result = $this->getDb()->query($select, $bind);
		$resultsetClass = $this->resultsetClass;
		$resultset = new $resultsetClass();
		while($row = $result->fetchRow()){
			$rowClass = $this->rowClass;
			$resultset->addRow(new $rowClass($row));
		}
		return $resultset;
	}
	
}