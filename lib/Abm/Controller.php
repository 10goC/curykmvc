<?php
/** Comlei Mvc Framework */

namespace Abm;

use Mvc\Application;
use Mvc\Controller as MvcController;
use Mvc\Translator;

/** You must extend this controller to use Abm entities */
class Controller extends MvcController
{
	/**
	 * The class name for the View object
	 * @var string
	 */
	protected $viewClass = 'Abm\\View';
	
	/**
	 * The current language
	 * @var string
	 */
	protected $lang = 'en_US';
	
	/**
	 * The Translator object
	 * @var Mvc\Translator
	 */
	protected $translator;
	
	/**
	 * Create a translator instance and initialize controller.
	 * @param Application $application the application gets automatically injected into the controller
	 */
	public function __construct(Application $application)
	{
		parent::__construct($application);
		$this->translator = new Translator($this);
	}
	
	/**
	 * Set language and load Abm and Application text domains.
	 * @param string $lang
	 */
	public function setLang($lang)
	{
		$this->lang = $lang;
		$this->translator->setLang($lang);
		$this->translator->loadTextDomain(View::TEXTDOMAIN);
		$this->translator->loadTextDomain(Application::TEXTDOMAIN);
	}
	
	/**
	 * Get current language.
	 * @return string
	 */
	public function getLang()
	{
		return $this->lang;
	}
	
	/**
	 * Get the translator object.
	 * @return Mvc\Translator
	 */
	public function getTranslator()
	{
		return $this->translator;
	}
	
}