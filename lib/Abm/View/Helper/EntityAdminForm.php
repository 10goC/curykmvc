<?php
/** Comlei Mvc Framework */

namespace Abm\View\Helper;

use Abm\Entity;
use Abm\View;

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
			$legend = is_string($this->legend) ? $this->legend : $this->view->__(ucwords($action), $view::TEXTDOMAIN).' '.$this->entity->getName();
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
		$entity = $this->entity;
		$view = $this->view;
		$formName = "{$action}_{$entity->getCleanName()}[$entityIndex]";
		$formId = "{$action}_{$entity->getCleanName()}_$entityIndex";
		
		$out[] = '<fieldset>
			'.$this->getLegend($action);
		foreach($entity->getFields() as $field){
			$fieldName = "{$formName}[{$field->getName()}]";
			$fieldId   = "{$formId}_{$field->getName()}";
			$fieldType = $field->getType();
			$defaultValue = $field->defaultValue;
			if($action == 'edit' && isset($this->records[$entityIndex])){
				$defaultValue = $this->records[$entityIndex]->{$field->getName()};
			}
			$value = isset($_POST[$fieldName]) ? $_POST[$fieldName] : $defaultValue;
			$out[] = "<div class=\"form-group $fieldType {$entity->getCleanName()}-{$field->getName()}-form-group\">
			<label class=\"control-label\" for=\"$fieldId\"><span class=\"label-text\">{$field->getTitle()}</span>";
			$required = $field->required ? 'required="required"' : '';
			$class =  $entity->getCleanName().'-'.$field->getName();
			switch ($fieldType) {
				case 'date':
					$date   = preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2})/', $value, $datePart);
					$day    = $date ? $datePart[3] : '';
					$month  = $date ? $datePart[2] : '';
					$year   = $date ? $datePart[1] : '';
					$months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
					$fields['D'] = "<select class=\"form-control\" name=\"{$fieldName}[d]\" id=\"{$fieldId}_d\" $required>
						<option value=\"\">{$this->view->__('Day')}</option>";
						for($d = 1; $d <= 31; $d++){
							$selected = $day == $d ? 'selected="selected"' : '';
							$fields['D'] .= "<option value=\"$d\" $selected>$d</option>";
						}
					$fields['D'] .= "</select>";
					$fields['M'] = "<select class=\"form-control\" name=\"{$fieldName}[m]\" id=\"{$fieldId}_m\" $required>
						<option value=\"\">{$this->view->__('Month')}</option>";
						foreach($months as $m => $monthName){
							$mm = $m +1;
							$selected = $month == $mm ? 'selected="selected"' : '';
							$fields['M'] .= "<option value=\"$mm\" $selected>{$this->view->__($monthName)}</option>";
						}
					$fields['M'] .= "</select>";
					$fields['Y'] = "<input placeholder=\"{$this->view->__('year')}\" class=\"form-control\" type=\"number\" name=\"{$fieldName}[y]\" id=\"{$fieldId}_y\" value=\"$year\" $required>";
					foreach(str_split($field->dateFieldsOrder) as $i){
						$out[] = $fields[$i];
					}
					break;
				case 'textarea':
					$placeholder = $field->placeholder ? "placeholder=\"$field->placeholder\"" : '';
					$out[] = "<textarea class=\"form-control\" name=\"$fieldName\" id=\"$fieldId\" $placeholder $required>$value</textarea>";
					break;
				case 'select':
				case 'dbSelect':
					$out[] = "<select class=\"form-control $class\" name=\"$fieldName\" id=\"$fieldId\" $required>";
					if($field->emptyFirstOption !== false){
						$out[] = "<option value=\"\">$field->emptyFirstOption</option>";
					}
					$options = $field->getOptions();
					foreach($options as $opVal => $option){
						$selected = $opVal == $value ? 'selected="selected"' : '';
						$out[] = "<option value=\"$opVal\" $selected>$option</option>";
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
		}
		$out[] = '</fieldset>';
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
			$out = '<div class="bg-warning has-warning">
				<div class="checkbox">
					<label for="delete_'.$entity->getCleanName().'_'.$entityIndex.'">
						<input type="hidden" name="delete_'.$entity->getCleanName().'['.$entityIndex.']" value="0">
						<input type="checkbox" name="delete_'.$entity->getCleanName().'['.$entityIndex.']" id="delete_'.$entity->getCleanName().'_'.$entityIndex.'" value="1">
						<span class="fa fa-warning"></span>
						<span>'.sprintf($view->__('Confirm deletion of %s?', $view::TEXTDOMAIN), $this->records[$entityIndex]->{$entity->firstField()}).'</span>
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
				$this->records[$row->{$entity->getPrimaryKey()}] = $row;
			}
		}
		if($delete && preg_match('/^[0-9]+(,[0-9]+)*$/', $delete)){
			$forms['delete'] = explode(',', $delete);
			$resultset = $entity->fetchIds($forms['delete']);
			foreach($resultset as $row){
				$this->records[$row->{$entity->getPrimaryKey()}] = $row;
			}
		}
		if(empty($forms) && empty($forms['delete'])){
			$forms['add'][] = 0;
		}
		return $forms;
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