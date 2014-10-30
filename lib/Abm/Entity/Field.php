<?php
namespace Abm\Entity;

use Abm\Entity\Field\Target;

use Abm\Entity;

class Field
{
	/**
	 * The related entity
	 * @var Abm\Entity
	 */
	protected $entity;
	
	/**
	 * Represents the corresponding database column name
	 * @var string
	 */
	protected $name;
	
	/**
	 * The display name
	 * @var string
	 */
	protected $title;
	
	/**
	 * The field options (for selectable fields)
	 * @var array
	 */
	protected $options = array();
	
	/**
	 * The data source
	 * @var Abm\Entity
	 */
	protected $source;
	
	/**
	 * An external entity that stores the field data
	 * @var Abm\Entity\Field\Target
	 */
	protected $target;
	
	/**
	 * The field type
	 * @var string
	 */
	protected $type = 'text';
	
	public function __construct(Entity $entity, $fieldId, $field)
	{
		$this->entity = $entity;
		if(is_numeric($fieldId)){
			if(is_string($field)){
				$this->name = $field;
				$this->title = $field;
			}
		}else{
			$this->name = $fieldId;
			$this->title = isset($field['title']) ? $field['title']: $fieldId;
			if(isset($field['type'])) $this->type = $field['type'];
			if(isset($field['options'])) $this->setOptions($field['options']);
			if(isset($field['source'])) $this->setSource($field['source']);
			if(isset($field['target'])) $this->setTarget($field['target']);
		}
	}
	
	/**
	 * get the related entity
	 * @return Abm\Entity
	 */
	public function getEntity()
	{
		return $this->entity;
	}
	
	/**
	 * Get the field display name
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}
	
	/**
	 * Get the corresponding database column name
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * Get the field type
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}
	
	/**
	 * Set the field options
	 * @param array $options
	 */
	public function setOptions($options)
	{
		$this->options = $options;
	}
	
	/**
	 * Set the field data source
	 * @param Abm\Entity|string $entity
	 */
	public function setSource($entity)
	{
		if(is_string($entity)){
			$this->source = new $entity($this->entity->getController());
		}else if($entity instanceof Entity){
			$this->source = $entity;
		}
	}
	
	/**
	 * Get the field data source
	 * @return Abm\Entity
	 */
	public function getSource()
	{
		return $this->source;
	}
	
	/**
	 * Set the destination model for storing the field data
	 * @param Abm\Entity\Field\Target|string $target
	 */
	public function setTarget($target)
	{
		if(is_string($target)){
			$this->target = new $target($this);
		}else if($target instanceof Target){
			$this->target = $target;
		}
	}
	
	/**
	 * Get the destination model that stores the field data
	 * @return \Abm\Entity\Field\Target
	 */
	public function getTarget()
	{
		return $this->target;
	}
	
	/**
	 * Get the field options
	 * @return array
	 */
	public function getOptions()
	{
		if(!$this->options && in_array($this->type, array('dbSelect', 'dbCheckbox')) && $this->source){
			$this->options = $this->source->fetchArray();
		}
		return $this->options;
	}
}