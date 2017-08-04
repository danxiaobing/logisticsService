<?php
/**
 * 文件上传管理控制器
 *
 * @author  Alan
 * @date    2016-08-06 10:17:32
 * @version file
 */
use OSS\OssClient;
use OSS\Core\OssException;
class UploadController extends Rpc
{

    /**
     * UserController::init()
     *
     * @return void
     */
    public function init()
    {
        parent::init();
    }

    /**
     *  Index 暂空
     *
     *
     */
    public function IndexFunc()
    {
        echo "123";
    }




    /**
     * 上传指定的本地文件内容
     *
     * @param OssClient $ossClient OSSClient实例
     * @param string $bucket 存储空间名称
     * @return null
     */
    public function uploadFileFunc($path,$file)
    {
       /* $Npatn = fopen($file['tmp_name'],'r');
        return $Npatn;*/

       if(!file_exists($file['tmp_name'])){
           return '文件不存在'.$file['tmp_name'];
       }
        $accessKeyId = "YDHIImnDl2cbEXud";
        $accessKeySecret = "YGrft4DKfpAz07HtHGTfMJqulmDzCi";
        $endpoint = "chinayiedoc.oss-cn-shanghai.aliyuncs.com";
        $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, true /* use cname */);
        $bucket = 'chinayiedoc';

        if ((($file["type"] == "image/gif")
                || ($file["type"] == "image/jpeg")
                || ($file["type"] == "image/pjpeg"))
            && ($file["size"] < 8000000)
        ) {
            if ($file["error"] > 0) {
                $fileArray['code'] = 401;
                $fileArray['error'] = "上传出错";
                return $fileArray;
            } else {
                $filePath = $path . '/' . $file["name"]; 
             #   $filePaths = __FILE__;

                $upload_file_by_content = $ossClient->uploadFile($bucket, $filePath, $file['tmp_name']);

                if (!$upload_file_by_content) {
                    $fileArray['code'] = 200;
                    $fileArray['path'] = "http://pic.chinayie.com/" . $filePath;
                    return $fileArray;
                } else {
                    $fileArray['code'] = 402;
                    $fileArray['error'] = "上传出错";
                    return $fileArray;
                }
            }
        } else {
            $fileArray['code'] = 403;
            $fileArray['error'] = "上传出错";
            return $fileArray;
        }

    }

    /**
     * 上传内存文件
     *
     * @param OssClient $ossClient OSSClient实例
     * @param string $bucket 存储空间名称
     * @return null
     */
    public function uploadObjFunc($name,$obj,$bucket='chinayiepic')
    {

        $accessKeyId = "YDHIImnDl2cbEXud";
        $accessKeySecret = "YGrft4DKfpAz07HtHGTfMJqulmDzCi";
        $endpoint = $bucket.".oss-cn-shanghai.aliyuncs.com";
        $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, true /* use cname */);
//        $bucket = 'chinayiedoc';

//        if(strlen($obj) > 2000000)    {
//            $fileArray['code'] = 403;
//            $fileArray['error'] = "上传出错";
//            return $fileArray;
//        }

        $res = $ossClient->putObject($bucket, $name, $obj);

        if ($res) {
            $fileArray['code'] = 200;
            if($bucket == 'chinayiepic') {
                $fileArray['path'] = "http://pic.chinayie.com/" . $name;
            } else {
                $fileArray['path'] = $name;
            }
            return $fileArray;
        } else {
            $fileArray['code'] = 402;
            $fileArray['error'] = "上传出错";
            return $fileArray;
        }


    }


    /**
     * 公共下载控制器
     *
     * @author  Alan
     * @param   OssClient $ossClient OSSClient实例
     * @param   string $bucket 存储空间名称
     * @return  null
     */

    public function getObjectFunc($object,$bucket='chinayipic')
    {
        $accessKeyId = "YDHIImnDl2cbEXud";
        $accessKeySecret = "YGrft4DKfpAz07HtHGTfMJqulmDzCi";
        $endpoint = $bucket.".oss-cn-shanghai.aliyuncs.com";
        $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, true /* use cname */);
//        $bucket = 'chinayiedoc';
        //$object = '/pact/20160818/inv57b554bd41dee.txt';
        try{
            $content = $ossClient->getObject($bucket, $object);
            $data['code'] = 200;
            $data['object'] = $content;
        } catch(OssException $e) {
            $data['code'] = 500;
            $data['error'] = __FUNCTION__ . ": FAILED " . $e->getMessage();
        }
        //print(__FUNCTION__ . ": OK" . "\n");
        return $data;
    }



}