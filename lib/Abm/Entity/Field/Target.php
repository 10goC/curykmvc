<?php
namespace Abm\Entity\Field;

use Abm\Entity;
use Abm\Entity\Field;

class Target extends Entity
{
	/**
	 * The related field
	 * @var Abm\Entity\Field
	 */
	protected $field;
	
	/**
	 * The primary key of the referenced entity
	 * @var string
	 */
	protected $foreignKey;
	
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
	
	public function getForeignKey()
	{
		return $this->foreignKey;
	}
	
	public function insertRelated($data, $foreignKeyValue)
	{
		foreach($data as $value){
			$values[$this->foreignKey] = $foreignKeyValue;
			$values[$this->firstField()] = $value;
			$this->insert($values);
		}
	}
	
	public function updateRelated($data, $foreignKeyValue)
	{
		$prevData = $this->fetchArray("$this->foreignKey = $foreignKeyValue");
		$insert = $data ? array_diff($data, $prevData) : array();
		$delete = $data ? array_diff($prevData, $data) : $prevData;
		$affectedRows = 0;
		if($delete){
			$affectedRows += $this->delete(array_flip($delete));
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