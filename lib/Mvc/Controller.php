<?php
namespace Mvc;

class Controller
{
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
	 * Store application injected object and initialize view
	 * @param Application $application
	 */
	public function __construct(Application $application)
	{
		$this->application = $application;
		$this->view = new View($this);
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
	
	public function __call($action, $arguments)
	{
		$controllerShortName = new \ReflectionClass($this);
		$controllerName = preg_replace('/Controller$/', '$1', $controllerShortName->getShortName());
		$actionName = preg_replace('/Action$/', '', $action);
		throw new \Exception("Action $actionName does not exist in $controllerName controller", 404);
	}
}