<?php
/** Comlei Mvc Framework */

namespace Abm\View\Helper;

use Abm\Entity;
use Abm\Entity\Field;
use Abm\View;
use Mvc\Db\Row;

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
	protected $hide = [];
	
	/**
	 * The list of items to show
	 * @var Mvc\Db\Resultset
	 */
	public $list;
	
	/**
	 * HTML Template for displaying page info
	 * @var string
	 */
	public $pageInfoTemplate = '<div class="page-info">%s</div>';
	
	/**
	 * Text content for the HTML Template that displays page info
	 * @var string
	 */
	public $pageInfoTemplateStr = 'Showing results {start} to {end} of {total}';
	
	/**
	 * Filters for the search query
	 * @var array
	 */
	public $filters = [];
	
	/**
	 * Receives the injected View and Entity objects and options
	 * @param View $view
	 * @param Entity $entity
	 * @param array $options
	 */
	public function __construct(View $view, Entity $entity, $options = [])
	{
		$this->view = $view;
		$this->entity = $entity;
		$this->options = $options;
		if (isset($options['hide'])) $this->hide = $options['hide'];
		if (isset($options['list'])) $this->list = $options['list'];
		if (isset($options['filters'])) $this->setFilters($options['filters']);
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
		$out = [];
		if (!$this->list) {
			$where = 1;
			$bind = [];
			if ($this->filters) {
				foreach ($this->filters as $wherePart => $bindPart) {
					$whereArray[] = "($wherePart)";
					if ($bindPart) {
						$bind = array_merge($bind, (array) $bindPart);
					}
				}
				$where = implode(' AND ', $whereArray);
			}
			$this->list = $entity->fetchOptions($this->options, $where, $bind);
		}
		$actions = isset($options['actions']) ? $options['actions'] : $entity->getActions();
		// Remove add action
		if (in_array('add', $actions)) {
			unset($actions[array_search('add', $actions)]);
		}
		if ($this->list && count($this->list)) {
			$out[] = $this->renderPageInfo();
			$out[] = "<table class=\"$view->listClass\">
			{$this->entityListHeader($entity, $actions, $options)}
			{$this->entityListBody($entity, $actions, $options)}
			</table>";
		} else {
			$out[] = '<p>'.$view->__(sprintf($view->__('No %s found', $view::TEXTDOMAIN), $view->__($entity->getPlural()))).'</p>';
		}
		if ($messages = $entity->getMessages()) {
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
		if ($this->entity->getPaginator()) {
			return str_replace(
				array('{start}', '{end}', '{total}'),
				array(
					$this->entity->getPaginator()->getPageFirstItem(),
					$this->entity->getPaginator()->getPageLastItem(),
					$this->entity->getPaginator()->getTotalItems()
				),
				sprintf(
					$this->pageInfoTemplate,
					$this->view->__(
						$this->view->__($this->pageInfoTemplateStr, View::TEXTDOMAIN)
					)
				)
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
	 * Set filters for the search query
	 * @param array $filters
	 * @return \Abm\View\Helper\EntityList
	 */
	public function setFilters(array $filters)
	{
		$this->filters = $filters;
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
		foreach ($entity->getFields() as $field) {
			if (!in_array($field->getName(), $this->hide)) {
				$out[] = $this->headerCell($field);
			}
		}
		if (!empty($actions)) {
		$colspan = count($actions) > 1 ? 'colspan="'.count($actions).'"' : '';
				$out[] = "<th $colspan>&nbsp;</th>";
		}
		$out[] = "</tr>
		</thead>";
		return implode(PHP_EOL, $out);
	}
	
	/**
	 * Render a table header cell with a field title
	 * @param Field $field
	 * @return string
	 */
	public function headerCell(Field $field)
	{
		return '<th>'.$this->view->__($field->getTitle()).'</th>';
	}

	/**
	 * Generate output for the list body
	 * @param Entity $entity
	 * @param array $actions
	 * @param array $options
	 * @return string
	 */
	protected function entityListBody(Entity $entity, $actions, $options)
	{
		$view = $this->view;
		$out[] = '<tbody>';
		foreach ($this->list as $row) {
			$out[] = '<tr>';
			foreach ($entity->getFields() as $field) {
				if (!in_array($field->getName(), $this->hide)) {
					$value = $row->{$field->getName()};
					$out[] = $this->bodyCell($field, $row);
				}
			}
			if (!empty($actions)) {
				foreach ($actions as $action) {
					$out[] = $this->actionCell($action, $entity, $field, $row);
				}
			}
			$out[] = '</tr>';
		}
		$out[] = '</tbody>';
		return implode(PHP_EOL, $out);
	}
	
	/**
	 * Render a table body cell with a field value
	 * @param Field $field
	 * @param Row $row
	 * @return string
	 */
	public function bodyCell(Field $field, Row $row)
	{
		return "<td>{$field->render($row)}</td>";
	}
	
	/**
	 * Render a table body action cell
	 * @param string $action
	 * @param Entity $entity
	 * @param Field $field
	 * @param Row $row
	 * @return string
	 */
	public function actionCell($action, Entity $entity, Field $field, Row $row)
	{
		$view = $this->view;
		if ($action == 'order') $action = 'move up';
		$param = str_replace(' ', '', "{$action}_{$entity->getCleanName()}");
		$id = $row->{$entity->getPrimaryKey()};
		return "<td><a href=\"?$param=$id\">
		<span class=\"action $action\" title=\"".$view->__(ucfirst($action), $view::TEXTDOMAIN)."\">
		<span class=\"fa {$this->getIcon($action)} fa-lg\"></span>
		<span class=\"text\">".$this->getActionName($action, $row)."</span>
		</span>
		</a></td>";
	}
	
	/**
	 * Get the text for an action button
	 * @param string $action
	 * @param Mvc\Db\Row $row
	 * @return string
	 */
	public function getActionName($action, $row)
	{
		$view = $this->view;
		return $view->__(ucfirst($action), $view::TEXTDOMAIN);
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
		return isset($icons[$action]) ? 'fa-'.$icons[$action] : 'icon';
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