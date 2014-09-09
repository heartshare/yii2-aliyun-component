<?php
/**
 * Created by PhpStorm.
 * User: 俊杰
 * Date: 14-9-9
 * Time: 下午2:39
 */

namespace iit\aliyun;

use iit\file\File;

class OssFile extends File
{
    public $accessKeyId;
    public $accessKeySecret;

    function getFielUrl($filePath)
    {
        // TODO: Implement getFielUrl() method.
    }

    function fileExist($filePath)
    {
        // TODO: Implement fileExist() method.
    }

    function getFile($filePath)
    {
        // TODO: Implement getFile() method.
    }

    function getFileInfo($filePath)
    {
        // TODO: Implement getFileInfo() method.
    }

    function saveFile($file)
    {
        // TODO: Implement saveFile() method.
    }

    function getFileList($path)
    {
        // TODO: Implement getFileList() method.
    }

    function createPath($pathName)
    {
        // TODO: Implement createPath() method.
    }

    function deletePath($pathName)
    {
        // TODO: Implement deletePath() method.
    }

    function renamePath($oldPathName, $newPathName)
    {
        // TODO: Implement renamePath() method.
    }
}