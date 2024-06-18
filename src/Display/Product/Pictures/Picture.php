<?php

namespace LeadingSystems\MerconisCustomStarterbaseBundle\Display\Product\Pictures;

class Picture
{
    public string $src;
    public string $title;
    public string $type;
    public bool $isSubstitute;
    public int $orderNr;

    public function __construct(string $src, string $title, string $type, bool $isSubstitute, int $orderNr)
    {
        $this->src = $src;
        $this->title = $title;
        $this->type = $type;
        $this->isSubstitute = $isSubstitute;
        $this->orderNr = $orderNr;
    }
}