<?php
/** Comlei Mvc Framework */

namespace Mvc\View\Helper\Nav\Menu;

/** An exension of SimpleXMLElement class that adds backwards compatibility PHP versions < 5.3 */
class Xml extends \SimpleXMLElement
{
	/**
	 * adds count method for PHP < 5.3
	 * @see SimpleXMLElement::count()
	 */
	public function count()
	{
		if(method_exists('SimpleXMLElement', 'count'))
			return parent::count();
		return count($this->children());
	}
}