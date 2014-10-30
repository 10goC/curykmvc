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
				$baseUrl = $this->application->getConfig()->baseUrl;
			} catch (\Exception $e) {
				$baseUrl = '';
			}
			if($baseUrl){
				$request = preg_replace("#^$baseUrl#", '', $request);
			}
			$parts = explode('/', trim($request, '/'));
			
			// Add index for empty controller name
			if(empty($parts[0])){
				$parts[0] = 'index';
			}
			// Add index for empty action name
			if(empty($parts[1])){
				$parts[1] = 'index';
			}
			$lastPart = end($parts);
			if($lastPart[0] == '?'){
				parse_str(substr(array_pop($parts), 1), $qs);
				foreach($qs as $key => $value){
					$parts[] = $key;
					$parts[] = $value;
				}
			}
			
			// Remove html extension
			if(strpos($parts[0], '.html')) $parts[0] = substr($parts[0], 0, -5);
			if(strpos($parts[1], '.html')) $parts[1] = substr($parts[1], 0, -5);
			$this->parts = $parts;
		}
		return $this->parts;
	}
}