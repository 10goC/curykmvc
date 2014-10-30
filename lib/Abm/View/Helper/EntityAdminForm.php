<?php
namespace Abm\View\Helper;

use Abm\Entity;
use Abm\View;

class EntityAdminForm
{
	protected $entity;
	protected $view;
	
	public function __construct(View $view, Entity $entity)
	{
		$this->entity = $entity;
		$this->view = $view;
	}
	
	public function render()
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
		if($edit && preg_match('/^[0-9]+(,[0-9]+)*$/', $edit)){
			$forms['edit'] = explode(',', $edit);
			$resultset = $entity->fetchIds($forms['edit']);
			foreach($resultset as $row){
				$record[$row->{$entity->getPrimaryKey()}] = $row;
			}
		}
		if($delete && preg_match('/^[0-9]+(,[0-9]+)*$/', $delete)){
			$deleteIds = explode(',', $delete);
			$resultset = $entity->fetchIds($deleteIds);
			foreach($resultset as $row){
				$record[$row->{$entity->getPrimaryKey()}] = $row;
			}
		}
		if(empty($forms) && empty($deleteIds)){
			$forms['add'][] = 0;
		}
		$out[] = '<form action="" method="post">';
		foreach($forms as $action => $form){
			foreach($form as $i){
				$formName = "{$action}_{$entity->getCleanName()}[$i]";
				$formId = "{$action}_{$entity->getCleanName()}_$i";
				$out[] = '<fieldset>
					<legend>'.$view->__(ucwords($action)).' '.$entity->getName().'</legend>';
				foreach($entity->getFields() as $field){
					$fieldName = "{$formName}[{$field->getName()}]";
					$fieldId   = "{$formId}_{$field->getName()}";
					$fieldType = $field->getType();
					$defaultValue = null;
					if($action == 'edit' && isset($record[$i])){
						$defaultValue = $record[$i]->{$field->getName()};
					}
					$value = isset($_POST[$fieldName]) ? $_POST[$fieldName] : $defaultValue;
					$out[] = "<div class=\"form-group $fieldType\">
					<label for=\"$fieldId\">".$field->getTitle();
					switch ($fieldType) {
						case 'textarea':
							$value =
							$out[] = "<textarea class=\"form-control\" name=\"$fieldName\" id=\"$fieldId\">$value</textarea>";
							break;
						case 'select':
						case 'dbSelect':
							$out[] = "<select class=\"form-control\" name=\"$fieldName\" id=\"$fieldId\">";
							$options = $field->getOptions();
							foreach($options as $opVal => $option){
								$selected = $opVal == $value ? 'selected="selected"' : '';
								$out[] = "<option value=\"$opVal\" $selected>$option</option>";
							}
							$out[] = "</select>";
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
							$out[] = "<input class=\"form-control\" type=\"$fieldType\" name=\"$fieldName\" id=\"$fieldId\" $value />";
							break;
					}
					$out[] = '</label>
						</div>';
				}
				$out[] = '</fieldset>';
			}
		}
		if(isset($deleteIds)){
			$out[] = '<fieldset>
				<legend>'.$view->__(ucwords('delete')).' '.$entity->getName().'</legend>';
			foreach($deleteIds as $deleteId){
				$out[] = '<div class="bg-warning has-warning">
					<div class="checkbox">
						<label for="delete_'.$entity->getCleanName().'_'.$deleteId.'">
							<input type="hidden" name="delete_'.$entity->getCleanName().'['.$deleteId.']" value="0">
							<input type="checkbox" name="delete_'.$entity->getCleanName().'['.$deleteId.']" id="delete_'.$entity->getCleanName().'_'.$deleteId.'" value="1">
							<span class="glyphicon glyphicon-warning-sign"></span>
							<span>'.sprintf($view->__('Confirm deletion of %s?'), $record[$deleteId]->{$entity->firstField()}).'</span>
						</label>
					</div>
				</div>';
			}
			$out[] = '</fieldset>';
		}
		$out[] = '<div class="form-group">
				<input type="submit" class="btn btn-primary" value="'.$view->__('Save changes').'">
				<p class="btn">
					<a class="text-danger" href="'.$view->removeQs().'">
						<span class="glyphicon glyphicon-ban-circle"></span>
						'.$view->__('Cancel').'
					</a>
				</p>
			</div>
		</form>';
		return implode(PHP_EOL, $out);
	}
	
	public function __toString()
	{
		try {
			$return = $this->render();
		} catch (\Exception $e) {
			$return = $e->getMessage();
		}
		return $return;
	}
}