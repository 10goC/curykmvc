<?php
namespace Mvc\View\Helper\Nav\Menu;

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