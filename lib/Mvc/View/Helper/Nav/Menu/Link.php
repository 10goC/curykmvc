<?php
/** Comlei Mvc Framework */

namespace Mvc\View\Helper\Nav\Menu;

use Mvc\View\Helper\Nav\Menu;

/** A navigation menu link */
class Link extends Menu
{
	/**
	 * Whether the link corresponds to the current page
	 * @var boolean
	 */
	var $active = false;
	
	/**
	 * CSS class for the menu item
	 * @var string
	 */
	var $class = false;
	
	/**
	 * Href HTML attribute
	 * @var string
	 */
	var $href = false;
	
	/**
	 * Target HTML attribute
	 * @var string
	 */
	var $target = false;
	
	/**
	 * Menu item ID
	 * @var string
	 */
	var $id;
	
	/**
	 * The order number
	 * @var int
	 */
	var $index;
	
	/**
	 * The depth of the current menu element
	 * @var int
	 */
	var $depth;
	
	/**
	 * Parent element
	 * @var Menu
	 */
	var $parent;
	
	/**
	 * The text to display
	 * @var string
	 */
	var $txt;
	
	/**
	 * The target URL
	 * @var string
	 */
	var $url;

	/**
	 * Initialize object based on XML definition, order number and depth
	 * @param Xml $link
	 * @param int $index
	 * @param int $depth
	 * @param Menu $parent
	 */
	public function __construct(Xml $link, $index, $depth, Menu $parent)
	{
		// set parent
		$this->parent = $parent;
		$this->prefix = $parent->prefix;
		$txt = (string)  $link->attributes()->txt;
		$url = (string) $link->attributes()->url;
		$this->index = $index;
		$this->depth = $depth;
		$this->linkCount = $link->count();
		$this->url = property_exists($link->attributes(), 'url') ? $url : $this->cleanUrl($txt);
		$this->id = $this->cleanUrl($txt);
		foreach($link->attributes() as $attr => $value){
			$this->$attr = (string) $value;
		}
		// set <a> HREF
		$this->href = !property_exists($link->attributes(), 'url') || strtolower($url) != 'false' ?
		$this->_renderLiAnchorHref($link) : false;
	}

	/**
	 * Determines whether current item is active
	 * according to the html request
	 * @return boolean
	 */
	public function isActive()
	{
		if($this->active) return true;
		// false if external link
		if(preg_match('#^http[s]?://#i', $this->url)) return false;
		$request = $this->getRequest();
		
		// check against request string
		if($request == $this->prefix.$this->url) return true;
	}

	/**
	 * Decides whether to use the url parameter or the txt parameter
	 * of the SimpleXMLElement $link to form the href parameter of the anchor element
	 * @param SimpleXMLElement $link
	 * @return string
	 */
	private function _renderLiAnchorHref($link){
		$url = property_exists($link->attributes(), 'url') ?
			(string) $link->attributes()->url :
			$this->cleanUrl($this->txt)
		;
		if(preg_match('#^http[s]?://#i', $url)) return $url;
		return $this->parent->view->baseUrl($this->prefix . $url);
	}

	/**
	 * Returns the <A> opening tag
	 * @return string
	 */
	public function getAnchor()
	{
		// print <A>
		$out = '<a';
		$txt = method_exists($this->parent->view, '__') ? $this->parent->view->__($this->txt) : $this->txt;
		if($this->href)   $out .= ' href="'.$this->href.'"';
		if($this->target) $out .= ' target="'.$this->target.'"';
		return $out.'>'.$txt;
	}
	
	/**
	 * Clean string in order to obtain a valid URL
	 * @param string $url
	 * @return string
	 */
	public function cleanUrl($url)
	{
		return strtolower(preg_replace(array(
			'/[^a-z0-9 -_]/i',
			'/ +/'
		), array(
			'',
			'-'
		), $url));
	}
	
	/**
	 * Get HTTP request
	 * @return string
	 */
	public function getRequest()
	{
		$request = $this->parent->view->removeQs();
		try {
			$basepath = $this->parent->view->getController()->getApplication()->getConfig()->basepath;
		} catch (\Exception $e) {
			$basepath = '';
		}
		if($basepath){
			// Remove Basepath
			$request = preg_replace("#^$basepath#", '', $request);
		}
		// Remove html extension
		$request = preg_replace('/\.html$/', '', $request);
		
		// Remove final slash
		return trim($request, '/');
	}

}