<?php
namespace Mvc;

class Controller
{
	const NOTICE  = 'notice';
	const ERROR   = 'error';
	const SUCCESS = 'success';
	
	const FLASH_MESSAGES = 'controller_flash_messages';
	
	/**
	 * The application
	 * @var Mvc\Application
	 */
	protected $application;
	
	/**
	 * The view object
	 * @var Mvc\View
	 */
	protected $view;
	
	/**
	 * The view object class
	 * @var string
	 */
	protected $viewClass = 'Mvc\\View';
	
	/**
	 * The name of the layout template to use
	 * @var string
	 */
	protected $layout = 'layout';
	
	/**
	 * The name of the template to use for rendering the current view
	 * @var string
	 */
	protected $template;
	
	/**
	 * A container for controller messages
	 * @var array
	 */
	protected $messages = array();
	
	/**
	 * Store application injected object and initialize view
	 * @param Application $application
	 */
	public function __construct(Application $application)
	{
		$viewClass = $this->viewClass;
		$this->application = $application;
		$this->view = new $viewClass($this);
		
		$session = &$this->getSession(self::FLASH_MESSAGES);
		foreach($session as $type => $messages){
			foreach($messages as $message){
				$this->addMessage($message, $type);
			}
			unset($session[$type]);
		}
	}
	
	public function init()
	{
	}
	
	/**
	 * Change the layout template
	 * @param string $layout
	 */
	public function setLayout($layout)
	{
		$this->layout = $layout;
	}
	
	/**
	 * Get layout template to use
	 * @return string
	 */
	public function getLayout()
	{
		return $this->layout;
	}
	
	/**
	 * Disable layout (view template will render on itself)
	 */
	public function disableLayout()
	{
		$this->setLayout(null);
	}
	
	/**
	 * Set template to render
	 * @param string $template
	 */
	public function setTemplate($template)
	{
		$this->template = $template;
	}
	
	/**
	 * Get current template
	 * @return string
	 */
	public function getTemplate()
	{
		return $this->template;
	}
	
	/**
	 * Get view object
	 * @return \Mvc\View
	 */
	public function getView()
	{
		return $this->view;
	}
	
	/**
	 * Gets the application
	 * @return \Mvc\Application
	 */
	public function getApplication()
	{
		return $this->application;
	}
	
	/**
	 * Generate output
	 */
	public function renderView()
	{
		$this->view->render($this);
	}
	
	/**
	 * Return GET param
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
	 * Return HTTP POST variable
	 * @param string $index
	 * @param mixed $default
	 * @return mixed
	 */
	public function getPost($index, $default = null)
	{
		return isset($_POST[$index]) ? $_POST[$index] : $default;
	}
	
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
	
	public function setOutputType($type)
	{
		switch (strtolower($type)) {
			case 'json':
				$this->setTemplate(null);
				$this->setLayout(null);
				header('Content-type: application/json');
			break;
		}
	}
	
	public function getMessages()
	{
		return $this->messages;
	}
	
	public function addMessage($message, $type = self::NOTICE)
	{
		if(!isset($this->messages[$type])) $this->messages[$type] = array();
		array_push($this->messages[$type], $message);
	}
	
	public function addFlashMessage($message, $type = self::NOTICE)
	{
		$session = &$this->getSession(self::FLASH_MESSAGES);
		$session[$type][] = $message;
	}
	
	public function redirect($url)
	{
		die(header("location:$url"));
	}
	
	public function __call($action, $arguments)
	{
		$controllerShortName = new \ReflectionClass($this);
		$controllerName = preg_replace('/Controller$/', '$1', $controllerShortName->getShortName());
		$actionName = preg_replace('/Action$/', '', $action);
		throw new \Exception("Action $actionName does not exist in $controllerName controller", 404);
	}
}