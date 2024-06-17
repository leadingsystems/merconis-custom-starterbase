<?php

namespace LeadingSystems\MerconisCustomHoehenflugBundle\Order\Exporter;

use LeadingSystems\MerconisCustomHoehenflugBundle\Order\Enum\OrderType;

interface ExporterInterface
{
    public function getVendorId(): int;
    public function setCompleteOriginalOrderData(array $completeOriginalOrder): void;
    public function setRelevantOrderItems(array $orderItems): void;
    public function setOrderType(OrderType $orderType): void;
    public function setSpecialSubfolderName(?string $specialSubfolderName): void;
    public function setSpecialFilenameAddition(?string $specialFilenameAddition): void;

    /*
     * Method must return the path to the written export file
     */
    public function export(): string;
}