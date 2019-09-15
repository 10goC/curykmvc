<?php
/** Comlei Mvc Framework */

namespace Mvc;

/** Autoload classes */
class Autoload
{
	/**
	 * The Application object
	 * @var Mvc\Application
	 */
	protected $application;
	
	/**
	 * The namespaces present in LIB_PATH
	 * @var array
	 */
	protected $libraries;
	
	/**
	 * Initialize object and load configuration
	 * @param Application $application
	 */
	public function __construct(Application $application)
	{
		require_once LIB_PATH . '/Mvc/Config.php';
		$this->application = $application;
		$this->libraries = $this->application->getConfig()->libraries->toArray();
	}
	
	/**
	 * Register autoload
	 */
	public function registerAutoload()
	{
		spl_autoload_register(array($this, 'autoload'));
	}
	
	/**
	 * Autoload a class
	 * @param string $class
	 * @throws \Exception
	 */
	public function autoload($class)
	{
		$classPart = explode('\\', $class);
		$filename = in_array($classPart[0], $this->libraries) ?
		LIB_PATH . str_replace('\\', DIRECTORY_SEPARATOR, "/$class.php") :
		APPLICATION_PATH . str_replace('\\', DIRECTORY_SEPARATOR, "/$class.php");
		if (file_exists($filename)) {
			include $filename;
		} else {
			$type = strpos($filename, 'Controller.php') ? 'Controller' : 'Class';
			throw new \Exception("$type $class does not exist");
		}
	}
}