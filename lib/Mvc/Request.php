<?php
namespace Mvc;

class Request
{
	protected $application;
	protected $parts;
	
	public function __construct(Application $application)
	{
		$this->application = $application;
	}
	
	public function getParts()
	{
		if($this->parts === null){
			$request = $_SERVER['REQUEST_URI'];
			try {
				$config = $this->application->getConfig()->baseUrl;
			} catch (\Exception $e) {
				$config = '';
			}
			if($config){
				$request = preg_replace("#^$config#", '', $request);
			}
			$this->parts = explode('/', trim($request, '/'));
		}
		return $this->parts;
	}
}