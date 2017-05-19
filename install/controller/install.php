<?php

//全局公共文件
require WEB_ROOT.'/apps/Conf/db.php';

function copydir($dirsrc,$dirto){
    if(is_file($dirto)){
        echo '目标不是目录不能创建';
        return;
    }

    if(!file_exists($dirto)){
        mkdir($dirto);
        echo '创建目录'.$dirto.'成功<br/>';
    }

    $dir=opendir($dirsrc);

    while($filename=readdir($dir)){
        if($filename!='.'&&$filename!='..'){
            $file1=$dirsrc.'/'.$filename;
            $file2=$dirto.'/'.$filename;

            if(is_dir($file1)){
                copydir($file1,$file2);
            }else{
                echo '复制文件的'.$file1.'成功<br/>';
                copy($file1,$file2);
            }
        }
    }

    closedir($dir);
}
//去掉注释
function get_sql($file){
    $sql = file_get_contents($file);
    $arr = array("/#.*/","/--\s+.*/","/\/\*.*?\*\//s");
    $sql = preg_replace($arr,'',$sql);
    return $sql;
}

//
function import_data($file,$db_pre,$link){
  $sql_array = file($file);

  foreach($sql_array as $k=>$v){

    $v=trim($v);
    if(strlen($v)==0){
        continue;
    }

    //跳过不是插入数据的sql语句
    if(stripos($v,'INSERT INTO')===false){
      continue;
    }

    //替换表前缀
    $v = preg_replace("/INSERT INTO `yzh_(.*?)` VALUES/is","INSERT INTO `{$db_pre}\\1` VALUES",$v);

    if(mysql_query($v,$link)==false){
      return $v;
    }

  }
  return TRUE;
}
view('install');