<?php
namespace Merconis\CustomStarterbase;

class merconis_custom_helper
{
    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['getProductData_priceCheapestVariantBeforeTax'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_getProductData_priceCheapestVariantBeforeTax');
     *
     * -- Invocation: --
     * When Merconis determines the price of the cheapest product variant
     *
     * -- Parameters: --
     * 	1. $obj_product - the product object
     *
     * -- Return value: --
     * $float_lowestPriceBeforeTax - holding the price that should be considered as the cheapest variant price
     *
     * -- Objective: --
     * e.g. skipping variants which don't have a realistic price. This can be necessary if a sample piece of the product is among the product's variants.
     *
     */
    public function merconis_hook_getProductData_priceCheapestVariantBeforeTax($obj_product) {
        $float_lowestPriceBeforeTax = $obj_product->_priceBeforeTax;
        $int_count = 0;
        foreach ($obj_product->_variants as $obj_variant) {
            if ($obj_variant->_flexContentExistsLanguageIndependent('flexContent1LanguageIndependent') && $obj_variant->_flexContentsLanguageIndependent['flexContent1LanguageIndependent'] == 'sample-piece') {
                continue;
            }
            if ($obj_variant->_priceBeforeTax < $float_lowestPriceBeforeTax || $int_count == 0) {
                $float_lowestPriceBeforeTax = $obj_variant->_priceBeforeTax;
            }
            $int_count++;
        }
        return $float_lowestPriceBeforeTax;
    }
	
    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['getProductData_unscaledPriceCheapestVariantBeforeTax'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_getProductData_unscaledPriceCheapestVariantBeforeTax');
     *
     * -- Invocation: --
     * When Merconis determines the price of the cheapest unscaled product variant
     *
     * -- Parameters: --
     * 	1. $obj_product - the product object
     *
     * -- Return value: --
     * $float_lowestPriceBeforeTax - holding the price that should be considered as the cheapest unscaled variant price
     *
     * -- Objective: --
     * e.g. skipping variants which don't have a realistic price. This can be necessary if a sample piece of the product is among the product's variants.
     *
     */
    public function merconis_hook_getProductData_unscaledPriceCheapestVariantBeforeTax($obj_product) {
        $float_lowestPriceBeforeTax = $obj_product->_unscaledPriceBeforeTax;
        $int_count = 0;
        foreach ($obj_product->_variants as $obj_variant) {
            if ($obj_variant->_flexContentExistsLanguageIndependent('flexContent1LanguageIndependent') && $obj_variant->_flexContentsLanguageIndependent['flexContent1LanguageIndependent'] == 'sample-piece') {
                continue;
            }
            if ($obj_variant->_unscaledPriceBeforeTax < $float_lowestPriceBeforeTax || $int_count == 0) {
                $float_lowestPriceBeforeTax = $obj_variant->_unscaledPriceBeforeTax;
            }
            $int_count++;
        }
        return $float_lowestPriceBeforeTax;
    }
	
    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['checkIfCacheCanBeUsed'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_checkIfCacheCanBeUsed');
     *
     * -- Invocation: --
     * When Merconis checks whether a product search must actually be performed or whether a previous search result
     * can be used from the cache.
     *
     * -- Parameters: --
     * 	1. $str_productListId - information about which product list this call is related to
     *  2. $bln_cacheCanBeUsed - true/false indicating whether or not the cache can be used based on prior checks in the merconis core
     *
     * -- Return value: --
     * $bln_cacheCanBeUsed (true/false) to indicate whether or not to use the cache
     *
     * -- Objective: --
     * e.g. disabling the cache on a project level based on product list ids. This can be necessary if product list
     * outputs are being manipulated with hooks.
     *
     */
    public function merconis_hook_checkIfCacheCanBeUsed($str_productListId, $bln_cacheCanBeUsed) {
        if ($str_productListId === 'standard') {
            $bln_cacheCanBeUsed = false;
        }

        return $bln_cacheCanBeUsed;
    }
	
    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['preparingOrderDataToStore'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_preparingOrderDataToStore');
     *
     * -- Invocation: --
     * When the order data is prepared to be stored in the database
     *
     * -- Parameters: --
     * 	1. $arr_order - the order array that can be manipulated with this hook
     *
     * -- Return value: --
     * $arr_order - the possibly manipulated order array
     *
     * -- Objective: --
     * e.g. manipulate the order data before it is stored in the database
     *
     */
    public static function merconis_hook_preparingOrderDataToStore($arr_order) {
        /*
         * Manipulate $arr_order here...
         */

        return $arr_order;
    }

    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['replaceWidgetTemplateForReview'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_replaceWidgetTemplateForReview');
     *
     * -- Invocation: --
     * When a form field is rendered while creating an automatic form review
     *
     * -- Parameters: --
     * 	1. $arrWidgetTypesToParse - an array that holds form field type names as keys and the names of templates to use for rendering the specific form field review as values
     * 	2. $objField
     *  3. $objWidget
     *  4. $strForm
     *  5. $arrForm
     *
     * -- Return value: --
     * $arr_item
     *
     * -- Objective: --
     * e.g. customizing the way an automatically created form review renders specific form field types
     *
     */
    public function merconis_hook_replaceWidgetTemplateForReview($arrWidgetTypesToParse, $objField, $objWidget, $strForm, $arrForm) {
        /*
         * In this example, we make sure that in a form review a calendar field, which comes with a third party extension
         * that Merconis normally would know nothing about, is being rendered as simple text.
         */
        $arrWidgetTypesToParse['calendar'] = 'form_ls_shop_reviewWidget';
        return $arrWidgetTypesToParse;
    }

    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['storeCartItemInOrder'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_storeCartItemInOrder');
     *
     * -- Invocation: --
     * When the order is being created during checkout and a cart item is written
     * to the order record.
     *
     * -- Parameters: --
     * 	1. $arr_item - an array holding the cart item data that has been processed so far
     * 	2. $obj_product - the product object which holds product information that can be used to extend the data stored in $arr_item
     *
     * -- Return value: --
     * $arr_item
     *
     * -- Objective: --
     * e.g. extending the cart item data that is stored in an order record
     *
     */
    public function merconis_hook_storeCartItemInOrder($arr_item, $obj_product) {
        /*
         * In this example, we assume that we created a custom extension that
         * extended the product's mainData by adding an "isUsed" flag and an
         * "isSinglePiece" flag. We want both flags to be accessible in a finished
         * order.
         *
         * We use the "extendedInfo" key of $arr_item to store the data because
         * this key is already provided for exactly this reason.
         */
        $arr_item['extendedInfo']['_isUsed'] = $obj_product->mainData['customMerconisExtension_isUsed'] ? true : false;
        $arr_item['extendedInfo']['_isSinglePiece'] = $obj_product->mainData['customMerconisExtension_isSinglePiece'] ? true : false;

        return $arr_item;
    }
	
    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['afterProductSearchBeforeFilter'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_afterProductSearchBeforeFilter');
     *
     * -- Invocation: --
     * After a product search has been performed and before the resulting product list is filtered.
     *
     * -- Parameters: --
     * 	1. $productListID - information about which product list this call is related to
     * 	2. $arrProducts - an array containing product ids as keys and product information arrays as values.
     *
     * -- Return value: --
     * $arrProducts
     *
     * -- Objective: --
     * e.g. manipulation of the product list output (e.g. adding a custom filter functionality
     * that can be combined with the standard filter)
     *
     */
    public function merconis_hook_afterProductSearchBeforeFilter($productListID, $arrProducts) {
	/*
	 * Example: Removing all products which don't have variants.
	 */
        if ($str_productListID !== 'standard') {
            return $arr_products;
        }

        if (!is_array($arr_products) || !count($arr_products)) {
            return $arr_products;
        }
	    
	foreach ($arr_products as $int_key => $arr_product) {
            if (!is_array($arr_product['variants']) || !count($arr_product['variants'])) {
		    unset($arr_products[$int_key]);
	    }
	}

        return $arr_products;
    }


    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['beforeProductlistOutputBeforePagination'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_beforeProductlistOutputBeforePagination');
     *
     * -- Invocation: --
     * Before a product list is rendered and before the pagination is created.
     * Make sure to use this hook instead of "beforeProductlistOutput" if you manipulate
     * the product list in a way that affects the pagination. Also use this hook if you
     * want your manipulation to be cached when Merconis caches the search result.
     *
     * -- Parameters: --
     * 	1. $productListID - information about which product list this call is related to
     * 	2. $arrProducts - an array containing the product ids of the products to display in the product list
     *
     * -- Return value: --
     * $arrProducts
     *
     * -- Objective: --
     * e.g. manipulation of the product list output (custom crossSeller types can
     * be created this way)
     *
     */
    public function merconis_hook_beforeProductlistOutputBeforePagination($productListID, $arrProducts) {
        /*
         * Example: Manipulate the product list of a specific crossSeller
         * in order to create a custom crossSeller type (here: random crossSeller)
         */
        if ($productListID == 'crossSeller_42') {
            $arrProducts = array();

            $productsInDbWithOddIDs = \Database::getInstance()->prepare("
				SELECT		`id`
				FROM		`tl_ls_shop_product`
				ORDER BY	RAND()
			")
                ->limit(10)
                ->execute();

            while ($productsInDbWithOddIDs->next()) {
                $arrProducts[] = $productsInDbWithOddIDs->id;
            }
        }

        return $arrProducts;
    }

    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['customAjaxHook'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_customAjaxHook');
     *
     * -- Invocation: --
     * When the "_hookedFunction" magic method is called on a product or variant object
     *
     * -- Parameters: --
     * none
     *
     * -- Return value: --
     * $arr_response - The not yet json-encoded ajax response with the following structure
     *
     * 	$arr_response = array(
     *		'success' => null, // true if the ajax call was successfull
     *		'value' => null, // some return value, free to use
     *		'error' => null // true if the ajax call was unsuccessfull
     *	);
     *
     * -- Objective: --
     * e.g. integrating custom ajax functionality using the merconis ajax module, which is
     * already installed and usable in a standard merconis setup
     *
     */
    public function merconis_hook_customAjaxHook() {
        /*
         * Custom code
         */
        $arr_response = array(
            'success' => null, // true if the ajax call was successfull
            'value' => null, // some return value, free to use
            'error' => null // true if the ajax call was unsuccessfull
        );

        return $arr_response;
    }


    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['callingHookedProductOrVariantFunction'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_callingHookedProductOrVariantFunction');
     *
     * -- Invocation: --
     * When the "_hookedFunction" magic method is called on a product or variant object
     *
     * -- Parameters: --
     * 1. $objProductOrVariant - The product or variant object, best to be received as a reference
     * 2. $strProductOrVariant - either 'product' or 'variant' indicating what $objProductOrVariant contains
     * 3. $customArgument - The custom argument specified when calling "_hookedFunction" on the product or variant object
     *
     * -- Return value: --
     * Can be anything or even nothing. The "_hookedFunction" call returns exactly what this function returns
     *
     * -- Objective: --
     * e.g. providing custom product/variant data or behaviour
     *
     */
    public function merconis_hook_callingHookedProductOrVariantFunction(&$objProductOrVariant, $strProductOrVariant, $customArgument) {
        /*
         * Custom code
         */
        return 'whatever';
    }


    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['prepareProductTemplate'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_prepareProductTemplate');
     *
     * -- Invocation: --
     * When the product template is prepared
     *
     * -- Parameters: --
     * 1. $objTemplate - the frontend template object
     * 2. $strTemplateName - the template name as a string
     * 3. $objProduct - the product object
     *
     * -- Return value: --
     * none
     *
     * -- Objective: --
     * e.g. passing custom data to the template (use the template object as a reference in this case)
     *
     */
    public function merconis_hook_prepareProductTemplate(&$objTemplate, $strTemplateName, $objProduct) {
        /*
         * Custom code
         */
    }


    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['onReceivingConfiguratorInput'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_onReceivingConfiguratorInput');
     *
     * -- Invocation: --
     * When currently received configurator input is being processed
     *
     * -- Parameters: --
     * none
     *
     * -- Return value: --
     * none
     *
     * -- Objective: --
     * e.g. implementing custom behaviour when configurator input is being processed
     *
     */
    public function merconis_hook_onReceivingConfiguratorInput() {
        /*
         * Custom code
         */
    }


    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['manipulateProductOrVariantData'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_manipulateProductOrVariantData');
     *
     * -- Invocation: --
     * Directly after the product or variant data is retrieved from the database
     *
     * -- Parameters: --
     * 1. $arrData - An array holding the product or variant data
     * 2. $strProductOrVariant - either 'product' or 'variant' indicating what kind of data $arrData holds
     *
     * -- Return value: --
     * $arrData - The possibly modified data array
     *
     * -- Objective: --
     * e.g. manipulation of product or variant data
     *
     */
    public function merconis_hook_manipulateProductOrVariantData($arrData, $strProductOrVariant) {
        /*
         * Custom code
         */
        return $arrData;
    }


    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['modifyPaymentModuleTypes'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_modifyPaymentModuleTypes');
     *
     * -- Invocation: --
     * When initializing the payment module system
     *
     * -- Parameters: --
     * 1. $arrPaymentModuleTypes - An array holding information about registered payment modules
     *
     * -- Return value: --
     * $arrPaymentModuleTypes - The possible modified array holding all registered payment module information
     *
     * -- Objective: --
     * e.g. registering a custom payment module
     *
     */
    public function merconis_hook_modifyPaymentModuleTypes($arrPaymentModuleTypes) {
        /*
         * Custom code
         */
        return $arrPaymentModuleTypes;
    }


    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['import_begin'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_import_begin');
     *
     * -- Invocation: --
     * When a new product import starts
     *
     * -- Parameters: --
     * none
     *
     * -- Return value: --
     * none
     *
     * -- Objective: --
     * e.g. making preparations for own functionality hooked with the other import hooks
     *
     */
    public function merconis_hook_import_begin() {
        /*
         * Custom code
         */
    }


    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['import_finished'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_import_finished');
     *
     * -- Invocation: --
     * When the product import is finished
     *
     * -- Parameters: --
     * none
     *
     * -- Return value: --
     * none
     *
     * -- Objective: --
     * e.g. performing final actions after own functionality hooked with the other import hooks
     *
     */
    public function merconis_hook_import_finished() {
        /*
         * Custom code
         */
    }


    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['import_beforeProcessingProductData'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_import_beforeProcessingProductData');
     *
     * -- Invocation: --
     * Before a single product's raw data from the import file is processed
     *
     * -- Parameters: --
     * 1. $arrRow - the product data array
     *
     * -- Return value: --
     * $arrRow - the possibly manipulated product data array
     *
     * -- Objective: --
     * e.g. manipulating the product data
     *
     */
    public function merconis_hook_import_beforeProcessingProductData($arrRow) {
        /*
         * Custom code
         */
        return $arrRow;
    }


    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['import_beforeWritingProductData'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_import_beforeWritingProductData');
     *
     * -- Invocation: --
     * Before a single product's processed data is written to the database
     *
     * -- Parameters: --
     * 1. $arrRow - the product data array
     * 2. $intAlreadyExistsAsID - the product's database id if it already exists or false if the product doesn't exist in the database yet
     *
     * -- Return value: --
     * $arrRow - the possibly manipulated product data array
     *
     * -- Objective: --
     * e.g. manipulating the product data
     *
     */
    public function merconis_hook_import_beforeWritingProductData($arrRow, $intAlreadyExistsAsID) {
        /*
         * Custom code
         */
        return $arrRow;
    }


    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['import_afterUpdatingProductData'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_import_afterUpdatingProductData');
     *
     * -- Invocation: --
     * After the product data of an already existing product has been updated in the database
     *
     * -- Parameters: --
     * 1. $intProductID - the product's database id
     *
     * -- Return value: --
     * none
     *
     * -- Objective: --
     * e.g. manipulating the product data
     *
     */
    public function merconis_hook_import_afterUpdatingProductData($intProductID) {
        /*
         * Custom code
         */
    }


    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['import_afterInsertingProductData'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_import_afterInsertingProductData');
     *
     * -- Invocation: --
     * After the product data of a new product has been inserted into the database
     *
     * -- Parameters: --
     * 1. $intProductID - the product's database id
     *
     * -- Return value: --
     * none
     *
     * -- Objective: --
     * e.g. manipulating the product data
     *
     */
    public function merconis_hook_import_afterInsertingProductData($intProductID) {
        /*
         * Custom code
         */
    }


    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['import_beforeProcessingVariantData'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_import_beforeProcessingVariantData');
     *
     * -- Invocation: --
     * Before a single variant's raw data from the import file is processed
     *
     * -- Parameters: --
     * 1. $arrRow - the variant data array
     *
     * -- Return value: --
     * $arrRow - the possibly manipulated variant data array
     *
     * -- Objective: --
     * e.g. manipulating the variant data
     *
     */
    public function merconis_hook_import_beforeProcessingVariantData($arrRow) {
        /*
         * Custom code
         */
        return $arrRow;
    }


    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['import_beforeWritingVariantData'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_import_beforeWritingVariantData');
     *
     * -- Invocation: --
     * Before a single variant's processed data is written to the database
     *
     * -- Parameters: --
     * 1. $arrRow - the variant data array
     * 2. $intAlreadyExistsAsID - the variant's database id if it already exists or false if the product doesn't exist in the database yet
     * 3. $intParentProductID - the parent product's database id
     *
     * -- Return value: --
     * $arrRow - the possibly manipulated variant data array
     *
     * -- Objective: --
     * e.g. manipulating the variant data
     *
     */
    public function merconis_hook_import_beforeWritingVariantData($arrRow, $intAlreadyExistsAsID, $intParentProductID) {
        /*
         * Custom code
         */
        return $arrRow;
    }


    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['import_afterUpdatingVariantData'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_import_afterUpdatingVariantData');
     *
     * -- Invocation: --
     * After the variant data of an already existing variant has been updated in the database
     *
     * -- Parameters: --
     * 1. $intVariantID - the variant's database id
     *
     * -- Return value: --
     * none
     *
     * -- Objective: --
     * e.g. manipulating the variant data
     *
     */
    public function merconis_hook_import_afterUpdatingVariantData($intVariantID) {
        /*
         * Custom code
         */
    }


    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['import_afterInsertingVariantData'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_import_afterInsertingVariantData');
     *
     * -- Invocation: --
     * After the variant data of a new variant has been inserted into the database
     *
     * -- Parameters: --
     * 1. $intVariantID - the variant's database id
     *
     * -- Return value: --
     * none
     *
     * -- Objective: --
     * e.g. manipulating the variant data
     *
     */
    public function merconis_hook_import_afterInsertingVariantData($intVariantID) {
        /*
         * Custom code
         */
    }


    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['import_beforeProcessingProductLanguageData'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_import_beforeProcessingProductLanguageData');
     *
     * -- Invocation: --
     * Before a single product language data row is processed
     *
     * -- Parameters: --
     * 1. $arrRow - the language data array
     *
     * -- Return value: --
     * $arrRow - the possibly manipulated language data array
     *
     */
    public function merconis_hook_import_beforeProcessingProductLanguageData($arrRow) {
        /*
         * Custom code
         */
        return $arrRow;
    }


    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['import_beforeWritingProductLanguageData'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_import_beforeWritingProductLanguageData');
     *
     * -- Invocation: --
     * Before a single already processed product language data row is processed
     *
     * -- Parameters: --
     * 1. $arrRow - the language data array
     * 2. $intParentProductID - the parent product's id
     *
     * -- Return value: --
     * $arrRow - the possibly manipulated language data array
     *
     */
    public function merconis_hook_import_beforeWritingProductLanguageData($arrRow, $intParentProductID) {
        /*
         * Custom code
         */
        return $arrRow;
    }


    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['import_afterWritingProductLanguageData'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_import_afterWritingProductLanguageData');
     *
     * -- Invocation: --
     * Before a single already processed product language data row is processed
     *
     * -- Parameters: --
     * 1. $intParentProductID - the parent product's id
     *
     * -- Return value: --
     * none
     *
     */
    public function merconis_hook_import_afterWritingProductLanguageData($intParentProductID) {
        /*
         * Custom code
         */
    }


    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['import_beforeProcessingVariantLanguageData'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_import_beforeProcessingVariantLanguageData');
     *
     * -- Invocation: --
     * Before a single variant language data row is processed
     *
     * -- Parameters: --
     * 1. $arrRow - the language data array
     *
     * -- Return value: --
     * $arrRow - the possibly manipulated language data array
     *
     */
    public function merconis_hook_import_beforeProcessingVariantLanguageData($arrRow) {
        /*
         * Custom code
         */
        return $arrRow;
    }


    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['import_beforeWritingVariantLanguageData'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_import_beforeWritingVariantLanguageData');
     *
     * -- Invocation: --
     * Before a single already processed product language data row is processed
     *
     * -- Parameters: --
     * 1. $arrRow - the language data array
     * 2. $intParentVariantID - the parent variant's id
     *
     * -- Return value: --
     * $arrRow - the possibly manipulated language data array
     *
     */
    public function merconis_hook_import_beforeWritingVariantLanguageData($arrRow, $intParentVariantID) {
        /*
         * Custom code
         */
        return $arrRow;
    }


    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['import_afterWritingVariantLanguageData'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_import_afterWritingVariantLanguageData');
     *
     * -- Invocation: --
     * Before a single already processed product language data row is processed
     *
     * -- Parameters: --
     * 1. $intParentVariantID - the parent variant's id
     *
     * -- Return value: --
     * none
     *
     */
    public function merconis_hook_import_afterWritingVariantLanguageData($intParentVariantID) {
        /*
         * Custom code
         */
    }



    ###

    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['beforeSendingOrderMessage'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_beforeSendingOrderMessage');
     *
     * -- Invocation: --
     * Before sending an order message
     *
     * -- Parameters: --
     * 	1. $arrMessageModel - the message model details
     * 	2. $arrOrder - the order details
     *
     * -- Return value: --
     * $arrMessageModel - the possibly manipulated message model details or null, if the message should not be sent
     *
     * -- Objective: --
     * e.g. manipulate the message model details or prevent a message from being sent under certain circumstances
     *
     */
    public function merconis_hook_beforeSendingOrderMessage($arrMessageModel, $arrOrder) {
        /*
         * In this example we have two message types which would be used as order confirmations.
         * One message type should only be used on fridays because it contains a text regarding
         * the coming weekend and the other should be used on every other day.
         * This hook would be called for both of them and it could decide which one should actually be sent.
         */
        $blnIsFriday = date('D') == 'Fri';

        /*
         * the parent id of the message model is the id of a message type. In this example the
         * message type with the id 5 is the one to send on friday and the message type with
         * the id 2 is the one to send on every other day.
         */
        if ($arrMessageModel['pid'] == 5 && !$blnIsFriday) {
            return null;
        }

        if ($arrMessageModel['pid'] == 2 && $blnIsFriday) {
            return null;
        }

        /*
         * In the second part of this example, we replace a custom wildcard with a coupon code
         * if the order amount is bigger than 500. If we modify multilanguage data, we have to make
         * sure to deal with the contents of the $arrMessageModel['multilanguage'] array.
         */
        $couponCode = $arrOrder['total'] >= 500 ? 'COUPONCODE500' : '';

        $arrMessageModel['multilanguage']['content_html'] = preg_replace('/(&#35;&#35;bigBuyerCouponCode&#35;&#35;)|(##bigBuyerCouponCode##)/siU', $couponCode, $arrMessageModel['multilanguage']['content_html']);
        $arrMessageModel['multilanguage']['content_rawtext'] = preg_replace('/(&#35;&#35;bigBuyerCouponCode&#35;&#35;)|(##bigBuyerCouponCode##)/siU', $couponCode, $arrMessageModel['multilanguage']['content_rawtext']);

        return $arrMessageModel;
    }

    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['beforeAddToCart'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_beforeAddToCart');
     *
     * -- Invocation: --
     * Before adding a new cart item to the cart
     *
     * -- Parameters: --
     * 	1. $arrItemInfoToAddToCart - the item info that will be added to the cart and that can be manipulated with this hook
     * 	2. $objProductOrVariant - the product or variant object
     *
     * -- Return value: --
     * $arrItemInfoToAddToCart - the possibly manipulated item info that will be put to the cart
     *
     * -- Objective: --
     * e.g. add specific information to a cart item, for example to use this information with a custom logic for detecting
     * the scale price quantity
     *
     */
    public function merconis_hook_beforeAddToCart($arrItemInfoToAddToCart, $objProductOrVariant) {
        /*
         * Example: Add the product code to the cart item information. This
         * could be used in the hook "getScalePriceQuantity" to change the way
         * the scale price quantity is calculated. For example, all cart items
         * with the same product code prefix could be grouped.
         */
        $arrItemInfoToAddToCart['productCode'] = $objProductOrVariant->_code;
        return $arrItemInfoToAddToCart;
    }

    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['getScalePriceQuantity'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_getScalePriceQuantity');
     *
     * -- Invocation: --
     * When detecting the quantity used for the following scale price calculation
     *
     * -- Parameters: --
     * 	1. $objProductOrVariant - the product or variant object
     *  2. $type - 'product' or 'variant' to tell whether the hook is called for a product or a variant
     *  3. $cartKey - the product's or the variant's cart key (productID-variantID_configuratorHash)
     *
     * -- Return value: --
     * $scalePriceQuantity - the detected quantity
     *
     * -- Objective: --
     * e.g. implement a custom logic to detect the scale price quantity
     *
     */
    public function merconis_hook_getScalePriceQuantity($objProductOrVariant, $type, $cartKey) {
        $scalePriceQuantity = 0;

        /*
         * Custom code to detect the quantity.
         *
         * The cart information in the session should be considered:
         * $_SESSION['lsShopCart']['items']
         *
         * In this example cart items whose product code's first character match,
         * will be grouped. If the product's settings demand different configurations
         * to be separated, this is respected in this example.
         *
         * the product code has been made available in the cart items' information
         * using the hook "beforeAddToCart". Please take a look at the hook's example code.
         */

        $arrSplitCartKey = \Merconis\Core\ls_shop_generalHelper::splitProductVariantID($cartKey);

        foreach ($_SESSION['lsShopCart']['items'] as $itemCartKey => $arrItemInfo) {
            $arrSplitItemCartKey = \Merconis\Core\ls_shop_generalHelper::splitProductVariantID($itemCartKey);

            if (substr($objProductOrVariant->_code, 0, 1) == substr($arrItemInfo['productCode'], 0, 1)) {
                if ($objProductOrVariant->_scalePriceQuantityDetectionAlwaysSeparateConfigurations && $arrSplitCartKey['configuratorHash'] != $arrSplitItemCartKey['configuratorHash']) {
                    continue;
                }
                $scalePriceQuantity = $scalePriceQuantity + $arrItemInfo['quantity'];
            }
        }

        return $scalePriceQuantity;
    }

    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['calculateScaledPrice'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_calculateScaledPrice');
     *
     * -- Invocation: --
     * When calculating the scaled price for a product.
     *
     * -- Parameters: --
     * 	1. $objProductOrVariant - the product or variant object
     *
     * -- Return value: --
     * $calculatedScaledPrice - the calculated scale price
     *
     * -- Objective: --
     * e.g. implement a custom scale price calculation logic
     *
     */
    public function merconis_hook_calculateScaledPrice($objProductOrVariant) {
        $calculatedScaledPrice = 12345;

        /*
         * Custom code to calculate the scaled price.
         * The following product or variant properties should be considered:
         *
         * $objProductOrVariant->_scalePrice
         * $objProductOrVariant->_scalePriceQuantity
         * $objProductOrVariant->_scalePriceType
         *
         */

        return $calculatedScaledPrice;
    }

    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['afterCheckout'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_afterCheckout');
     *
     * -- Invocation: --
     * Right after the order has been finished.
     *
     * -- Parameters: --
     * 	1. $orderID - the database id of the currently finished order
     * 	2. $order - an array containing the order details
     *
     * -- Return value: --
     * none
     *
     * -- Objective: --
     * e.g. exchange with external systems (inventory control systems etc.)
     *
     */
    public function merconis_hook_afterCheckout($orderID, $order) {
        /*
         * Custom code
         */
    }

    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['beforeAjaxSearch'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_beforeAjaxSearch');
     *
     * -- Invocation: --
     * Before an ajax search is performed.
     *
     * -- Parameters: --
     * 	1. $arrSearchCriteria - an array containing the search criteria
     *
     * -- Return value: --
     * $arrSearchCriteria
     *
     * -- Objective: --
     * e.g. manipulation of the search criteria
     *
     */
    public function merconis_hook_beforeAjaxSearch($arrSearchCriteria) {
        /*
         * Example: Extend the search term if it contains a specific word
         */
        if (strstr($arrSearchCriteria['fulltext'], 'aperiam')) {
            $arrSearchCriteria['fulltext'] .= ' gubergren';
        }

        return $arrSearchCriteria;
    }

    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['afterAjaxSearch'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_afterAjaxSearch');
     *
     * -- Invocation: --
     * After an ajax search has been performed.
     *
     * -- Parameters: --
     * 	1. $arrSearchCriteria - an array containing the search criteria
     * 	2. $arrProducts - an array containing the product ids in the result set
     *
     * -- Return value: --
     * $arrProducts
     *
     * -- Objective: --
     * e.g. profiling the user's search behaviour, manipulation of the search result
     *
     */
    public function merconis_hook_afterAjaxSearch($arrSearchCriteria, $arrProducts) {
        /*
         * Example: Add a product to the result set if it is not in it yet
         * but another product (possibly somehow related to it) is.
         */
        if (in_array(577, $arrProducts) && !in_array(564, $arrProducts)) {
            $arrProducts[] = 564;
        }

        /*
         * If you need to access detailed product information, you can
         * get the product object like this:
         */
        $objLsShopController = \System::importStatic('ls_shop_controller');
        foreach ($arrProducts as $productID) {
            $objProduct = $objLsShopController->getObjProduct($productID);
            /*
             * do something...
             */
        }

        return $arrProducts;
    }

    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['beforeSearch'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_beforeSearch');
     *
     * -- Invocation: --
     * Before a search is performed.
     *
     * -- Parameters: --
     * 	1. $arrSearchCriteria - an array containing the search criteria
     *
     * -- Return value: --
     * $arrSearchCriteria
     *
     * -- Objective: --
     * e.g. manipulation of the search criteria
     *
     */
    public function merconis_hook_beforeSearch($arrSearchCriteria) {
        /*
         * Example: Extend the search term if it contains a specific word
         */
        if (strstr($arrSearchCriteria['fulltext'], 'aperiam')) {
            $arrSearchCriteria['fulltext'] .= ' gubergren';
        }

        return $arrSearchCriteria;
    }

    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['afterSearch'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_afterSearch');
     *
     * -- Invocation: --
     * After a search has been performed.
     *
     * -- Parameters: --
     * 	1. $arrSearchCriteria - an array containing the search criteria
     * 	2. $arrProducts - an array containing the product ids in the result set
     *
     * -- Return value: --
     * $arrProducts
     *
     * -- Objective: --
     * e.g. profiling the user's search behaviour, manipulation of the search result
     *
     */
    public function merconis_hook_afterSearch($arrSearchCriteria, $arrProducts) {
        /*
         * Example: Add a product to the result set if it is not in it yet
         * but another product (possibly somehow related to it) is.
         */
        if (in_array(577, $arrProducts) && !in_array(564, $arrProducts)) {
            $arrProducts[] = 564;
        }

        /*
         * If you need to access detailed product information, you can
         * get the product object like this:
         */
        $objLsShopController = \System::importStatic('ls_shop_controller');
        foreach ($arrProducts as $productID) {
            $objProduct = $objLsShopController->getObjProduct($productID);
            /*
             * do something...
             */
        }

        return $arrProducts;
    }

    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['beforeProductlistOutput'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_beforeProductlistOutput');
     *
     * -- Invocation: --
     * Before a product list is rendered.
     *
     * -- Parameters: --
     * 	1. $productListID - information about which product list this call is related to
     * 	2. $arrProducts - an array containing the product ids of the products to display in the product list
     *
     * -- Return value: --
     * $arrProducts
     *
     * -- Objective: --
     * e.g. manipulation of the product list output (custom crossSeller types can
     * be created this way)
     *
     */
    public function merconis_hook_beforeProductlistOutput($productListID, $arrProducts) {
        /*
         * Example: Manipulate the product list of a specific crossSeller
         * in order to create a custom crossSeller type (here: random crossSeller)
         */
        if ($productListID == 'crossSeller_42') {
            $arrProducts = array();

            $productsInDbWithOddIDs = \Database::getInstance()->prepare("
				SELECT		`id`
				FROM		`tl_ls_shop_product`
				ORDER BY	RAND()
			")
                ->limit(10)
                ->execute();

            while ($productsInDbWithOddIDs->next()) {
                $arrProducts[] = $productsInDbWithOddIDs->id;
            }
        }

        return $arrProducts;
    }

    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['beforeProductSingleviewOutput'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_beforeProductSingleviewOutput');
     *
     * -- Invocation: --
     * Before a product is displayed in the singleview.
     *
     * -- Parameters: --
     * 	1. $productID - the id of the product to be displayed in the singleview
     *
     * -- Return value: --
     * none
     *
     * -- Objective: --
     * e.g. profiling user behaviour
     *
     */
    public function merconis_hook_beforeProductSingleviewOutput($productID) {
        /*
         * Custom code
         */
    }

    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['addToCart'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_addToCart');
     *
     * -- Invocation: --
     * After a product has been put into the cart
     *
     * -- Parameters: --
     * 	1. $objProduct - the product object of the product that has just been put into the cart
     * 	2. $desiredQuantity - the quantity that the user wanted to put into the cart
     * 	3. $quantityPutInCart - the quantity that has actually been put into the cart, which might differ if stock was insufficient
     *
     * -- Return value: --
     * none
     *
     * -- Objective: --
     * e.g. profiling user behaviour
     *
     */
    public function merconis_hook_addToCart($objProduct, $desiredQuantity, $quantityPutInCart) {
        /*
         * Custom code
         */
    }

    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['beforeRedirectionToSeparateDataEntryPage'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_beforeRedirectionToSeparateDataEntryPage');
     *
     * -- Invocation: --
     * Before the user is redirected to the separate data entry page
     *
     * -- Parameters: --
     * none
     *
     * -- Return value: --
     * none
     *
     * -- Objective: --
     * e.g. profiling user behaviour
     *
     */
    public function merconis_hook_beforeRedirectionToSeparateDataEntryPage() {
        /*
         * Custom code
         */
    }

    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['beforeRedirectionBackToCart'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_beforeRedirectionBackToCart');
     *
     * -- Invocation: --
     * Before the user is redirected back to the cart
     *
     * -- Parameters: --
     * none
     *
     * -- Return value: --
     * none
     *
     * -- Objective: --
     * e.g. profiling user behaviour
     *
     */
    public function merconis_hook_beforeRedirectionBackToCart() {
        /*
         * Custom code
         */
    }

    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['beforeRedirectionToReviewOrderPage'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_beforeRedirectionToReviewOrderPage');
     *
     * -- Invocation: --
     * Before the user is redirected to the order review page
     *
     * -- Parameters: --
     * none
     *
     * -- Return value: --
     * none
     *
     * -- Objective: --
     * e.g. profiling user behaviour
     *
     */
    public function merconis_hook_beforeRedirectionToReviewOrderPage() {
        /*
         * Custom code
         */
    }

    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['paymentOptionSelected'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_paymentOptionSelected');
     *
     * -- Invocation: --
     * When the user selects a payment method
     *
     * -- Parameters: --
     * 	1. $methodID - the id of the selected payment method
     *
     * -- Return value: --
     * none
     *
     * -- Objective: --
     * e.g. profiling user behaviour
     *
     */
    public function merconis_hook_paymentOptionSelected($methodID) {
        /*
         * Custom code
         */
    }

    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['shippingOptionSelected'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_shippingOptionSelected');
     *
     * -- Invocation: --
     * When the user selects a shipping method
     *
     * -- Parameters: --
     * 	1. $methodID - the id of the selected shipping method
     *
     * -- Return value: --
     * none
     *
     * -- Objective: --
     * e.g. profiling user behaviour
     *
     */
    public function merconis_hook_shippingOptionSelected($methodID) {
        /*
         * Custom code
         */
    }

    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['initializeCartController'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_initializeCartController');
     *
     * -- Invocation: --
     * When the cart controller is initialized
     *
     * -- Parameters: --
     * 	1. $cart - an array holding all cart information
     * 	2. $itemsExtended - an array holding extended item information
     * 	3. $calculation - an array holding current calculation details
     *
     * -- Return value: --
     * none
     *
     * -- Objective: --
     * e.g. cart manipulation
     *
     */
    public function merconis_hook_initializeCartController($cart, $itemsExtended, $calculation) {
        /*
         * Example: Adding products to the cart depending on the products that are currently in the cart.
         * In this example we assume that we have different brochures that we want to hand out if someone
         * orders a related product.
         *
         * We use the product's flexContent to store the relation between a product and a brochure
         * (flexContent key "associatedBrochures").
         *
         * Please note that this example is oversimplified in order to make the basic idea behind this hook
         * better understandable. This function is not intended to be used as it is in a real life scenario.
         * You will most likely have to complete this function and add some control mechanisms, trim the
         * comma separated list etc.
         */

        $arrBrochures = array();
        $arrBrochureIDs = array();

        /*
         * Walking through the cart item, using the extended array because we want to access
         * extended product information for which we need the product object
         */
        foreach ($itemsExtended as $item) {
            /*
             * If the product has a corresponding flexContent, we assume it's a comma separated list holding
             * the brochure's product codes.
             */
            if ($item['objProduct']->_flexContentExists('associatedBrochures')) {
                $arrBrochures = array_merge($arrBrochures, explode(',', $item['objProduct']->_flexContents['associatedBrochures']));
            }
        }
        $arrBrochures = array_unique($arrBrochures);

        /*
         * Get the product ids of the brochures
         */
        foreach ($arrBrochures as $brochureArtNr) {
            $objBrochureProduct = \Database::getInstance()->prepare("
				SELECT		`id`
				FROM		`tl_ls_shop_product`
				WHERE		`lsShopProductCode` = ?
					AND		`published` = ?
			")
                ->execute($brochureArtNr, '1');

            if ($objBrochureProduct->numRows) {
                $objBrochureProduct->first();
                $arrBrochureIDs[] = $objBrochureProduct->id;
            }
        }

        /*
         * We store the product ids of already added brochures in the session
         * because we don't want to add a brochure more than once.
         */
        if (!isset($_SESSION['myMerconis']['brochureIDsAddedToCart'])) {
            $_SESSION['myMerconis']['brochureIDsAddedToCart'] = array();
        }

        foreach ($arrBrochureIDs as $brochureID) {
            if (in_array($brochureID, $_SESSION['myMerconis']['brochureIDsAddedToCart'])) {
                continue;
            }
            \Merconis\Core\ls_shop_cartHelper::addToCart($brochureID, 1);
            $_SESSION['myMerconis']['brochureIDsAddedToCart'][] = $brochureID;
        }
    }

    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['modifyPaymentOrShippingMethodInfo'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_modifyPaymentOrShippingMethodInfo');
     *
     * -- Invocation: --
     * After the method info has been processed and before the method info is used by other functions
     *
     * -- Parameters: --
     *           1. $arrMethodInfo - an array holding the method info
     *           2. $type - the method type (shipping or payment)
     *
     * -- Return value: --
     * $arrMethodInfo - the possibly manipulated method info
     *
     * -- Objective: --
     * e.g. manipulate the method info, most likely in order to implement a custom method fee calculation
     *
     */
    public function merconis_hook_modifyPaymentOrShippingMethodInfo($arrMethodInfo, $type) {
        /*
         * If the logic of this function depends on customer data, e.g. the customers postal code, we can get it like this:
         *
         * $customerPostalCode = \Merconis\Core\ls_shop_checkoutData::getInstance()->arrCheckoutData['arrCustomerData']['postal']['value'];
         */

        /*
         * If the logic of this function depends on the current cart calculation, we can get it like this:
         *
         * $calculation = \Merconis\Core\ls_shop_cartX::getInstance()->calculation;
         */

        /*
         * If the logic of this function depends on specific details of the products that are currently contained in the cart,
         * we can get this information like this:
         *
         * $cartItems = \Merconis\Core\ls_shop_cartX::getInstance()->itemsExtended;
         * foreach ($cartItems as $cartItem) {
         *                     // $cartItem['objProduct'] holds a product object that can be used just like the product object available e.g. in product details templates.
         *                     $productTitle = $cartItem['objProduct']->_title;
         * }
         *
         */

        /*
         * This hook function gets called every time a method information for a shipping or
         * payment method is requested. Since we only want to manipulate the fee calculation
         * for a specific shipping method we have to abort this function by returning the
         * unmodified $arrMethodInfo if the currently requested method is not the one we want
         */
        if ($type != 'shipping' || $arrMethodInfo['id'] != 1) {
            return $arrMethodInfo;
        }

        /*
         * If we only want to modify the already calculated fee given in $arrMethodInfo
         * it is important to know that this fee value is already a "display price" which
         * means that the price is already calculated as a net or gross price depending
         * on what the customer should see.
         *
         * Example:
         *           - In the backend/database we define all prices as net prices.
         *  - The fee for a shipping method is 10 EUR net and it is taxed with 19 % VAT
         *  - The customer is not a business customer so he should see gross prices, in this case 10 EUR + 1.90 EUR VAT = 11.90 EUR
         *  - The fee contained in $arrMethodInfo is the display price 11.90 EUR
         *
         * When programming this fee calculation we don't know if the prices are stored as net or gross prices in the database
         * and whether the customer sees net or gross prices but it does not matter because we know that $arrMethodInfo['feePrice']
         * holds the correct display price.
         *
         * So, if we want to double the fee if it is after 6 pm but before 10 pm, we can simply multiply the fee by 2.
         *
         */
        if (date('G') >= 18 && date('G') < 22) {
            $arrMethodInfo['feePrice'] = $arrMethodInfo['feePrice'] * 2;
            return $arrMethodInfo;
        }

        /*
         * If we want to return a special fee if it is after 10 pm and we want to hardcode this value right
         * here in this function we have to make sure that we use a net or gross price depending on what we
         * use in the backend/database and that we convert this value into a display price. Because we
         * don't know whether the customer sees net or gross prices, we use the function "getDisplayPrice()"
         * which takes care of the correct conversion. The first parameter is the price that we want to convert
         * and the second parameter is the tax rate for the shipping or payment method. The third parameter has
         * to be set to false in this case.
         */
        else if (date('G') >= 22) {
            $arrMethodInfo['feePrice'] = \Merconis\Core\ls_shop_generalHelper::getDisplayPrice(35, $arrMethodInfo['steuersatz'], false);
            return $arrMethodInfo;
        }

        /*
         * If it is before 6 pm we do not manipulate the fee and therefore we return
         * the unmodified $arrMethodInfo.
         */
        else {
            return $arrMethodInfo;
        }
    }

    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['checkIfPaymentOrShippingMethodIsAllowed'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_checkIfPaymentOrShippingMethodIsAllowed');
     *
     * -- Invocation: --
     * When it's checked whether a payment or shipping method is allowed. If one
     * the standard checks performed by the Merconis core already determined that
     * a method is not allowed, this hook will not be called for this specific method.
     *
     *
     * -- Parameters: --
     * 1. $arr_method - an array holding the method info
     * 2. $str_type - the method type (shipping or payment)
     *
     * -- Return value: --
     * $bln_methodIsAllowed - true if the method is allowed, false if it's not
     *
     * -- Objective: --
     * implement custom rules defining when a method is allowed
     *
     */
    public function merconis_hook_checkIfPaymentOrShippingMethodIsAllowed($arr_method, $str_type) {
        /*
         * If the logic of this function depends on customer data, e.g. the customers postal code, we can get it like this:
         *
         * $customerPostalCode = \Merconis\Core\ls_shop_checkoutData::getInstance()->arrCheckoutData['arrCustomerData']['postal']['value'];
         */

        /*
         * If the logic of this function depends on the current cart calculation, we can get it like this:
         *
         * $calculation = \Merconis\Core\ls_shop_cartX::getInstance()->calculation;
         */

        /*
         * If the logic of this function depends on specific details of the products that are currently contained in the cart,
         * we can get this information like this:
         *
         * $cartItems = \Merconis\Core\ls_shop_cartX::getInstance()->itemsExtended;
         * foreach ($cartItems as $cartItem) {
         *                     // $cartItem['objProduct'] holds a product object that can be used just like the product object available e.g. in product details templates.
         *                     $productTitle = $cartItem['objProduct']->_title;
         * }
         *
         */

        /*
         * This hook function gets called every time a method information for a
         * shipping or payment method is requested. If we only want to deal with
         * a specific shipping method, we have to abort this function by returning
         * true if the currently requested method is not the one we want
         */
        if ($str_type != 'shipping' || $arr_method['id'] != 1) {
            return true;
        }

        /*
         * For example, we can allow the method only if it is between 18 pm and 20 pm
         */
        if (date('G') < 18 || date('G') > 20) {
            return false;
        }

        return true;
    }

    /*
     * -- Registration: --
     * $GLOBALS['MERCONIS_HOOKS']['sortPaymentOrShippingMethods'][] = array('Merconis\CustomStarterbase\merconis_custom_helper', 'merconis_hook_sortPaymentOrShippingMethods');
     *
     * -- Invocation: --
     * After the allowed payment or shipping methods have been determined
     *
     *
     * -- Parameters: --
     * 1. $arr_methods - an array holding all the methods
     * 2. $str_type - the method type (shipping or payment)
     *
     * -- Return value: --
     * $arr_methods - the modified/sorted methods array
     *
     * -- Objective: --
     * implement custom sorting of payment or shipping methods
     *
     */
    public function merconis_hook_sortPaymentOrShippingMethods($arr_methods, $str_type) {
        switch ($str_type) {
            case 'payment':
                krsort($arr_methods);
                break;

            case 'shipping':
                ksort($arr_methods);
                break;
        }

        return $arr_methods;
    }
}
