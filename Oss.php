<?php
/**
 * Created by PhpStorm.
 * User: 俊杰
 * Date: 14-9-16
 * Time: 下午11:22
 */

namespace iit\aliyun;


use yii\base\Component;
use yii\helpers\FileHelper;

class Oss extends component
{

    public $accessSecret;
    public $accessKey;
    private $_aliyun;

    /**
     * @return \iit\aliyun\Aliyun $_aliyun
     */

    public function getAliyun()
    {
        return $this->_aliyun;
    }

    public function init()
    {
        parent::init();
        $this->_aliyun = \Yii::createObject([
            'class' => '\iit\aliyun\Aliyun',
            'accessKey' => $this->accessKey,
            'accessSecret' => $this->accessSecret
        ]);
        $this->getAliyun()
            ->setInterface(Aliyun::OSS_INTERFACE);
    }

    public function put($filePath)
    {
        var_dump($this->getAliyun()
            ->setInterface(Aliyun::OSS_INTERFACE)
            ->setType(FileHelper::getMimeType($filePath))
            ->setLength(filesize($filePath))
            ->setUrl('ilovintit.oss-cn-hangzhou.aliyuncs.com')
            ->setResource('/' . pathinfo($filePath, PATHINFO_BASENAME))
            ->setResourcePrefix('/ilovintit')
            ->setBody(file_get_contents($filePath))
            ->setMethod(Aliyun::PUT_METHOD)
            ->send());
    }

} 