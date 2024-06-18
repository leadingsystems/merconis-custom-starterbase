<?php

namespace LeadingSystems\MerconisCustomStarterbaseBundle\Order\Exporter\Cash\Common;

use DOMDocument;
use LeadingSystems\MerconisCustomStarterbaseBundle\Display\Product\Data\Data;
use LeadingSystems\MerconisCustomStarterbaseBundle\Order\Enum\OrderType;
use LeadingSystems\MerconisCustomStarterbaseBundle\Order\Exporter\ExporterInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class Exporter implements ExporterInterface
{
    protected int $vendorId;
    protected string $pathToExportFolder;
    protected string $exportFilenameTemplate;
    private array $completeOriginalOrder = [];
    private array $orderItems = [];
    private string $projectDir;
    private SluggerInterface $slugger;
    private OrderType $orderType;
    private ?string $specialSubfolderName = null;
    private ?string $specialFilenameAddition = null;
    protected string $buyerNumberSHK = '';


    public function __construct(string $projectDir, SluggerInterface $slugger)
    {
        $this->projectDir = $projectDir;
        $this->slugger = $slugger;
    }

    public function getVendorId(): int
    {
        return $this->vendorId;
    }

    public function setRelevantOrderItems(array $orderItems): void
    {
        $this->orderItems = $orderItems;
    }

    public function setOrderType(OrderType $orderType): void
    {
        $this->orderType = $orderType;
    }

    public function setSpecialSubfolderName(?string $specialSubfolderName): void
    {
        $this->specialSubfolderName = $specialSubfolderName;
    }

    public function setSpecialFilenameAddition(?string $specialFilenameAddition): void
    {
        $this->specialFilenameAddition = $specialFilenameAddition;
    }

    public function export(): string
    {
        /*
         * Do me! Better error handling. If any exception happens and the order can't be exported or delivered,
             * an admin must be informed with all information necessary to make sure that the order export can
             * be created and sent manually.
         */
        if (!$this->buyerNumberSHK) {
            throw new \Exception('No buyer number present. Order with order id ' . $this->completeOriginalOrder['orderNr'] . ' can not be exported. Please fix this issue manually.');
        }

        $xml = $this->generateOrderXml();

        $this->handleExportFolder();

        $exportFileName = $this->projectDir . '/' . $this->pathToExportFolder . '/' . sprintf($this->exportFilenameTemplate, $this->slugger->slug($this->completeOriginalOrder['orderNr']), $this->specialFilenameAddition ? '_' . $this->specialFilenameAddition : '');
        if (!file_put_contents($exportFileName, $xml)) {
            throw new \Exception('Export file ' . $exportFileName . ' was not written');
        }
        return $exportFileName;
    }

    private function handleExportFolder(): void
    {
        $subfolderName = 'StandardOrders';
        if ($this->orderType === OrderType::COLLECTIVEORDER) {
            $subfolderName = 'CollectiveOrders/' . ($this->specialSubfolderName ?: 'UNKNOWN');
        }

        $this->pathToExportFolder = sprintf($this->pathToExportFolder, $subfolderName);

        if (!is_dir($this->projectDir . '/' . $this->pathToExportFolder)) {
            mkdir($this->projectDir . '/' . $this->pathToExportFolder, 0777, true);
        }
    }

    public function setCompleteOriginalOrderData(array $completeOriginalOrder): void
    {
        $this->completeOriginalOrder = $completeOriginalOrder;
    }

    private function generateOrderXml(): string
    {
        $gln = '4399899090770';
        ob_start();
        echo '<?xml version="1.0" encoding="utf-8"?>' . "\r\n";
?>
<ORDER version="2.1" type="standard">
    <ORDER_HEADER>
        <CONTROL_INFO>
            <GENERATION_DATE><?= date('c') ?></GENERATION_DATE>
        </CONTROL_INFO>
        <ORDER_INFO>
<?php
            /*
             * ISSUE (solved) https://lsboard.de/project/232/task/7352#comment-3330
             */
?>
            <ORDER_ID><?= htmlspecialchars($this->completeOriginalOrder['orderNr'], ENT_XML1 | ENT_QUOTES, 'UTF-8') ?></ORDER_ID>
            <ORDER_DATE><?= date('c', $this->completeOriginalOrder['orderDateUnixTimestamp']) ?></ORDER_DATE>
            <DELIVERY_DATE>
<?php
                /*
                 * ISSUE (solved) https://lsboard.de/project/232/task/7352#comment-3331
                 */
                $dummyDeliveryStartDateUT = strtotime('+1day');
                $dummyDeliveryEndDateUT = strtotime('+1day');
?>
                <DELIVERY_START_DATE><?= date('c', $dummyDeliveryStartDateUT) ?></DELIVERY_START_DATE>
                <DELIVERY_END_DATE><?= date('c', $dummyDeliveryEndDateUT) ?></DELIVERY_END_DATE>
            </DELIVERY_DATE>

            <PARTIES>
                <PARTY>
<?php
                    /*
                     * ISSUE (solved) https://lsboard.de/project/232/task/7352#comment-3337
                     */
?>
                    <bmecat:PARTY_ID type="supplier_specific"><?= $this->buyerNumberSHK ?></bmecat:PARTY_ID>
                    <PARTY_ROLE>buyer</PARTY_ROLE>
                    <ADDRESS>
                        <CONTACT_DETAILS>
                            <bmecat:CONTACT_NAME>Frau Johanna Semere</bmecat:CONTACT_NAME>
                        </CONTACT_DETAILS>
                    </ADDRESS>
                </PARTY>


                <PARTY>
<?php
                    /*
                     * ISSUE (solved) https://lsboard.de/project/232/task/7352#comment-3340
                     */
                    $str_alternativeShippingAddressSuffixIfRequired = (isset($this->completeOriginalOrder['customerData']['personalData_originalOptionValues']['useDeviantShippingAddress']) && $this->completeOriginalOrder['customerData']['personalData_originalOptionValues']['useDeviantShippingAddress']) ? '_alternative' : '';
?>
                    <bmecat:PARTY_ID type="other"><?= ($this->completeOriginalOrder['customerData']['personalData']['shkNumber'] ?? null) ?: 0 ?></bmecat:PARTY_ID>
                    <PARTY_ROLE>delivery</PARTY_ROLE>
                    <ADDRESS>
<?php
                        $name2 = $this->completeOriginalOrder['customerData']['personalData']['firstname' . $str_alternativeShippingAddressSuffixIfRequired] . ' ' . $this->completeOriginalOrder['customerData']['personalData']['lastname' . $str_alternativeShippingAddressSuffixIfRequired];
                        $name = $this->completeOriginalOrder['customerData']['personalData']['company' . $str_alternativeShippingAddressSuffixIfRequired];
                        if (!$name) {
                            $name = $name2;
                            $name2 = '';
                        }
                        $phone = $this->completeOriginalOrder['customerData']['personalData']['phone' . $str_alternativeShippingAddressSuffixIfRequired] ?? '';
                        if (!$phone) {
                            $phone = $this->completeOriginalOrder['customerData']['personalData']['phone'] ?? '';
                        }
                        $email = $this->completeOriginalOrder['customerData']['personalData']['email' . $str_alternativeShippingAddressSuffixIfRequired] ?? '';
                        if (!$email) {
                            $email = $this->completeOriginalOrder['customerData']['personalData']['email'] ?? '';
                        }
?>
                        <bmecat:NAME><?= htmlspecialchars($name, ENT_XML1 | ENT_QUOTES, 'UTF-8') ?></bmecat:NAME>
                        <bmecat:NAME2><?= htmlspecialchars($name2, ENT_XML1 | ENT_QUOTES, 'UTF-8') ?></bmecat:NAME2>
                        <bmecat:STREET><?= htmlspecialchars($this->completeOriginalOrder['customerData']['personalData']['street' . $str_alternativeShippingAddressSuffixIfRequired], ENT_XML1 | ENT_QUOTES, 'UTF-8') ?></bmecat:STREET>
                        <bmecat:ZIP><?= htmlspecialchars($this->completeOriginalOrder['customerData']['personalData']['postal' . $str_alternativeShippingAddressSuffixIfRequired], ENT_XML1 | ENT_QUOTES, 'UTF-8') ?></bmecat:ZIP>
                        <bmecat:CITY><?= htmlspecialchars($this->completeOriginalOrder['customerData']['personalData']['city' . $str_alternativeShippingAddressSuffixIfRequired], ENT_XML1 | ENT_QUOTES, 'UTF-8') ?></bmecat:CITY>
                        <bmecat:COUNTRY_CODED><?= htmlspecialchars($this->completeOriginalOrder['customerData']['personalData_originalOptionValues']['country' . $str_alternativeShippingAddressSuffixIfRequired], ENT_XML1 | ENT_QUOTES, 'UTF-8') ?></bmecat:COUNTRY_CODED>
                        <bmecat:PHONE><?= htmlspecialchars($phone, ENT_XML1 | ENT_QUOTES, 'UTF-8') ?></bmecat:PHONE>
                        <bmecat:EMAILS>
                            <bmecat:EMAIL><?= htmlspecialchars($email, ENT_XML1 | ENT_QUOTES, 'UTF-8') ?></bmecat:EMAIL>
                        </bmecat:EMAILS>
                    </ADDRESS>
                </PARTY>


                <PARTY>
                    <bmecat:PARTY_ID type="supplier_specific"><?= $this->buyerNumberSHK ?></bmecat:PARTY_ID>
                    <PARTY_ROLE>invoice_recipient</PARTY_ROLE>
                </PARTY>
            </PARTIES>
            <ORDER_PARTIES_REFERENCE>
                <bmecat:BUYER_IDREF type="supplier_specific"><?= $this->buyerNumberSHK ?></bmecat:BUYER_IDREF>
<?php
                /*
                 * ISSUE (solved) https://lsboard.de/project/232/task/7352#comment-3333
                 */
?>
                <bmecat:SUPPLIER_IDREF type="gln"><?= $gln ?></bmecat:SUPPLIER_IDREF>
                <INVOICE_RECIPIENT_IDREF type="supplier_specific"><?= $this->buyerNumberSHK ?></INVOICE_RECIPIENT_IDREF>
            </ORDER_PARTIES_REFERENCE>
            <bmecat:CURRENCY>EUR</bmecat:CURRENCY>
        </ORDER_INFO>
    </ORDER_HEADER>
    <ORDER_ITEM_LIST>
<?php
        /*
         * We have to use a line item counter here because the item position that the item has in the order
         * items array comes from the original order and since not all the positions of the original order
         * are necessarily part of this export, this item position is not reliable.
         */
        $lineItemCounter = 0;

        /*
         * We have to cumulate the total amount here, because the total amount of the original value is
         * not reliable because the original order might have included items that come from another vendor
         * and therefore aren't part of this export and must not be included in the total amount in the export.
         */
        $totalAmountCumulator = 0;
        foreach ($this->orderItems as $orderItem) {
            $vendorOriginalData = $orderItem['extendedInfo']['vendorOriginalData'];
            $lineItemCounter++;
?>
            <ORDER_ITEM>
                <LINE_ITEM_ID><?= $lineItemCounter ?></LINE_ITEM_ID>
                <PRODUCT_ID>
                    <bmecat:SUPPLIER_PID type="supplier_specific"><?= htmlspecialchars($vendorOriginalData['article_nr'], ENT_XML1 | ENT_QUOTES, 'UTF-8') ?></bmecat:SUPPLIER_PID>
                    <bmecat:INTERNATIONAL_PID type="ean"><?= $vendorOriginalData['gtin'] ?></bmecat:INTERNATIONAL_PID>
                    <bmecat:DESCRIPTION_SHORT><?= htmlspecialchars($orderItem['productTitle'], ENT_XML1 | ENT_QUOTES, 'UTF-8') ?></bmecat:DESCRIPTION_SHORT>
                </PRODUCT_ID>
                <QUANTITY><?= $orderItem['quantity'] * (floatval($vendorOriginalData['packaging_unit_size']) ?: 1) ?></QUANTITY>
<?php
                /*
                 * ISSUE (solved) https://lsboard.de/project/232/task/7352#comment-3342
                 */
?>
                <bmecat:ORDER_UNIT><?= $vendorOriginalData['quantity_unit'] ?></bmecat:ORDER_UNIT>
<?php
                /*
                 * ISSUE (solved) https://lsboard.de/project/232/task/7352#comment-3354
                 */

                $singlePrice = floatval($vendorOriginalData['price']) / (floatval($vendorOriginalData['base_price_quantity']) ?: 1);
?>
                <PRODUCT_PRICE_FIX>
                    <bmecat:PRICE_AMOUNT><?= $singlePrice ?></bmecat:PRICE_AMOUNT>
                </PRODUCT_PRICE_FIX>
                <PRICE_LINE_AMOUNT><?= $singlePrice * (floatval($vendorOriginalData['packaging_unit_size']) ?: 1) * $orderItem['quantity'] ?></PRICE_LINE_AMOUNT>
            </ORDER_ITEM>
<?php
        }
?>
    </ORDER_ITEM_LIST>
    <ORDER_SUMMARY>
        <TOTAL_ITEM_NUM><?= count($this->orderItems) ?></TOTAL_ITEM_NUM>
    </ORDER_SUMMARY>
</ORDER>
        <?php
        return ob_get_clean();
    }
}