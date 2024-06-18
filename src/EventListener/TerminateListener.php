<?php

namespace LeadingSystems\MerconisCustomStarterbaseBundle\EventListener;

use LeadingSystems\MerconisCustomStarterbaseBundle\CRUD\CategoryManager\CategoryManager;
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