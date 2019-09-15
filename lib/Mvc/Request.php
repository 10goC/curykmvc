<?php
/** Comlei Mvc Framework */

namespace Mvc;

/** A class for parsing the HTTP request */
class Request
{
	/**
	 * The application object
	 * @var Mvc\Application
	 */
	protected $application;
	
	/**
	 * An array containing the different parts of the analyzed request
	 * @var array
	 */
	protected $parts;
	
	/**
	 * Receives an Application instance and stores it into a variable
	 * @param Application $application
	 */
	public function __construct(Application $application)
	{
		$this->application = $application;
	}
	
	/**
	 * Analyize the HTTP request and parse into an array
	 * @return array
	 */
	public function getParts()
	{
		if ($this->parts === null) {
			$request = $_SERVER['REQUEST_URI'];
			if (strpos($request, '?')) {
				$request = strstr($request, '?', 1);
			}
			
			try {
				$basepath = $this->application->getConfig()->basepath;
			} catch (\Exception $e) {
				$basepath = '';
			}
			if ($basepath) {
				$request = preg_replace("#^$basepath#", '', $request);
			}
			
			$parts = explode('/', trim($request, '/'));
			
			// Add index for empty controller name
			if (empty($parts[0])) {
				$parts[0] = 'index';
			}
			// Add index for empty action name
			if (empty($parts[1])) {
				$parts[1] = 'index';
			}
			if (!empty($_SERVER['QUERY_STRING'])) {
				parse_str($_SERVER['QUERY_STRING'], $qs);
				foreach ($qs as $key => $value) {
					$parts[] = $key;
					$parts[] = $value;
				}
			}
			
			// Remove html extension
			if (strpos($parts[0], '.html')) $parts[0] = substr($parts[0], 0, -5);
			if (strpos($parts[1], '.html')) $parts[1] = substr($parts[1], 0, -5);
			$this->parts = $parts;
		}
		return $this->parts;
	}
}