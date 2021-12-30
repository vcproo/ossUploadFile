<?php
if (is_file(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}else{
    echo "File not found";exit;
}
include_once __DIR__ . '/common.php';
include_once __DIR__ . '/sts.php';
use OSS\OssClient;
use OSS\Core\OssException;
class getUploadOSS{
    /**
     * 上传文件到OSS
     * @param string $localUrl  需要上传的文件路径
     * @param string $ossUrl  oss的存储路径 填写Object完整路径，例如exampledir/exampleobject.jpg。Object完整路径中不能包含Bucket名称。
     * @param int $type  是否使用RAM访问控制授权（sts） 默认关闭 0关闭 1开启
     * @param string $RoleSessionName 当$type=1时使用 此参数用来区分不同的Token，以标明谁在使用此Token，便于审计。 至少需要2个或2个以上的字符
     * @author 韩
     * @date 2021-12-24 14:40
     */
    public function uploadFile($localUrl,$ossUrl,$type=0,$RoleSessionName=''){
        //获取OSS相关参数
        $content = read_file('config.json');
        $publicParam = json_decode($content);
        //使用阿里云主账号上传
        if($type == 0){
            try{
                $ossClient = new OssClient($publicParam->AccessKeyID, $publicParam->AccessKeySecret, $publicParam->Endpoint);
                $data = $ossClient->uploadFile($publicParam->BucketName, $ossUrl, $localUrl);
                $result = array('code'=>200,'msg'=>"请求成功",'data'=>$data);
            } catch(OssException $e) {
                $result = array('code'=>500,'msg'=>"请求异常",'data'=>$e->getMessage());
            }
        }
        //RAM访问控制授权（sts）
        elseif($type ==1){
            $stsInt = new stsInt();
            $config = $stsInt->getSts($RoleSessionName);
            try {
                $ossClient = new OssClient($config['AccessKeyId'], $config['AccessKeySecret'], $publicParam->Endpoint, false, $config['SecurityToken']);
                $data = $ossClient->uploadFile($publicParam->BucketName, $ossUrl, $localUrl);
                $result = array('code'=>200,'msg'=>"请求成功",'data'=>$data);
            } catch (OssException $e) {
                $result = array('code'=>500,'msg'=>"请求异常",'data'=>$e->getMessage());
            }
        }else{
            $result = array('code'=>500,'msg'=>"参数异常",'data'=>'');
        }
        return $result;
    }

    /**
     * 下载到本地文件
     * @param string $localPath 本地指定的文件路径 例如/users/local/。
     * @param string $fileName 本地指定的文件名 例如123.jpg。
     * @param string $ossUrl 表示从OSS下载文件时，需要指定文件所在存储空间的完整名称，即包含文件后缀在内的完整路径，例如abc/123.jpg。
     * @param int $type 是否使用RAM访问控制授权（sts） 默认关闭 0关闭 1开启
     * @param string $RoleSessionName 当$type=1时使用 此参数用来区分不同的Token，以标明谁在使用此Token，便于审计。 至少需要2个或2个以上的字符
     * @author 韩
     * @date 2021-12-27 10:28
     */
    public function downloadFile($localPath,$fileName,$ossUrl,$type=0,$RoleSessionName=''){
        if(!file_exists($localPath)){
            mkdir($localPath,777,true);
        }
        $localUrl = $localPath.$fileName;
        //获取OSS相关参数
        $content = read_file('config.json');
        $publicParam = json_decode($content);
        $accessKeyId = $publicParam->AccessKeyID;
        $accessKeySecret = $publicParam->AccessKeySecret;
        $endpoint = $publicParam->Endpoint;
        $bucket = $publicParam->BucketName;
        if($type == 0){
            $options = array(
                OssClient::OSS_FILE_DOWNLOAD => $localUrl
            );
            try{
                $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
                $data = $ossClient->getObject($bucket, $ossUrl, $options);
                $result = array('code'=>200,'msg'=>"请求成功",'data'=>$data);
            } catch(OssException $e) {
                $result = array('code'=>500,'msg'=>"请求异常",'data'=>$e->getMessage());
                return $result;
            }
        }
        if($type == 1){
            $stsInt = new stsInt();
            $config = $stsInt->getSts($RoleSessionName);
            $accessKeyId = $config['AccessKeyId'];
            $accessKeySecret = $config['AccessKeySecret'];
            // 从STS服务获取的安全令牌（SecurityToken）。
            $securityToken = $config['SecurityToken'];
            // 设置签名URL的有效时长为3600秒。
            $timeout = 3600;
            try {
                $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, false, $securityToken);
                // 生成GetObject的签名URL。
                $signedUrl = $ossClient->signUrl($bucket, $ossUrl, $timeout);
            } catch (OssException $e) {
                $result = array('code'=>500,'msg'=>"请求异常",'data'=>$e->getMessage());
                return $result;
            }
            $sData = file_get_contents($signedUrl);
            $data = file_put_contents($localUrl, $sData);
            if($data){
                $result = array('code'=>200,'msg'=>"请求成功",'data'=>$data);
            }else{
                $result = array('code'=>200,'msg'=>"下载失败",'data'=>"查看signedUrl是否能在地址栏中下载");
            }
        }
        return $result;
    }

    /**
     * 判断文件是否存在
     * @param string $ossUrl 表示需要指定文件所在存储空间的完整名称，即包含文件后缀在内的完整路径，例如abc/123.jpg。
     * @param int $type 是否使用RAM访问控制授权（sts） 默认关闭 0关闭 1开启
     * @param string $RoleSessionName 当$type=1时使用 此参数用来区分不同的Token，以标明谁在使用此Token，便于审计。 至少需要2个或2个以上的字符
     * @return array data=>true 表示文件存在 false文件不存在
     * @throws OssException
     * @throws \AlibabaCloud\Client\Exception\ClientException
     * @author 韩
     * @date 2021-12-28 16:47
     */
    public function isExist($ossUrl,$type,$RoleSessionName){
        //获取OSS相关参数
        $content = read_file('config.json');
        $publicParam = json_decode($content);
        $accessKeyId = $publicParam->AccessKeyID;
        $accessKeySecret = $publicParam->AccessKeySecret;
        $endpoint = $publicParam->Endpoint;
        $bucket = $publicParam->BucketName;
        if($type == 1){
            $stsInt = new stsInt();
            $config = $stsInt->getSts($RoleSessionName);
            $accessKeyId = $config['AccessKeyId'];
            $accessKeySecret = $config['AccessKeySecret'];
            // 从STS服务获取的安全令牌（SecurityToken）。
            $securityToken = $config['SecurityToken'];
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, false, $securityToken);
        }else{
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
        }
        try{
            $exist = $ossClient->doesObjectExist($bucket, $ossUrl);
            // $exist true 文件存在 false 文件不存在
            $result = array('code'=>200,'msg'=>"请求成功",'data'=>$exist);
        } catch(OssException $e) {
            $result = array('code'=>500,'msg'=>"请求异常",'data'=>$e->getMessage());
        }
        return $result;
    }
}
