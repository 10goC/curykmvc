<?php
/** Comlei Mvc Framework */

namespace Abm\View\Helper;

use Abm\Entity;
use Abm\View;

/** View Helper for creating automated Entity listings */
class EntityList
{
	/**
	 * The Entity object
	 * @var Abm\Entity
	 */
	protected $entity;
	
	/**
	 * The View object from which the helper has been invoked
	 * @var Abm\View
	 */
	protected $view;
	
	/**
	 * The array of options
	 * @var array
	 */
	protected $options;
	
	/**
	 * An array of fields to be ommitted from listing
	 * @var array
	 */
	protected $hide = array();
	
	/**
	 * HTML Template for displaying page info
	 * @var string
	 */
	public $pageInfoTemplate = '<div class="page-info">Showing results {start} to {end} of {total}</div>';
	
	/**
	 * Receives the injected View and Entity objects and options
	 * @param View $view
	 * @param Entity $entity
	 * @param array $options
	 */
	public function __construct(View $view, Entity $entity, $options = array())
	{
		$this->view = $view;
		$this->entity = $entity;
		$this->options = $options;
		if(isset($options['hide'])) $this->hide = $options['hide'];
	}
	
	/**
	 * Generate output
	 * @return string
	 */
	public function render()
	{
		$view = $this->view;
		$entity = $this->entity;
		$options = $this->options;
		$out = array();
		$list = $entity->fetchOptions($this->options);
		$actions = isset($options['actions']) ? $options['actions'] : $entity->getActions();
		// Remove add action
		if(in_array('add', $actions)){
			unset($actions[array_search('add', $actions)]);
		}
		if($list && count($list)){
			$out[] = $this->renderPageInfo();
			$out[] = "<table class=\"$view->listClass\">
			{$this->entityListHeader($entity, $actions, $options)}
			{$this->entityListBody($entity, $list, $actions, $options)}
			</table>";
		}else{
			$out[] = '<p>'.sprintf($view->__('No %s found', $view::TEXTDOMAIN), $entity->getPlural()).'</p>';
		}
		if($messages = $entity->getMessages()){
			array_unshift($out, $view->renderMessages($messages));
			$entity->flushMessages();
		}
		return implode(PHP_EOL, $out);
	}
	
	/**
	 * Render information about current page
	 * @return string
	 */
	public function renderPageInfo()
	{
		if($this->entity->getPaginator()){
			return str_replace(
				array('{start}', '{end}', '{total}'),
				array(
					$this->entity->getPaginator()->getPageFirstItem(),
					$this->entity->getPaginator()->getPageLastItem(),
					$this->entity->getPaginator()->getTotalItems()
				),
				$this->view->__($this->pageInfoTemplate)
			);
		}
		return '';
	}
	
	/**
	 * Set HTML template for showing information about current page
	 * @param string $template
	 * @return \Abm\View\Helper\EntityList
	 */
	public function setPageInfoTemplate($template)
	{
		$this->pageInfoTemplate = $template;
		return $this;
	}
	
	/**
	 * Generate output for the list header
	 * @param Entity $entity
	 * @param array $actions
	 * @param array $options
	 * @return string
	 */
	protected function entityListHeader(Entity $entity, $actions, $options)
	{
		$out[] = "<thead>
		<tr>";
		foreach($entity->getFields() as $field){
			if(!in_array($field->getName(), $this->hide)){
				$out[] = '<th>'.$field->getTitle().'</th>';
			}
		}
		if(!empty($actions)){
		$colspan = count($actions) > 1 ? 'colspan="'.count($actions).'"' : '';
				$out[] = "<th $colspan>&nbsp;</th>";
		}
		$out[] = "</tr>
		</thead>";
		return implode(PHP_EOL, $out);
	}

	/**
	 * Generate output for the list body
	 * @param Entity $entity
	 * @param Mvc\Db\Resultset $list
	 * @param array $actions
	 * @param array $options
	 * @return string
	 */
	protected function entityListBody(Entity $entity, $list, $actions, $options)
	{
		$view = $this->view;
		$out[] = '<tbody>';
		foreach($list as $row){
			$out[] = '<tr>';
			foreach($entity->getFields() as $field){
				if(!in_array($field->getName(), $this->hide)){
					$value = $row->{$field->getName()};
					$out[] = "<td>{$field->render($row)}</td>";
				}
			}
			if(!empty($actions)){
				foreach($actions as $action){
					if($action == 'order') $action = 'move up';
					$param = str_replace(' ', '', "{$action}_{$entity->getCleanName()}");
					$id = $row->{$entity->getPrimaryKey()};
					$out[] = "<td><a href=\"?$param=$id\">
					<span class=\"action $action\" title=\"".$view->__(ucfirst($action), $view::TEXTDOMAIN)."\">
						<span class=\"fa fa-{$this->getIcon($action)} fa-lg\"></span>
						<span class=\"text\">".$view->__(ucfirst($action), $view::TEXTDOMAIN)."</span>
					</span>
					</a></td>";
				}
			}
			$out[] = '</tr>';
		}
		$out[] = '</tbody>';
		return implode(PHP_EOL, $out);
	}
	
	/**
	 * Get icon name for default actions
	 * @param string $action
	 * @return string
	 */
	public function getIcon($action)
	{
		$icons = array(
			'edit' => 'edit',
			'delete' => 'trash',
			'move up' => 'arrow-up',
		);
		return isset($icons[$action]) ? $icons[$action] : '';
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
		return $return;
	}
}