<?php

class DatabaseAction extends PublicAction
{

    protected $db = '';
    protected $datadir = '';

    function _initialize()
    {
        parent::_initialize();
        $this->datadir = ROOT.'/public/data/';
        D('');
        $this->db = DB::getInstance();
    }

    public function index()
    {
        $dataList = $this->db->query("SHOW TABLE STATUS LIKE '".C('DB_PREFIX')."%'");
        $total = 0;
        foreach ($dataList as $row){
            $total += $row['Data_length'];
        }

        $filelist = dir_list($this->datadir);
        foreach ((array)$filelist as $r){
            $files[] = array('path'=> $r,'name' => basename($r), 'size' => filesize($r), 'time' => filemtime($r));
        }
        $this->assign('files',$files);
        $this->assign('totalSize', $total);
        $this->assign("dataList", $dataList);
        $this->display();
    }

    public function excuteQuery($sql='')
    {
        if(empty($sql)) {
            $this->error(L('do_empty'));
        }
        $queryType = 'INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|LOAD DATA|SELECT .* INTO|COPY|ALTER|GRANT|TRUNCATE|REVOKE|LOCK|UNLOCK';
        if (preg_match('/^\s*"?(' . $queryType . ')\s+/i', $sql)) {
            $data['result'] = $this->db->execute($sql);
            $data['type'] = 'execute';
        }else {
            $data['result'] = $this->db->query($sql);
            $data['type'] = 'query';
        }
        $data['dberror'] = $this->db->error();
        return $data;
    }

    public function query()
    {
        $this->display();
    }

    public function doquery()
    {
        $sqls = explode("\n",stripcslashes($_POST['sql']));
        foreach ((array)$sqls as $sql) {
            if($sql)
                $r = $this->excuteQuery($sql);
        }

        if($r['result']!=''){
            $this->success(L('do_ok'));
        }else{
            if($r['dberror']) $this->error(L($r['dberror']));
        }
    }

    public function recover()
    {
        if($_GET['do']=='delete'){

            foreach ((array)$_POST['files'] as $r){
                unlink($r);
            }
            $this->success(L('do_ok'));
        }elseif($_GET['do']=='import'){
            header('Content-Type: text/html; charset=UTF-8');
            $filename = $_GET['filename'];
            $file = $this->datadir.$filename;

            //读取数据文件
            $sqldata = file_get_contents($file);
            $sqlFormat = sql_split($sqldata, C('DB_PREFIX'));

            foreach ((array)$sqlFormat as $sql){
                $sql = trim($sql);
                if (strstr($sql, 'CREATE TABLE')){
                    preg_match('/CREATE TABLE `([^ ]*)`/', $sql, $matches);
                    $ret = $this->excuteQuery($sql);
                    if($ret){
                        //echo   L('CREATE_TABLE_OK').$matches[0].' <br />';
                    }else{
                        echo 'Error sql:'.$sql;
                    }
                }else{
                    $this->excuteQuery($sql);
                }
            }

            $this->success('还原成功！');
        }else{
            $filelist = dir_list($this->datadir);
            foreach ((array)$filelist as $r){
                $files[] = array('path'=> $r,'name' => basename($r), 'size' => filesize($r), 'time' => filemtime($r));
            }
            $this->assign('files',$files);
            $this->display();
        }
    }

    public function docommand()
    {
        $tables = $_POST['tables'];
        $do= trim($_GET['do']);
        if(empty($do) || empty($tables)) $this->error(L('do_empty'));
        if($do=='show'){
             foreach ((array)$tables as $t){
                $this->excuteQuery("SHOW COLUMNS FROM {$t}");
            }
        }else{
            $tables = implode(',',$tables);
            $r = $this->excuteQuery($do.' TABLE '.$tables);
            if (false != $r) {
                $this->success(L('do_ok'));
            } else {
                $this->error($r['dbError']);
            }
        }
    }

    public function backup(){

        if(empty($_POST['tables'])) {
            $dataList = $this->db->query("SHOW TABLE STATUS LIKE '".C('DB_PREFIX')."%'");
            foreach ($dataList as $row){
                $tables[] = $row['Name'];
            }
        }else{
            $tables = $_POST['tables'];
        }

        $sql ="-- ".toDate(time());
        foreach($tables as $key=>$table) {
            $sql .= "--\n-- 表的结构 `$table`\n-- \n";
            $sql .= "DROP TABLE IF EXISTS `$table`;\n";
            $info = $this->db->query("SHOW CREATE TABLE  $table");
            $sql .= str_replace(array('USING BTREE','ROW_FORMAT=DYNAMIC'),'',$info[0]['Create Table']).";\n";
            $result = $this->db->query("SELECT * FROM $table ");
            if($result)
                $sql .= "\n-- \n-- 导出`$table`表中的数据 `$table`\n--\n";
            foreach($result as $key=>$val) {
                foreach ($val as $k=>$field){
                    if(is_string($field)) {
                        $val[$k] = '\''. $this->db->escapeString($field).'\'';
                    }elseif(empty($field)){
                        $val[$k] = 'NULL';
                    }
                }
                $sql .= "INSERT INTO `$table` VALUES (".implode(',', $val).");\n";
            }
        }
        $filename = empty($fileName)? date('YmdH').'_'.rand_string(10) : $fileName;

        $r = file_put_contents($this->datadir . $filename.'.sql', trim($sql));

        if($r){
            $this->assign('jumpUrl', U('Database/recover') );
            $this->success('备份成功！');
        }
    }

    function download(){

        $filename = $_GET['filename'];
        $filepath = './public/data/'.$filename;

        $filetype = strtolower(trim(substr(strrchr($filename, '.'), 1, 10)));
        $filesize = filesize($filepath);
        if(ob_get_length() !== false) {
            @ob_end_clean();
        }
        header('Pragma: public');
        header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: pre-check=0, post-check=0, max-age=0');
        header('Content-Transfer-Encoding: binary');
        header('Content-Encoding: none');
        header('Content-type: '.$filetype);
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Content-length: '.$filesize);
        readfile($filepath);

        exit;
    }
}

function  sql_split($sql,$tablepre) {

    if($tablepre != "fyt_") {
        $sql = str_replace("fyt_", $tablepre, $sql);
    }
    //$sql = preg_replace("/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=utf8",$sql);

    if($r_tablepre != $s_tablepre) {
        $sql = str_replace($s_tablepre, $r_tablepre, $sql);
    }
    $sql = str_replace("\r", "\n", $sql);
    $ret = array();
    $num = 0;
    $queriesarray = explode(";\n", trim($sql));
    unset($sql);
    foreach($queriesarray as $query) {
        $ret[$num] = '';
        $queries = explode("\n", trim($query));
        $queries = array_filter($queries);
        foreach($queries as $query) {
            $str1 = substr($query, 0, 1);
            if($str1 != '#' && $str1 != '-') $ret[$num] .= $query;
        }
        $num++;
    }
    return $ret;
}