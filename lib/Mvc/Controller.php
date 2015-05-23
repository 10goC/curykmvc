<?php
/** Comlei Mvc Framework */

namespace Mvc;

use Abm\View as AbmView;

/** The controller class. Where all apllication actions live. All controllers should extend this class. */
class Controller
{
	const NOTICE  = 'notice';
	const ERROR   = 'error';
	const SUCCESS = 'success';
	
	const FLASH_MESSAGES = 'controller_flash_messages';
	
	/**
	 * The application.
	 * @var Mvc\Application
	 */
	protected $application;
	
	/**
	 * The view object.
	 * @var Mvc\View
	 */
	protected $view;
	
	/**
	 * The view object class.
	 * @var string
	 */
	protected $viewClass = 'Mvc\\View';
	
	/**
	 * The name of the layout template to use.
	 * @var string
	 */
	protected $layout = 'layout';
	
	/**
	 * The name of the template to use for rendering the current view.
	 * @var string
	 */
	protected $template;
	
	/**
	 * A container for controller messages.
	 * @var array
	 */
	protected $messages = array();
	
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
	 * Store application injected object, initialize view and create a translator instance.
	 * @param Mvc\Application $application the application gets automatically injected into the controller
	 */
	public function __construct(Application $application)
	{
		$viewClass = $this->viewClass;
		$this->application = $application;
		$this->view = new $viewClass($this);
		$this->translator = new Translator($this);
		
		$session = &$this->getSession(self::FLASH_MESSAGES);
		foreach($session as $type => $messages){
			foreach($messages as $message){
				$this->addMessage($message, $type);
			}
			unset($session[$type]);
		}
	}
	
	/** This method runs before any action in the controller. */
	public function init()
	{
	}
	
	/**
	 * Change the layout template.
	 * @param string $layout
	 */
	public function setLayout($layout)
	{
		$this->layout = $layout;
	}
	
	/**
	 * Get layout template to use.
	 * @return string
	 */
	public function getLayout()
	{
		return $this->layout;
	}
	
	/**
	 * Disable layout (view template will be rendered alone).
	 */
	public function disableLayout()
	{
		$this->setLayout(null);
	}
	
	/**
	 * Set template to render.
	 * @param string $template
	 */
	public function setTemplate($template)
	{
		$this->template = $template;
	}
	
	/**
	 * Get current template.
	 * @return string
	 */
	public function getTemplate()
	{
		return $this->template;
	}
	
	/**
	 * Get the view object.
	 * @return \Mvc\View
	 */
	public function getView()
	{
		return $this->view;
	}
	
	/**
	 * Gets the application.
	 * @return \Mvc\Application
	 */
	public function getApplication()
	{
		return $this->application;
	}
	
	/**
	 * Generate output.
	 */
	public function renderView()
	{
		$this->view->render($this);
	}
	
	/**
	 * Return GET param.
	 * @param string $param
	 * @param mixed $default
	 * @return mixed
	 */
	public function getParam($param, $default = null)
	{
		$request = $this->getApplication()->getRequest();
		return isset($request[$param]) ? $request[$param] : $default;
	}
	
	/**
	 * Return HTTP POST variable.
	 * @param string $index
	 * @param mixed $default
	 * @return mixed
	 */
	public function getPost($index, $default = null)
	{
		return isset($_POST[$index]) ? $_POST[$index] : $default;
	}
	
	/**
	 * Get a reference to $_SESSION superglobal. Call it as reference, i.e: $session = &$this->getSession().
	 * @param string $section the name of the section to fetch. If the key does not exist in SESSION array, create it and return an empty array.
	 * @return array|string
	 */
	public function &getSession($section = null)
	{
		if(!isset($_SESSION)){
			@session_start();
		}
		if($section){
			if(!isset($_SESSION[$section])){
				$_SESSION[$section] = array();
			}
			return $_SESSION[$section];
		}
		return $_SESSION;
	}
	
	/**
	 * Change the output for the action.
	 * @param string $type The desired output type. Possible values: json|attachment
	 * @param array|string $options The options will depend on the type, i.e: in case of attachment type,the options variable must be the attachment filename.
	 */
	public function setOutputType($type, $options = null)
	{
		switch (strtolower($type)) {
			case 'json':
				$this->setTemplate(null);
				$this->setLayout(null);
				header('Content-type: application/json');
				break;
			case 'xml':
				$this->setLayout(null);
				header('Content-type: text/xml; charset=utf-8');
				break;
			case 'attachment':
				$filename = $options ? $options : 'download';
				header('Content-type: application/octet-stream');
				header("Content-Description: File Transfer");
				header('Content-Disposition: attachment; filename="'.$filename.'"');
				break;
		}
	}
	
	/**
	 * Get controller messages.
	 * @return array
	 */
	public function getMessages()
	{
		return $this->messages;
	}
	
	/**
	 * Add a message to the controller.
	 * @param string $message
	 * @param string $type Can be anything. The most common types are defined as class constants: NOTICE|ERROR|SUCCESS
	 */
	public function addMessage($message, $type = self::NOTICE)
	{
		if(!isset($this->messages[$type])) $this->messages[$type] = array();
		array_push($this->messages[$type], $message);
	}
	
	/**
	 * Add a message to be displayed on the next action.
	 * @param string $message
	 * @param string $type can be anything. The most common types are defined as class constants: NOTICE|ERROR|SUCCESS
	 */
	public function addFlashMessage($message, $type = self::NOTICE)
	{
		$session = &$this->getSession(self::FLASH_MESSAGES);
		$session[$type][] = $message;
	}
	
	/**
	 * Set language and load Abm and Application text domains.
	 * @param string $lang
	 */
	public function setLang($lang)
	{
		$this->lang = $lang;
		$this->translator->setLang($lang);
		$this->translator->loadTextDomain(AbmView::TEXTDOMAIN);
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
	
	/**
	 * Redirect the browser to another URL.
	 * @param string $url
	 */
	public function redirect($url)
	{
		die(header("location:$url"));
	}
	
	/**
	 * Throw an exception when a certain method does not exist in controller.
	 * The exception message assumes that the called method is an non-existent action (probable cause: bad URL). 
	 * @param string $action
	 * @param array $arguments
	 * @throws \Exception
	 */
	public function __call($action, $arguments)
	{
		$controllerShortName = new \ReflectionClass($this);
		$controllerName = preg_replace('/Controller$/', '$1', $controllerShortName->getShortName());
		$actionName = preg_replace('/Action$/', '', $action);
		throw new \Exception("Action $actionName does not exist in $controllerName controller", 404);
	}
}