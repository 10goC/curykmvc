<?php
namespace Abm;

use Abm\Entity\Field;
use Mvc\Application;
use Mvc\Db\Table;

class Entity
{
	/**
	 * The controller
	 * @var \Abm\Controller
	 */
	protected $controller;
	protected $table;
	protected $tableName;
	protected $tableClass = 'Mvc\Db\Table';
	protected $name;
	protected $cleanName;
	protected $plural;
	protected $fields;
	protected $primaryKey = 'id';
	protected $messages = array();
	protected $actions = array();
	protected $callbacks = array();
	private $fieldObjects;
	
	const NOTICE  = 'notice';
	const ERROR   = 'error';
	const SUCCESS = 'success';
	
	const FLASH_MESSAGES = 'entity_flash_messages';
	
	public function __construct(Controller $controller)
	{
		$this->controller = $controller;
		$session = &$this->controller->getSession(self::FLASH_MESSAGES);
		if(isset($session[$this->getCleanName()])){
			foreach($session[$this->getCleanName()] as $type => $messages){
				foreach($messages as $message){
					$this->addMessage($message, $type);
				}
			}
			unset($session[$this->getCleanName()]);
		}
	}
	
	/**
	 * Get controller
	 * @return \Abm\Controller
	 */
	public function getController()
	{
		return $this->controller;
	}
	
	public function getTableName()
	{
		if($this->tableName === null){
			$this->tableName = false;
			$this->addMessage(sprintf($this->__('Table name not set for entity %s'), $this->getName()), self::ERROR);
		}
		return $this->tableName;
	}
	
	public function getTable()
	{
		if($this->table === null){
			if($tableName = $this->getTableName()){
				$tableClass = $this->tableClass;
				$this->table = new $tableClass($this->controller, $tableName);
			}else{
				$this->table = false;
			}
		}
		if($this->table && !$this->table instanceof Table){
			throw new \Exception(sprintf('Incorrect table class specified for entity %s', $this->name));
			$this->table = false;
		}
		return $this->table;
	}
	
	public function getName()
	{
		if(!$this->name){
			$reflection = new \ReflectionClass($this);
			$this->name = $reflection->getShortName();
			$this->addMessage(sprintf($this->__('Entity name not set for entity %s'), $this->name), self::NOTICE);
		}
		return $this->name;
	}
	
	public function getCleanName()
	{
		if(!$this->cleanName){
			$this->cleanName = strtolower(preg_replace('/[^a-z0-9]/i', '', $this->getName()));
		}
		return $this->cleanName;
	}
	
	public function getPlural()
	{
		return $this->plural ?
			$this->plural :
			$this->getName() . 's'
		;
	}
	
	public function getFields()
	{
		if($this->fieldObjects === null){
			if($this->fields === null){
				$this->fields = array();
				$this->fieldObjects = array();
				$this->addMessage(sprintf($this->__('Fields not set for entity %s'), $this->getName()), self::ERROR);
			}
			foreach($this->fields as $fieldId => $field){
				$fieldObject = new Field($this, $fieldId, $field);
				$this->fieldObjects[] = $fieldObject;
			}
		}
		return $this->fieldObjects;
	}
	
	public function firstField()
	{
		$fields = $this->getFields();
		return $fields[0]->getName();
	}
	
	public function getPrimaryKey()
	{
		return $this->primaryKey;
	}
	
	public function setActions(array $actions)
	{
		$this->actions = $actions;
	}
	
	public function getActions()
	{
		return $this->actions;
	}
	
	public function doActions(array $actions = null)
	{
		$actions = ($actions) ?: $this->getActions();
		$entityName = $this->getCleanName();
		$performed = array();
		foreach($actions as $action){
			switch ($action) {
				// Add
				case 'add':
					$add = isset($_POST["add_$entityName"]) ? $_POST["add_$entityName"] : null;
					if($add){
						foreach($add as $i => $values){
							$insertedId = $this->insert($this->getInsertValues($values));
							if($insertedId){
								$performed['added'][] = $insertedId;
								if($this->callbacks){
									foreach($this->callbacks as $callback){
										$target = $callback['field']->getTarget();
										$target->insertRelated($callback['values'], $insertedId);
									}
								}
							}
						}
					}
					break;
						
				// Edit
				case 'edit':
					$edit = isset($_POST["edit_$entityName"]) ? $_POST["edit_$entityName"] : null;
					if($edit){
						foreach($edit as $id => $values){
							if(is_numeric($id)){
								$success = $this->update($id, $this->getUpdateValues($values));
								if($this->callbacks){
									foreach($this->callbacks as $callback){
										$target = $callback['field']->getTarget();
										$success += $target->updateRelated($callback['values'], $id);
									}
								}
								if($success){
									$performed['edited'][] = $id;
								}
							}
						}
					}
					break;
						
				// Delete
				case 'delete':
					$delete = isset($_POST["delete_$entityName"]) ? $_POST["delete_$entityName"] : null;
					if($delete){
						foreach($delete as $id => $value){
							if(is_numeric($id) && $value == 1){
								$deleteIds[] = $id;
							}
						}
								
						if(!empty($deleteIds)){
							$deleted = $this->delete($deleteIds);
							if($deleted){
								$performed['deleted'] = $delete;
							}
						}
					}
					break;
			}
		}
		if($performed){
			$this->addPerformedActionsFlashMessages($performed);
		}
		return count($performed);
	}
	
	public function getMessages()
	{
		return $this->messages;
	}
	
	public function addMessage($message, $type = self::NOTICE)
	{
		if(!isset($this->messages[$type])) $this->messages[$type] = array();
		array_push($this->messages[$type], $message);
	}
	
	public function addPerformedActionsFlashMessages($performed)
	{
		foreach($performed as $action => $items){
			$message = count($items) == 1 ?
				$this->__(sprintf($this->__("%s $action successfully"), $this->getName()), Application::TEXTDOMAIN) :
				sprintf($this->__('%d'.sprintf($this->__(" %s $action successfully"), $this->getPlural()), Application::TEXTDOMAIN), count($items))
			;
			$this->addFlashMessage(sprintf($this->__($message), count($items)), self::SUCCESS);
		}
	}
	
	public function addFlashMessage($message, $type = self::NOTICE)
	{
		$session = &$this->getController()->getSession(self::FLASH_MESSAGES);
		$session[$this->getCleanName()][$type][] = $message;
	}
	
	public function __($str, $textDomain = View::TEXTDOMAIN)
	{
		return $this->getController()->getTranslator()->translate($str, $textDomain);
	}
	
	public function fetch($where = 1)
	{
		$table = $this->getTable();
		if(!$table) return array();
		$from = $this->tableName;
		$columns[] = "`$this->tableName`.`{$this->getPrimaryKey()}`";
		$group = "";
		foreach ($this->getFields() as $field){
			if($field->getType() == 'dbCheckbox'){
				$columns[] = "GROUP_CONCAT({$field->getTarget()->firstField()} SEPARATOR ',') AS `{$field->getName()}`";
				$targetTable = $field->getTarget()->getTableName();
				$foreignKey = $field->getTarget()->getForeignKey();
				$from .= " LEFT JOIN `$targetTable` ON `$this->tableName`.`{$this->getPrimaryKey()}` = `$targetTable`.`$foreignKey`";
				$group = "GROUP BY `$this->tableName`.`{$this->getPrimaryKey()}`";
			}else{
				$columns[] = "`$this->tableName`.`{$field->getName()}`";
			}
		}
		$columns = implode(', ', $columns);
		$result = $table->fetch("SELECT $columns FROM $from WHERE $where $group");
		return $result;
	}
	
	public function fetchIds($ids)
	{
		return $this->fetch("`$this->tableName`.`$this->primaryKey` IN( ".implode(', ', (array) $ids)." )");
	}
	
	public function fetchArray($where = 1)
	{
		$controller = $this->getController();
		$cache = $controller->getCache($this->getName());
		if($cache){
			$out = $cache;
		}else{
			$out = array();
			$result = $this->fetch($where);
			foreach($result as $row){
				$out[$row->{$this->primaryKey}] = $row->{$this->firstField()};
			}
			$controller->addToCache($this->getName(), $out);
		}
		return $out;
	}
	
	public function insert(array $values)
	{
		$table = $this->getTable();
		if(!$table) return null;
		$result = $table->insert($values);
		return $result;
	}
	
	public function update($id, array $values)
	{
		$table = $this->getTable();
		if(!$table) return null;
		$result = $table->update($this->getPrimaryKey(), $id, $values);
		return $result;
	}
	
	public function delete(array $values, $key = null)
	{
		$table = $this->getTable();
		if(!$table) return null;
		foreach($this->getFields() as $field){
			if($field->getType() == 'dbCheckbox'){
				$target = $field->getTarget();
				$target->delete($values, $target->getForeignKey());
			}
		}
		if(!$key){
			$key = $this->getPrimaryKey();
		}
		$result = $table->delete($key, $values);
		return $result;
	}
	
	public function getInsertValues($data)
	{
		return $this->getValues('add', $data);
	}
	
	public function getUpdateValues($data)
	{
		return $this->getValues('edit', $data);
	}
	
	public function getValues($action, $data)
	{
		foreach($this->getFields() as $field){
			if($field->getType() == 'dbCheckbox'){
				$callbackValues = isset($data[$field->getName()]) ? $data[$field->getName()] : null;
				$this->callbacks[] = array('field' => $field, 'values' => $callbackValues);
			}else{
				$values[$field->getName()] = isset($data[$field->getName()]) ? $data[$field->getName()] : null;
			}
		}
		return $values;
	}
}