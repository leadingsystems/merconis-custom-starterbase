<?php

namespace LeadingSystems\MerconisCustomStarterbaseBundle\Importer\Cash\ArticleConditionZentrallager;

use LeadingSystems\MerconisCustomStarterbaseBundle\CRUD\BatchInsertUpdate\Batch;
use LeadingSystems\MerconisCustomStarterbaseBundle\CRUD\BatchInsertUpdate\BatchInsertUpdate;
use LeadingSystems\MerconisCustomStarterbaseBundle\Importer\ImporterBase;
use LeadingSystems\MerconisCustomStarterbaseBundle\ImportFileHandler\ImportFileHandler;

class Processor extends BaseDefinition
{
    private BatchInsertUpdate $batchInsertUpdate;
    private Batch $productsBatch;

    public function __construct(ImportFileHandler $importFileHandler, BatchInsertUpdate $batchInsertUpdate)
    {
        parent::__construct($importFileHandler);
        $this->batchInsertUpdate = $batchInsertUpdate;
        $this->productsBatch = $this->batchInsertUpdate->createBatch('tl_shk_vendor_products', 1000);
    }

    public function run(): void
    {
        $this->importFileHandler->setMaxLinesToRead(100000);
        ImporterBase::run();
    }

    protected function work(): void
    {
        foreach ($this->importFileHandler->readLine() as $line) {
            $this->lineData = $line;

            try {
                $this->processLine();
            } catch (\Exception $e) {
                $this->importFileHandler->moveToFailedFolder();
                throw new \Exception('Processing import file failed | ' . $e->getMessage(), 0, $e);
            }
        }

        /*
         * ISSUE https://lsboard.de/project/232/task/7203
         * Do me! writeLeftovers soll hier eigentlich gar nicht aufgerufen werden müssen, allerdings wird der
             * TerminateEvent scheinbar bei der Ausführung über den Cron-Aufruf in der Kommandozeile nicht ausgeführt.
             * Sobald die als Issue verlinkte Aufgabe erledigt und dieses Problem gelöst ist, kann und soll der
             * Aufruf hier entfernt werden!
         */
        $this->batchInsertUpdate->writeLeftovers();
    }

    private function processLine(): void
    {
        $this->productsBatch->add($this->getProductDataToStore());
    }

    private function getProductDataToStore(): array
    {
        $dataToSerialize = $this->lineData;

        $data = [
            /*
             * ISSUE https://lsboard.de/project/232/task/7189 Warning! Consider relevance
                 * of the timestamp for product deletions, especially when vendors deliver
                 * product data deltas because deleting products based on the timestamp
                 * would not be possible then!
             */
            'import_file_tstamp' => $this->importFileHandler->getCurrentDataSourceTime('U'),

            /*
             * ISSUE https://lsboard.de/project/232/task/7188 The currently hard-coded
                 * dummy vendor number must be replaced with the actual vendor number
             *
             */
            'vendor_nr' => '1',

            'article_nr' => $this->lineData['SupplierProductId'],
            'gtin' => $this->lineData['GTIN'],

            /*
             * Unless the vendor provides a mapping table for us to figure out the ITEK manufacturer id
             * (i.e. ITEK sSupplierShortName), we can't use a manufacturer id that is vendor specific.
             * However, since such a mapping table could be provided later, we store the vendor's original
             * manufacturer id so that it can be translated later.
             */
            'manufacturer_id' => null,
            'manufacturer_id_vendor' => $this->lineData['ManufacturerID'],

            'manufacturer_product_id' => $this->lineData['ManufacturerProductId'],
            'price' => (float) ($this->lineData['StandardPrice'] ?: 0),
            'quantity_unit' => $this->lineData['QuantityUnitSales'],

            /*
             * ISSUE (solved) https://lsboard.de/project/232/task/6902#comment-3274
             */
            'base_price_quantity' => (float) ($this->lineData['BasePriceQuantity'] ?: 1),
            'packaging_unit_size' => (float) ($this->lineData['PackagingUnitSize'] ?: 1),

            'quantity_unit_package' => $this->lineData['QuantityUnitPackage'],
            'data_json' => json_encode($dataToSerialize),
        ];

        return $data;
    }
}