<?php

namespace LeadingSystems\MerconisCustomHoehenflugBundle\Order\Exporter\Cash\Zentrallager;

class Exporter extends \LeadingSystems\MerconisCustomHoehenflugBundle\Order\Exporter\Cash\Common\Exporter {
    protected int $vendorId = 1;
    protected string $buyerNumberSHK = '48936';
    protected string $pathToExportFolder = 'files/exports/Cash/Zentrallager/%s';
    protected string $exportFilenameTemplate = 'Order_%s_Zentrallager%s.xml';

}