<?php

namespace LeadingSystems\MerconisCustomStarterbaseBundle\CRUD\BatchInsertUpdate\EventListener;

use LeadingSystems\MerconisCustomStarterbaseBundle\CRUD\BatchInsertUpdate\BatchInsertUpdate;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

class TerminateListener
{
    private BatchInsertUpdate $batchInsertUpdate;

    public function __construct(BatchInsertUpdate $batchInsertUpdate)
    {
        $this->batchInsertUpdate = $batchInsertUpdate;
    }

    public function __invoke(TerminateEvent $event): void
    {
        /*
         * ISSUE https://lsboard.de/project/232/task/7203
         * Do me! Der TerminateEvent wird scheinbar bei der Ausführung unseres ITEK-Originaldaten-Imports über
             * den Cron-Aufruf in der Kommandozeile nicht ausgeführt. Zumindest wurde der Rest im letzten Batch
             * nicht geschrieben. Muss unbedint geprüft und gefixt werden!
         */
        $this->batchInsertUpdate->writeLeftovers();
    }
}