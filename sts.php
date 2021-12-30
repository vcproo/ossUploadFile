<?php
if (is_file(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}else{
    echo "File not found";exit;
}
include_once __DIR__ . '/common.php';
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
class stsInt{
    /**
     * 获取STS参数
     * @param string $RoleSessionName 此参数用来区分不同的Token，以标明谁在使用此Token，便于审计。 至少需要2个或2个以上的字符
     * @return array
     * @throws ClientException
     * @author 韩
     * @date 2021-12-24 15:05
     */
    public function getSts($RoleSessionName=''){
        //获取STS使用相关参数
        if(empty($RoleSessionName)){
            $RoleSessionName = 'userid';
        }
        $content = read_file('config.json');
        $config = json_decode($content);
        //构建一个阿里云客户端，用于发起请求。
        //构建阿里云客户端时需要设置AccessKey ID和AccessKey Secret。
        AlibabaCloud::accessKeyClient($config->STSAccessKeyID, $config->STSAccessKeySecret)
            ->regionId($config->Regon)
            ->asDefaultClient();
        //设置参数，发起请求。关于参数含义和设置方法，请参见《API参考》。
        try {
            $result = AlibabaCloud::rpc()
                ->product('Sts')
                ->scheme('https') // https | http
                ->version('2015-04-01')
                ->action('AssumeRole')
                ->method('POST')
                ->host('sts.aliyuncs.com')
                ->options([
                    'query' => [
                        'RegionId' => $config->Regon,
                        'RoleArn' => $config->STSRoleArn,
                        'RoleSessionName' => $RoleSessionName, // 此参数用来区分不同的Token，以标明谁在使用此Token，便于审计
                    ],
                ])
                ->request();
            return $result->toArray()['Credentials'];
            //print_r($result->toArray());
        } catch (ClientException $e) {
            echo $e->getErrorMessage() . PHP_EOL;exit;
        } catch (ServerException $e) {
            echo $e->getErrorMessage() . PHP_EOL;exit;
        }
    }
}


