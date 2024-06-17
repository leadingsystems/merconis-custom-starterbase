<?php

namespace LeadingSystems\MerconisCustomHoehenflugBundle\CRUD\BatchInsertUpdate;

class BatchInsertUpdate
{
    private array $batches = [];
    private Batch $batchFactory;
    public int $maxAllowedPacket;

    public function __construct(Batch $batchFactory)
    {
        $this->batchFactory = $batchFactory;
        $this->readMaxAllowedPacketSetting();
    }

    public function createBatch(string $tableName, int $size): Batch
    {
        $this->batches[] = $batch = $this->batchFactory->create($this, $tableName, $size);
        return $batch;
    }

    public function writeLeftovers(): void
    {
        /** @var $batch Batch */
        foreach ($this->batches as $batch) {
            $batch->write();
        }
    }

    public function insertOrUpdateRecords(string $tableName, array $data): void
    {
        if (!count($data)) {
            return;
        }

        // Extract field names from the first row of data
        $fields = array_keys($data[0]);

        // Build the base query
        $stmt = "
            INSERT INTO $tableName (" . implode(', ', $fields) . ")
            VALUES
        ";

        $valuePlaceholders = array_fill(0, count($data), '(' . implode(', ', array_fill(0, count($fields), '?')) . ')');
        $stmt .= implode(', ', $valuePlaceholders);

        $stmt .= " ON DUPLICATE KEY UPDATE ";
        foreach ($fields as $field) {
            $stmt .= $field . " = VALUES(" . $field . "), ";
        }
        $stmt = rtrim($stmt, ', '); // Remove trailing comma and space

        // Prepare the statement and execute
        $params = [];
        foreach ($data as $row) {
            foreach ($row as $value) {
                $params[] = $value;
            }
        }

        \Database::getInstance()->prepare($stmt)->execute(...$params);
    }

    private function readMaxAllowedPacketSetting(): void
    {
        $dbres_maxAllowedPacket = \Database::getInstance()
            ->prepare("SHOW VARIABLES LIKE 'max_allowed_packet'")
            ->execute();

        if (!$dbres_maxAllowedPacket->numRows) {
            trigger_error('max_allowed_packet could not be read from MySQL.', E_USER_WARNING);
        }

        $this->maxAllowedPacket = $dbres_maxAllowedPacket->first()->Value;
    }
}