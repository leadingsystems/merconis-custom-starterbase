<?php

namespace LeadingSystems\MerconisCustomStarterbaseBundle\Display\Product\Pictures;

use Contao\System;
use Merconis\Core\ls_shop_product;

class Pictures
{
    private ls_shop_product $product;
    private bool $allowDummy = true;
    private string $pathToDummy = 'files/merconisfiles/themes/theme10/images/placeholder/product-placeholder.jpg';
    private string $pathToPictures = 'files/media/%s/pictures/';
    private array $pictures = [];
    private array $validScreenSuffixes = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];

    public static function create(ls_shop_product $product, bool $allowDummy = true): self
    {
        if (!isset($GLOBALS['merconis-custom-cache'][__CLASS__][$product->ls_ID])) {
            $instance = new self();
            $instance->initialize($product, $allowDummy);
            $GLOBALS['merconis-custom-cache'][__CLASS__][$product->ls_ID] = $instance;
        }
        return $GLOBALS['merconis-custom-cache'][__CLASS__][$product->ls_ID];
    }

    public function initialize(ls_shop_product $product, bool $allowDummy = true): void
    {
        $this->product = $product;
        $this->allowDummy = $allowDummy;
        $this->pathToPictures = sprintf($this->pathToPictures, $this->product->_producer);
        $this->readPictures();
    }

    public function hasWebPictures(): bool
    {
        return count($this->getWebPictures()) > 0;
    }

    public function getWebPictures(): array
    {
        return $this->pictures['Web'] ?? [];
    }

    public function hasPrintPictures(): bool
    {
        return count($this->getPrintPictures()) > 0;
    }

    public function getPrintPictures(): array
    {
        return $this->pictures['Druck'] ?? [];
    }

    public function hasScreenPictures(): bool
    {
        return count($this->getScreenPictures()) > 0;
    }

    public function getScreenPictures(): array
    {
        if (!isset($this->pictures['screen'])) {
            $this->pictures['screen'] = $this->hasWebPictures() ? $this->getWebPictures() : $this->getPrintPictures();

            $this->pictures['screen'] = array_filter($this->pictures['screen'], function ($picture) {
                $fileExtension = pathinfo($picture->src, PATHINFO_EXTENSION);
                return in_array(strtolower($fileExtension), $this->validScreenSuffixes);
            });

            $this->pictures['screen'] = array_values($this->pictures['screen']);

        }
        return $this->pictures['screen'];
    }

    public function hasMainScreenPicture(): bool
    {
        return $this->getMainScreenPicture() !== null;
    }

    public function getMainScreenPicture(): ?Picture
    {
        return $this->getScreenPictures()[0] ?? null;
    }

    private function readPictures(): void
    {
        /*
         * Do me! Our factories must be rewritten in a way that allows them to receive other services through
             * proper DI. Using getContainer() ist just a quick workaround.
         */
        $dataFactory = System::getContainer()->get('LeadingSystems\MerconisCustomStarterbaseBundle\Display\Product\Data\Data');
        $data = $dataFactory->create($this->product);
        $productCodeWithoutSKPrefix = $data->getProductCode();
        $dbres_productPictures = \Database::getInstance()
            ->prepare("
                SELECT *
                FROM tl_itek_pictures
                WHERE sSupplierShortName_sArticle = ?
                ORDER BY 
                    CASE
                        WHEN cPictureUse = 'Web' THEN 1
                        WHEN cPictureUse = 'Druck' THEN 2
                        ELSE 3
                    END,
                    bSubstitute ASC,
                    CASE
                        WHEN cPictureType = 'B_' THEN 1
                        WHEN cPictureType = 'S_' THEN 2
                        WHEN cPictureType = 'MI' THEN 3
                        WHEN cPictureType = 'U_' THEN 4
                        WHEN cPictureType = 'V_' THEN 5
                        WHEN cPictureType = 'X_' THEN 6
                        WHEN cPictureType = 'DT' THEN 7
                        ELSE 8
                    END,
                    nDocOrder ASC
            ")
            ->execute($productCodeWithoutSKPrefix);

        while ($dbres_productPictures->next()) {
            $this->pictures[$dbres_productPictures->cPictureUse][] = new Picture($this->pathToPictures . $dbres_productPictures->sDocument, $dbres_productPictures->sDocDescription ?? '', $dbres_productPictures->cPictureType, (bool) $dbres_productPictures->bSubstitute, $dbres_productPictures->nDocOrder);
        }

        if (!count($this->pictures) && $this->allowDummy) {
            $this->pictures['screen'][] = new Picture($this->pathToDummy, '', '', true, 1);
        }
    }
}