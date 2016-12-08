<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XC\Camera51\View;


/**
 * Configuration instructions widget for SocialLogin
 */
class Instructionssetting extends \XLite\View\FormField\Label\ALabel
{
    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list[] = [];
     //   $list = parent::getCSSFiles();

     //   $list[] = 'modules/CDev/SocialLogin/style.css';

        return $list;
    }

    /**
     * Process all occurencies of WEB_LC_ROOT
     *
     * @param mixed $str Input string
     *
     * @return string
     */
    public function processUrls($str)
    {
//
        $customerId = \XLite\Core\Config::getInstance()->XC->Camera51->customer_id ;
        $accessToken = htmlentities( \XLite\Core\Config::getInstance()->XC->Camera51->access_token);

        if(empty($customerId) || empty($accessToken)) {
            return "<b>To manage your account pleaser add User Id and Access Token info and submit. </b>";
        }

        $tmpStr = str_replace(
            "{{WEB_LC_ROOT}}",
            htmlentities($customerId),
            $str
        );

        return str_replace(
            "{{WEB_LC_TOKEN}}",
            htmlentities($accessToken),
            $tmpStr
        );
    }

    /**
     * Return field template
     *
     * @return string
     */
    protected function getFieldTemplate()
    {
        return 'modules/XC/Camera51/form_field/instructions.twig';
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'modules/XC/Camera51/form_field/instructions.twig';
    }
}
