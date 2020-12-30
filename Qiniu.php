<?php

namespace tegic\qiniu;

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use think\Exception;


require 'qiniu_sdk/autoload.php';

class Qiniu
{
    private $_accessKey;
    private $_secretKey;
    private $_bucket;

    private $_error;

    /**
     * Qiniu constructor.
     * @param string $accessKey
     * @param string $secretKey
     * @param string $bucketName
     * 初始化参数
     */
    public function __construct($accessKey = "", $secretKey = "", $bucketName = "")
    {
        if (empty($accessKey) || empty($secretKey) || empty($bucketName)) {
            $qiniuConfig = config('qiniu');
            if (empty($qiniuConfig['accesskey']) || empty($qiniuConfig['secretkey']) || empty($qiniuConfig['bucket'])) {
                $this->_error = '配置信息不完整';
                return false;
            }
            $this->_accessKey = $qiniuConfig['accesskey'];
            $this->_secretKey = $qiniuConfig['secretkey'];
            $this->_bucket    = $qiniuConfig['bucket'];
        } else {
            $this->_accessKey = $accessKey;
            $this->_secretKey = $secretKey;
            $this->_bucket    = $bucketName;
        }
    }

    /**
     * @return bool|string
     * 获取bucket
     */
    private function _getBucket()
    {
        return $this->_bucket;
    }

    /**
     * @param string $saveName
     * @param string $bucket
     * @return mixed
     * @throws Exception
     * @throws \Exception
     * 单文件上传，如果添加多个文件则只上传第一个
     */
    public function upload($saveName = '', $bucket = '')
    {
        $token = $this->_getUploadToken($bucket);

        $files = $_FILES;
        if (empty($files)) {
            throw new Exception('没有文件被上传', 10002);
        }
        $values = array_values($files);

        $uploadManager = new UploadManager();
        if (empty($saveName)) {
            $saveName = hash_file('sha1', $values[0]['tmp_name']) . time();
        }
        $infoArr         = explode('.', $values[0]['name']);
        $extension       = array_pop($infoArr);
        $fileInfo        = $saveName . '.' . $extension;
        list($ret, $err) = $uploadManager->putFile($token, $saveName, $values[0]['tmp_name']);
        if ($err !== null) {
            throw new Exception('上传出错' . serialize($err));
        }
        return $ret['key'];
    }

    /**
     * 上传二进制流到七牛, 内部使用
     *
     * @param $upToken    上传凭证
     * @param $key        上传文件名
     * @param $data       上传二进制流
     * @param $params     自定义变量，规格参考
     *                    http://developer.qiniu.com/docs/v6/api/overview/up/response/vars.html#xvar
     * @param $mime       上传数据的mimeType
     * @param $checkCrc   是否校验crc32
     *
     * @return array    包含已上传文件的信息，类似：
     *                                              [
     *                                                  "hash" => "<Hash string>",
     *                                                  "key" => "<Key string>"
     *                                              ]
     */
    public function binUpload($stream, $data, $bucket = '')
    {
        $toke = $this->_getUploadToken($bucket);
        $size = $data['size'];
        $mine = 'image/png';
        if(!$data['file_name']) {
            throw new Exception('请填写名称');
        }

        $formUploader = new FormUploader();

//        $resumeUpload = new ResumeUploader($toke, $data['file_name'], $stream, $data['size'], '', $mine, '');
//
//        $ret = $resumeUpload->upload();

        $ret = $formUploader->put($toke, $data['file_name'], $stream, '','', $mine, false);

        return $ret;

    }

    /**
     * @param $bucketName
     * @return mixed|string
     * @throws Exception
     * 只有设置到配置的bucket才会使用缓存功能
     */
    private function _getUploadToken($bucketName)
    {
        $upToken = cache('qiniu:token');
        if (!empty($upToken) && empty($bucketName)) {
            return $upToken;
        } else {
            $auth   = new Auth($this->_accessKey, $this->_secretKey);
            $bucket = empty($bucketName) ? $this->_getBucket() : $bucketName;
            if ($bucket === false) {
                throw new Exception('未设置bucket', 100001);
            }
            $upToken = $auth->uploadToken($bucket);
            cache('qiniu:token', $upToken);
            return $upToken;
        }
    }

}
