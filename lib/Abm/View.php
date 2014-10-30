<?php
namespace Abm;

use Abm\View\Helper\EntityList;

use Abm\View\Helper\EntityAdminForm;

use Mvc\View as MvcView;

class View extends MvcView
{
	const TEXTDOMAIN = 'Abm';
	
	public $listClass = 'list table';
	
	public function entityAdminForm(Entity $entity)
	{
		$helper = new EntityAdminForm($this, $entity);
		return $helper;
	}
	
	public function entityList(Entity $entity, $options = array())
	{
		$helper = new EntityList($this, $entity, $options);
		return $helper;
	}
	
	public function __($str)
	{
		return $this->getController()->getTranslator()->translate($str, self::TEXTDOMAIN);
	}
	
}