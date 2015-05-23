<?php
/** Comlei Mvc Framework */

namespace Mvc;

use Application\Bootstrap;

/** The core object of the framework */
class Application
{
	const TEXTDOMAIN = 'Application';
	
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
		$autoload = new Autoload($this);
		$autoload->registerAutoload();
		
		// Get request
		$request = new Request($this);
		$requestPart = $request->getParts();
		$controllerRequest = $requestPart[0];
		$actionRequest = $requestPart[1];
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
			$this->controller->init();
			if(
				!method_exists($this->controller, $action) && 
				isset($this->request['action']) &&
				method_exists($this->controller, $this->request['action'] . 'Action')
			){
				// Allow action to be passed in query string
				$action = $this->request['action'] . 'Action';
				$this->controller->setTemplate("$controllerRequest/{$this->request['action']}");
			}
			$this->controller->$action();
			
			// Render view
			ob_start();
			$this->controller->renderView();
			$output = ob_get_clean();
			echo $output;
		} catch (\Exception $e) {
			$responseCode = ($e->getCode()) ?: 404;
			if(function_exists('http_response_code')){
				http_response_code($responseCode);
			}else{
				header($_SERVER['SERVER_PROTOCOL'] . ' ' . $responseCode);
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
			$this->controller->setLayout('error');
			$this->controller->setTemplate('error/error');
			$this->controller->getView()->exception = $e;
			
			// Render view
			@ob_clean();
			$this->controller->renderView();
		}
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
		return preg_replace_callback("/-[a-zA-Z]/", function($matches){
			return strtoupper($matches[0][1]);
		}, $string);
	}

}