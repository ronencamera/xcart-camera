<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\Camera51\Malabi\Controller\Admin;

use XLite\Module\Camera51\Malabi\Logic;


/**
 * Class ImageProcessor
 */
class ImageProcessor extends \XLite\Controller\Admin\AAdmin
{
    //test asdasda asdas
    /**
     * Define the actions with no secure token
     *
     * @return array
     */
    public static function defineFreeFormIdActions()
    {
        $list = parent::defineFreeFormIdActions();
        $list[] = 'remove_background';
        $list[] = 'process_multiple';

        return $list;
    }

    protected static function getConfig($name = null)
    {
        return $name
            ? \XLite\Core\Config::getInstance()->XC->Camera51->{$name}
            : \XLite\Core\Config::getInstance()->XC->Camera51;
    }

    /**
     * Removing image background
     */
    public function doActionRemoveBackground()
    {

        if(empty(static::getConfig('customer_id')) || empty(static::getConfig('access_token'))){
            $error = "<span style='font-size:19px'>To activate <u>Remove background</u> go to: </span><br> 
            <span style='font-size:19px'>Modules – Camera51 background removal</span>";
            \XLite\Core\TopMessage::addError($error);
            $this->displayJSON([
                'success'   => false,
                'resultUrl' => "",
                'trackId' => "",
            ]);
            return;
        }

        $this->set('silent', true);
        $this->setSuppressOutput(true);
        $trackId = "user_" .  static::getConfig('customer_id') . "_" . time() ."_" . rand(1000,3000);
        $urlWithoutBackground = $this->getImageWithoutBackgroundUrl(
            \XLite\Core\Request::getInstance()->malabiUrl
            ,$trackId
        );
        $this->translateTopMessagesToHTTPHeaders();
        $this->displayJSON([
            'success'   => (bool) $urlWithoutBackground,
            'resultUrl' => $urlWithoutBackground,
            'trackId' => $trackId
        ]);
    }

    /**
     * Removing image background
     */
    public function doActionProcessMultiple()
    {
        $objects = is_array(\XLite\Core\Request::getInstance()->select)
            ? array_filter(\XLite\Core\Request::getInstance()->select)
            : [];

        $processedCount = 0;
        foreach ($objects as $objectsId => $objectClass) {
            /** @var \XLite\Model\Base\Image $object */
            $object = \XLite\Core\Database::getRepo($objectClass)->find($objectsId);
            if ($object) {
                $success = $this->processImage($object);
                if ($success) {
                    $object->setBackgroundRemoved();
                    $object->setTrackId();
                    $processedCount++;
                }
            }
        }
        \XLite\Core\Database::getEM()->flush();

        \XLite\Core\TopMessage::addInfo('{{count}} images was processed successfully', [
            'count' => $processedCount
        ]);
    }

    /**
     * Removing image background
     */
    public function doActionProcessAllAsync()
    {
        $itemsList = \XLite\Core\Request::getInstance()->itemsList;
    
        if (!$itemsList) {
            return;
        }
        $listObject = new $itemsList();

        $objects = $listObject->getDataPublic();
        $processedCount = 0;

        /** @var \XLite\Model\Base\Image $object */
        foreach ($objects as $object) {
            $success = $this->processImageAsync($object);
            if ($success) {
                $object->setBackgroundInProgress();
                $object->setTrackId();
                $processedCount++;
            }
        }
        \XLite\Core\Database::getEM()->flush();

        \XLite\Core\TopMessage::addInfo('{{count}} images was marked to process', [
            'count' => $processedCount
        ]);
    }
    
    /**
     * Removing image background
     */
    public function doActionProcessMultipleAsync()
    {
        $objects = is_array(\XLite\Core\Request::getInstance()->select)
            ? array_filter(\XLite\Core\Request::getInstance()->select)
            : [];

        $processedCount = 0;
        foreach ($objects as $objectsId => $objectClass) {
            /** @var \XLite\Model\Base\Image $object */
            $object = \XLite\Core\Database::getRepo($objectClass)->find($objectsId);
            if ($object) {
                $success = $this->processImageAsync($object);
                if ($success) {
                    $object->setBackgroundInProgress();
                    $processedCount++;
                }
            }
        }
        \XLite\Core\Database::getEM()->flush();

        \XLite\Core\TopMessage::addInfo('{{count}} images was marked to process', [
            'count' => $processedCount
        ]);
    }

    /**
     * @param \XLite\Model\Base\Image $image
     *
     * @return bool
     */
    protected function processImage(\XLite\Model\Base\Image $image)
    {
        $newUrl = Logic\ImageProcessor::removeBackgroundByImage($image);

        if (!$newUrl) {
            return false;
        }

        return $image->loadFromURL($newUrl, true);
    }

    /**
     * @param \XLite\Model\Base\Image $image
     *
     * @return bool
     */
    protected function processImageAsync(\XLite\Model\Base\Image $image)
    {
        $newUrl = Logic\ImageProcessor::removeBackgroundByImageAsync($image);

        if (!$newUrl) {
            return false;
        }

        return true;
    }

    /**
     * @param $url string Original url
     *
     * @return string
     */
    protected function getImageWithoutBackgroundUrl($url, $trackId)
    {
        return Logic\ImageProcessor::removeBackgroundByUrl($url, $trackId);
    }
}