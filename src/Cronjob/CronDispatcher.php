<?php

namespace LeadingSystems\MerconisCustomStarterbaseBundle\Cronjob;

use LeadingSystems\MerconisCustomStarterbaseBundle\Scheduler\SchedulerDispatcher;

class CronDispatcher
{
    private SchedulerDispatcher $schedulerDispatcher;

    public function __construct(SchedulerDispatcher $schedulerDispatcher)
    {

        $this->schedulerDispatcher = $schedulerDispatcher;
    }

    public function dispatchScheduler()
    {
        $this->schedulerDispatcher->dispatch();
    }
}