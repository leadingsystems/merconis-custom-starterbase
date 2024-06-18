<?php

namespace LeadingSystems\MerconisCustomStarterbaseBundle\CRUD\BatchInsertUpdate;

class Batch
{
    private BatchInsertUpdate $parent;
    private string $tableName;
    private int $preferredSize;
    private int $size;
    private array $dataStack = [];
    private float $batchSizeControlAtStackSizeFactor = 0.25;
    private float $targetFactor = 0.50;
    private int $assumedOverheadInBytesForEachValueSet = 500;
    private int $assumedOverheadForRestOfStatement = 1000;

    public static function create(?BatchInsertUpdate $parent = null, ?string $tableName = null, ?int $size = null): self
    {
        $batchInstance = new self();
        $batchInstance->parent = $parent;
        $batchInstance->tableName = $tableName;
        $batchInstance->setSize($size);
        return $batchInstance;
    }

    public function setSize(int $size): void
    {
        $this->preferredSize = $this->size = $size;
    }

    /**
     * Add data to the stack. Multiple data sets can be passed as separate arguments.
     */
    public function add(array ...$dataSets): void
    {
        foreach ($dataSets as $data) {
            $this->dataStack[] = $data;
            $this->adjustBatchSizeIfNecessary();
            if (count($this->dataStack) >= $this->size) {
                $this->write();
            }
        }
    }

    public function write(): void
    {
        $this->parent->insertOrUpdateRecords($this->tableName, $this->dataStack);
        $this->dataStack = [];

        /*
         * Reset the batch size to the originally preferred size in case it was automatically decreased.
         */
        $this->size = $this->preferredSize;
    }

    /*
     * This function determines the estimated packet size of the MySQL query that would be created based on the current
     * stack contents at 10 % of the currently targeted stack size. Then it decides whether it is likely that actually
     * filling the stack up to its targeted stack size would cause the query to exceed the MySQL setting 'max_allowed_packets'
     * or even comes close to it.
     * If it seems necessary, the stack size will be decreased accordingly.
     */
    private function adjustBatchSizeIfNecessary(): void
    {
        if (count($this->dataStack) == ceil($this->size * $this->batchSizeControlAtStackSizeFactor)) {
            /*
             * Get the actual payload of the current stack
             */
            $contentString = '';
            foreach ($this->dataStack as $stackEntry) {
                $contentString .= implode('', $stackEntry);
            }

            /*
             * Calculate the byte size of the payload plus some assumed overhead for the actual statement
             */
            $estimatedSizeOfStatementInBytes = strlen($contentString) + count($this->dataStack) * $this->assumedOverheadInBytesForEachValueSet + $this->assumedOverheadForRestOfStatement;
            $estimatedSizeOfStatementInBytesAtFullStack = $estimatedSizeOfStatementInBytes / $this->batchSizeControlAtStackSizeFactor;

            /*
             * Determine the factor of how
             */
            $f = $estimatedSizeOfStatementInBytesAtFullStack / $this->parent->maxAllowedPacket;

            if ($f > $this->targetFactor) {
                /*
                 * If we assume to exceed the target factor, we decrease the stack size accordingly;
                 */
                $this->size = floor($this->size / $f * $this->targetFactor);
            }
        }
    }
}