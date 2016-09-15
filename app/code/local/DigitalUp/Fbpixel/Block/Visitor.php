<?php
/**
 * Created by DigitalUp.
 * Author: Dionisis Papanikolaou
 * Date: 23/6/2016
 */

class DigitalUp_Fbpixel_Block_Visitor extends Mage_Core_Block_Template
{

    public function FBpageType(){
        $fullpixel = null;
        $fullpixel .= "fbq('track', 'ViewContent');". PHP_EOL; //always
        if (Mage::app()->getRequest()->getModuleName() == 'catalogsearch'){
            $fullpixel .= "fbq('track', 'Search');". PHP_EOL;
        }

        if (Mage::helper('checkout/cart')->getItemsCount() > 0){
            $fullpixel .= "fbq('track', 'AddToCart');". PHP_EOL;
        }

        if (Mage::app()->getRequest()->getModuleName() == 'checkout'){
            $fullpixel .= "fbq('track', 'InitiateCheckout');". PHP_EOL;
        }

        if (Mage::app()->getRequest()->getModuleName() == 'onestepcheckout'){
            $fullpixel .= "fbq('track', 'AddPaymentInfo');". PHP_EOL;
        }

        return $fullpixel;
    }

    public function FBpageRemarketingType(){
        $fullpixel = null;
//        $fullpixel .= PHP_EOL."fbq('track', 'ViewContent');". PHP_EOL; //always
        $fullpixel .= PHP_EOL.""; //always
        if (Mage::app()->getRequest()->getModuleName() == 'catalogsearch'){

            $term = Mage::helper('catalogsearch')->getQueryText();
            $query = Mage::getModel('catalogsearch/query')->setQueryText($term)->prepare();
            $fulltextResource = Mage::getResourceModel('catalogsearch/fulltext')->prepareResult(
                Mage::getModel('catalogsearch/fulltext'),
                $term,
                $query
            );

            $collection = Mage::getResourceModel('catalog/product_collection');
            $collection->getSelect()->joinInner(
                array('search_result' => $collection->getTable('catalogsearch/result')),
                $collection->getConnection()->quoteInto(
                    'search_result.product_id=e.entity_id AND search_result.query_id=?',
                    $query->getId()
                ),
                array('relevance' => 'relevance')
            );

            $productIds = array();
            $productSkus = array();
            $productIds = $collection->getAllIds();
            $productIds = array_slice($productIds, 0, 10);

            foreach ( $productIds as $productId ){
                array_push( $productSkus, Mage::getModel('catalog/product')->load($productId)->getSku());
            }
            if ( sizeOf($productSkus) ){
                $results = "['". implode("','", $productSkus) ."'],";
            }else {
                $results = "[],";
            }

            $fullpixel .= "fbq('track', 'Search', {". PHP_EOL;
            $fullpixel .= "search_string: '".$term."',". PHP_EOL;
            $fullpixel .= "content_ids: ".$results.",". PHP_EOL;
            $fullpixel .= "content_type: 'product'". PHP_EOL;
            $fullpixel .= "});". PHP_EOL;
        }

        if (Mage::helper('checkout/cart')->getItemsCount() > 0){

            $items = Mage::getSingleton('checkout/session')->getQuote()->getAllItems();
            $results = array();
            $total = 0;
            foreach ($items as $item){
                array_push( $results, $item->getProduct()->getSku());
                $total += $item->getQty()*$item->getProduct()->getFinalPrice();
            }

            $fullpixel .= "fbq('track', 'AddToCart', {". PHP_EOL;
            $fullpixel .= "content_ids: ['".implode("','",$results)."'],". PHP_EOL;
            $fullpixel .= "content_type: 'product',". PHP_EOL;
            $fullpixel .= "value: ". number_format($total,2).",". PHP_EOL;
            $fullpixel .= "currency: 'EUR',". PHP_EOL;
            $fullpixel .= "});". PHP_EOL;

        }

        if (Mage::app()->getRequest()->getControllerName() == 'product'){
            $_product = Mage::registry('current_product');

            $fullpixel .= "fbq('track', 'ViewContent', {". PHP_EOL;
            $fullpixel .= "content_ids: '".$_product->getSku()."',". PHP_EOL;
            $fullpixel .= "content_type: 'product',". PHP_EOL;
            $fullpixel .= "value: ". number_format($_product->getFinalPrice(),2).",". PHP_EOL;
            $fullpixel .= "currency: 'EUR',". PHP_EOL;
            $fullpixel .= "});". PHP_EOL;
        }

        if (Mage::app()->getRequest()->getModuleName() == 'checkout'){
            $fullpixel .= "fbq('track', 'InitiateCheckout');". PHP_EOL;
        }

        if (Mage::app()->getRequest()->getModuleName() == 'onestepcheckout'){
            $fullpixel .= "fbq('track', 'AddPaymentInfo');". PHP_EOL;
        }

        if (Mage::app()->getFrontController()->getRequest()->getActionName() == 'success'){
            $orderId    = Mage::getSingleton('checkout/session')->getLastRealOrderId();
            $order      = Mage::getModel('sales/order')->loadByIncrementId($orderId);

            $items  = $order->getAllVisibleItems();
            $skus   = array();
            foreach($items as $item){
                array_push( $skus, Mage::getModel('catalog/product')->getResource()->getIdBySku($item->getSku()) );
            }

            $fullpixel .= "fbq('track', 'Purchase', { ". PHP_EOL;
            $fullpixel .= "content_ids: ['".implode("', '",$skus)."'],". PHP_EOL;
            $fullpixel .= "content_type: 'product',". PHP_EOL;
            $fullpixel .= "value: ".round($order->getGrandTotal(),2).",". PHP_EOL;
            $fullpixel .= "currency: 'EUR'". PHP_EOL;
            $fullpixel .= "});". PHP_EOL;
        }

        $helper = Mage::helper("digitalup_fbpixel");
        $ip = getenv('HTTP_CLIENT_IP')?: getenv('HTTP_X_FORWARDED_FOR')?:  getenv('HTTP_X_FORWARDED')?: getenv('HTTP_FORWARDED_FOR')?: getenv('HTTP_FORWARDED')?: getenv('REMOTE_ADDR');
        if ( $ip == $helper->getDebugAllowedIp() & $helper->isDebugModeEnabled() == '1' ) {
            Zend_Debug::dump($fullpixel);
        }
        return $fullpixel;
    }
}