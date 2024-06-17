<?php

namespace LeadingSystems\MerconisCustomHoehenflugBundle\CRUD\ProductManager;

use Merconis\Core\ls_shop_generalHelper;
use Merconis\Core\ls_shop_productManagementApiHelper;
use Merconis\Core\ls_shop_productManagementApiPreprocessor;

class ProductManager
{
    public function writeDataRow(array $productOrVariantOrLanguageData): void
    {
        /*
         * TODO:
         * We are mimicking the behaviour of ls_shop_apiController_productManagement which is a quick'n'dirty
         * solution to get things going.
         *
         * Of course, if we want to use the preprocessor etc. like this, we should modify it, so that we
         * can use it in a clean way.
         */

        ls_shop_productManagementApiPreprocessor::$throwExceptionForMissingOrWrongAttributesOrValues = false;
        ls_shop_productManagementApiPreprocessor::$throwExceptionForMissingImageFiles = false;
        ls_shop_productManagementApiHelper::$int_numImportableAttributesAndValues = 75;

        /** @var array $preprocessingResult */
        $preprocessingResult = ls_shop_productManagementApiPreprocessor::preprocess([$productOrVariantOrLanguageData], 'apiResource_writeProductData');

        if ($preprocessingResult['bln_hasError']) {
            /*
             * Because we only sent one data row to the preprocessor, we know that error messages must refer to line 1
             * and therefore we can directly address key 1.
             */
            throw new \Exception($this->createErrorMessageStringFromPreprocessorErrors($preprocessingResult['arr_messages'][1]));
        }

        $importResult = $this->performImport($preprocessingResult['arr_preprocessedDataRows']);

        ls_shop_generalHelper::saveLastBackendDataChangeTimestamp();

        if ($importResult['bln_hasError']) {
            /*
             * FIXME: We use json_encode just for a quick test.
             */
            throw new \Exception(json_encode($importResult['arr_messages']));
        }
    }

    public function translateRecommendedProductCodesInIDs(): void
    {
        /*
         * Translation of recommended product codes into ids must not happen too often.
         * Since it is not an unlikely scenario that the "performImport" method is called for single
         * products it is quite likely that calling the translation method there would result in
         * calling it after every single product. This would be terrible for import performance
         * because the translation method itself is not optimal performance-wise.
         *
         * Therefore, the ProductManager does not call the translation method on its own. Instead,
         * it offers a public method which an importer script can use to call the translation method
         * when it seems appropriate in the context of the entire import process.
         *
         * An importer script might choose not to call the translation method at all, if
         * recommended products are not an issue anyway.
         */
        ls_shop_productManagementApiHelper::translateRecommendedProductCodesInIDs();
    }

    private function performImport($arr_dataRows): array
    {
        $arr_result = array(
            'bln_hasError' => false,
            'arr_messages' => []
        );

        foreach (ls_shop_productManagementApiHelper::$dataRowTypesInOrderToProcess as $str_dataRowType) {
            foreach ($arr_dataRows as $int_rowNumber => $arr_dataRow) {
                /*
                 * Since we import one data row type at a time, we skip rows that have the wrong type
                 */
                if ($arr_dataRow['type'] != $str_dataRowType) {
                    continue;
                }

                try {
                    switch ($str_dataRowType) {
                        case 'product':
                            ls_shop_productManagementApiHelper::insertOrUpdateProductRecord($arr_dataRow);
                            break;

                        case 'variant':
                            ls_shop_productManagementApiHelper::insertOrUpdateVariantRecord($arr_dataRow);
                            break;

                        case 'productLanguage':
                            ls_shop_productManagementApiHelper::writeProductLanguageData($arr_dataRow);
                            break;

                        case 'variantLanguage':
                            ls_shop_productManagementApiHelper::writeVariantLanguageData($arr_dataRow);
                            break;
                    }
                } catch (\Exception $e) {
                    $arr_result['bln_hasError'] = true;
                    $arr_result['arr_messages'][$int_rowNumber + 1] = $e->getMessage();
                }
            }
        }

        return $arr_result;
    }

    private function createErrorMessageStringFromPreprocessorErrors(array $errorMessages): string
    {
        $errorMessage = '';
        $glue = ' | ';

        foreach ($errorMessages as $fieldName => $message) {
            $errorMessage .= $fieldName . ': ' . $message . $glue;
        }

        $errorMessage = rtrim($errorMessage, $glue);

        return $errorMessage;
    }
}