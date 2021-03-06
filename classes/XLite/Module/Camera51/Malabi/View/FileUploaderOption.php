<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\Camera51\Malabi\View;
use XLite\Core\Validator\Exception;

/**
 * Class FileUploaderOption
 */
class FileUploaderOption extends \XLite\View\AView
{
    const PARAM_IMAGE = 'image';

    protected $isRemoved = false;

    /**
     * Define widget params
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            static::PARAM_IMAGE       => new \XLite\Model\WidgetParam\TypeObject(
                'Image',
                null,
                false,
                'XLite\Model\Base\Image'
            ),
        );
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultTemplate()
    {
        return 'modules/Camera51/Malabi/option/body.twig';
    }

    /**
     * @inheritDoc
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        $list[] = array(
            'file'      => 'modules/Camera51/Malabi/option/component.js',
            'no_minify' => true
        );
        
   //     $list[] = 'modules/XC/Camera51/option/component.js';
        
        return $list;
    }

    /**
     * @inheritDoc
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'modules/Camera51/Malabi/option/style.less';

        return $list;
    }

    /**
     * @inheritDoc
     */
    protected function isVisible()
    {

        return parent::isVisible()
            && \XLite\Module\Camera51\Malabi\Main::isReadyToProcess()
            && $this->getImage()
            && $this->hasFile();

        return parent::isVisible()
            && \XLite\Module\Camera51\Malabi\Main::isReadyToProcess();

    }
//        if(parent::isVisible()
//            && \XLite\Module\Camera51\Camera51\Main::isReadyToProcess()
//            && $this->getImage()
//            && $this->hasFile()){
//
//            \XLite\Logger::logCustom('camera51', $this->getImage()->isBackgroundRemoved() );
//            $this->isRemoved = $this->getImage()->isBackgroundRemoved();
//        }
//        var_dump( parent::isVisible()
//            && \XLite\Module\Camera51\Camera51\Main::isReadyToProcess()
//            && $this->getImage()
//            && $this->hasFile()
//            && !$this->getImage()->isBackgroundRemoved());
//        return ;
////        return parent::isVisible()
////            && \XLite\Module\Camera51\Camera51\Main::isReadyToProcess()
////            && $this->getImage()
////            && $this->hasFile()
////            && !$this->getImage()->isBackgroundRemoved();
//    }

    /**
     * Get image
     *
     * @return \XLite\Model\Base\Image
     */
    protected function getImage()
    {
        return $this->getParam(static::PARAM_IMAGE);
    }

    /**
     * Check widget has file or not
     *
     * @return boolean
     */
    protected function hasFile()
    {
        return $this->getImage()->getId();
    }
}