<?php
/**
 * Created by PhpStorm.
 * User: 俊杰
 * Date: 14-9-15
 * Time: 下午2:12
 */

namespace iit\aliyun;


use Httpful\Request;
use iit\queue\Queue;

class Mqs extends Queue
{

    const VERSION = '2014-07-08';
    const TYPE = 'text/xml;utf-8';
    public $queueName;
    public $accessSecret;
    public $accessKey;
    public $url;
    private $_aliyun;

    public function init()
    {
        parent::init();
        $this->_aliyun = \Yii::createObject([
            'class' => '\iit\aliyun\Aliyun',
            'accessKey' => $this->accessKey,
            'accessSecret' => $this->accessSecret
        ]);
        $this->getAliyun()
            ->setHeader('x-mqs-version', self::VERSION)
            ->setInterface(Aliyun::MQS_INTERFACE)
            ->setResource('/' . $this->queueName . '/messages')
            ->setType(self::TYPE)
            ->setUrl($this->url);
    }

    /**
     * @return \iit\aliyun\Aliyun $_aliyun
     */

    public function getAliyun()
    {
        return $this->_aliyun;
    }


    /**
     * 发送消息实现方法，子类必须实现此方法
     * @param $msg
     * @param $delay
     * @param $level
     * @return bool
     */
    protected function send($msg, $delay, $level)
    {
        $body = [
            'Message' => [
                'MessageBody' => $msg,
                'DelaySeconds' => $delay,
                'Priority' => $this->levelToPriority($level)
            ]
        ];
        $result = $this->getAliyun()
            ->setMethod(Aliyun::POST_METHOD)
            ->setResource('/' . $this->queueName . '/messages')
            ->setBody($this->getAliyun()->arrayToXml($body))
            ->send();
        $obj = new \SimpleXMLElement($result->body);
        return $obj->MessageBodyMD5;
    }

    /**
     * 从队列中取出消息实现方法，子类必须实现此方法
     * @return mixed
     */
    protected function receive()
    {
        $result = $this->getAliyun()
            ->setMethod(Aliyun::GET_METHOD)
            ->send();
        $obj = new \SimpleXMLElement($result->body);
        if (empty($obj->ReceiptHandle)) {
            return false;
        } else {
            $return['msgId'] = (String)$obj->ReceiptHandle;
            $return['body'] = (String)$obj->MessageBody;
            return $return;
        }
    }

    /**
     * 删除消息实现方法，子类必须实现此类
     * @param $msgId
     * @return mixed
     */
    protected function delete($msgId)
    {
        $result = $this->getAliyun()
            ->setMethod(Aliyun::DEL_METHOD)
            ->setResourceParam('ReceiptHandle', $msgId)
            ->send();
        return $result->meta_data['http_code'] == 204 ? true : false;
    }

    /**
     * 设置被取出消息超时时间实现方法，子类必须实现此方法
     * @param $msgId
     * @param $timeout
     * @return mixed
     */
    protected function setVisibilityTimeout($msgId, $timeout)
    {
        $result = $this->getAliyun()
            ->setMethod(Aliyun::PUT_METHOD)
            ->setResourceParams([
                'ReceiptHandle' => $msgId,
                'VisibilityTimeout' => $timeout
            ])->send();
        $obj = new \SimpleXMLElement($result->body);
        $return['msgId'] = (String)$obj->ReceiptHandle;
        $return['nextTimeout'] = (String)$obj->NextVisibleTime;
        return $return;
    }

    protected function levelToPriority($level)
    {
        switch ($level) {
            case Queue::LEVEL_NORMAL:
                $priority = 8;
                break;
            case Queue::LEVEL_HIGH:
                $priority = 15;
                break;
            case Queue::LEVEL_LOW:
                $priority = 1;
                break;
            default:
                $priority = 8;
        }
        return $priority;
    }


}