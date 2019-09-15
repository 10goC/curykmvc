<?php
/** Comlei Mvc Framework */

namespace Mvc;

/** Provides string translation funcionality */
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
	
	/**
	 * Receives the injected Controller
	 * @param Controller $controller
	 */
	public function __construct(Controller $controller)
	{
		$this->controller = $controller;
		
	}
	
	/**
	 * Translate a string
	 * @param string $str
	 * @param string $textDomain
	 * @return string
	 */
	public function translate($str, $textDomain = 'Application')
	{
		$texts = $this->getTexts();
		return isset($texts[$textDomain][$str]) ? $texts[$textDomain][$str] : $str;
	}
	
	/**
	 * Load a text domain
	 * @param string $domain
	 */
	public function loadTextDomain($domain)
	{
		$libraries = $this->controller->getApplication()->getConfig()->libraries->toArray();
		$filename = in_array($domain, $libraries) ? 
			LIB_PATH."/languages/$domain/$this->lang.php" :
			APPLICATION_PATH."/languages/$this->lang.php";
		if (file_exists($filename)) {
			$texts = include $filename;
			$this->texts[$domain] = $texts;
		}
	}
	
	/**
	 * Get all translation strings
	 * @return array
	 */
	public function getTexts()
	{
		return $this->texts;
	}
	
	/**
	 * Set language
	 * @param string $lang
	 */
	public function setLang($lang)
	{
		$this->lang = $lang;
	}
}