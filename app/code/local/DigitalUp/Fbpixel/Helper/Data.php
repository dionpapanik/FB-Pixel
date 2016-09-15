<?php
/**
 * Created by DigitalUp.
 * Author: Dionisis Papanikolaou
 * Date: 23/6/2016
 */
class DigitalUp_Fbpixel_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isVisitorPixelEnabled()
    {
        return Mage::getStoreConfig("digitalup_fbpixel/visitor/enabled");
    }

    public function isConversionPixelEnabled()
    {
        return Mage::getStoreConfig("digitalup_fbpixel/conversion/enabled");
    }

    public function getVisitorPixelId()
    {
        return Mage::getStoreConfig("digitalup_fbpixel/visitor/pixel_id");
    }

    public function getConversionPixelId()
    {
        return Mage::getStoreConfig("digitalup_fbpixel/conversion/pixel_id");
    }
    
    public function isDynamicRemarketingEnabled()
    {
        return Mage::getStoreConfig("digitalup_fbpixel/visitor/dynamic");
    }
    
    public function isDebugModeEnabled()
    {
        return Mage::getStoreConfig("digitalup_fbpixel/debugging/enable_debugging");
    }

    public function getDebugAllowedIp()
    {
        return Mage::getStoreConfig("digitalup_fbpixel/debugging/developer_ip");
    }

}