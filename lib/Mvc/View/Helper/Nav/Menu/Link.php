<?php
namespace Mvc\View\Helper\Nav\Menu;

use Mvc\View\Helper\Nav\Menu;

class Link extends Menu
{
	var $active = false;
	var $class = false;
	var $href = false;
	var $target = false;
	var $id;
	var $index;
	var $depth;
	/**
	 * Parent element
	 * @var Menu
	 */
	var $parent;
	var $txt;
	var $url;

	public function __construct($link, $index, $depth, Menu $parent)
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
		if(0){ // URLS_AUTO_NEST){
			// check all depths
			$request = explode('/', $request);
			$matches = 0;
			for($requestDepth = 0; $requestDepth < count($this->_currentPage) && $requestDepth < count($request); $requestDepth++){
				if($request[$requestDepth] == $this->_currentPage[$requestDepth])
					$matches++;
			}
			return $matches == count($this->_currentPage);
		}else{
			// check against request string
			if($request == $this->url) return true;
		}

	}

	/**
	 * Decides whether to use the url parameter or the txt parameter
	 * of the SimpleXMLElement $link to form the href parameter of the anchor element
	 * @param SimpleXMLElement $link
	 * @return string
	 */
	private function _renderLiAnchorHref($link){
		return property_exists($link->attributes(), 'url') ?
		$this->parent->view->serverUrl((string) $link->attributes()->url, $this->prefix) :
		$this->parent->view->serverUrl($this->cleanUrl($this->txt), $this->prefix);
	}

	/**
	 * Returns the <A> opening tag
	 * @return string
	 */
	public function getAnchor()
	{
		// print <A>
		$out = '<a';
		if($this->href)   $out .= ' href="'.$this->href.'"';
		if($this->target) $out .= ' target="'.$this->target.'"';
		return $out.'>'.$this->txt;
	}
	
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
	
	public function getRequest()
	{
		$request = $this->parent->view->removeQs();
		try {
			$baseUrl = $this->parent->view->getController()->getApplication()->getConfig()->baseUrl;
		} catch (\Exception $e) {
			$baseUrl = '';
		}
		if($baseUrl){
			$request = preg_replace("#^$baseUrl#", '', $request);
		}
		return trim($request, '/');
	}

}