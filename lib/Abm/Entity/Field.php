<?php
/** Comlei Mvc Framework */

namespace Abm\Entity;

use Mvc\Application;
use Mvc\Db\Row;
use Mvc\Db\Table;
use Abm\Entity;
use Abm\Entity\Field\Target;

/** Entity field */
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
	 * A text for an empty first option (or false for not including one)
	 * @var string 
	 */
	public $emptyFirstOption = false;
	
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
	
	/**
	 * Upload directory
	 * @var string
	 */
	protected $uploadDir;
	
	/**
	 * Upload URL
	 * @var string
	 */
	protected $uploadUrl;
	
	/**
	 * Order of field components for dates
	 * @var string
	 */
	public $dateFieldsOrder = 'MDY';
	
	/**
	 * Field components for time fields
	 * @var string
	 */
	public $timeFields = 'hm';
	
	/**
	 * Default value
	 * @var string
	 */
	public $defaultValue;
	
	/**
	 * Placeholder HTML attribute
	 * @var string
	 */
	public $placeholder;
	
	/**
	 * Whether the field is required when submitting an admin form
	 * @var boolean
	 */
	public $required = false;
	
	/**
	 * The database table where this field belongs
	 * @var Mvc\Db\Table
	 */
	public $table;
	
	/**
	 * The reference column for fields from different table
	 * @var string
	 */
	public $ref;
	
	/**
	 * JOIN clause mode (INNER|LEFT) in case this is a field from a different table
	 * @var string
	 */
	public $join;
	
	/**
	 * Initialize the object based on provided definition
	 * @param Entity $entity
	 * @param string $fieldId
	 * @param array $field The field definition
	 */
	public function __construct(Entity $entity, $fieldId, $field)
	{
		$this->entity = $entity;
		if(is_numeric($fieldId)){
			if(is_string($field)){
				$this->name = $field;
				$this->title = $field;
				$this->setTable($entity->getTable());
			}
		}else{
			$this->name = $fieldId;
			$this->title = isset($field['title']) ? $field['title']: $fieldId;
			$table = isset($field['table']) ? $field['table'] : $entity->getTable();
			$this->setTable($table);
			if(isset($field['ref'])) $this->ref = $field['ref'];
			if(isset($field['type'])) $this->type = $field['type'];
			if(isset($field['options'])) $this->setOptions($field['options']);
			if(isset($field['source'])) $this->setSource($field['source']);
			if(isset($field['target'])) $this->setTarget($field['target']);
			if(isset($field['required'])) $this->required = $field['required'];
			if(isset($field['placeholder'])) $this->placeholder = $field['placeholder'];
			if(isset($field['default'])) $this->defaultValue = $field['default'];
			if(isset($field['dateFieldsOrder'])) $this->dateFieldsOrder = $field['dateFieldsOrder'];
			if(isset($field['timeFields'])) $this->timeFields = $field['timeFields'];
			if(isset($field['uploadDir'])) $this->setUploadDir($field['uploadDir']);
			if(isset($field['uploadUrl'])){
				$this->setUploadUrl($field['uploadUrl']);
				if(!isset($field['uploadDir'])){
					$this->setUploadDir(PUBLIC_PATH . '/' . trim($field['uploadUrl'], '/'));
				}
			}
			if(isset($field['emptyFirstOption'])) $this->emptyFirstOption = $field['emptyFirstOption'];
		}
	}
	
	/**
	 * Get the related entity
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
	 * Set the field display name
	 * @param string $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
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
	 * Set field table
	 * @param string|object $table
	 * @throws \Exception
	 */
	public function setTable($table)
	{
		if(is_object($table)){
			if($table instanceof Table){
				$this->table = $table;
			}else{
				throw new \Exception('Field table must extend Mvc\Db\Table');
			}
		}else{
			$table = (string) $table;
			try {
				if(class_exists($table)){
					$tableObject = new $table($this->getEntity()->getController());
				}
			} catch (\Exception $e) {
				$tableObject = new Table($this->getEntity()->getController(), $table);
			}
			$this->table = $tableObject;
		}
		
	}
	
	/**
	 * Whether it is a file upload field
	 * @return boolean
	 */
	public function isFile()
	{
		return in_array($this->type, array('file', 'image'));
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
	
	/**
	 * Get upload directory
	 * @return string
	 */
	public function getUploadDir()
	{
		if(!$this->uploadDir){
			$this->uploadDir = PUBLIC_PATH . '/uploads';
		}
		return $this->uploadDir;
	}
	
	/**
	 * Set upload directory
	 * @param string $uploadDir
	 */
	public function setUploadDir($uploadDir)
	{
		$this->uploadDir = $uploadDir;
	}
	
	/**
	 * Get upload URL
	 * @return string
	 */
	public function getUploadUrl()
	{
		if(!$this->uploadUrl){
			$this->uploadUrl = '/uploads';
		}
		$view = $this->getEntity()->getController()->getView();
		return $view->baseUrl($this->uploadUrl);
	}
	
	/**
	 * Set upload URL
	 * @param string $uploadUrl
	 */
	public function setUploadUrl($uploadUrl)
	{
		$this->uploadUrl = $uploadUrl;
	}
	
	/**
	 * Generate output
	 * @param Row $row
	 * @return string
	 */
	public function render(Row $row)
	{
		$value = $row->{$this->getName()};
		switch ($this->getType()) {
			// Select
			case 'dbSelect':
			case 'select':
				$source = $this->getOptions();
				if(isset($source[$value])){
					$value = $source[$value];
				}else{
					// Search within optgroups
					foreach($source as $option){
						if(is_array($option) && isset($option[$value])){
							$value = $option[$value];
						}
					}
				}
				break;
				
			// File
			case 'file':
				$value = pathinfo($value, PATHINFO_BASENAME);
				break;
				
			// Image
			case 'image':
				$value = '<img class="thumb" src="'.$this->getUploadUrl().'/'.$value.'">';
				break;
				
			// Boolean
			case 'boolean':
				$icons = array('times', 'check');
				if(!$this->getOptions()){
					$this->setOptions(array('Inactive', 'Active'));
				}
				$values = $this->getOptions();
				// Translate using library text domain
				$word = $this->getEntity()->__($values[$value]);
				// Translate again using Application text domain
				$word = $this->getEntity()->__($word, Application::TEXTDOMAIN);
				$value = '<span class="'.strtolower($values[$value]).'">
					<span class="fa fa-'.$icons[$value].'" title="'.$word.'"></span>
					<span class="text">'.$word.'</span>
				</span>';
				break;
				
			// (DB) Checkbox
			case 'dbCheckbox':
				$source = $this->getOptions();
				$values = explode(',', $value);
				$value = implode(', ',
					array_intersect_key(
						$source,
						array_flip($values)
					)
				);
				break;
		}
		return $value;
	}
}