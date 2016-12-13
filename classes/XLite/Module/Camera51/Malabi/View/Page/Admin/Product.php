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


        return 'modules/Camera51/Malabi/option/product.twig';
    }

    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        $list[] = array(
            'file'      => 'modules/Camera51/Malabi/option/component.js',
            'no_minify' => true
        );
        return $list;
    }


    public static function getAllowedTargets()
    {
        return array_merge(parent::getAllowedTargets(), array('product'));
    }

}