<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\Camera51\Malabi\Logic;


class ImageProcessor
{
    // TODO Production endpoint is not provided yet tgus us
    const PRODUCTION_ASYNC_ENDPOINT = 'https://api.malabi.co/Camera51Server/processImageAsync';
    const PRODUCTION_SYNC_ENDPOINT     = 'https://api.malabi.co/Camera51Server/processImage';
    const PRODUCTION_USER_CREDIT     = 'https://users.malabi.co/UsersServer/v1/getUserCredit';



    /**
     * Returns processor Id
     *
     * @return string
     */
    public function getProcessorId()
    {
        return 'malabi';
    }

    /**
     * @param \XLite\Model\Base\Image $image
     *
     * @return string
     */
    public static function removeBackgroundByImage(\XLite\Model\Base\Image $image)
    {
        $originalUrl = $image->getFrontURL();

        return static::requestImageProcessing($originalUrl, $image->getId());
    }

    /**
     * @param \XLite\Model\Base\Image $image
     *
     * @return string
     */
    public static function removeBackgroundByImageAsync(\XLite\Model\Base\Image $image)
    {
        $originalUrl = $image->getFrontURL();

        $callbackUrl = static::getCallbackUrl(get_class($image), $image->getId());

        return static::requestImageProcessingAsync($originalUrl, $image->getId(), $callbackUrl);
    }

    /**
     * @param $url
     *
     * @return string
     */
    public static function removeBackgroundByUrl($originalUrl, $trackId)
    {
        return static::requestImageProcessing($originalUrl, $trackId);
    }

    /**
     * @param     $originalUrl
     * @param int $trackId
     */
    protected static function requestImageProcessing($originalUrl, $trackId = 555)
    {
        return static::_requestImageProcessing(
            static::getSyncEndpoint(),
            $originalUrl,
            $trackId
        );
    }

    /**
     * @param        $api_endpoint
     * @param        $originalUrl
     * @param int    $trackId
     * @param string $callbackUrl
     *
     * @return null
     */
    public static function requestUserCredit( $userId, $userToken)
    {


        $data = array_merge(
            static::getCommonData(),
            [
                'userId'  => $userId,
                'userToken' => $userToken,
            ]
        );

        $request = new \XLite\Core\HTTP\Request( static::getUserCreditEndpoint());
        $request->verb = 'POST';
        $request->body = json_encode($data);
        $response = $request->sendRequest();


        $result = null;

        if ($response->body) {
            $responseDataRaw = json_decode($response->body, true);

            \XLite\Logger::logCustom('camera51', [
                $responseDataRaw
            ]);

            if(isset($responseDataRaw['status']) && $responseDataRaw['status'] == "fail") {
                $error = "<span style='font-size:19px'>There is a problem with your subscription</span><br> 
                            <span style='font-size:19px'>GoTo Module Setting – Malabi background removal</span>";
                return $error;
            }

            if(isset($responseDataRaw['status']) && $responseDataRaw['status'] == "success") {
                if(isset($responseDataRaw['userCredit']) && $responseDataRaw['userCredit'] >0 ) {
                    return 1;
                } else {
                    return 0;
                }
            }
        } else {
            //\XLite\Core\TopMessage::addError('Service response was identified as empty');
            return "Service response was identified as empty";
        }

        return $result;
    }



    /**
     * @param        $api_endpoint
     * @param        $originalUrl
     * @param int    $trackId
     * @param string $callbackUrl
     *
     * @return null
     */
    protected static function _requestImageProcessing($api_endpoint, $originalUrl, $trackId = 555, $callbackUrl = '')
    {
        $data = array_merge(
            static::getCommonData(),
            [
                'originalImageURL'  => $originalUrl,
                'trackId'           => $trackId,
            ]
        );

        if ($callbackUrl) {
            $data['callbackURL'] = $callbackUrl;
        }

        $request = new \XLite\Core\HTTP\Request($api_endpoint);
        $request->verb = 'POST';
        $request->body = $data;
        $response = $request->sendRequest();

        \XLite\Logger::logCustom('camera51', [
            'request url'   => $api_endpoint,
            'request data'  => $data,
            'response data' => $response->body ? json_decode($response->body, true) : null,
        ]);

        $result = null;

        if ($response->body) {
            $responseDataRaw = json_decode($response->body, true);

            $responseData = isset($responseDataRaw['response'])
                ? $responseDataRaw['response']
                : null;

            $result = static::processResponse($responseData);
        } else {
            \XLite\Core\TopMessage::addError('Service response was identified as empty');
        }

        return $result;
    }

    /**
     * @return array
     */
    protected static function getCommonData()
    {
        return [
            'shadow'            => static::getConfig('shadow') ?: 'true',
            'transparent'       => static::getConfig('transparent') ?: 'false',
            'userId'        => static::getConfig('customer_id'),//sss
            'token'             => static::getConfig('access_token'),
            'forceResultImage'  => 'true',
        ];
    }

    /**
     * @return string
     */
    protected static function getCallbackUrl($type, $id)
    {
        return \XLite\Core\Converter::buildFullURL('image_processor', 'process_image',
            [
                'type'  => $type,
                'id'    => $id,
            ]
        );
    }

    /**
     * @return string
     */
    protected static function getAsyncEndpoint()
    {
        return static::PRODUCTION_SYNC_ENDPOINT;
    }

    /**
     * @return string
     */
    protected static function getSyncEndpoint()
    {
        return static::PRODUCTION_SYNC_ENDPOINT;
    }

    /**
     * @return string
     */
    protected static function getUserCreditEndpoint()
    {
        return static::PRODUCTION_USER_CREDIT;
    }


    /**
     * @return bool
     */
    public static function isReadyToProcess()
    {

        return true; // we always want to show the background removal option.
//        return static::getConfig('customer_id')
//            && static::getConfig('access_token')
//            && static::isConfiguredProperly();
    }

    /**
     * @param null $name
     *
     * @return mixed
     */
    protected static function getConfig($name = null)
    {
        return $name
            ? \XLite\Core\Config::getInstance()->Camera51->Malabi->{$name}
            : \XLite\Core\Config::getInstance()->Camera51->Malabi;
    }

    /**
     * @return bool
     */
    public static function isConfiguredProperly()
    {
        // TODO check account status
//        var_dump(static::getConfig('customer_id'));
//        var_dump(static::getConfig('access_token'));


        return true;
    }

    /**
     * @param $data
     *
     * @return null|string
     */
    protected static function processResponse($data)
    {
        $result = null;


        if (isset($data['resultImageURL']) && $data['resultImageURL'] && isset($data['processingResultCode']) && $data['processingResultCode'] < 100 ) {
            $result = $data['resultImageURL'];
        } else {
            if (isset($data['errors']) && is_array($data['errors'])) {
                foreach ($data['errors'] as $error) {
                    \XLite\Core\TopMessage::addError($error);
                }

            } elseif (isset($data['processingResult'])) {
                $processingResultReadable = static::getProcessingResultName($data['processingResultCode']);
                \XLite\Core\TopMessage::addError('Cannot remove background: ' . $processingResultReadable);

            } else {
                \XLite\Core\TopMessage::addError('Cannot process the image');
            }
        }
        
        return $result;
    }

    /**
     * @param $processingResult
     */
    protected static function getProcessingResultName($processingResult)
    {
        $names = static::getReadableProcessingResultNames();
        return array_key_exists(intval($processingResult), $names)
            ? $names[$processingResult]
            : 'Unknown processing error';
    }

    /**
     * @return array
     */
    protected static function getReadableProcessingResultNames()
    {
        return [
            1   => 'Can\'t process',
            100   =>  'Image cannot be processed',
            101   => 'Image cannot be processed',
            103   => 'Image too small (image size should be at least 70x70px)',
        ];
    }
}