<?php
# oss.php
namespace App\Service;
use OSS\OssClient;
use Illuminate\Support\Facades\Log;

class OSS
{

    private $ossClient;
    private $bucketName;
    public function __construct()
    {
        $serverAddress = config('alioss.isInternal') ? config('alioss.ossServerInternal') : config('alioss.ossServer');
        $this->ossClient = new OssClient(
            config('alioss.AccessKeyId'),
            config('alioss.AccessKeySecret'),
            $serverAddress);
        $this->bucketName=config('alioss.BucketName');
        Log::info($serverAddress);
        print_r($this->ossClient);
    }

    public static function oss_list($url, $delimiter = '/')
    {
        $oss = new OSS(); // 上传文件使用内网，免流量费
        $options = array(
            'delimiter' => $delimiter,
            'prefix' => $url,
        );
        try {
            Log::info($oss->bucketName);
            Log::info(var_export($options,true));
            $listObjectInfo = $oss->ossClient->listObjects($oss->bucketName, $options);
            $objectList = $listObjectInfo->getObjectList();
            if (!empty($objectList)) {
                $arr = array();
                foreach ($objectList as $objectInfo) {
                    $obj = array();
                    $obj['name'] = $objectInfo->getKey();
                    $obj['size'] = $objectInfo->getSize();
                    $obj['time'] = $objectInfo->getLastModified();
                    array_push($arr, $obj);
                    Log::info($objectInfo->getKey() . "\t" . $objectInfo->getSize() . "\t" . $objectInfo->getLastModified() . ",");
                }
                return $arr;
             }
        } catch (OssException $e) {
             Log::error($e->getMessage());
        }
    }


    public static function upload($ossKey, $filePath)
    {
        $oss = new OSS(); // 上传文件使用内网，免流量费
        $oss->ossClient->setBucket($oss->bucketName);
        $res = $oss->ossClient->uploadFile($ossKey, $filePath);
        return $res;
    }


    /**
     * 删除存储在oss中的文件
     *
     * @param string $ossKey 存储的key（文件路径和文件名）
     * @return
     */
    public static function deleteObject($ossKey)
    {
        $oss = new OSS(false); // 上传文件使用内网，免流量费
        return $oss->ossClient->deleteObject(config('alioss.BucketName'), $ossKey);
    }
}
