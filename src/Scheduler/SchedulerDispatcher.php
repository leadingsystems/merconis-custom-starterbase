<?php

namespace LeadingSystems\MerconisCustomStarterbaseBundle\Scheduler;

use Cron\CronExpression;
use LeadingSystems\MerconisCustomStarterbaseBundle\Scheduler\Exception\SchedulerExecutionResultException;
use LeadingSystems\MerconisCustomStarterbaseBundle\Scheduler\Models\SchedulerJobModel;
use LeadingSystems\MerconisCustomStarterbaseBundle\Scheduler\Traits\SchedulableTrait;

class SchedulerDispatcher
{
    private iterable $schedulableServices;

    public function __construct(iterable $schedulableServices)
    {
        $this->schedulableServices = $schedulableServices;
    }

    public function dispatch(bool $overrideCronExpressionCheck = false)
    {
        $jobs = SchedulerJobModel::findByActive('1');
        foreach ($jobs as $job) {
            if (!$overrideCronExpressionCheck && !$this->check_mustRun($job)) {
                continue;
            }

            try {
                $job->lastExecutionResult = $this->runJob($job, !$overrideCronExpressionCheck);
            } catch (SchedulerExecutionResultException $e) {
                $job->lastExecutionResult = $e->getMessage();
            } catch (\Throwable $e) {
                $job->lastExecutionResult = "Script execution failed.\r\n\r\nPlease make sure that you define a script to execute for the job, that the script is a correctly registered service and that your script uses the \"SchedulableTrait\" in order to provide necessary methods.\r\n\r\nDetailed error message:\r\n" . $e->getMessage() . ' (' . $e->getFile() . ' on line ' . $e->getLine() . ')';
            }

            $job->save();
        }
    }

    private function getServiceByFQCN(string $FQCN): mixed
    {
        /*
         * FIXME:
         * As soon as a service is accessed here in order to check if it is the one that we want,
         * it will already be instantiated. We should look for a different solution that can find the
         * required service without instantiating the others. If all services don't actually do anything
         * unless their "run" method is called, instantiating would probably not be much of an issue,
         * but if the constructor of a service actually does something, it might be a problem.
         * If the constructor of a service that is not currently requested produces a fatal error
         * (because of a missing parameter or something like that) it will prevent the service that is
         * requested from running!
         *
         * We tried making the services lazy which should make sure that they will not get instantiated
         * unless they are actually being used. Unfortunately, $this->schedulableServices could not be
         * walked through with "foreach" and therefore no services could be found.
         *
         * If a better solution has been found, it should also be considered for
         * DCACallbackHelper::getScriptsForSchedulerExecutionAsOptions where schedulable services
         * are currently being handled in the same way with potentially the same problems.
         */
        foreach ($this->schedulableServices as $service) {
            if ($service instanceof $FQCN) {
                return $service;
            }
        }
        return null;
    }

    private function runJob(SchedulerJobModel $job, bool $markAsRunning = true): string
    {
        if (!$job->scriptToExecute) {
            throw new \Exception('No script to execute given for scheduler job "' . $job->title . '" (ID ' . $job->id . ')');
        }

        if ($job->currentlyRunning) {
            $hoursRunningByNow = floor(((time() - $job->tstampLastRun) / 3600) * 100) / 100;
            $stillRunningExceptionMessage = '<span style="color: #AA0000; font-weight: bold;">Still running from previous dispatch (' . date('Y-m-d, H:i:s', $job->tstampLastRun) . ', ' . $hoursRunningByNow . ' hours until now) and therefore could not be started again on ' . date('Y-m-d, H:i:s') . '</span>';

            if ($hoursRunningByNow >= 5) {
                $stillRunningExceptionMessage .= '<br><span style="color: #AA0000; font-weight: bold; font-size: 135%;">Job runs for more than ' . $hoursRunningByNow . ' hours now. Go check!</span>';
            }
            throw new SchedulerExecutionResultException($stillRunningExceptionMessage);
        }

        /** @var SchedulableTrait  $jobService */
        $jobService = $this->getServiceByFQCN($job->scriptToExecute);
        if (is_object($jobService)) {
            $job->tstampLastRun = time();
            if ($markAsRunning) {
                $job->currentlyRunning = '1';
            }
            $job->save();
            $jobService->run();
            if ($markAsRunning) {
                $job->currentlyRunning = '';
                $job->save();
            }
            return $jobService->getExecutionResultMessage();
        }

        throw new \Exception('The script to execute could not be found. No service with the FQCN "' . $job->scriptToExecute . '" seems to be registered.');
    }

    private function check_mustRun(SchedulerJobModel $job): bool
    {
        $nextRunDate = CronExpression::factory($job->cronExpression)->getNextRunDate(\DateTime::createFromFormat('U', $job->tstampLastRun));
        return $nextRunDate->getTimestamp() <= time();
    }
}