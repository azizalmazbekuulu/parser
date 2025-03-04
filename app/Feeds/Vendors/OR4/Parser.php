<?php

namespace App\Feeds\Vendors\OR4;

use App\Feeds\Parser\HtmlParser;

class Parser extends HtmlParser
{
    public function getMpn(): string
    {
        return $this->getText('span[itemprop="sku"]');
    }

    public function getProduct(): string
    {
        return $this->getText('h1[itemprop="name"]');
    }

    public function getShortDescription(): array
    {
        return [$this->getText('div#short_description_content span')];
    }

    public function getDescription(): string
    {
        return str_replace('</h2>', "</h2>\n", preg_replace('/<\/h\d/', '</h2', $this->getHtml('#ptab-info div.rte') ) );
    }

    public function getImages(): array
    {
        return array_values(array_unique( $this->getLinks('a.fancybox') ));
    }

    public function getCostToUs(): float
    {
        return $this->getMoney('#our_price_display');
    }

    public function getAvail(): ?int
    {
        return $this->getText('#availability_value') === "Available" || $this->getText('#availability_value') === "In Stock" ? self::DEFAULT_AVAIL_NUMBER : 0;
    }
}