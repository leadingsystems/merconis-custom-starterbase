<?php

namespace LeadingSystems\MerconisCustomHoehenflugBundle\EventListener;

use LeadingSystems\MerconisCustomHoehenflugBundle\CRUD\CategoryManager\CategoryManager;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

class TerminateListener
{
    private CategoryManager $categoryManager;

    public function __construct(CategoryManager $categoryManager)
    {
        $this->categoryManager = $categoryManager;
    }

    public function __invoke(TerminateEvent $event): void
    {
        $this->categoryManager->cleanup();
    }
}