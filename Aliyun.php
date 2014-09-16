<?php
/**
 * Created by PhpStorm.
 * User: 俊杰
 * Date: 14-9-15
 * Time: 上午11:30
 */

namespace iit\aliyun;

use Httpful\Request;
use yii\base\InvalidParamException;
use yii\base\Object;
use yii\helpers\ArrayHelper;

class Aliyun extends Object
{
    const POST_METHOD = 'POST';
    const PUT_METHOD = 'PUT';
    const GET_METHOD = 'GET';
    const DEL_METHOD = 'DELETE';
    const HEAD_METHOD = 'HEAD';
    const MQS_INTERFACE = 'MQS';
    const OSS_INTERFACE = 'OSS';
    const TYPE = 'text/xml;utf-8';
    public $accessKey;
    public $accessSecret;
    private $_date;
    private $_headers;
    private $_resource = '/';
    private $_resourceParam;
    private $_body;
    private $_interface;
    private $_method;
    private $_url;

    public function init()
    {
        parent::init();
        if (empty($this->accessSecret) || empty($this->accessKey)) {
            throw new InvalidParamException('Please Input accessKey Or accessSecret');
        }
    }

    public function setHeader($key, $value)
    {
        $this->_headers[$key] = $value;
        return $this;
    }

    public function getHeader($key)
    {
        return $this->_headers[$key];
    }

    public function setHeaders($array)
    {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $this->setHeader($key, $value);
            }
        }
        return $this;
    }

    public function getHeaders()
    {
        return $this->_headers;
    }

    public function setResource($string)
    {
        $this->_resource = $string;
        return $this;
    }

    public function getResource()
    {
        return $this->_resource;
    }

    public function setResourceParam($key, $value)
    {
        $this->_resourceParam[$key] = $value;
        return $this;
    }

    public function getResourceParam($key)
    {
        return $this->_resourceParam[$key];
    }

    public function setResourceParams($array)
    {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $this->setResourceParam($key, $value);
            }
        }
        return $this;
    }

    public function getResourceParams()
    {
        if (is_array($this->_resourceParam)) {
            ksort($this->_resourceParam);
        }
        return $this->_resourceParam;
    }

    public function setBody($body)
    {
        $this->_body = $body;
        return $this;
    }

    public function getBody()
    {
        return $this->_body;
    }

    public function setInterface($type)
    {
        $this->_interface = $type;
        return $this;
    }

    public function getInterface()
    {
        return $this->_interface;
    }

    public function getDate()
    {
        if ($this->_date === null) {
            date_default_timezone_set("UTC");
            $this->_date = date('D, d M Y H:i:s \G\M\T', time());
        }
        return $this->_date;
    }

    public function setMethod($method)
    {
        $this->_method = $method;
        return $this;
    }

    public function getMethod()
    {
        return $this->_method;
    }

    public function getMd5()
    {
        return $this->getBody() === null ? '' : base64_encode(md5($this->getBody()));
    }

    public function getAllResource()
    {
        return $this->getResource() . ($this->getResourceParams() === null ? '' : '?' . http_build_query($this->getResourceParams()));
    }

    /**
     * @return bool|\Httpful\Response
     */

    public function send()
    {
        if ($this->check()) {
            $requestHeaders = ArrayHelper::merge([
                'Host' => $this->getUrl(),
                'Date' => $this->getDate(),
                'Content-Type' => self::TYPE,
                'Content-MD5' => $this->getMd5(),
                'Authorization' => $this->signature(),
            ], $this->getHeaders());
            $url = 'http://' . $this->getUrl() . $this->getAllResource();
            $request = Request::init($this->getMethod());
            $request->addHeaders($requestHeaders)->uri($url);
            if ($this->getBody() !== null) {
                $request->body($this->getBody());
            }
            return $request->send();
        } else {
            return false;
        }
    }

    public function check()
    {
        if ($this->getMethod() === null) {
            throw new InvalidParamException('Please Input Method');
        } elseif ($this->getHeaders() === null) {
            throw new InvalidParamException('Please Input Headers');
        } elseif ($this->getInterface() === null) {
            throw new InvalidParamException('Please Input Interface');
        } elseif ($this->getUrl() === null) {
            throw new InvalidParamException('Please Input Interface');
        } else {
            return true;
        }
    }

    public function signature()
    {
        $headers = $this->getHeaders();
        ksort($headers);
        $headers_string = '';
        foreach ($headers as $key => $value) {
            $headers_string .= join(':', [strtolower($key), $value . "\n"]);
        }
        $signatureString = sprintf(
            "%s\n%s\n%s\n%s\n%s%s",
            $this->getMethod(),
            $this->getMd5(),
            self::TYPE,
            $this->getDate(),
            $headers_string,
            $this->getAllResource()
        );
        return $this->getInterface() . ' ' . $this->accessKey . ':' . base64_encode(hash_hmac('sha1', $signatureString, $this->accessSecret, true));
    }

    public function setUrl($url)
    {
        $this->_url = $url;
        return $this;
    }

    public function getUrl()
    {
        return $this->_url;
    }

    public static function arrayToXml(array $array, $addXml = true)
    {
        $xml = $addXml === true ? '<?xml version="1.0" encoding="UTF-8"?>' : '';
        foreach ($array as $key => $val) {
            $xml .= (is_numeric($key) ? '' : '<' . $key . '>') . (is_array($val) ? self::arrayToXml($val, false) : $val) . (is_numeric($key) ? '' : '</' . $key . '>');
        }
        return $xml;
    }

} 