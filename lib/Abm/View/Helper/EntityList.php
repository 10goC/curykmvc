<?php
namespace Abm\View\Helper;

use Abm\Entity;
use Abm\View;

class EntityList
{
	protected $entity;
	protected $view;
	protected $options;
	
	public function __construct(View $view, Entity $entity, $options = array())
	{
		$this->view = $view;
		$this->entity = $entity;
		$this->options = $options;
	}
	
	public function render()
	{
		try {
			$view = $this->view;
			$entity = $this->entity;
			$options = $this->options;
			$out = array();
			$list = $entity->fetch();
			$actions = isset($options['actions']) ? $options['actions'] : $entity->getActions();
			// Remove add action
			if(in_array('add', $actions)){
				unset($actions[array_search('add', $actions)]);
			}
			if($list && count($list)){
				$out[] = "<table class=\"$view->listClass\">
				{$this->entityListHeader($entity, $actions, $options)}
				{$this->entityListBody($entity, $list, $actions, $options)}
				</table>";
			}else{
				$out[] = '<p>'.sprintf($view->__('No %s found'), $entity->getPlural()).'</p>';
			}
			if($messages = $entity->getMessages()){
				array_unshift($out, $view->renderMessages($messages));
			}
			return implode(PHP_EOL, $out);
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}
	
	protected function entityListHeader(Entity $entity, $actions, $options)
	{
		$out[] = "<thead>
		<tr>";
		foreach($entity->getFields() as $field){
			$out[] = '<th>'.$field->getTitle().'</th>';
		}
		if(!empty($actions)){
		$colspan = count($actions) > 1 ? 'colspan="'.count($actions).'"' : '';
				$out[] = "<th $colspan>&nbsp;</th>";
		}
		$out[] = "</tr>
		</thead>";
		return implode(PHP_EOL, $out);
	}

	protected function entityListBody(Entity $entity, $list, $actions, $options)
	{
		$view = $this->view;
		$out[] = '<tbody>';
		foreach($list as $row){
			$out[] = '<tr>';
			foreach($entity->getFields() as $field){
				$value = $row->{$field->getName()};
				switch ($field->getType()) {
					case 'dbSelect':
						$source = $field->getOptions();
						$out[] = "<td>{$source[$value]}</td>";
						break;
					
					case 'dbCheckbox':
						$source = $field->getOptions();
						$values = explode(',', $value);
						$value = implode(', ', 
							array_intersect_key(
								$source,
								array_flip($values) 
							)
						);
						$out[] = "<td>$value</td>";
						break;
					
					default:
						$out[] = "<td>$value</td>";
						break;
				}
			}
			if(!empty($actions)){
				foreach($actions as $action){
					$param = "{$action}_{$entity->getCleanName()}";
					$id = $row->{$entity->getPrimaryKey()};
					$out[] = "<td><a href=\"?$param=$id\">
					<span class=\"fa fa-{$this->getIcon($action)} fa-lg\"></span>
					<span class=\"text\">".$view->__(ucwords($action))."</span>
					</a></td>";
				}
			}
			$out[] = '</tr>';
		}
		$out[] = '</tbody>';
		return implode(PHP_EOL, $out);
	}
	
	public function getIcon($action)
	{
		$icons = array(
			'edit' => 'edit',
			'delete' => 'trash'
		);
		return isset($icons[$action]) ? $icons[$action] : '';
	}
	
	public function __toString()
	{
		return $this->render();
	}
}