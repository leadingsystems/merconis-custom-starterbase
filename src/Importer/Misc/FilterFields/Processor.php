<?php

namespace LeadingSystems\MerconisCustomStarterbaseBundle\Importer\Misc\FilterFields;

use LeadingSystems\MerconisCustomStarterbaseBundle\CRUD\FilterFieldManager\FilterFieldManager;
use LeadingSystems\MerconisCustomStarterbaseBundle\Importer\ImporterBase;
use LeadingSystems\MerconisCustomStarterbaseBundle\ImportFileHandler\Enum\Mode;
use LeadingSystems\MerconisCustomStarterbaseBundle\ImportFileHandler\ImportFileHandler;

class Processor extends ImporterBase
{
    private FilterFieldManager $filterFieldManager;

    public function __construct(ImportFileHandler $importFileHandler, FilterFieldManager $filterFieldManager)
    {
        parent::__construct($importFileHandler);

        $this->importFileHandler->setMode(Mode::DB);
        $this->importFileHandler->setDbQuery(
            <<<EOT
                SELECT tl_ls_shop_attributes.*
                FROM tl_ls_shop_attributes
                LEFT JOIN tl_ls_shop_filter_fields
                    ON tl_ls_shop_filter_fields.sourceAttribute = tl_ls_shop_attributes.id
                WHERE tl_ls_shop_filter_fields.sourceAttribute IS NULL
            EOT
        );
        $this->importFileHandler->setDbQueryParams(null);
        $this->filterFieldManager = $filterFieldManager;
        $this->filterFieldManager->setAliasOfRecordToDuplicate('filter-field-template-1-for-automatic-creation');
    }

    public function run(): void
    {
        $this->importFileHandler->setMaxLinesToRead(5000);
        ImporterBase::run();
    }

    protected function work(): void
    {
        foreach ($this->importFileHandler->readLine() as $attribute) {
            $this->filterFieldManager->createFilterFieldIfNecessary($attribute['alias'], $attribute['title_de'], $attribute['id']);
        }
    }
}
