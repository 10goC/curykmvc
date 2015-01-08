<?php
/** Comlei Mvc Framework */

namespace Abm;

use Mvc\Application;
use Mvc\Db\Table;
use Mvc\Paginator;

/**
 * The Entity base class.
 * Represents a model to be handled with the Abm module for CRUD standard operations and listings.
 * 
 */
class Entity
{
	/**
	 * The controller
	 * @var \Abm\Controller
	 */
	protected $controller;
	
	/**
	 * The entity Table object
	 * @var Mvc\Db\Table
	 */
	protected $table;
	
	/**
	 * The database table name
	 * @var string
	 */
	protected $tableName;
	
	/**
	 * The classname for the Table object
	 * @var string
	 */
	protected $tableClass = 'Mvc\Db\Table';
	
	/**
	 * The classname for the entity row objects
	 * @var string
	 */
	protected $rowClass = 'Mvc\Db\Row';
	
	/**
	 * The default classname for Field objects 
	 * @var string
	 */
	protected $defaultFieldClass = 'Abm\Entity\Field';
	
	/**
	 * The entity name
	 * @var string
	 */
	protected $name;
	
	/**
	 * A clean version of the entity name.
	 * This will default to a lower case conversion with non-alphanumeric characters removed.
	 * @var string
	 */
	protected $cleanName;
	
	/**
	 * The plural form of the entity name.
	 * This will default to the name with an 's' appended.
	 * @var string
	 */
	protected $plural;
	
	/**
	 * The entity fields definitions array.
	 * @var array
	 */
	protected $fields;
	
	/**
	 * The entity primary key in the database table.
	 * @var string
	 */
	protected $primaryKey = 'id';
	
	/**
	 * The array of messages associated with the entity.
	 * @var array
	 */
	protected $messages = array();
	
	/**
	 * The actions to be performed.
	 * @var array
	 */
	protected $actions = array();
	
	/**
	 * The array of fields and values to be updated after the current action pertaining to foreign tables.
	 * @var array
	 */
	protected $callbacks = array();
	
	/**
	 * The array of performed actions.
	 * @var array
	 */
	protected $performed = array();
	
	/**
	 * Whether the entity rows should be ordered by a certain column.
	 * @var boolean|array
	 */
	protected $ordered = false;
	
	/**
	 * Whether the entity rows should be fetched filtered by a certain column.
	 * @var boolean|array
	 */
	protected $categorized = false;
	
	/**
	 * Flag for interrupting the current operation in case of error.
	 * @var boolean
	 */
	protected $abort = false;
	
	/**
	 * The paginator object.
	 * @var Mvc\Paginator
	 */
	protected $paginator;
	
	/**
	 * The array of Field objects
	 * @var array
	 */
	private $fieldObjects;
	
	const NOTICE  = 'notice';
	const ERROR   = 'error';
	const SUCCESS = 'success';
	
	const ORDER_FIRST = 'first';
	const ORDER_LAST = 'last';
	
	const FLASH_MESSAGES = 'entity_flash_messages';
	
	/**
	 * Store controller and check for pending messages from previous action.
	 * @param Abm\Controller $controller
	 */
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
	 * Get controller.
	 * @return Abm\Controller
	 */
	public function getController()
	{
		return $this->controller;
	}

	/**
	 * Get the database table name for the entity.
	 * @return string
	 */
	public function getTableName()
	{
		if($this->tableName === null){
			$this->tableName = false;
			$this->addMessage(sprintf($this->__('Table name not set for entity %s'), $this->getName()), self::ERROR);
		}
		return $this->tableName;
	}
	
	/**
	 * Get database Table object.
	 * @throws \Exception
	 * @return Mvc\Db\Table
	 */
	public function getTable()
	{
		if($this->table === null){
			if($tableName = $this->getTableName()){
				$tableClass = $this->tableClass;
				$this->table = new $tableClass($this->controller, $tableName);
				$this->table->setRowClass($this->rowClass);
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
	
	/**
	 * Get entity name. Guess from class name if not defined.
	 * @return string
	 */
	public function getName()
	{
		if(!$this->name){
			$reflection = new \ReflectionClass($this);
			$this->name = $reflection->getShortName();
			$this->addMessage(sprintf($this->__('Entity name not set for entity %s'), $this->name), self::NOTICE);
		}
		return $this->name;
	}
	
	/**
	 * Get clean version of name.
	 * Remove non-alphanumeric characters and convert to lowercase.
	 * @return string
	 */
	public function getCleanName()
	{
		if(!$this->cleanName){
			$this->cleanName = strtolower(preg_replace('/[^a-z0-9]/i', '', $this->getName()));
		}
		return $this->cleanName;
	}
	
	/**
	 * Get plural word for entity name.
	 * @return string
	 */
	public function getPlural()
	{
		return $this->plural ?
			$this->plural :
			$this->getName() . 's'
		;
	}
	
	/**
	 * Get field object by name.
	 * @param string $fieldName
	 * @throws \Exception
	 * @return Abm\Entity\Field
	 */
	public function getField($fieldName)
	{
		$fieldObjects = $this->getFields();
		if(isset($fieldObjects[$fieldName])){
			return $fieldObjects[$fieldName];
		}else{
			throw new \Exception(sprintf($this->__("Field $fieldName does not exist in entity %s"), $this->getName()));
		}
	}
	
	/**
	 * Get Field objects. Returns an array of Abm\Entity\Field objects.
	 * @return array
	 */
	public function getFields()
	{
		if($this->fieldObjects === null){
			$this->fieldObjects = array();
			if($this->fields === null){
				$this->fields = array();
				$this->addMessage(sprintf($this->__('Fields not set for entity %s'), $this->getName()), self::ERROR);
			}
			$this->addFields($this->fields);
		}
		return $this->fieldObjects;
	}
	
	/**
	 * Get the name of the first field.
	 * @return string
	 */
	public function firstField()
	{
		return reset($this->getFields())->getName();
	}
	
	/**
	 * Add fields to the entity.
	 * @param array $fields
	 */
	public function addFields(array $fields)
	{
		// Initialize fieldObject array in case getFields has not been called yet
		$this->getFields();
		
		// Add new fields
		foreach($fields as $fieldId => $field){
			$fieldClass = is_array($field) && isset($field['class']) ? $field['class'] : $this->defaultFieldClass;
			$fieldObject = new $fieldClass($this, $fieldId, $field);
			$this->fieldObjects[$fieldObject->getName()] = $fieldObject;
		}
	}
	
	/**
	 * Remove a field by name.
	 * @param string $field
	 */
	public function removeField($field)
	{
		if(isset($this->fields[$field])){
			unset($this->fields[$field]);
		}
		if(isset($this->fieldObjects[$field])){
			unset($this->fieldObjects[$field]);
		}
	}
	
	/**
	 * Get primary key column name.
	 * @return string
	 */
	public function getPrimaryKey()
	{
		return $this->primaryKey;
	}
	
	/**
	 * Set actions to be performed.
	 * @param array $actions
	 */
	public function setActions(array $actions)
	{
		$this->actions = $actions;
	}
	
	/**
	 * Get actions to be performed.
	 * @return array
	 */
	public function getActions()
	{
		return $this->actions;
	}
	
	/**
	 * Add an action and a value to the list of performed actions.
	 * @param string $action the type of action performed
	 * @param int $value normally this represents the primary key value
	 */
	public function addPerformed($action, $value)
	{
		if(isset($this->performed[$action])){
			array_push($this->performed[$action], $value);
		}else{
			$this->performed[$action] = array($value);
		}
	}
	
	/**
	 * Get list of performed actions.
	 * @param string $action the type of action desired, or NULL to retrieve the full array
	 * @return array|null
	 */
	public function getPerformed($action = null)
	{
		if($action){
			return isset($this->performed[$action]) ? $this->performed[$action] : null;
		}
		return $this->performed;
	}
	
	/**
	 * Perform all pending actions.
	 * @param array $actions (Optional) Setup a specific list of actions
	 * @return int The number of types of actions performed. 
	 * Will return 0 if no rows were affected in the database.
	 */
	public function doActions(array $actions = null)
	{
		$actions = ($actions) ?: $this->getActions();
		$entityName = $this->getCleanName();
		foreach($actions as $action){
			switch ($action) {
				// Add
				case 'add':
					$add = $this->getPostValues('add');
					if($add){
						foreach($add as $i => $values){
							$values = $this->getInsertValues($values);
							if($this->abort){
								$this->abort = false;	
							}else{
								$insertedId = $this->insert($values);
								if($insertedId){
									$this->addPerformed('added', $insertedId);
								}
							}
						}
					}
					break;
						
				// Edit
				case 'edit':
					$edit = $this->getPostValues('edit');
					if($edit){
						foreach($edit as $id => $values){
							$values = $this->getUpdateValues($values);
							if($this->abort){
								$this->abort = false;
							}else{
								$success = $this->update($id, $values);
								if($success){
									$this->addPerformed('edited', $id);
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
								$this->addPerformed('deleted', $delete);
							}
						}
					}
					break;
				case 'order':
					$moveUp = isset($_GET["moveup_$entityName"]) ? $_GET["moveup_$entityName"] : null;
					if($moveUp){
						if($this->isOrdered()){
							$ordField = $this->ordered['field'];
							$list = $this->fetchIds(array($moveUp));
							if(count($list)){
								$item = current($list);
								$currentOrder = $item->$ordField;
								$newOrder = $currentOrder -1;
								if($newOrder > 0){
									$updated = $this->update($moveUp, array($ordField => $newOrder));
									if($updated){
										$this->addPerformed('moved up', $moveUp);
										$sql = "UPDATE `{$this->getTableName()}`
										SET `$ordField` = $currentOrder
										WHERE `$ordField` = $newOrder
										AND `{$this->getPrimaryKey()}` != ?";
										$bind = array($moveUp);
										if($this->isCategorized()){
											$catField = $this->categorized['field'];
											$sql .= " AND `$catField` = ?";
											$bind[] = $item->$catField;
										}
										$this->getTable()->getDb()->query($sql, $bind);
									}
								}
							}
						}
					}
					break;
			}
		}
		if($this->performed){
			$this->addPerformedActionsFlashMessages();
		}
		return count($this->performed);
	}
	
	/**
	 * Get messages associated to the entity.
	 * @return array
	 */
	public function getMessages()
	{
		return $this->messages;
	}
	
	/**
	 * Empty the array of associated messages.
	 */
	public function flushMessages()
	{
		$this->messages = array();
	}
	
	/**
	 * Add a message to the entity.
	 * @param string $message
	 * @param string $type Can be anything. The most common types are defined as class constants: NOTICE|ERROR|SUCCESS
	 */
	public function addMessage($message, $type = self::NOTICE)
	{
		if(!isset($this->messages[$type])) $this->messages[$type] = array();
		array_push($this->messages[$type], $message);
	}
	
	/**
	 * Add messages to be displayed on next action, according to performed actions.
	 */
	public function addPerformedActionsFlashMessages()
	{
		foreach($this->performed as $action => $items){
			$message = count($items) == 1 ?
				$this->__(sprintf($this->__("%s $action successfully"), $this->getName()), Application::TEXTDOMAIN) :
				sprintf($this->__('%d'.sprintf($this->__(" %s $action successfully"), $this->getPlural()), Application::TEXTDOMAIN), count($items))
			;
			$this->addFlashMessage(sprintf($this->__($message), count($items)), self::SUCCESS);
		}
	}
	
	/**
	 * Add a message to be displayed on next action.
	 * @param string $message
	 * @param string $type Can be anything. The most common types are defined as class constants: NOTICE|ERROR|SUCCESS
	 */
	public function addFlashMessage($message, $type = self::NOTICE)
	{
		$session = &$this->getController()->getSession(self::FLASH_MESSAGES);
		$session[$this->getCleanName()][$type][] = $message;
	}
	
	/**
	 * Translate a text.
	 * @param string $str
	 * @param string $textDomain Defaults to Abm text domain
	 * @return string
	 */
	public function __($str, $textDomain = View::TEXTDOMAIN)
	{
		return $this->getController()->getTranslator()->translate($str, $textDomain);
	}
	
	/**
	 * Tells whether the entity rows should be fetched filtered by a certain column.
	 * @return boolean
	 */
	public function isCategorized()
	{
		if($this->categorized !== false){
			if(isset($this->categorized['field'])){
				return true;
			}else{
				$this->addMessage(sprintf($this->__('Missing \'field\' key in \'categorized\' property for entity %s'), $this->name), self::NOTICE);
			}
		}
		return false;
	}
	
	/**
	 * Tells whether the entity rows should be ordered by a certain column.
	 * @return boolean
	 */
	public function isOrdered()
	{
		if($this->ordered !== false){
			if(isset($this->ordered['field'])){
				return true;
			}else{
				$this->addMessage(sprintf($this->__('Ordering field not defined for entity %s'), $this->name), self::NOTICE);
			}
		}
		return false;
	}
	
	/**
	 * Tells the default position for new rows, first or last.
	 * @return string
	 */
	public function defaultOrder()
	{
		return isset($this->ordered['default']) ? $this->ordered['default'] : self::ORDER_LAST;
	}
	
	/**
	 * Get ordering field value for adding a new row at the end.
	 * @param string $cat (Optional) The value of the categorizing column in the case of categorized entities
	 * @return number
	 */
	public function getNextOrderValue($cat = null)
	{
		$bind = array();
		$sql = "SELECT IFNULL(MAX(`{$this->ordered['field']}`), 0) AS `max` FROM `{$this->getTableName()}`";
		if($this->isCategorized() && $cat){
			$sql .= " WHERE `{$this->categorized['field']}` = ?";
			$bind = array($cat);
		}
		$result = $this->getTable()->fetch($sql, $bind);
		return current($result)->max +1;
	}
	
	/**
	 * Get a list of database records for this entity.
	 * @param string $where (Optional) Where clause for SQL query
	 * @param array $bind (Optional) An array of values to be bound into the query 
	 * WHERE clause. Question mark (?) placeholders must be present in $where
	 * @return Mvc\Db\Resultset
	 */
	public function fetch($where = 1, $bind = array())
	{
		$table = $this->getTable();
		if(!$table) return array();
		$from = "`$this->tableName`";
		$columns[] = "`$this->tableName`.`{$this->getPrimaryKey()}`";
		$group = "";
		$order = "";
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
		if($this->isCategorized()){
			$catField = "`$this->tableName`.`{$this->categorized['field']}`";
			if(!in_array($catField, $columns)){
				$columns[] = $catField;
			}
		}
		if($this->isOrdered()){
			$orderField = "`$this->tableName`.`{$this->ordered['field']}`";
			if(!in_array($orderField, $columns)){
				$columns[] = $orderField;
			}
			if(!preg_match("/ORDER BY/i", $where)){
				$order = " ORDER BY $orderField";
			}
		}
		$columns = implode(', ', $columns);
		$result = $this->fetchSql("SELECT $columns FROM $from WHERE $where $group $order", $bind);
		return $result;
	}
	
	/**
	 * Fetch results based on a given SQL query.
	 * @param string $sql The SQL query to be executed
	 * @param array $bind (Optional) An array of values to be bound into 
	 * the query. Question mark (?) placeholders must be present in $sql
	 * @return Mvc\Db\Resultset
	 */
	public function fetchSql($sql, $bind = array())
	{
		$table = $this->getTable();
		if(!$table) return array();
		if($this->paginator){
			$countSql = preg_replace(
				array('/SELECT.*FROM/is', '/GROUP BY.*/i' ), 
				array('SELECT COUNT(DISTINCT `'.$this->getPrimaryKey().'` ) AS `count` FROM ', ''), 
				$sql
			);
			$result = $table->fetch($countSql, $bind);
			$count = current($result)->count;
			$this->paginator->setTotalItems($count);
			$sql .= ' LIMIT '.$this->paginator->getStartIndex().', '.$this->paginator->getItemsPerPage();
		}
		$result = $table->fetch($sql, $bind);
		return $result;
	}
	
	/**
	 * Base method to be overriden by entities that need to apply options to fetch method.
	 * By default ignores $options and does nothing different than the fetch method.
	 * @param array $options
	 * @param string $where
	 * @param array $bind
	 * @return Mvc\Db\Resultset
	 */
	public function fetchOptions(array $options = array(), $where = 1, $bind = array())
	{
		return $this->fetch($where = 1, $bind = array());
	}
	
	/**
	 * Fetch rows from database based on primary key values.
	 * @param array $ids
	 * @return Mvc\Db\Resultset
	 */
	public function fetchIds($ids)
	{
		$placeholders = array_fill(0, count((array) $ids), '?');
		return $this->fetch("`$this->tableName`.`$this->primaryKey` IN( ".implode(', ', $placeholders)." )", (array) $ids);
	}
	
	/**
	 * Fetch rows and convert resultset to array.
	 * Fetched rows are converted to a one-dimensional associative array, where the keys 
	 * are the primary key values and the values are the content of the main column 
	 * (defined by firstColumn method, defaulted to the first in the array of fields)
	 * @param string $where
	 * @param array $bind (Optional) An array of values to be bound into the query 
	 * WHERE clause. Question mark (?) placeholders must be present in $where
	 * @return array
	 */
	public function fetchArray($where = 1, $bind = array())
	{
		$out = array();
		$result = $this->fetch($where, $bind);
		foreach($result as $row){
			$out[$row->{$this->primaryKey}] = $row->{$this->firstField()};
		}
		return $out;
	}
	
	/**
	 * Insert a row into the database.
	 * @param array $values An array of key / value 
	 * pairs representing column names and values
	 * @return int|NULL The inserted Id or null on failure
	 */
	public function insert(array $values)
	{
		$table = $this->getTable();
		if(!$table) return null;
		if($this->isOrdered() && $this->defaultOrder() == self::ORDER_LAST){
			// New item goes last, fetch next order value
			$cat = $this->isCategorized() ? $values[$this->categorized['field']] : null;
			$values[$this->ordered['field']] = $this->getNextOrderValue($cat);
		}
		$result = $table->insert($values);
		if($result){
			if($this->isOrdered() && $this->defaultOrder() == self::ORDER_FIRST){
				// New items goes first, move down the rest
				$sql = "UPDATE `{$this->getTableName()}`
				SET `{$this->ordered['field']}` = `{$this->ordered['field']}` +1
				WHERE `{$this->getPrimaryKey()}` != $result";
				$bind = array();
				if($this->isCategorized()){
					$sql .= " AND `{$this->categorized['field']}` = ?";
					$bind[] = $values[$this->categorized['field']];
				}
				$this->getTable()->getDb()->query($sql, $bind);
			}
			if($this->callbacks){
				foreach($this->callbacks as $callback){
					$target = $callback['field']->getTarget();
					$target->insertRelated($callback['values'], $result);
				}
			}
		}
		return $result;
	}
	
	/**
	 * Update a record in the database.
	 * @param string $id
	 * @param array $values An array of key / value 
	 * pairs representing column names and values
	 * @return int|NULL The number of affected rows or null on failure.
	 */
	public function update($id, array $values)
	{
		$table = $this->getTable();
		if(!$table) return null;
		$recategorize = false;
		if($this->isOrdered() && $this->isCategorized() && isset($values[$catField])){
			// Category may have changed
			$catField = $this->categorized['field'];
			$ordField = $this->ordered['field'];
			$result = $this->fetchIds($id);
			if($result){
				$row = current($result);
				$rowOrder = $row->$ordField;
				$recategorize = $values[$catField] != $row->$catField;
				if($recategorize){
					// Category has changed
					if($this->defaultOrder() == self::ORDER_LAST){
						// Move last
						$values[$ordField] = $this->getNextOrderValue($values[$catField]);
					}else{
						// Move first
						$values[$ordField] = 1;
					}
				}
			}
		}
		
		$success = $table->update($this->getPrimaryKey(), $id, $values);
		if($success && $recategorize){
			// Category changed, move up items to fill the gap
			$sql = "UPDATE `{$this->getTableName()}`
			SET `$ordField` = `$ordField` -1
			WHERE `$ordField` > $rowOrder
			AND `$catField` = ?";
			$bind = array($row->$catField);
			$this->getTable()->getDb()->query($sql, $bind);
			
			if($this->defaultOrder() == self::ORDER_FIRST){
				// Default first, move down the rest
				$sql = "UPDATE `{$this->getTableName()}`
				SET `$ordField` = `$ordField` +1
				WHERE `{$this->getPrimaryKey()}` != $id
				AND `$catField` = ?";
				$bind = array($values[$catField]);
				$this->getTable()->getDb()->query($sql, $bind);
			}
			
		}
		if($this->callbacks){
			foreach($this->callbacks as $callback){
				$target = $callback['field']->getTarget();
				$success += $target->updateRelated($callback['values'], $id);
			}
		}
		return $success;
	}
	
	/**
	 * Delete a record from the database.
	 * @param array $values The IDs of the rows to delete.
	 * @param string $key (Optional) The name for the identification column. 
	 * Defaults to the primary key name.
	 * @return int|NULL The number of affected rows or null on failure.
	 */
	public function delete(array $values, $key = null)
	{
		$table = $this->getTable();
		if(!$table) return null;
		foreach($this->getFields() as $field){
			if($field->getType() == 'dbCheckbox'){
				$target = $field->getTarget();
				$target->delete($values, $target->getForeignKey());
			}
			if($field->isFile()){
				// Attempt to delete the file
				$records = $this->fetchIds($values);
				foreach ($records as $record){
					$filename = $field->getUploadDir() . '/' . $record->{$field->getName()};
					if(file_exists($filename)){
						if(unlink($filename)){
							// Then attempt to delete container directory
							$path = pathinfo($filename, PATHINFO_DIRNAME);
							@rmdir($path);
						}else{
							$this->addFlashMessage("The file $filename could not be deleted from filsystem");
						}
					}
				}
			}
		}
		if(!$key){
			$key = $this->getPrimaryKey();
		}
		if($this->isOrdered()){
			$result = $this->fetchIds($values);
			foreach($result as $row){
				$ordered[] = $row->{$this->ordered['field']};
			}
			rsort($ordered);
			foreach($ordered as $rowOrder){
				$bind = array();
				$sql = "UPDATE `{$this->getTableName()}`
				SET `{$this->ordered['field']}` = `{$this->ordered['field']}` -1
				WHERE `{$this->ordered['field']}` > $rowOrder";
				if($this->isCategorized()){
					$sql .= " AND `{$this->categorized['field']}` = ?";
					$bind = array($row->{$this->categorized['field']});
				}
				$this->getTable()->getDb()->query($sql, $bind);
			}
		}
		$result = $table->delete($key, $values);
		return $result;
	}
	
	/**
	 * Apply necessary filters to raw data previous to perform INSERT actions.
	 * @param array $data
	 * @return array
	 */
	public function getInsertValues($data)
	{
		return $this->getValues('add', $data);
	}
	
	/**
	 * Apply necessary filters to raw data previous to perform UPDATE actions.
	 * @param array $data
	 * @return array
	 */
	public function getUpdateValues($data)
	{
		return $this->getValues('edit', $data);
	}
	
	/**
	 * Apply necessary filters to raw data previous to perform actions.
	 * Also set actions to be performed afterwards, i.e. the case of fields pertaining to foreign tables 
	 * @param string $action
	 * @param array $data
	 * @return array
	 */
	public function getValues($action, $data)
	{
		foreach($this->getFields() as $field){
			switch ($field->getType()) {
				case 'file':
				case 'image':
					$file = $data['$_FILES'][$field->getName()];
					if(!$file['error'] && $file['size']){
						$dir = substr(md5(uniqid($file['tmp_name'])), 0, 16);
						$path = $field->getUploadDir() . "/$dir";
						$cleanName = preg_replace('/[^a-z0-9_\-\.]/i', '-', $file['name']);
						if(!is_dir($path)){
							if(!mkdir($path, 0777, 1)){
								$this->addMessage("Directory $path could not be created", self::ERROR);
								$this->abort = true;
								return false;
							}
						}
						$destination = "$path/$cleanName";
						if(!move_uploaded_file($file['tmp_name'], $destination)){
							$this->addMessage("Uploaded file could not be moved to $destination", self::ERROR);
							$this->abort = true;
							return false;
						}
						$values[$field->getName()] = "$dir/$cleanName";
					}
					break;
				case 'dbCheckbox':
					$callbackValues = isset($data[$field->getName()]) ? $data[$field->getName()] : null;
					$this->callbacks[] = array('field' => $field, 'values' => $callbackValues);
					break;
				default:
					$values[$field->getName()] = isset($data[$field->getName()]) ? $data[$field->getName()] : $field->defaultValue;
					break;
			}
		}
		return $values;
	}
	
	/**
	 * Set paginator object.
	 * @param Paginator $paginator
	 */
	public function setPaginator(Paginator $paginator = null)
	{
		$this->paginator = $paginator;
	}
	
	/**
	 * Get paginator object.
	 * @return Mvc\Paginator
	 */
	public function getPaginator()
	{
		return $this->paginator;
	}
	
	/**
	 * Get upload error message.
	 * @param int $error
	 * @return string
	 */
	public function getUploadError($error)
	{
		$maxSize = ini_get('upload_max_filesize');
		$fileUploadErrors = array(
			UPLOAD_ERR_INI_SIZE => sprintf($this->__('Please upload a file smaller than %s'), $maxSize),
			UPLOAD_ERR_FORM_SIZE => sprintf($this->__('Please upload a file smaller than %s'), $maxSize),
			UPLOAD_ERR_PARTIAL => $this->__('The file was not uploaded correctly. Please try again.'),
			UPLOAD_ERR_NO_FILE => $this->__('No file uploaded'),
			UPLOAD_ERR_NO_TMP_DIR => $this->__('Temp dir not found'),
			UPLOAD_ERR_CANT_WRITE => $this->__('An error occurred while writing file to disk'),
			UPLOAD_ERR_EXTENSION => $this->__('System error'),
		);
		return $fileUploadErrors[$error];
	}
	
	/**
	 * Get POST values for performing actions, including file upload data.
	 * @param string $action
	 * @return array
	 */
	public function getPostValues($action)
	{
		$entityName = $this->getCleanName();
		$data = isset($_POST["{$action}_$entityName"]) ? $_POST["{$action}_$entityName"] : array();
		return $this->arrangeFilesData($data, "{$action}_$entityName");
	}
	
	/**
	 * Gather file upload data and append it organized to the $data array
	 * @param array $data
	 * @param string $action
	 * @return array
	 */
	protected function arrangeFilesData($data, $action)
	{
		if(isset($_FILES[$action])){
			foreach($_FILES[$action]['name'] as $entityId => $values){
				foreach($_FILES[$action]['error'][$entityId] as $key => $error){
					if($error && $error != UPLOAD_ERR_NO_FILE){
						$this->addMessage($this->getUploadError($error), self::ERROR);
					}
					$data[$entityId]['$_FILES'][$key] = array(
						'name'     => $_FILES[$action]['name'][$entityId][$key],
						'type'     => $_FILES[$action]['type'][$entityId][$key],
						'tmp_name' => $_FILES[$action]['tmp_name'][$entityId][$key],
						'error'    => $_FILES[$action]['error'][$entityId][$key],
						'size'     => $_FILES[$action]['size'][$entityId][$key],
					);
				}
			}
		}
		return $data;
	}
}