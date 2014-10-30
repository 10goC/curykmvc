<?php
namespace Mvc\View\Helper\Nav;

use Mvc\View;
use Mvc\View\Helper\Nav\Menu\Link;

class Menu
{
	public $id;
	public $links;
	public $linkCount;
	public $depth = 0;
	public $prefix = false;
	public $parent = null;
	public $attributes;
	public $view;
	protected $menu;
	protected $brand = '';
	protected $prepend = '';
	protected $_currentPage;
	
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
			
			echo '<li id = "'.$id.'" class="'.$class.'">';
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
	
	public function prepend($prepend)
	{
		$this->prepend = $prepend;
		return $this;
	}
	
	public function setBrand($brand)
	{
		$this->brand = '<a class="navbar-brand" href="#">'.$brand.'</a>';
		return $this;
	}
	
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
	
	public function __toString()
	{
		return $this->render();
	}
	
}
