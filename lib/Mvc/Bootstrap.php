<?php
namespace Mvc;

abstract class Bootstrap
{
	/**
	 * 
	 * @var Mvc\Application
	 */
	protected $application;
	
	/**
	 * Set Application object
	 * @param Mvc\Application $application
	 */
	public function __construct(Application $application)
	{
		$this->application = $application;
	}
	
	/**
	 * Get application
	 * @return Mvc\Application
	 */
	public function getApplication()
	{
		return $this->application;
	}
	
	public abstract function bootstrap();
}