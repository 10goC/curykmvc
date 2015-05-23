<?php
/** Comlei Mvc Framework */

namespace Abm;

use Abm\View\Helper\EntityList;
use Mvc\Application;
use Mvc\View as MvcView;

/** An extension of the Mvc\View class with a couple additional helpers for CRUD actions */
class View extends MvcView
{
	const TEXTDOMAIN = 'Abm';
	
	/**
	 * The CSS class name for the listing container element
	 * @var string
	 */
	public $listClass = 'list table';
	
	/**
	 * Class name of the helper to use for the entityAdminForm method
	 * @var string
	 */
	public $entityAdminFormHelperClass = 'Abm\View\Helper\EntityAdminForm';
	
	/**
	 * Class name of the helper to use for the entityList method
	 * @var string
	 */
	public $entityListHelperClass = 'Abm\View\Helper\EntityList';
	
	/**
	 * Renders one or more forms according to the required actions for one entity (add, delete, etc)
	 * @param Entity $entity
	 * @param array $options
	 * @return Abm\View\Helper\EntityAdminForm
	 */
	public function entityAdminForm(Entity $entity, $options = array())
	{
		$helperClass = isset($options['helper']) ? $options['helper'] : $this->entityAdminFormHelperClass;
		$helper = new $helperClass($this, $entity);
		if(isset($options['legend'])){
			$helper->legend = $options['legend'];
		}
		return $helper;
	}
	
	/**
	 * Renders a listing of database records for one entity
	 * @param Entity $entity
	 * @param array $options
	 * @return Abm\View\Helper\EntityList
	 */
	public function entityList(Entity $entity, $options = array())
	{
		$helperClass = isset($options['helper']) ? $options['helper'] : $this->entityListHelperClass;
		$helper = new $helperClass($this, $entity, $options);
		if(isset($options['listClass'])){
			$this->listClass = $options['listClass'];
		}
		return $helper;
	}
	
}