<?php

namespace XLite\Module\Camera51\Malabi\View\Page\Admin;


/**
 * @ListChild (list="admin.center", zone="admin")
 */

class Product extends \XLite\View\AView
{


    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {


        return 'modules/XC/Camera51/option/product.twig';
    }

    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        $list[] = array(
            'file'      => 'modules/XC/Camera51/option/component.js',
            'no_minify' => true
        );
        return $list;
    }


    public static function getAllowedTargets()
    {
        return array_merge(parent::getAllowedTargets(), array('product'));
    }

}