<?php
/** Comlei Mvc Framework */

namespace Mvc\View\Helper;

use Mvc\View;

/** Abstract View Helper */
abstract class AbstractHelper
{
	/**
	 * The view object
	 * @var Mvc\View
	 */
	protected $view;
	
	/**
	 * An optional array of arguments
	 * @var array
	 */
	protected $args = [];
	
	/**
	 * Receive View object and optional arguments
	 * @param Mvc\View $view
	 * @param array $arguments
	 */
	public function __construct(View $view, $arguments = null)
	{
		$this->view = $view;
		$this->args = $arguments;
	}
	
	public function __toString()
	{
		return $this->render();
	}
	
	public abstract function render();
	
}
