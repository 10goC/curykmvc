<?php
/** Comlei Mvc Framework */

namespace Abm\Entity\Field;

use Abm\Entity;
use Abm\Entity\Field;

/** An entity that connects two database tables */
class Target extends Entity
{
	/**
	 * The field that this entity represents
	 * @var Abm\Entity\Field
	 */
	protected $field;
	
	/**
	 * The primary key of the referenced entity
	 * @var string
	 */
	protected $foreignKey;
	
	/**
	 * Initialize object
	 * @param Field $field The field that this entity represents
	 */
	public function __construct(Field $field)
	{
		$this->field = $field;
		
		// If no foreign key defined use primary key of related entity
		if(!$this->foreignKey){
			$this->foreignKey = $field->getEntity()->getPrimaryKey();
		}
		
		// If no fields defined use source primary key
		if(!$this->fields){
			$this->fields = array($this->field->getSource()->getPrimaryKey());
		}
		parent::__construct($field->getEntity()->getController());
	}
	
	/**
	 * Get foreign key name
	 * @return string
	 */
	public function getForeignKey()
	{
		return $this->foreignKey;
	}
	
	/**
	 * Insert data into database
	 * @param array $data
	 * @param string $foreignKeyValue The Entity ID
	 */
	public function insertRelated($data, $foreignKeyValue)
	{
		if(is_array($data)){
			foreach($data as $value){
				$values[$this->foreignKey] = $foreignKeyValue;
				$values[$this->firstField()] = $value;
				$this->insert($values);
			}
		}
	}
	
	/**
	 * Update database records (insert new or remove deleted)
	 * @param array $data
	 * @param string $foreignKeyValue The Entity ID
	 * @return int Affected rows
	 */
	public function updateRelated($data, $foreignKeyValue)
	{
		$prevData = $this->fetchArray("$this->foreignKey = ?", $foreignKeyValue);
		$insert = $data ? array_diff($data, $prevData) : array();
		$delete = $data ? array_diff($prevData, $data) : $prevData;
		$affectedRows = 0;
		if($delete){
			$affectedRows += $this->delete(array_keys($delete));
		}
		foreach($insert as $value){
			$values[$this->foreignKey] = $foreignKeyValue;
			$values[$this->firstField()] = $value;
			if($this->insert($values)){
				$affectedRows++;
			}
		}
		return $affectedRows;
	}
}