<?php
//读取文件
function read_file($fname)
{
    $content = '';
    if (!file_exists($fname)) {
        echo "The file $fname does not exist\n";
        exit (0);
    }
    $handle = fopen($fname, "rb");
    while (!feof($handle)) {
        $content .= fread($handle, 10000);
    }
    fclose($handle);
    return $content;
}
?>