<?php
/** Comlei Mvc Framework */

namespace Abm\View\Helper;

use Abm\Entity;
use Abm\View;
use Mvc\Db\Row;

/** Entity admin form view helper */
class EntityAdminForm
{
	/**
	 * The Entity object
	 * @var Abm\Entity
	 */
	protected $entity;
	
	/**
	 * The View object
	 * @var Abm\View
	 */
	protected $view;
	
	/**
	 * The enctype HTML attribute for the form
	 * @var string
	 */
	protected $enctype = '';
	
	/**
	 * The entity Paginator object
	 * @var Mvc\Paginator
	 */
	protected $paginator;
	
	/**
	 * Database records
	 * @var array
	 */
	protected $records = array();
	
	/**
	 * A string for the legend HTML tag or a boolean value for determining whether to generate one
	 * @var string|boolean
	 */
	public $legend = true;
	
	/**
	 * Initialize object and remove Paginator from Entity
	 * @param View $view
	 * @param Entity $entity
	 */
	public function __construct(View $view, Entity $entity)
	{
		$this->entity = $entity;
		$this->view = $view;
		// Remove paginator
		$this->paginator = $entity->getPaginator();
		$this->entity->setPaginator(null);
	}
	
	/**
	 * Generate output
	 * @return string
	 */
	public function render()
	{
		$entity = $this->entity;
		$view = $this->view;
		$forms = $this->getForms();
		foreach($entity->getFields() as $field){
			if($field->isFile()){
				$this->enctype = 'enctype="multipart/form-data"';
			}
		}
		$out[] = '<form '.$this->enctype.' action="" class="'.$entity->getCleanName().'-admin-form" method="post">';
		foreach($forms as $action => $form){
			if($action == 'delete'){
				$out[] = '<fieldset>
					'.$this->getLegend('delete');
			}
			foreach($form as $i){
				if($action == 'delete'){
					$out[] = $this->deleteForm($i);
				}else{
					$out[] = $this->innerForm($action, $i);
				}
			}
			if($action == 'delete'){
				$out[] = '</fieldset>';
			}
		}
		$out[] = '<div class="form-group">
				<input type="submit" class="btn btn-primary" value="'.$view->__('Save changes', $view::TEXTDOMAIN).'">
				<p class="btn">
					<a class="text-danger cancel-btn" href="'.$view->removeQs().'">
						<span class="fa fa-ban"></span>
						'.$view->__('Cancel', $view::TEXTDOMAIN).'
					</a>
				</p>
			</div>
		</form>';
		return implode(PHP_EOL, $out);
	}
	
	/**
	 * Get legend HTML tag
	 * @param string $action
	 * @return string
	 */
	public function getLegend($action)
	{
		if($this->legend){
			$view = $this->view;
			$legend = is_string($this->legend) ? $this->legend : $view->__($view->__(ucwords($action), $view::TEXTDOMAIN).' '.$view->__($this->entity->getName()));
			return "<legend>$legend</legend>";
		}
		return '';
	}
	
	/**
	 * The fieldset for a certain actions to be performed
	 * @param string $action
	 * @param int $entityIndex An index number for the current action / entity
	 * @return string
	 */
	public function innerForm($action, $entityIndex)
	{
		$entity   = $this->entity;
		$view     = $this->view;
		$formName = "{$action}_{$entity->getCleanName()}[$entityIndex]";
		$formId   = "{$action}_{$entity->getCleanName()}_$entityIndex";
		
		$out[] = '<fieldset>
			'.$this->getLegend($action);
		foreach($entity->getFields() as $field){
			$out[] = $this->renderInputField($field, $action, $entityIndex);
		}
		$out[] = '</fieldset>';
		return implode(PHP_EOL, $out);
	}
	
	public function renderInputField($field, $action, $entityIndex, $ref = null)
	{
		$entity       = $this->entity;
		$view         = $this->view;
		$formName     = "{$action}_{$entity->getCleanName()}[$entityIndex]";
		$formId       = "{$action}_{$entity->getCleanName()}_$entityIndex";
		$fieldName    = "{$formName}[{$field->getName()}]";
		$fieldId      = "{$formId}_{$field->getName()}";
		$fieldType    = $field->getType();
		$defaultValue = $field->defaultValue;
		if($action == 'edit' && isset($this->records[$entityIndex])){
			$defaultValue = $this->records[$entityIndex]->{$field->getName()};
		}
		$value = isset($_POST[$fieldName]) ? $_POST[$fieldName] : $defaultValue;
		if(is_array($value) && $ref){
			$value = isset($value[$ref]) ? $value[$ref] : null;
		}
		if($ref){
			$fieldName .= "[$ref]";
			$fieldId   .= "_$ref";
		}
		$out[] = "<div class=\"form-group $fieldType {$entity->getCleanName()}-{$field->getName()}-form-group\">
		<label class=\"control-label\" for=\"$fieldId\"><span class=\"label-text\">{$this->view->__($field->getTitle())}</span>";
		$required = $field->required ? 'required="required"' : '';
		$class =  $entity->getCleanName().'-'.$field->getName();
		switch ($fieldType) {
			case 'date':
			case 'time':
			case 'datetime':
				preg_match('/(?:([0-9]{4})-([0-9]{2})-([0-9]{2}) ?)?(?:([0-9]{2}):([0-9]{2}):([0-9]{2}))?/', $value, $datePart);
				$isDate = strpos($fieldType, 'date') !== false;
				$isTime = strpos($fieldType, 'time') !== false;
				$date   = count($datePart) > 1;
				$day    = $date ? $datePart[3] : '';
				$month  = $date ? $datePart[2] : '';
				$year   = $date ? $datePart[1] : '';
				$hour   = $date && $isTime ? $datePart[4] : '';
				$minute = $date && $isTime ? $datePart[5] : '';
				$second = $date && $isTime ? $datePart[6] : '';
				$months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
				$fields['D'] = "<select class=\"form-control day $class\" name=\"{$fieldName}[d]\" id=\"{$fieldId}_d\" $required>
				<option value=\"\">{$this->view->__('Day', $view::TEXTDOMAIN)}</option>";
				for($d = 1; $d <= 31; $d++){
				$selected = $day == $d ? 'selected="selected"' : '';
					$fields['D'] .= "<option value=\"$d\" $selected>$d</option>";
				}
				$fields['D'] .= "</select>";
				$fields['M'] = "<select class=\"form-control month $class\" name=\"{$fieldName}[m]\" id=\"{$fieldId}_m\" $required>
				<option value=\"\">{$this->view->__('Month', $view::TEXTDOMAIN)}</option>";
				foreach($months as $m => $monthName){
					$mm = $m +1;
					$selected = $month == $mm ? 'selected="selected"' : '';
					$fields['M'] .= "<option value=\"$mm\" $selected>{$this->view->__($monthName, $view::TEXTDOMAIN)}</option>";
				}
				$fields['M'] .= "</select>";
				$fields['Y'] = "<input placeholder=\"{$this->view->__('Year', $view::TEXTDOMAIN)}\" class=\"form-control year $class\" type=\"number\" name=\"{$fieldName}[y]\" id=\"{$fieldId}_y\" value=\"$year\" $required>";
				$fields['h'] = "<input placeholder=\"{$this->view->__('Hour', $view::TEXTDOMAIN)}\" class=\"form-control hour $class\" type=\"number\" name=\"{$fieldName}[h]\" id=\"{$fieldId}_h\" value=\"$hour\" min=\"0\" max=\"23\" $required>";
				$fields['m'] = "<input placeholder=\"{$this->view->__('Minute', $view::TEXTDOMAIN)}\" class=\"form-control minute $class\" type=\"number\" name=\"{$fieldName}[min]\" id=\"{$fieldId}_min\" value=\"$minute\" min=\"0\" max=\"59\" $required>";
				$fields['s'] = "<input placeholder=\"{$this->view->__('Second', $view::TEXTDOMAIN)}\" class=\"form-control second $class\" type=\"number\" name=\"{$fieldName}[s]\" id=\"{$fieldId}_s\" value=\"$second\" min=\"0\" max=\"59\" $required>";
				if($isDate){
					$out[] = '<div class="date-fields">';
					foreach(str_split(strtoupper($field->dateFieldsOrder)) as $dateField => $i){
						if($dateField){
							$out[] = '<span class="separator"></span>';
						}
						$out[] = $fields[$i];
					}
					$out[] = '</div>';
				}
				if($isTime){
					$out[] = '<div class="time-fields">';
					foreach(str_split(strtolower($field->timeFields)) as $timeField => $i){
						if($timeField){
							$out[] = '<span class="separator"></span>';
						}
						$out[] = $fields[$i];
					}
					$out[] = '</div>';
				}
				break;
			case 'textarea':
				$placeholder = $field->placeholder ? "placeholder=\"$field->placeholder\"" : '';
				$out[] = "<textarea class=\"form-control $class\" name=\"$fieldName\" id=\"$fieldId\" $placeholder $required>$value</textarea>";
				break;
				case 'select':
				case 'dbSelect':
				$out[] = "<select class=\"form-control $class\" name=\"$fieldName\" id=\"$fieldId\" $required>";
				if($field->emptyFirstOption !== false){
					$out[] = '<option value="">'.$this->view->__($field->emptyFirstOption).'</option>';
				}
				$options = $field->getOptions();
				foreach($options as $opVal => $option){
					if(is_array($option)){
						$out[] = '<optgroup label="'.$this->view->__($opVal).'">';
						foreach($option as $v => $o){
							$selected = $v == $value ? 'selected="selected"' : '';
							$out[] = "<option value=\"$v\" $selected>".$this->view->__($o)."</option>";
						}
						$out[] = '</optgroup>';
					}else{
						$selected = $opVal == $value ? 'selected="selected"' : '';
						$out[] = "<option value=\"$opVal\" $selected>".$this->view->__($option)."</option>";
					}
				}
				$out[] = "</select>";
				break;
			case 'boolean':
				$checked = $value ? 'checked="checked"' : '';
				$out[] = "<input type=\"hidden\" name=\"$fieldName\" value=\"0\" />
				<input type=\"checkbox\" name=\"$fieldName\" id=\"$fieldId\" value=\"1\" $checked />";
				break;
				case 'checkbox':
				$options = $field->getOptions();
				foreach($options as $opVal => $option){
					$optionName = "{$fieldName}[$opVal]";
					$optionId = "{$fieldId}_$opVal";
					$checked = isset($value[$opVal]) && $value[$opVal] ? 'checked="checked"' : '';
					$out[] = "<div class=\"checkbox\">
						<label for=\"$optionId\">
							<input type=\"hidden\" name=\"$optionName\" value=\"0\" />
							<input type=\"checkbox\" name=\"$optionName\" id=\"$optionId\" value=\"1\" $checked />
							<span>$option</span>
						</label>
					</div>";
				}
				break;
			case 'dbCheckbox':
				$options = $field->getOptions();
				$out[] = "<div class=\"checkbox-options\">";
				foreach($options as $opVal => $option){
					$optionName = "{$fieldName}[]";
					$optionId = "{$fieldId}_$opVal";
					$values = explode(',', $value);
					$checked = $value && in_array($opVal, $values) ? 'checked="checked"' : '';
					$out[] = "<div class=\"checkbox\">
						<label for=\"$optionId\">
							<input type=\"checkbox\" name=\"$optionName\" id=\"$optionId\" value=\"$opVal\" $checked />
							<span>$option</span>
						</label>
					</div>";
				}
				$out[] = "</div>";
				break;
			default:
				if($value){
					$value = "value=\"$value\"";
				}
				if($fieldType == 'image'){
					$fieldType = 'file';
				}
				$placeholder = $field->placeholder ? "placeholder=\"$field->placeholder\"" : '';
				$out[] = "<input class=\"form-control $class\" type=\"$fieldType\" name=\"$fieldName\" id=\"$fieldId\" $value $placeholder $required />";
				break;
		}
		$out[] = '</label>
		</div>';
		return implode(PHP_EOL, $out);
	}
	
	/**
	 * A form for deletion confirmation
	 * @param int $entityIndex An index number for the current action / entity
	 * @return string
	 */
	public function deleteForm($entityIndex)
	{
		$view = $this->view;
		$entity = $this->entity;
		$out = '';
		if(isset($this->records[$entityIndex])){
			$recordName = $this->records[$entityIndex]->{$entity->firstField()};
			if(is_array($recordName)) $recordName = current($recordName);
			$out = '<div class="bg-warning has-warning">
				<div class="checkbox">
					<label for="delete_'.$entity->getCleanName().'_'.$entityIndex.'">
						<input type="hidden" name="delete_'.$entity->getCleanName().'['.$entityIndex.']" value="0">
						<input type="checkbox" name="delete_'.$entity->getCleanName().'['.$entityIndex.']" id="delete_'.$entity->getCleanName().'_'.$entityIndex.'" value="1">
						<span class="fa fa-warning"></span>
						<span>'.sprintf($view->__('Confirm deletion of %s?', $view::TEXTDOMAIN), $recordName).'</span>
					</label>
				</div>
			</div>';
		}
		return $out;
	}
	
	/**
	 * Get the actions to be performed based on HTTP request
	 * @return array
	 */
	public function getForms()
	{
		$forms = array();
		$entity = $this->entity;
		$view = $this->view;
		$add = $view->getController()->getParam('add_'.$entity->getCleanName());
		$edit = $view->getController()->getParam('edit_'.$entity->getCleanName());
		$delete = $view->getController()->getParam('delete_'.$entity->getCleanName());
		if(is_numeric($add)){
			for($i = 0; $i < $add; $i++){
				$forms['add'][] = $i;
			}
		}
		if($edit && preg_match('/^[0-9a-z_]+(,[0-9a-z_]+)*$/i', $edit)){
			$forms['edit'] = explode(',', $edit);
			$resultset = $entity->fetchIds($forms['edit']);
			foreach($resultset as $row){
				$this->addRecord($row->{$entity->getPrimaryKey()}, $row);
			}
		}
		if($delete && preg_match('/^[0-9]+(,[0-9]+)*$/', $delete)){
			$forms['delete'] = explode(',', $delete);
			$resultset = $entity->fetchIds($forms['delete']);
			foreach($resultset as $row){
				$this->addRecord($row->{$entity->getPrimaryKey()}, $row);
			}
		}
		if(empty($forms) && empty($forms['delete'])){
			$forms['add'][] = 0;
		}
		return $forms;
	}
	
	/**
	 * Add DB record to the fetched records array
	 * @param int $id
	 * @param Row $row
	 */
	public function addRecord($id, Row $row)
	{
		$this->records[$id] = $row;
	}
	
	/**
	 * Generate output
	 * @return string
	 */
	public function __toString()
	{
		try {
			$return = $this->render();
		} catch (\Exception $e) {
			$entity = $this->entity;
			$entity->addMessage($e->getMessage(), $entity::ERROR);
			$return = $this->view->renderMessages($entity->getMessages());
		}
		// Restore paginator
		$this->entity->setPaginator($this->paginator);
		return $return;
	}
}