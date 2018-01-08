<?php

namespace Ffcms\Core\Helper\HTML;

use Ffcms\Core\Helper\Type\Any;
use Ffcms\Core\Helper\Type\Arr;
use Ffcms\Core\Helper\Type\Obj;

/**
 * Class SimplePagination. Build and display html pagination block
 * @package Ffcms\Core\Helper\HTML
 */
class SimplePagination
{
    protected $url;
    protected $page;
    protected $step;
    protected $total;

    /**
     * SimplePagination constructor. Parse passed elements
     * @param array $elements
     */
    public function __construct(array $elements)
    {
        $this->url = $elements['url'];
        $this->page = (int)$elements['page'];
        $this->step = (int)$elements['step'] > 0 ? (int)$elements['step'] : 5;
        $this->total = (int)$elements['total'];
    }

    /**
     * Display pagination html code. Must be initialized via (new SimplePagination([params]))->display(['class' => 'test'])
     * @param array|null $property
     * @return null|string
     */
    public function display(array $property = null): ?string
    {
        // total items is less to pagination requirement
        if (($this->page * $this->step) + 1 > $this->total) {
            return null;
        }

        $lastPage = ceil($this->total / $this->step); // 6/5 ~ 2 = 0..2

        if ($lastPage <= 1) {
            return null;
        }

        // prevent hack-boy's any try
        if ($this->page > $lastPage) {
            return null;
        }

        $items = [];
        // more then 10 items in pagination
        if ($lastPage > 10) {
            if ($this->page < 4 || $lastPage - $this->page <= 4) { // list start, page in [0..4]
                // from start to 4
                $items = $this->generateItems(0, 4);

                // add "..." button
                $items[] = [
                    'type' => 'link', 'link' => '#', 'text' => '...', 'property' => ['class' => 'disabled']
                ];
                // add middle page
                $middlePage = ceil($lastPage / 2);
                $items[] = [
                    'type' => 'link', 'link' => $this->setUrlPage($middlePage), 'text' => $middlePage + 1
                ];
                // add "..." button
                $items[] = [
                    'type' => 'link', 'link' => '#', 'text' => '...', 'property' => ['class' => 'disabled']
                ];
                $items = Arr::merge($items, $this->generateItems($lastPage - 4, $lastPage));
            } else { // meanwhile on middle
                // generate 1-2 pages
                $items = $this->generateItems(0, 2);

                // add "..." button
                $items[] = [
                    'type' => 'link', 'link' => '#', 'text' => '...', 'property' => ['class' => 'disabled']
                ];

                // add middle variance -3..mid..+3
                $items = Arr::merge($items, $this->generateItems($this->page - 3, $this->page + 3));

                // add "..." button
                $items[] = [
                    'type' => 'link', 'link' => '#', 'text' => '...', 'property' => ['class' => 'disabled']
                ];

                // add latest 2 items
                $items = Arr::merge($items, $this->generateItems($lastPage - 2, $lastPage));
            }
        } else { // less then 10 items in pagination
            $items = $this->generateItems(0, $lastPage);
        }

        return Listing::display([
            'type' => 'ul',
            'property' => $property,
            'items' => $items
        ]);
    }

    /**
     * Set GET param to property array
     * @param int|string $pageId
     * @return array
     */
    protected function setUrlPage($pageId)
    {
        $url = $this->url;
        switch (count($url)) { // check nulls if not set
            case 1: // only controller/action is defined
                $url[1] = null;
                $url[2] = null;
                break;
            case 2:
                $url[2] = null;
                break;
        }

        // add page param if > 0
        if ((int)$pageId > 0) {
            // merge with ?page if query is not empty
            $url[3] = (Any::isArray($url[3]) ? Arr::merge($url[3], ['page' => $pageId]) : ['page' => $pageId]);
        }

        return $url;
    }

    /**
     * Generate item array to build pagination listing
     * @param int $start
     * @param int $end
     * @return array|null
     */
    protected function generateItems($start, $end)
    {
        // prevent any shit's
        if ($end <= $start) {
            return null;
        }

        $items = [];
        for ($i = $start; $i < $end; $i++) {
            $items[] = [
                'type' => 'link', 'link' => $this->setUrlPage($i), 'text' => $i + 1
            ];
        }
        return $items;
    }
}
