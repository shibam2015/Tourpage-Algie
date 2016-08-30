<?php

/*
 * Copyright (C) 2016 amit
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Tourpage\Library;

use Phalcon\Mvc\User\Component;

/**
 * Library for Pagination
 * @author amit
 */
class Pager extends Component {

    /**
     * Model Data
     * @var object 
     */
    private $data = null;

    /**
     * Record limit per page
     * @var int 
     */
    private $limit = 10;

    /**
     * Current page number
     * @var int 
     */
    private $currentPage = 0;

    /**
     * Maximum link shown for pagination
     * @var int 
     */
    private $maxButtonCount = 10;

    /**
     * Next button link label
     * @var string 
     */
    private $nextPageLabel = '<span aria-hidden="true">&raquo;</span>';

    /**
     * Previous button link label
     * @var string
     */
    private $prevPageLabel = '<span aria-hidden="true">&laquo;</span>';

    /**
     * First button link label
     * @var string
     */
    private $firstPageLabel = '<span aria-hidden="true">&larr;</span>';

    /**
     * Last button link label
     * @var string
     */
    private $lastPageLabel = '<span aria-hidden="true">&rarr;</span>';

    /**
     * Pager link url pattern
     * @var string
     */
    private $urlPattern = '';

    /**
     * Page url query string segment
     * @var string
     */
    private $pageQuery = '';

    /**
     * Paginator Model Object
     * @see \Phalcon\Paginator\Adapter\Model()
     * @var object
     */
    private $paginate = null;

    /**
     * List of items object to show per page
     * @var array of object
     */
    private $items = null;

    /**
     * Total item count
     * @var int
     */
    private $totalItems = 0;

    /**
     * Total page link to show for pagination
     * @var int
     */
    private $totalPages = 0;

    /**
     * Constructor for Pager
     * @param array $config
     */
    public function __construct($config = array()) {
        if (count($config) > 0) {
            if (isset($config['data'])) {
                $this->data = & $config['data'];
            }
            if (isset($config['limit'])) {
                $this->limit = $config['limit'];
            }
            if (isset($config['page'])) {
                $this->currentPage = $config['page'];
            }
            if (isset($config['nextPageLabel'])) {
                $this->nextPageLabel = $config['nextPageLabel'];
            }
            if (isset($config['prevPageLabel'])) {
                $this->prevPageLabel = $config['prevPageLabel'];
            }
            if (isset($config['firstPageLabel'])) {
                $this->firstPageLabel = $config['firstPageLabel'];
            }
            if (isset($config['lastPageLabel'])) {
                $this->lastPageLabel = $config['lastPageLabel'];
            }
            if (isset($config['maxButtonCount'])) {
                $this->maxButtonCount = $config['maxButtonCount'];
            }
        }
        $this->initialize();
        $this->items = $this->paginate->items;
        $this->totalItems = $this->paginate->total_items;
        $this->totalPages = $this->paginate->total_pages;
        if ($this->currentPage <= 0) {
            $this->currentPage = 1;
        }
    }

    /**
     * Initializing Phalcon Paginator Model
     */
    private function initialize() {
        $paginate = new \Phalcon\Paginator\Adapter\Model(array(
            "data" => $this->data,
            "limit" => $this->limit,
            "page" => $this->currentPage
        ));
        $this->paginate = $paginate->getPaginate();
    }

    /**
     * Getting total item count
     * @return int
     */
    public function getTotalItems() {
        return $this->totalItems;
    }

    /**
     * Getting total page count
     * @return int
     */
    public function getTotalPages() {
        return $this->totalPages;
    }

    /**
     * Getting current page count
     * @return int
     */
    public function getCurrentPage() {
        $pageCount = $this->getTotalPages();
        if ($this->currentPage >= $pageCount) {
            $this->currentPage = $pageCount;
        }
        return $this->currentPage;
    }

    /**
     * Getting paginate items
     * @return array of object
     */
    public function getItems() {
        return $this->items;
    }

    /**
     * Getting limit of items per page
     * @return int
     */
    public function getLimit() {
        return $this->limit;
    }

    /**
     * Getting paginator navigation links
     * @return html
     */
    public function getLinks() {
        $buttons = $this->createPageButtons();
        if (empty($buttons)) {
            return;
        }
        echo '<ul class="pagination">';
        echo implode("\n", $buttons);
        echo '</ul>';
    }

    /**
     * Creating pagination links
     * @return html
     */
    protected function createPageButtons() {
        if ($this->getTotalItems() <= $this->getLimit()) {
            return array();
        }
        $pageCount = $this->getTotalPages();
        list($beginPage, $endPage) = $this->getPageRange();
        $currentPage = $this->getCurrentPage();
        $buttons = array();
        $buttons[] = $this->createPageButton($this->firstPageLabel, 1, '', $currentPage <= 1, false);
        if (($page = $currentPage - 1) < 0) {
            $page = 1;
        }
        $buttons[] = $this->createPageButton($this->prevPageLabel, $page, '', $currentPage <= 1, false);
        for ($i = $beginPage; $i <= $endPage; ++$i) {
            $buttons[] = $this->createPageButton($i, $i, '', false, ($i) == $currentPage);
        }
        if (($page = $currentPage + 1) >= $pageCount) {
            $page = $pageCount;
        }
        $buttons[] = $this->createPageButton($this->nextPageLabel, $page, '', $currentPage >= $pageCount, false);
        $buttons[] = $this->createPageButton($this->lastPageLabel, $pageCount, '', $currentPage >= $pageCount, false);
        return $buttons;
    }

    /**
     * Generate single pagination link
     * @param string $label
     * @param int $page
     * @param string $class
     * @param bool $hidden
     * @param bool $selected
     * @return html
     */
    protected function createPageButton($label, $page, $class = '', $hidden = '', $selected = '') {
        if ($hidden) {
            $class .= ' disabled';
        }
        if ($selected) {
            $class .= ' active';
        }
        return '<li class="' . $class . '">' . ($hidden ? $label : $this->tag->linkTo(array($this->createPageUrl($page), $label))) . '</li>';
    }

    /**
     * Getting page range to show
     * That means start paginator link and end paginator link
     * @return array
     */
    protected function getPageRange() {
        $currentPage = $this->getCurrentPage();
        $pageCount = $this->getTotalPages();
        $beginPage = max(1, $currentPage - (int) ($this->maxButtonCount / 2));
        if (($endPage = ($beginPage + $this->maxButtonCount) - 1) >= $pageCount) {
            $endPage = $pageCount;
            $beginPage = max(1, ($endPage - $this->maxButtonCount) + 1);
        }
        return array($beginPage, $endPage);
    }

    /**
     * Creating pager link url
     * @param int $page
     * @return html
     */
    protected function createPageUrl($page) {
        $pageUrl = '';
        if (empty($this->urlPattern)) {
            $rewriteUri = $this->router->getRewriteUri();
            $controller = $this->dispatcher->getControllerName();
            $action = $this->dispatcher->getActionName();
            $pageUrl .= substr($rewriteUri, 0, strpos($rewriteUri, $controller));
            $pageUrl .= $controller . '/';
            $pageUrl .= $action . '/';
            $pageUrl .= $page;
        } else {
            $pageUrl .= str_replace('{page}', $page, $this->urlPattern);
        }
        $this->attachQuery($_GET);
        if (!empty($this->pageQuery)) {
            $pageUrl .= $this->pageQuery;
        }
        return $pageUrl;
    }

    /**
     * Set page url pattern. This will be a custom route
     * @param string $pattern
     */
    public function setUriPattern($pattern) {
        $this->urlPattern = $pattern;
    }

    /**
     * Generate query string in the form or name value pair
     * from global $_GET array
     * @param array $params
     */
    protected function attachQuery($params) {
        if (isset($params['_url'])) {
            unset($params['_url']);
        }
        $query = '';
        if (count($params) > 0) {
            foreach ($params as $key => $value) {
                if (!empty($key) && !empty($value)) {
                    $query .= $key . '=' . $value . '&';
                }
            }
        }
        $query = ((substr($query, -1) == '&') ? substr($query, 0, -1) : $query);
        if ($query) {
            $this->pageQuery = '?' . $query;
        }
    }

}
