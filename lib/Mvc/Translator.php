<?php
namespace Mvc;

class Translator
{
	/**
	 * The language
	 * @var string
	 */
	protected $lang = 'en_US';
	
	/**
	 * The controller
	 * @var Mvc\Controller
	 */
	protected $controller;
	
	/**
	 * The translations array
	 * @var array
	 */
	protected $texts;
	
	public function __construct(Controller $controller)
	{
		$this->controller = $controller;
		
	}
	
	public function translate($str, $textDomain = 'Application')
	{
		$texts = $this->getTexts();
		return isset($texts[$textDomain][$str]) ? $texts[$textDomain][$str] : $str;
	}
	
	public function loadTextDomain($domain)
	{
		$libraries = $this->controller->getApplication()->getConfig()->libraries->toArray();
		$filename = in_array($domain, $libraries) ? 
			LIB_PATH."/languages/$domain/$this->lang.php" :
			APPLICATION_PATH."/languages/$this->lang.php";
		if(file_exists($filename)){
			$texts = include $filename;
			$this->texts[$domain] = $texts;
		}
	}
	
	public function getTexts()
	{
		return $this->texts;
	}
	
	public function setLang($lang)
	{
		$this->lang = $lang;
	}
}