<?php

namespace LeadingSystems\MerconisCustomHoehenflugBundle\Order\Distributor;

use LeadingSystems\MerconisCustomHoehenflugBundle\Order\Enum\OrderType;
use LeadingSystems\MerconisCustomHoehenflugBundle\Order\Exporter\ExporterInterface;
use Merconis\Core\ls_shop_generalHelper;

class Distributor
{
    private iterable $exporterServices;
    private string $projectDir;

    private int $orderId;
    private array $order;
    private OrderType $orderType = OrderType::STANDARD;
    private ?string $specialSubfolderName = null;
    private ?string $specialFilenameAddition = null;
    private array $itemsByVendorIds = [];
    private array $availableExporters = [];
    private array $pathsToWrittenExportFiles = [];
    private array $errorMsgs = [];

    public function __construct(iterable $exporterServices, string $projectDir)
    {
        $this->exporterServices = $exporterServices;
        $this->projectDir = $projectDir;
    }

    public function distribute(int $orderId): void
    {
        $this->orderId = $orderId;
        $this->order = ls_shop_generalHelper::getOrder($this->orderId);

        $this->collectItemsByVendorIds();
        $this->determineAvailableExportersForVendorIds();
        if (!$this->requiredExportersExist()) {
            throw new \Exception('Order "' . $this->order['orderNr'] . '" can not be processed! Not all required exporters exist!');
        }

        foreach ($this->itemsByVendorIds as $vendorId => $itemsForVendor) {
            /** @var ExporterInterface $exporterService */
            $exporterService = $this->availableExporters[$vendorId];
            $exporterService->setCompleteOriginalOrderData($this->order);
            $exporterService->setOrderType($this->orderType);
            if ($this->orderType === OrderType::COLLECTIVEORDER) {
                $exporterService->setSpecialSubfolderName($this->specialSubfolderName);
                $exporterService->setSpecialFilenameAddition($this->specialFilenameAddition);
            }
            $exporterService->setRelevantOrderItems($itemsForVendor);
            try {
                $this->pathsToWrittenExportFiles[$vendorId] = $exporterService->export();
            } catch (\Throwable $e) {
                $this->errorMsgs[] = $e->getMessage();
            }
        }

        /*
         * Do me! Instantiate vendor specific distributor classes that do the actual job of delivering the exported
             * files to the receiver, which might be a folder on the SEP server or a vendor FTP server or an API.
         */

        if (count($this->errorMsgs) > 0) {
            /*
             * Do me! Here we need a proper logging. Throwing an exception is just a quick and dirty way to get to
                 * see occuring errors during development!
             */
            throw new \Exception(implode(" || ", $this->errorMsgs));
        }
    }

    private function requiredExportersExist(): bool
    {
        return count(array_intersect(array_keys($this->itemsByVendorIds), array_keys($this->availableExporters))) === count(array_keys($this->itemsByVendorIds));
    }

    private function collectItemsByVendorIds(): void
    {
        if (is_array($this->order['items'] ?? null)) {
            foreach ($this->order['items'] as $item) {
                $artNrParts = explode('_', $item['artNr'], 2);
                if (count($artNrParts) !== 2) {
                    throw new \Exception('The item\'s article number (' . $item['artNr'] . ') is not vendor-prefixed as expected! Order "' . $this->order['orderNr'] . '" can not be processed!');
                }
                $vendorId = $artNrParts[0];

                /*
                 * Check for additional prefixes (like e.g. sk1234567890# for collective order products)
                 */
                if (str_contains($vendorId, '#')) {
                    $vendorIdParts = explode('#', $vendorId, 2);
                    if (count($vendorIdParts) !== 2) {
                        throw new \Exception('Unexpected prefix in article number "' . $item['artNr'] . '"');
                    }

                    $vendorId = $vendorIdParts[1];

                    /*
                     * We know that a collective order can only contain one item, so it is okay to set
                     * the specialSubfolderName, the specialFileNameAddition and the orderType, which both
                     * affect the whole export, right here.
                     */
                    $this->orderType = OrderType::COLLECTIVEORDER;
                    $this->specialFilenameAddition = $this->specialSubfolderName = $vendorIdParts[0];
                }

                $this->itemsByVendorIds[$vendorId][] = $item;
            }
        }
    }

    private function determineAvailableExportersForVendorIds(): void
    {
        /** @var ExporterInterface $exporterService */
        foreach ($this->exporterServices as $exporterService) {
            $this->availableExporters[$exporterService->getVendorId()] = $exporterService;
        }
    }
}