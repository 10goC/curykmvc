<?php
namespace Abm;

use Mvc\Application;
use Mvc\Controller as MvcController;
use Mvc\Translator;

class Controller extends MvcController
{
	protected $viewClass = 'Abm\\View';
	protected $lang = 'en_US';
	protected $translator;
	protected $cache = array();
	
	public function __construct(Application $application)
	{
		parent::__construct($application);
		$this->translator = new Translator($this);
	}
	
	public function setLang($lang)
	{
		$this->lang = $lang;
		$this->translator->setLang($lang);
		$this->translator->loadTextDomain(View::TEXTDOMAIN);
		$this->translator->loadTextDomain(Application::TEXTDOMAIN);
	}
	
	public function getLang()
	{
		return $this->lang;
	}
	
	public function getTranslator()
	{
		return $this->translator;
	}
	
	public function getCache($section = null)
	{
		if($section){
			if(!isset($this->cache[$section])){
				$this->cache[$section] = array();
			}
			return $this->cache[$section];
		}
		return $this->cache;
	}
	
	public function addToCache($section, $values)
	{
		$this->cache[$section] = array_merge($this->cache[$section], $values);
	}
}