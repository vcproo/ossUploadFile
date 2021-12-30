# 阿里云 对象存储 OSS

## 内容

封装了OSS上传、下载文件，包含使用RAM授权方式上传 下载

## 目录说明：

1. vendor 官方SDK
2. config.json 配置文件 
3. common.php 公共文件
4. sts.php STS类
5. oss.php OSS上传下载类
6. demo.php 

## 使用说明

- config.json 参数说明（注意：该文件不能写注释）

  公共参数 必填部分

  `"BucketName" : "","Endpoint" : "","Regon":"",`

  未开启STS时：填写主账号 不用写子账号

  `"AccessKeyID" : "","AccessKeySecret" : "",`

  开启STS时：填写子账号 不用写主账号

  `"STSAccessKeyID" : "","STSAccessKeySecret" : "","STSRoleArn" : ""`

  

## 参考文档

对象存储OSS的PHP SDK各种使用场景下的示例代码 [链接](https://help.aliyun.com/document_detail/32099.html )

STS PHP SDK示例代码 [链接](https://help.aliyun.com/document_detail/28792.html )

##其他

- SDK下载方式

执行以下命令，安装Alibaba Cloud SDK for PHP至当前目录下 

`composer require alibabacloud/sdk `

或者参考[链接](https://help.aliyun.com/document_detail/53111.htm?spm=a2c4g.11186623.0.0.4ba13c681jpYm4#concept-b43-j4j-zdb )
- 更多功能

参考[链接](https://help.aliyun.com/document_detail/32098.html)
