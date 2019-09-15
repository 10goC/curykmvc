<?php
/** Comlei Mvc Framework */

namespace Mvc;

/** This class provides helper functions for creating pages from database results */
class Paginator
{
	/**
	 * The injected controller.
	 * @var Mvc\Controller
	 */
	protected $controller;
	
	/**
	 * The current page number.
	 * @var int
	 */
	protected $currentPage;
	
	/**
	 * The number items to show per page.
	 * @var int
	 */
	protected $itemsPerPage;
	
	/**
	 * The default number of items to show per page.
	 * @var int
	 */
	protected $defaultItemsPerPage = 10;
	
	/**
	 * The number of total pages.
	 * @var int
	 */
	protected $totalPages;
	
	/**
	 * The number of total items.
	 * @var int
	 */
	protected $totalItems;
	
	/**
	 * The number of items in current page
	 * @var int
	 */
	protected $pageTotalItems;
	
	/**
	 * The number of pages to show when rendering page numbers navigation links.
	 * @var int
	 */
	public $showPages = 10;
	
	/**
	 * The name of the query string parameter to determine the page number.
	 * @var string
	 */
	public $pageParam = 'page';
	
	/**
	 * The name of the query string parameter to determine the number of items pe page.
	 * @var string
	 */
	public $perPageParam = 'perPage';
	
	/**
	 * The HTML tag for the wrapper element
	 * @var string
	 */
	public $wrapperTag = 'div';
	
	/**
	 * The CSS clas for the wrapper element
	 * @var string
	 */
	public $wrapperClass = 'page-numbers';
	
	/**
	 * HTML Template to prepend to page number i.e.: <li>
	 * @var string
	 */
	public $beforePageNumber = '';
	
	/**
	 * HTML Template to append to page number i.e.: </li>
	 * @var string
	 */
	public $afterPageNumber = '';
	
	/**
	 * Store the injected controller.
	 * @param Controller $controller
	 */
	public function __construct(Controller $controller)
	{
		$this->controller = $controller;
	}
	
	/**
	 * Set number of items per page.
	 * @param int $itemsPerPage
	 */
	public function setItemsPerPage($itemsPerPage)
	{
		$this->defaultItemsPerPage = $itemsPerPage;
	}
	
	/**
	 * Get the current number of items per page.
	 * @return int
	 */
	public function getItemsPerPage()
	{
		if (!$this->itemsPerPage) {
			$itemsPerPage = $this->controller->getParam($this->perPageParam, $this->defaultItemsPerPage);
			if (is_numeric($itemsPerPage) && $itemsPerPage > 0) {
				$this->itemsPerPage = $itemsPerPage;
			} else {
				$this->itemsPerPage = $this->defaultItemsPerPage;
			}
		}
		return $this->itemsPerPage;
	}
	
	/**
	 * Get the index of the first element of the resultset to be fetched.
	 * Can be used to generate SQL query for a specific page.
	 * @return int
	 */
	public function getStartIndex()
	{
		return $this->getItemsPerPage() * ($this->getCurrentPage() -1);
	}
	
	/**
	 * Get index number of the first element of current page
	 * @return int
	 */
	public function getPageFirstItem()
	{
		return $this->getStartIndex() + 1;
	}
	
	/**
	 * Get index number of the last element of current page
	 * @return int
	 */
	public function getPageLastItem()
	{
		return $this->getStartIndex() + $this->getPageTotalItems();
	}
	
	/**
	 * Get items count for current page
	 * @return int
	 */
	public function getPageTotalItems()
	{
		return $this->pageTotalItems;
	}
	
	/**
	 * Set number of total items in current page
	 * @param int $number
	 */
	public function setPageTotalItems($number)
	{
		$this->pageTotalItems = $number;
	}
	
	/**
	 * Get the current page number.
	 * @return number
	 */
	public function getCurrentPage()
	{
		if (!$this->currentPage) {
			$currentPage = $this->controller->getParam($this->pageParam, 1);
			if (is_numeric($currentPage) && $currentPage > 0) {
				$this->currentPage = ceil($currentPage);
			} else {
				$this->currentPage = 1;
			}
		}
		return $this->currentPage;
	}
	
	/**
	 * Get previous page number. Returns false if there isn't one.
	 * @return boolean|number
	 */
	public function getPrevPage()
	{
		$prev = $this->getCurrentPage() -1;
		if ($prev < 1) return false;
		return $prev;
	}

	/**
	 * Get next page number. Returns false if there isn't one.
	 * @return boolean|number
	 */
	public function getNextPage()
	{
		$next = $this->getCurrentPage() +1;
		if ($next > $this->getTotalPages()) return false;
		return $next;
	}
	
	/**
	 * Get total pages number.
	 * @return number
	 */
	public function getTotalPages()
	{
		if (!$this->totalPages) {
			$this->totalPages = ceil($this->getTotalItems() / $this->getItemsPerPage());
		}
		return $this->totalPages;
	}
	
	/**
	 * Set number of total items.
	 * @param int $count
	 */
	public function setTotalItems($count)
	{
		$this->totalItems = $count;
	}
	
	/**
	 * Get number of total items.
	 * @return int
	 */
	public function getTotalItems()
	{
		return $this->totalItems;
	}
	
	/**
	 * Generates output for paged navigation. Creates prev / next and page numbers links.
	 * @return string
	 */
	public function render()
	{
		if ($this->getTotalPages() > 1) {
			$out[] = $this->renderNavigation();
			$out[] = $this->renderPageNumbers();
			return implode(PHP_EOL, $out);
		}
		return '';
	}
	
	/**
	 * Generates output for prev and next links.
	 * @return string
	 */
	public function renderNavigation()
	{
		$view = $this->controller->getView();
		// Previous page
		if ($prev = $this->getPrevPage()) {
			$out[] = '<a class="prev-page" href="'.$this->getPageLink($prev).'"><span>'.$view->__('Previous', $view::TEXTDOMAIN).'</span></a>';
		} else {
			$out[] = '<span class="prev-page disabled">'.$view->__('Previous', $view::TEXTDOMAIN).'</span>';
		}
		// Next page
		if ($next = $this->getNextPage()) {
			$out[] = '<a class="next-page" href="'.$this->getPageLink($next).'"><span>'.$view->__('Next', $view::TEXTDOMAIN).'</span></a>';
		} else {
			$out[] = '<span class="next-page disabled">'.$view->__('Next', $view::TEXTDOMAIN).'</span>';
		}
		return implode(PHP_EOL, $out);
	}
	
	/**
	 * Generates output for page numbers links.
	 * @return string
	 */
	public function renderPageNumbers()
	{
		$out[] = '<'.$this->wrapperTag.' class="'.$this->wrapperClass.'">';
		$pagesBack = ceil(($this->showPages -1) /2);
		$pagesAhead = floor(($this->showPages -1) /2);
		$firstPage = $this->getCurrentPage() -$pagesBack;
		if ($firstPage < 1) $firstPage = 1;
		if ($firstPage > 1) $out[] = $this->beforePageNumber.'<a class="page-number first-page" href="'.$this->getPageLink(1).'">1</a>'.$this->afterPageNumber;
		if ($firstPage > 2) $out[] = $this->beforePageNumber.'<span class="pages-inbetween">...</span>'.$this->afterPageNumber;
		$lastPage = $this->getTotalPages();
		if ($lastPage > $this->getCurrentPage() +$pagesAhead) $lastPage = $this->getCurrentPage() +$pagesAhead;
		if ($lastPage < $this->showPages && $this->showPages <= $this->getTotalPages()) $lastPage = $this->showPages;
		for ($i = $firstPage; $i <= $lastPage; $i++) {
			if ($i == $this->getCurrentPage()) {
				$out[] = "$this->beforePageNumber<span class=\"page-number disabled\">$i</span>$this->afterPageNumber";
			} else {
				$out[] = $this->beforePageNumber.'<a class="page-number" href="'.$this->getPageLink($i).'">'.$i.'</a>'.$this->afterPageNumber;
			}
		}
		if ($lastPage < $this->getTotalPages() -1) $out[] = $this->beforePageNumber.'<span class="pages-inbetween">...</span>'.$this->afterPageNumber;
		if ($lastPage < $this->getTotalPages()) $out[] = $this->beforePageNumber.'<a class="page-number last-page" href="'.$this->getPageLink($this->getTotalPages()).'">'.$this->getTotalPages().'</a>'.$this->afterPageNumber;
		$out[] = '</'.$this->wrapperTag.'>';
		return implode(PHP_EOL, $out);
	}
	
	/**
	 * Modifies the current URL to specify a page number in the query string.
	 * @param int $page
	 * @return string
	 */
	public function getPageLink($page)
	{
		$view = $this->controller->getView();
		return $view->addQsParams(array('page' => $page));
	}
	
	/**
	 * Generate output.
	 * @return string
	 */
	public function __toString()
	{
		return $this->render();
	}
}