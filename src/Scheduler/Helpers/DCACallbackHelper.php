<?php

namespace LeadingSystems\MerconisCustomStarterbaseBundle\Scheduler\Helpers;

use Contao\DataContainer;
use Cron\CronExpression;

class DCACallbackHelper
{
    private iterable $schedulableServices;

    public function __construct(iterable $schedulableServices)
    {
        $this->schedulableServices = $schedulableServices;
    }

    public function cronExpressionBackendFieldValidation($value, DataContainer $dc): mixed
    {
        if (!CronExpression::isValidExpression($value)) {
            throw new \Exception($GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['misc']['invalidCronExpressionErrorMessage']);
        }
        return $value;
    }

    public function getSchedulerJobBackendListLabel(array $row, string $label, DataContainer $dc, array $labels): string
    {
        ob_start();
        ?>
        <div<?= !$row['active'] ? ' style="opacity: 0.3"' : '' ?>>
            <h2 style="margin-bottom: 1rem; text-decoration: underline;"><?= $row['title'] ?></h2>
            <?php
            if ($row['description']) {
                ?>
                <p><?= $row['description'] ?></p>
                <?php
            }
            ?>
            <p>
                <strong><?php echo $GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['scriptToExecute'][0]; ?>:</strong> <?= $row['scriptToExecute'] ?>
            </p>
            <p>
                <strong><?php echo $GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['cronExpression'][0]; ?>:</strong> <?= $row['cronExpression'] ?> (<?= $row['tstampLastRun'] <= 0 ? $GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['misc']['notYetRun'] : $GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['misc']['lastRunLabel'] . ' ' . date('d.m.Y H:i', $row['tstampLastRun']) ?>)
            </p>
            <?php
            if ($row['currentlyRunning']) {
                ?>
                <p style="color: #00AA00; font-weight: bold;">... Currently running!</p>
                <?php
            }
            ?>
            <?php
            if ($row['lastExecutionResult']) {
                ?>
                <pre style="white-space: pre-wrap;"><?= $row['lastExecutionResult'] ?></pre>
                <?php
            }
            ?>
        </div>
        <?php
        $label = ob_get_clean();
        return $label;
    }

    public function getScriptsForSchedulerExecutionAsOptions(): array
    {
        $arr_scriptFiles = [];

        foreach ($this->schedulableServices as $schedulableService) {
            $arr_scriptFiles[] = $schedulableService::class;
        }

        return $arr_scriptFiles;
    }

}