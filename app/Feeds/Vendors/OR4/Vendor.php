<?php

namespace App\Feeds\Vendors\OR4;

use App\Feeds\Feed\FeedItem;
use App\Feeds\Processor\HttpProcessor;

class Vendor extends HttpProcessor
{
    protected const DELAY_S = 0.1;

    public array $first = ['https://organicsmanufacturer.com/21-4organics-supplements'];

    public const PRODUCT_LINK_CSS_SELECTORS = ['div.product-container a.product-name'];

    public function isValidFeedItem(FeedItem $fi) : bool
    {
        return $fi->getCostToUs() > 0;
    }
}