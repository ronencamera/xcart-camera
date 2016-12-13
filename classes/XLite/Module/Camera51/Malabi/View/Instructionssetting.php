<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\Camera51\Malabi\View;


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
        $customerId = \XLite\Core\Config::getInstance()->Camera51->Malabi->customer_id ;
        $accessToken = htmlentities( \XLite\Core\Config::getInstance()->Camera51->Malabi->access_token);

        if(empty($customerId) || empty($accessToken)) {
            return "";
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
        return 'modules/Camera51/Malabi/form_field/instructions_buttons.twig';
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'modules/Camera51/Malabi/form_field/instructions_buttons.twig';
    }
}
