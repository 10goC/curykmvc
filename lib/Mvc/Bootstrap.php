<?php
/** Comlei Mvc Framework */

namespace Mvc;

/** Abstract class for defining actions to be performed before any Controller Action */
abstract class Bootstrap
{
	/**
	 * The injected Application object
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
	
	/**
	 * Abstract method to be extended with the required funcionality
	 */
	public abstract function bootstrap();
}