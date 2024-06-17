<?php

namespace Merconis\CustomHoehenflug;

use LeadingSystems\MerconisCustomHoehenflugBundle\Scheduler\Models\SchedulerJobModel;

if (TL_MODE == 'BE') {
	$GLOBALS['TL_CSS'][] = 'bundles/leadingsystemsmerconiscustom/be/css/style.css';
}

$GLOBALS['BE_MOD']['merconis_custom'] = array(
	'merconis_custom_dummy' => array(
		'tables' => array('tl_merconis_custom_dummy')
	),
	'merconis_custom_scheduler' => array(
		'tables' => array('tl_merconis_custom_scheduler_job')
	),
);

$GLOBALS['FE_MOD']['merconis_custom'] = array(
	'mod_merconis_custom_dummy' => 'Merconis\CustomHoehenflug\mod_merconis_custom_dummy'
);

// MODELS
$GLOBALS['TL_MODELS']['tl_merconis_custom_scheduler_job'] = SchedulerJobModel::class;

// API
$GLOBALS['LS_API_HOOKS']['apiReceiver_processRequest'][] = array('LeadingSystems\MerconisCustomHoehenflugBundle\API\APIGeneral', 'processRequest');


$GLOBALS['MERCONIS_HOOKS']['storeCartItemInOrder'][] = array('LeadingSystems\MerconisCustomHoehenflugBundle\Order\Storage\Manipulator', 'extendCartItemRecordForStorage');
$GLOBALS['MERCONIS_HOOKS']['preparingOrderDataToStore'][] = array('LeadingSystems\MerconisCustomHoehenflugBundle\Order\Storage\Manipulator', 'extendOrderRecordForStorage');

$GLOBALS['MERCONIS_HOOKS']['afterCheckout'][] = array('LeadingSystems\MerconisCustomHoehenflugBundle\Order\Distributor\Distributor', 'distribute');

$GLOBALS['MERCONIS_HOOKS']['manipulateLiveHit'][] = array('LeadingSystems\MerconisCustomHoehenflugBundle\Display\Product\LiveHits\Manipulator', 'manipulate');

$GLOBALS['MERCONIS_HOOKS']['checkIfPaymentOrShippingMethodIsAllowed'][] = array('LeadingSystems\MerconisCustomHoehenflugBundle\Shipping\OptionsChecker', 'checkIfAllowed');


//$GLOBALS['MERCONIS_HOOKS']['getProductData_priceCheapestVariantBeforeTax'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_getProductData_priceCheapestVariantBeforeTax');
//$GLOBALS['MERCONIS_HOOKS']['getProductData_unscaledPriceCheapestVariantBeforeTax'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_getProductData_unscaledPriceCheapestVariantBeforeTax');
//$GLOBALS['MERCONIS_HOOKS']['checkIfCacheCanBeUsed'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_checkIfCacheCanBeUsed');
//$GLOBALS['MERCONIS_HOOKS']['preparingOrderDataToStore'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_preparingOrderDataToStore');
//$GLOBALS['MERCONIS_HOOKS']['replaceWidgetTemplateForReview'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_replaceWidgetTemplateForReview');
//$GLOBALS['MERCONIS_HOOKS']['storeCartItemInOrder'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_storeCartItemInOrder');
//$GLOBALS['MERCONIS_HOOKS']['afterProductSearchBeforeFilter'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_afterProductSearchBeforeFilter');
//$GLOBALS['MERCONIS_HOOKS']['beforeProductlistOutputBeforePagination'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_beforeProductlistOutputBeforePagination');
//$GLOBALS['MERCONIS_HOOKS']['customAjaxHook'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_customAjaxHook');
//$GLOBALS['MERCONIS_HOOKS']['callingHookedProductOrVariantFunction'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_callingHookedProductOrVariantFunction');
//$GLOBALS['MERCONIS_HOOKS']['prepareProductTemplate'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_prepareProductTemplate');
//$GLOBALS['MERCONIS_HOOKS']['onReceivingConfiguratorInput'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_onReceivingConfiguratorInput');
//$GLOBALS['MERCONIS_HOOKS']['manipulateProductOrVariantData'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_manipulateProductOrVariantData');
//$GLOBALS['MERCONIS_HOOKS']['modifyPaymentModuleTypes'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_modifyPaymentModuleTypes');
//$GLOBALS['MERCONIS_HOOKS']['import_begin'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_import_begin');
//$GLOBALS['MERCONIS_HOOKS']['import_finished'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_import_finished');
//$GLOBALS['MERCONIS_HOOKS']['import_beforeProcessingProductData'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_import_beforeProcessingProductData');
//$GLOBALS['MERCONIS_HOOKS']['import_beforeWritingProductData'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_import_beforeWritingProductData');
//$GLOBALS['MERCONIS_HOOKS']['import_afterUpdatingProductData'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_import_afterUpdatingProductData');
//$GLOBALS['MERCONIS_HOOKS']['import_afterInsertingProductData'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_import_afterInsertingProductData');
//$GLOBALS['MERCONIS_HOOKS']['import_beforeProcessingVariantData'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_import_beforeProcessingVariantData');
//$GLOBALS['MERCONIS_HOOKS']['import_beforeWritingVariantData'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_import_beforeWritingVariantData');
//$GLOBALS['MERCONIS_HOOKS']['import_afterUpdatingVariantData'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_import_afterUpdatingVariantData');
//$GLOBALS['MERCONIS_HOOKS']['import_afterInsertingVariantData'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_import_afterInsertingVariantData');
//$GLOBALS['MERCONIS_HOOKS']['import_beforeProcessingProductLanguageData'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_import_beforeProcessingProductLanguageData');
//$GLOBALS['MERCONIS_HOOKS']['import_beforeWritingProductLanguageData'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_import_beforeWritingProductLanguageData');
//$GLOBALS['MERCONIS_HOOKS']['import_afterWritingProductLanguageData'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_import_afterWritingProductLanguageData');
//$GLOBALS['MERCONIS_HOOKS']['import_beforeProcessingVariantLanguageData'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_import_beforeProcessingVariantLanguageData');
//$GLOBALS['MERCONIS_HOOKS']['import_beforeWritingVariantLanguageData'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_import_beforeWritingVariantLanguageData');
//$GLOBALS['MERCONIS_HOOKS']['import_afterWritingVariantLanguageData'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_import_afterWritingVariantLanguageData');
//$GLOBALS['MERCONIS_HOOKS']['beforeSendingOrderMessage'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_beforeSendingOrderMessage');
//$GLOBALS['MERCONIS_HOOKS']['beforeAddToCart'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_beforeAddToCart');
//$GLOBALS['MERCONIS_HOOKS']['getScalePriceQuantity'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_getScalePriceQuantity');
//$GLOBALS['MERCONIS_HOOKS']['calculateScaledPrice'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_calculateScaledPrice');
//$GLOBALS['MERCONIS_HOOKS']['beforeAjaxSearch'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_beforeAjaxSearch');
//$GLOBALS['MERCONIS_HOOKS']['afterAjaxSearch'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_afterAjaxSearch');
//$GLOBALS['MERCONIS_HOOKS']['beforeSearch'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_beforeSearch');
//$GLOBALS['MERCONIS_HOOKS']['afterSearch'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_afterSearch');
//$GLOBALS['MERCONIS_HOOKS']['beforeProductlistOutput'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_beforeProductlistOutput');
//$GLOBALS['MERCONIS_HOOKS']['beforeProductSingleviewOutput'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_beforeProductSingleviewOutput');
//$GLOBALS['MERCONIS_HOOKS']['addToCart'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_addToCart');
//$GLOBALS['MERCONIS_HOOKS']['beforeRedirectionToSeparateDataEntryPage'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_beforeRedirectionToSeparateDataEntryPage');
//$GLOBALS['MERCONIS_HOOKS']['beforeRedirectionBackToCart'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_beforeRedirectionBackToCart');
//$GLOBALS['MERCONIS_HOOKS']['beforeRedirectionToReviewOrderPage'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_beforeRedirectionToReviewOrderPage');
//$GLOBALS['MERCONIS_HOOKS']['paymentOptionSelected'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_paymentOptionSelected');
//$GLOBALS['MERCONIS_HOOKS']['shippingOptionSelected'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_shippingOptionSelected');
//$GLOBALS['MERCONIS_HOOKS']['initializeCartController'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_initializeCartController');
//$GLOBALS['MERCONIS_HOOKS']['modifyPaymentOrShippingMethodInfo'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_modifyPaymentOrShippingMethodInfo');
//$GLOBALS['MERCONIS_HOOKS']['checkIfPaymentOrShippingMethodIsAllowed'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_checkIfPaymentOrShippingMethodIsAllowed');
//$GLOBALS['MERCONIS_HOOKS']['sortPaymentOrShippingMethods'][] = array('Merconis\CustomHoehenflug\merconis_custom_helper', 'merconis_hook_sortPaymentOrShippingMethods');
