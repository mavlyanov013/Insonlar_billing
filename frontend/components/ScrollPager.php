<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2017. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

/**
 * Created by PhpStorm.
 * Date: 12/13/17
 * Time: 4:59 AM
 */

namespace frontend\components;


use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

class ScrollPager extends LinkPager
{

    public $buttonText;
    public $emptyText;
    public $perLoad;
    public $loadParam = 'load';

    public function init()
    {
        parent::init();
        if (empty($this->buttonText)) {
            $this->buttonText = __('Load more');
        }
        if (empty($this->emptyText)) {
            $this->emptyText = __('All data loaded');
        }
        if (empty($this->perLoad)) {
            $this->perLoad = 4;
        }
        Html::removeCssClass($this->options, 'pagination');
        Html::addCssClass($this->options, 'btn btn-secondary');
    }

    /**
     * Renders the page buttons.
     * @return string the rendering result
     */
    protected function renderPageButtons()
    {
        $pageCount = $this->pagination->getPageCount();
        if ($pageCount < 2 && $this->hideOnSinglePage) {
            return '';// Html::a($this->emptyText, '#', ['class' => 'btn primary wide']);
        }

        $buttons     = [];
        $currentPage = $this->pagination->getPage();

        // first page
        $firstPageLabel = $this->firstPageLabel === true ? '1' : $this->firstPageLabel;
        if ($firstPageLabel !== false) {
            $buttons[] = $this->renderPageButton($firstPageLabel, 0, $this->firstPageCssClass, $currentPage <= 0, false);
        }

        // prev page
        if ($this->prevPageLabel !== false) {
            if (($page = $currentPage - 1) < 0) {
                $page = 0;
            }
            $buttons[] = $this->renderPageButton($this->prevPageLabel, $page, $this->prevPageCssClass, $currentPage <= 0, false);
        }

        // internal pages
        list($beginPage, $endPage) = $this->getPageRange();
        for ($i = $beginPage; $i <= $endPage; ++$i) {
            $buttons[] = $this->renderPageButton($i + 1, $i, null, $this->disableCurrentPageButton && $i == $currentPage, $i == $currentPage);
        }

        // next page
        if ($this->nextPageLabel !== false) {
            if (($page = $currentPage + 1) >= $pageCount - 1) {
                $page = $pageCount - 1;
            }
            $buttons[] = $this->renderPageButton($this->nextPageLabel, $page, $this->nextPageCssClass, $currentPage >= $pageCount - 1, false);
        }

        // last page
        $lastPageLabel = $this->lastPageLabel === true ? $pageCount : $this->lastPageLabel;
        if ($lastPageLabel !== false) {
            $buttons[] = $this->renderPageButton($lastPageLabel, $pageCount - 1, $this->lastPageCssClass, $currentPage >= $pageCount - 1, false);
        }
        $options = $this->options;
        return Html::a($this->buttonText, Url::current([$this->loadParam => $this->perLoad + $this->pagination->getPageSize()]), $options);
    }


}