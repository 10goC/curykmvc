<?php
/** Comlei Mvc Framework */

namespace Mvc;

use Mvc\View\Helper\Nav\Menu;

/** The standard View object */
class View
{
	/**
	 * The controller
	 * @var \Mvc\Controller
	 */
	protected $controller;
	
	/**
	 * The generated output
	 * @var string
	 */
	protected $content;
	
	/**
	 * HTML tag for wrapping messages
	 * @var string
	 */
	public $messagesOuterTag = 'div';
	
	/**
	 * CSS class to apply to the HTML messages container element
	 * @var string
	 */
	public $messagesOuterClass = 'messages';
	
	/**
	 * HTML tag for individual messages
	 * @var string
	 */
	public $messagesInnerTag = 'p';
	
	/**
	 * CSS class to apply to individual messages
	 * @var string
	 */
	public $messagesInnerClass = 'message';
	
	/**
	 * A string for separating messages between each other
	 * @var string
	 */
	public $messagesSeparator = PHP_EOL;
	
	/**
	 * Injects the Controller object
	 * @param Controller $controller
	 */
	public function __construct(Controller $controller)
	{
		$this->controller = $controller;
	}
	
	/**
	 * Set content to output
	 * @param string $content
	 */
	public function setContent($content)
	{
		$this->content = $content;
	}
	
	/**
	 * Generate output
	 * @param Controller $controller
	 */
	public function render(Controller $controller)
	{
		extract(get_object_vars($this));
		if($template = $controller->getTemplate()){
			$templateUrl = APPLICATION_PATH . "/view/$template.phtml";
			if(file_exists($templateUrl)){
				ob_start();
				include( $templateUrl );
				$this->content = ob_get_clean();
			}else{
				throw new \Exception("Template $template not found", 404);
			}
		}
		if($layout = $controller->getLayout()){
			$layoutUrl = APPLICATION_PATH . "/view/layout/$layout.phtml";
			if(file_exists($layoutUrl)){
				include( $layoutUrl );
			}else{
				throw new \Exception("Layout $layout not found", 404);
			}
		}else{
			echo $this->content;
		}
	}
	
	/**
	 * Get the Controller object
	 * @return Mvc\Controller
	 */
	public function getController()
	{
		return $this->controller;
	}
	
	/**
	 * Return null for any inexistent variable
	 * @param string $var
	 * @return NULL
	 */
	public function __get($var)
	{
		return null;
	}
	
	/**
	 * Autoload view helper
	 * @param string $name
	 * @param array $args
	 * @throws \Exception
	 * @return Mvc\View\Helper
	 */
	public function __call($name, $args = null)
	{
		try {
			$helperClass = (string) $this->getController()->getApplication()->getConfig()->view->helpers->$name;
			$helper = new $helperClass($this, $args);
			return $helper;
		} catch (\Exception $e) {
			throw new \Exception("View helper $name not found", 500, $e);
		}
	}
	
	/**
	 * Generates a full URL
	 * @param string $url
	 * @return string
	 */
	public function serverUrl($url = '/')
	{
		$protocol = preg_match('/https/i', $_SERVER['SERVER_PROTOCOL']) ? 'https' : 'http';
		return $protocol . '://' . $_SERVER['SERVER_NAME'] . $this->baseUrl($url);
	}
	
	/**
	 * Generate a link appending the basepath if present in config
	 * @param string $url
	 * @return string
	 */
	public function baseUrl($url = '/')
	{
		try {
			$basepath = $this->controller->getApplication()->getConfig()->basepath;
		} catch (\Exception $e) {
			$basepath = '';
		}
		return $basepath . '/' . trim($url, '/');
	}
	
	/**
	 * Remove the Query String portion of the current Request URI
	 * @return string
	 */
	public function removeQs()
	{
		$request = str_replace('?'.$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
		return $request;
	}
	
	/**
	 * Add or update one or more parameters in the current Query String
	 * @param array $params an associative array with key value pairs
	 * @return string
	 */
	public function addQsParams(array $params)
	{
		parse_str($_SERVER['QUERY_STRING'], $qs);
		$qs = array_merge($qs, $params);
		return empty($qs) ? '' : '?'.http_build_query($qs);
	}
	
	/**
	 * Get Navigation Menu Helper
	 * @param string $id Must be defined in 
	 * APPLICATION_PATH . '/navigation.xml' config file
	 * @return Mvc\View\Helper\Nav\Menu
	 */
	public function navMenu($id)
	{
		$menu = new Menu($this, $id);
		return $menu;
	}
	
	/**
	 * Render messages
	 * @param array $messages
	 * @return string
	 */
	public function renderMessages($messages)
	{
		$out = array();
		foreach($messages as $type => $message){
			$out[] = "<$this->messagesOuterTag class=\"$this->messagesOuterClass $type\">
			<$this->messagesInnerTag class=\"$this->messagesInnerClass\">".
			implode("</$this->messagesInnerTag>
					<$this->messagesInnerTag class=\"$this->messagesInnerClass\">", array_map(array($this, '__'), $message)).
					"</$this->messagesInnerTag></$this->messagesOuterTag>";
		}
		return implode(PHP_EOL, $out);
	}
	
	/**
	 * Render a view inside another view.
	 * @param string $partial
	 * @param string $layout
	 * @return string
	 */
	public function partial($partial, $layout = false)
	{
		$out = '';
		$filename = APPLICATION_PATH . '/view/' . $partial . '.phtml';
		if(is_file($filename)){
			ob_start();
			extract(get_object_vars($this));
			include $filename;
			if($layout){
				$layoutFilename = APPLICATION_PATH . '/view/layout/' . $layout . '.phtml';
				if(is_file($layoutFilename)){
					$this->content = ob_get_clean();
					ob_start();
					include $layoutFilename;
				}
			}
			return ob_get_clean();
		}
		return $out;
	}
	
	/**
	 * Translate a string
	 * @param string $str
	 * @param string $textDomain
	 * @return string
	 */
	public function __($str, $textDomain = Application::TEXTDOMAIN)
	{
		return $this->getController()->getTranslator()->translate($str, $textDomain);
	}
	
}