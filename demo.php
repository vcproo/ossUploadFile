<?php
require __DIR__ . '/oss.php';
if ($_FILES["file"]["error"] > 0)
{
    echo "Error: " . $_FILES["file"]["error"] . "";exit;
}
//$filePath = $_FILES["file"]['tmp_name'];
//$object = "1228/2.png";
$upload = new getUploadOSS();
//$result = $upload -> uploadFile($filePath,$object,1,'stsuser');
//var_dump($result);
$object = "upload/test/student_2021/zip/972.zip";
//$local = "./upload/10.jpg";/upload/test/student_2021/zip/972.zip
$local = "./upload/b/";
$fileName = "972.zip";
$result = $upload -> downloadFile($local,$fileName,$object,1,'stsuser');
//$result = $upload -> isExist($object,1,'stsuser');
var_dump($result);