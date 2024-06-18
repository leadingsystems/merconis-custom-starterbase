<?php
namespace LeadingSystems\MerconisCustomStarterbaseBundle\Scheduler\Traits;

trait SchedulableTrait {
    protected string $executionResultMessage = '';

    public function run(): void
    {

    }

    public function getExecutionResultMessage(): string
    {
        return $this->executionResultMessage ?: 'Executed successfully without returning specific execution result message.';
    }
}