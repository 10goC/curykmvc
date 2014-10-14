<?php
namespace Mvc;

class Autoload
{
	public function registerAutoload()
	{
		spl_autoload_register(function($class){
			$filename = strpos($class, 'Mvc\\') === 0 ?
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