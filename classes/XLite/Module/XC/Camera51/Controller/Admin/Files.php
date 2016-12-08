<?php
namespace XLite\Module\XC\Camera51\Controller\Admin;

class Files extends \XLite\Controller\Admin\Files implements \XLite\Base\IDecorator
{
    protected function doActionUploadFromMalabi()
    {
        $file = \XLite\Core\Request::getInstance()->register
            ? new \XLite\Model\Image\Content()
            : new \XLite\Model\TemporaryFile();
        $message = '';
        if ($file->loadFromURL(\XLite\Core\Request::getInstance()->uploadedUrl, \XLite\Core\Request::getInstance()->copy)) {
            $this->checkFile($file);
            $file->setTrackId(\XLite\Core\Request::getInstance()->trackId);
            \XLite\Core\Database::getEM()->persist($file);
            \XLite\Core\Database::getEM()->flush();
        } else {
            $message = static::t('File is not uploaded');
        }
        $this->sendResponse($file, $message);
    }
}