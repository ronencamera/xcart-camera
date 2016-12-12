<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\Camera51\Malabi;
use XLite\Module\Camera51\Malabi\Logic\ImageProcessor;

/**
 * Module main class
 */
abstract class Main extends \XLite\Module\AModule
{
    /**
     * Author name
     *
     * @return string
     */
    public static function getAuthorName()
    {
        return 'Camera51';
    }

    /**
     * Module name
     *
     * @return string
     */
    public static function getModuleName()
    {
        return 'Camera51 Automatic Background Remover';
    }

    /**
     * Module description
     *
     * @return string
     */
    public static function getDescription()
    {
        return 'Grow sales and make your store look great with Camera51! Camera51 will quickly and automatically remove the background from any product photo. Simply upload a photo, and our state of the art algorithm will automatically remove the background and deliver a studio quality product image within seconds. Enjoy Camera51 for unlimited number of images monthly!';
    }

//    /**
//     * Return link to settings form
//     *
//     * @return string
//     */
//    public static function getSettingsForm()
//    {
//        return \XLite\Core\Converter::buildURL('malabi');
//    }

    /**
     * Get module major version
     *
     * @return string
     */
    public static function getMajorVersion()
    {
        return '5.3';
    }

    /**
     * Module version
     *
     * @return string
     */
    public static function getMinorVersion()
    {
        return '0';
    }

    /**
     * Get minor core version which is required for the module activation
     *
     * @return string
     */
    public static function getMinorRequiredCoreVersion()
    {
        return '2';
    }

    /**
     * Determines if we need to show settings form link
     *
     * @return boolean
     */
    public static function showSettingsForm()
    {
        return true;
    }

    public static function isReadyToProcess()
    {
        return ImageProcessor::isReadyToProcess();
    }

    public static function getModuleType()
    {
        return static::MODULE_TYPE_CUSTOM_MODULE;
    }
}
