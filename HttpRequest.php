<?php
/**
 * Created by PhpStorm.
 * User: 俊杰
 * Date: 14-9-15
 * Time: 上午11:30
 */

namespace iit\aliyun;


class HttpRequest
{
    public function getDateTime()
    {
        date_default_timezone_set("UTC");
        return date('D, d M Y H:i:s \G\M\T', time());
    }

    public function getSignature()
    {

    }
} 