<?php
/** Comlei Mvc Framework */

namespace Abm;

use Mvc\Application;
use Mvc\Controller as MvcController;
use Mvc\Translator;

/** You must extend this controller to use Abm entities */
class Controller extends MvcController
{
	/**
	 * The class name for the View object
	 * @var string
	 */
	protected $viewClass = 'Abm\\View';
	
}