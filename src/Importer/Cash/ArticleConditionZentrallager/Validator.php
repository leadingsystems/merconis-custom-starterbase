<?php

namespace LeadingSystems\MerconisCustomHoehenflugBundle\Importer\Cash\ArticleConditionZentrallager;

use LeadingSystems\MerconisCustomHoehenflugBundle\Importer\ImporterBase;

class Validator extends BaseDefinition
{
    public function run(): void
    {
        $this->importFileHandler->setMaxLinesToRead(70000);
        $this->importFileHandler->setMoveBrokenCsvFilesToFailedFolder(true);
        ImporterBase::run();
    }

    protected function work(): void
    {
        foreach ($this->importFileHandler->readLine() as $line) {
            $this->lineData = $line;
            $this->validateLine();
        }
    }

    private function validateLine(): void
    {
        foreach (array_keys($this->lineData) as $fieldName) {
            if (!method_exists($this, 'validateField__' . $fieldName)) {
                continue;
            }
            $this->{'validateField__' . $fieldName}($fieldName);
        }
    }

    /**
     * @used-by validateLine
     */
    private function validateField__SupplierProductId(string $fieldName): void
    {
        if (!$this->lineData[$fieldName]) {
            $this->addErrorMessage($fieldName . ' is empty.', true);
        }
    }

    /**
     * @used-by validateLine
     */
    private function validateField__GTIN(string $fieldName): void
    {
        if (!$this->lineData[$fieldName]) {
            $this->addErrorMessage($fieldName . ' is empty.', true);
        }
    }

    /**
     * @used-by validateLine
     */
    private function validateField__StandardPrice(string $fieldName): void
    {
        if (!$this->lineData[$fieldName]) {
            $this->addErrorMessage($fieldName . ' is empty.', true);
        }
    }

    /**
     * @used-by validateLine
     */
    private function validateField__QuantityUnitSales(string $fieldName): void
    {
        if (!$this->lineData[$fieldName]) {
            $this->addErrorMessage($fieldName . ' is empty.', true);
        }
    }
}