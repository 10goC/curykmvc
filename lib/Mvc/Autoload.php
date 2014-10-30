<?php
namespace Mvc;

class Autoload
{
	protected $application;
	protected $libraries;
	
	public function __construct(Application $application)
	{
		require_once LIB_PATH . '/Mvc/Config.php';
		$this->application = $application;
		$this->libraries = $this->application->getConfig()->libraries->toArray();
	}
	
	public function registerAutoload()
	{
		spl_autoload_register(function($class){
			$classPart = explode('\\', $class);
			$filename = in_array($classPart[0], $this->libraries) ?
				LIB_PATH . str_replace('\\', DIRECTORY_SEPARATOR, "/$class.php") :
				APPLICATION_PATH . str_replace('\\', DIRECTORY_SEPARATOR, "/$class.php");
			if(file_exists($filename)){
				include $filename;
			}else{
				$type = strpos($filename, 'Controller.php') ? 'Controller' : 'Class';
				throw new \Exception("$type $class does not exist");
			}
		});
	}
}