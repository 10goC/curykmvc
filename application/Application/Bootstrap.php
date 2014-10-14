<?php
namespace Application;

use Mvc\Bootstrap as MvcBootstrap;

class Bootstrap extends MvcBootstrap
{
	public function bootstrap()
	{
		$config = $this->getApplication()->getConfig();
		$this->getApplication()->getController()->getView()->title = $config->site->title;
		$this->getApplication()->getController()->getView()->description = $config->site->description;
		$this->getApplication()->getController()->getView()->skin = 'default';
	}
}