<?php
/** Comlei Mvc Framework */

namespace Mvc\View\Helper\Nav;

use Mvc\View;
use Mvc\View\Helper\Nav\Menu\Link;

/** View Helper for rendering navigation menus */
class Menu
{
	/**
	 * The navigation menu ID
	 * @var string
	 */
	public $id;
	
	/**
	 * The array of navigation links
	 * @var array
	 */
	public $links;
	
	/**
	 * The ammount of links in the navigation menu
	 * @var int
	 */
	public $linkCount;
	
	/**
	 * The depth of the current menu element
	 * @var int
	 */
	public $depth = 0;
	
	/**
	 * An optional prefix for all generated links
	 * @var string
	 */
	public $prefix = false;
	
	/**
	 * The parent element of the current menu element
	 * @var Mvc\View\Helper\Nav\Menu
	 */
	public $parent = null;
	
	/**
	 * HTML attributes for the current element
	 * @var string
	 */
	public $attributes;
	
	/**
	 * The injected View object
	 * @var Mvc\View
	 */
	public $view;
	
	/**
	 * The XML object that contains the navigation menu definition
	 * @var Mvc\View\Helper\Nav\Menu\Xml
	 */
	protected $menu;
	
	/**
	 * Logo or brand to render aside of the navigation menu
	 * @var string
	 */
	protected $brand = '';
	
	/**
	 * Looks for the menu definition within navigation.xml by ID,
	 * reads the configuration and initializes the navigation menu 
	 * @param View $view
	 * @param string $id
	 * @throws \Exception
	 */
	public function __construct(View $view, $id)
	{
		$this->view = $view;
		$this->id = $id;
		if(!is_file(APPLICATION_PATH.'/navigation.xml')){
			throw new \Exception("Navigation menu '$id' not found");
			return;
		}
		$xml = simplexml_load_file(APPLICATION_PATH.'/navigation.xml', 'Mvc\View\Helper\Nav\Menu\Xml');
		if(!$xml || empty($xml->menu)){
			throw new \Exception("Navigation menu '$id' is not valid XML");
			return;
		}
		foreach($xml->menu as $xmlMenu){
			if((string) $xmlMenu->attributes()->id == $id){
				$this->menu = $xmlMenu;
			}
		}
		if(empty($this->menu)){
			throw new \Exception("Navigation menu '$id' not found in XML map file");
			return;
		}
		$this->linkCount = $this->menu->count();
		if(property_exists($this->menu->attributes(), 'prefix')) $this->prefix = (string) $this->menu->attributes()->prefix;
		$this->attributes = 'id="'.(string) $this->menu->attributes()->id.'"';
		if(property_exists($this->menu->attributes(), 'class')) $this->attributes .= ' class="'.(string) $this->menu->attributes()->class.'"';
	}
	
	/**
	 * Processes the XML page elements
	 * @param SimpleXMLElement $menu
	 */
	private function _processLinks($menu){
		$linkCount = 1;
		foreach($menu->page as $link){
	
			$item = new Link($link, $linkCount, $this->depth+1, $this);
			
			$txt = (string) $link->attributes()->txt;
			if(empty($txt)) continue;
			
			$this->links[] = $item;
			
			if($item->linkCount) $item->_processLinks($link);
			
			if($item->isActive()){
				// mark ancestors as active
				$parent = $item;
				while($parent->parent != null){
					$parent = $parent->parent;
					$parent->active = true;
				}
			}
			
			$linkCount++;
		}
		
	}
	
	/**
	 * Prints the <LI> elements recursively
	 */
	private function _renderLinks()
	{
		foreach($this->links as $link){
			// set <li> ID
			$parent = $this;
			$parentIds = array($this->id);
			while($parent->parent != null){
				$parentIds[] = $parent->parent->id;
				$parent = $parent->parent;
			}
			$parentIds = array_reverse($parentIds);
			$id = 'menu-item-'.implode('-', $parentIds).'-'.$link->id;
			
			// set <li> CLASS
			$class = 'menu-item level-'.$this->depth;
			if($link->index == 1)                        $class .= " first";
			if($link->index == $link->parent->linkCount) $class .= " last";
			if($link->isActive())                        $class .= " active";
			
			echo '<li id="'.$id.'" class="'.$class.'">';
			echo $link->getAnchor();
			// render sublevels recursively
			if($link->linkCount){
				echo '<![if gt IE 6]></a><![endif]><!--[if lte IE 6]><table><tr><td><![endif]--><ul>';
				$link->_renderLinks();
				echo '</ul><!--[if lte IE 6]></td></tr></table></a><![endif]-->';
			}else echo '</a>';
			echo '</li>';
		}
	}
	
	/**
	 * Set the logo or brand to be rendered aside of the navigation menu
	 * @param string $brand
	 * @return Mvc\View\Helper\Nav\Menu
	 */
	public function setBrand($brand)
	{
		$this->brand = '<a class="navbar-brand" href="#">'.$brand.'</a>';
		return $this;
	}
	
	/**
	 * Generate output
	 * @return string
	 */
	public function render()
	{
		ob_start();
		echo '<nav '.$this->attributes.'>
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#'.$this->id.'-collapse">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				'.$this->brand.'
			</div>
			<div class="collapse navbar-collapse" id="'.$this->id.'-collapse">
				<ul class="nav navbar-nav">';
		$this->_processLinks($this->menu);
		$this->_renderLinks();
		echo '</ul></div></nav><!-- end navigation #'.$this->id.' -->';
		return ob_get_clean();
	}
	
	/**
	 * Generate output
	 * @return string
	 */
	public function __toString()
	{
		return $this->render();
	}
	
}
