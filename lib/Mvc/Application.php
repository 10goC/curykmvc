<?php
namespace Mvc;

use Application\Bootstrap;

class Application
{
	/**
	 * The Database connection
	 * @var Mvc\Db\Adapter\AdapterInterface
	 */
	protected $db;
	
	/**
	 * The application configuration object
	 * @var Mvc\Config
	 */
	protected $config;
	
	/**
	 * Default database adapter class
	 * @var string
	 */
	protected $defaultDbAdapter = 'Mvc\Db\Adapter\MysqliAdapter';
	
	/**
	 * The current controller
	 * @var Mvc\Controller
	 */
	protected $controller;
	
	/**
	 * An array containing the different parts of the HTTP request
	 * @var array
	 */
	protected  $request;
	
	/**
	 * Start the application
	 */
	public function run()
	{
		// Autoload classes
		require_once LIB_PATH . '/Mvc/Autoload.php';
		$autoload = new Autoload();
		$autoload->registerAutoload();
		
		// Get request
		$request = new Request($this);
		$requestPart = $request->getParts();
		$controllerRequest = empty($requestPart[0]) ? 'index' : $requestPart[0];
		$actionRequest = empty($requestPart[1]) ? 'index' : $requestPart[1];
		$this->request = array(
			'controller' => $controllerRequest,
			'action' => $actionRequest
		);
		if(count($requestPart > 2)){
			for($i = 2; $i < count($requestPart); $i += 2){
				if(isset($requestPart[$i+1])){
					$this->request[$requestPart[$i]] = $requestPart[$i + 1];
				}
			}
		}
		$controllerClass = 'Application\\Controller\\' . ucfirst($this->dashesToCamelCase($controllerRequest)) . 'Controller';
		$action = $this->dashesToCamelCase($actionRequest) . 'Action';
		
		// Load Bootstrap
		$bootstrap = new \Application\Bootstrap($this);
		
		try {
			// Set controller
			$this->controller = new $controllerClass($this);
			$this->controller->setTemplate("$controllerRequest/$actionRequest");
			
			// Bootstrap
			$bootstrap->bootstrap();
			
			// Do action
			$this->controller->$action();
		} catch (\Exception $e) {
			if($e->getCode()){
				http_response_code($e->getCode());
			}
			if(!$this->controller){
				$this->controller = new \Mvc\Controller\ErrorController($this);
				// Retry boostrap
				try {
					$bootstrap->bootstrap();
				} catch (\Exception $e) {
					// :(
				}
			}
			$this->controller->setTemplate('error/error');
			$this->controller->getView()->exception = $e;
		}
		
		// Render view
		$this->controller->renderView();
	}
	
	/**
	 * Get application configuration
	 * @return Mvc\Config
	 */
	public function getConfig()
	{
		if(!$this->config){
			$global = include APPLICATION_PATH . '/config.global.php';
			$local = include APPLICATION_PATH . '/config.local.php';
			$this->config = new Config(array_merge($global, $local));
		}
		return $this->config;
	}
	
	/**
	 * Get controller
	 * @return \Mvc\Controller
	 */
	public function getController()
	{
		return $this->controller;
	}
	
	/**
	 * Gets the database connection and creates one if none exists
	 * @return \Mvc\Db\Adapter\AdapterInterface
	 */
	public function getDb()
	{
		if(!$this->db){
			$dbConfig = $this->getConfig()->db;
			$dbAdapterClass = isset($dbConfig->adapter) ?
				'Mvc\Db\Adapter\\'.ucfirst(strtolower($dbConfig->adapter)).'Adapter' :
				$this->defaultDbAdapter;
			$this->db = new $dbAdapterClass($dbConfig);
		}
		return $this->db;
	}
	
	/**
	 * Get an array of the different parts of the HTTP request
	 * @return array
	 */
	public function getRequest()
	{
		return $this->request;
	}
	
	/**
	 * Convert a string from words separated by dashes to camel case
	 * @param string $string
	 * @param bool $capitalizeFirstCharacter
	 * @return string
	 */
	public function dashesToCamelCase($string, $capitalizeFirstCharacter = false) {
		return preg_replace_callback("/-[a-zA-Z]/", function(){
			return strtoupper($matches[0][1]);
		}, $string);
	}

}