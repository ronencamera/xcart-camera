<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\Camera51\Malabi\Model\Base;


class Image extends \XLite\Model\Base\Image implements \XLite\Base\IDecorator
{
    /**
     * Constants for status
     */
    const BG_ORIGINAL               = 'O';
    const BG_REMOVED                = 'R';
    const BG_REMOVAL_IN_PROGRESS    = 'P';

    /**
     * Width
     *
     * @var string
     *
     * @Column (type="string", options={ "fixed": true }, length=32)
     */
    protected $trackId = "";

    /**
     * Width
     *
     * @var integer
     *
     * @Column (type="string", options={ "fixed": true }, length=1)
     */
    protected $backgroundStatus = self::BG_ORIGINAL;

    /**
     * @return int
     */
    public function getBackgroundStatus()
    {
        if(empty($this->backgroundStatus)){
            return static::BG_ORIGINAL;
        }
        return $this->backgroundStatus;
    }

    /**
     * @return array
     */
    protected function getReadableNames()
    {
        return [
            static::BG_ORIGINAL             => 'Not processed',
            static::BG_REMOVAL_IN_PROGRESS  => 'Process in progress',
            static::BG_REMOVED              => 'Processed',
        ];
    }

    /**
     * @return mixed
     */
    public function getBackgroundStatusReadable()
    {

        $names = $this->getReadableNames();

        return $this->getBackgroundStatus() && isset($names[$this->getBackgroundStatus()])
            ? $names[$this->getBackgroundStatus()]
            : $names[static::BG_ORIGINAL];   
    }

    /**
     * @param string $trackId
     *
     * @return $this
     */
    public function setTrackId($trackId){
        $this->trackId = $trackId;
        return $this;
    }


    public function getTrackId(){
        return $this->trackId;
    }
    /**
     * @param string $backgroundStatus
     *
     * @return $this
     */
    public function setBackgroundStatus($backgroundStatus)
    {
        $this->backgroundStatus = $backgroundStatus;
        return $this;
    }

    /**
     * @return $this
     */
    public function setBackgroundRemoved()
    {
        $this->backgroundStatus = static::BG_REMOVED;
        return $this;
    }

    /**
     * @return $this
     */
    public function setBackgroundInProgress()
    {
        $this->backgroundStatus = static::BG_REMOVAL_IN_PROGRESS;
        return $this;
    }

    /**
     * @return int
     */
    public function isBackgroundInProgress()
    {
        return $this->getBackgroundStatus() === static::BG_REMOVAL_IN_PROGRESS;
    }

    /**
     * @return int
     */
    public function isBackgroundRemoved()
    {
        return $this->getBackgroundStatus() === static::BG_REMOVED;
    }

    /**
     * @inheritDoc
     */
    public function postprocessByTemporary(\XLite\Model\TemporaryFile $temporaryFile)
    {
        parent::postprocessByTemporary($temporaryFile);

        $this->setBackgroundStatus($temporaryFile->getBackgroundStatus());
        $this->setTrackId($temporaryFile->getTrackId());
    }

    /**
     * @inheritDoc
     */
    public function loadFromURL($url, $copy2fs = false)
    {

        $success = parent::loadFromURL($url, $copy2fs);
        if ($success && \XLite\Core\Request::getInstance()->withoutBackground) {
            $this->setBackgroundRemoved();
            $this->setTrackId(\XLite\Core\Request::getInstance()->trackId);
        }

        return $success;
    }

    /**
     * @inheritDoc
     */
    public function loadFromRequest($key)
    {

        $success = parent::loadFromRequest($key);

        if ($success && \XLite\Core\Request::getInstance()->withoutBackground) {
            $this->setBackgroundRemoved();
            $this->setTrackId(\XLite\Core\Request::getInstance()->trackId);

        }
        
        return $success;
    }
}