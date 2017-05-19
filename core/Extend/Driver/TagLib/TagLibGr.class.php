<?php

/**
 *
 * TagLib (标签库)
 *
 */
class TagLibGr extends TagLib
{
    // 标签定义
    protected $tags = array(
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'list' => array('attr' => 'name,field,limit,order,catid,thumb,posid,where,sql,key,page,mod,id,ids,status', 'level' => 3),
        'cat' => array('attr' => 'catid,type,self,key,id,count', 'level' => 3),
        'wapcat' => array('attr' => 'catid,type,self,key,id,count', 'level' => 3),
        'catpos' => array('attr' => 'catid,space', 'close' => 0),
        'link' => array('attr' => 'typeid,linktype,field,limit,order,field', 'level' => 3),
        'type' => array('attr' => 'typeid,keyid,limit,order,id', 'level' => 3),
        'db' => array('attr' => 'dbname,sql,key,mod,id', 'level' => 3),
        'block' => array('attr' => 'blockid,key,id', 'close' => 0),
        'slide' => array('attr' => 'slideid,key,mod,id', 'close' => 3),
        'plugin' => array('attr' => 'id,name,key', 'close' => 3),
        'tags' => array('attr' => 'keywords,list,key,mod,modelid,id,limit,order', 'close' => 3),
    );

    public function _tags($attr, $content)
    {
        $tag = $this->parseXmlAttr($attr, 'tags');
        $id = !empty($tag['id']) ? $tag['id'] : 'r';
        $key = !empty($tag['key']) ? $tag['key'] : 'i';
        $mod = isset($tag['mod']) ? $tag['mod'] : '2';
        $limit = isset($tag['limit']) ? $tag['limit'] : '12';
        $order = isset($tag['order']) ? $tag['order'] : 'id desc';
        $keywords = !empty($tag['keywords']) ? $tag['keywords'] : '';
        $list = !empty($tag['list']) ? $tag['list'] : '';
        $modelid = !empty($tag['modelid']) ? $tag['modelid'] : '';

        if ($modelid && !is_numeric($modelid)) {
            $modelid = $this->tpl->get($modelid);
        }

        preg_match("/[a-zA-Z]+/", $keywords, $keywordsa);

        if (!empty($keywordsa[0])) {
            $keywords = $this->tpl->get($keywords);
        }

        if ($keywords) {
            $keyarr = explode(',', $keywords);
            $keywords = "'" . implode('\',\'', $keyarr) . "'";
        }

        $where = ' lang=' . LANG_ID;
        $where .= $modelid ? ' and modelid=' . $modelid : '';
        $where .= $keywords ? " and name in($keywords)" : '';

        if ($list) {

            $tagids = M("Tags")->where($where)->order($order)->limit($limit)->select();

            $where = " b.status=1 ";

            if ($tagids[0]) {

                foreach ((array)$tagids as $r) $tagidarr[] = $r['id'];

                $tagid = implode(',', $tagidarr);

                $where .= " and a.tagid in($tagid)";

            }
            $M = getCache('Model');

            $prefix = C("DB_PREFIX");

            $modelid = $modelid ? $modelid : '2';
            $mtable = $prefix . strtolower($M[$modelid]['tablename']);
            $field = 'b.id,b.catid,b.userid,b.url,b.username,b.title,b.keywords,b.description,b.thumb,b.createtime';
            $table = $prefix . 'tags_data as a';
            $mtable = $mtable . " as b on a.id=b.id";

            $sql = "M(\"Tags_data\")->field(\"{$field}\")->table(\"{$table}\")->join(\"{$mtable}\")->where(\"{$where}\")->order(\"{$order}\")->group(\"b.id\")->limit(\"{$limit}\")->select();";

        } else {
            $sql = "M(\"Tags\")->where(\"{$where}\")->order(\"{$order}\")->limit(\"{$limit}\")->select()";
        }

        //下面拼接输出语句

        $parsestr = '<?php
        $_result=' . $sql . ';
        if ($_result){
             $' . $key . '=0;
            foreach($_result as $key=>$' . $id . '){
                ++$' . $key . ';
                $mod = ($' . $key . ' % ' . $mod . ' );
                ?>'.$content.'<?php
            }
        }
        ?>';

        return $parsestr;
    }

    public function _slide($attr, $content)
    {
        $tag = $this->parseXmlAttr($attr, 'flash');
        $id = !empty($tag['id']) ? $tag['id'] : 'r';  //定义数据查询的结果存放变量
        $key = !empty($tag['key']) ? $tag['key'] : 'i';
        $mod = isset($tag['mod']) ? $tag['mod'] : '2';
        $slideid = !empty($tag['slideid']) ? $tag['slideid'] : '';
        $where = ' status=1 ';

        $wherelang = ' and lang=' . LANG_ID;

        if ($slideid)
            $where .= " and id=$slideid ";

        $slide = M('Slide')->where($where)->find();

        if (empty($slide)) return '';

        $wherepic = " status=1 and fid=$slideid " . $wherelang;

        $order = " listorder ASC ,id DESC ";

        $limit = $slide['num'] ? $slide['num'] : 5;

        $sql = "M('Slide_data')->where(\"{$wherepic}\")->order(\"{$order}\")->limit(\"{$limit}\")->select();";

        //下面拼接输出语句
        $parsestr = '';
        $parsestr .= '<?php  $_result=' . $sql . ';
        $count = count($_result);
        if ($_result): $' . $key . '=0;
        foreach($_result as $key=>$' . $id . '):
            $' . $key . '++;
            $mod = ($' . $key . ' % ' . $mod . ' );
            parse_str($' . $id . '[\'data\'],$' . $id . '[\'param\']);
        ?>';

        $parsestr .= $content;
        $parsestr .= '<?php endforeach; endif;?>';

        return $parsestr;
    }

    public function _block($attr, $content)
    {
        $tag = $this->parseXmlAttr($attr, 'block');
        $id = !empty($tag['id']) ? $tag['id'] : 'r';  //定义数据查询的结果存放变量
        $key = !empty($tag['key']) ? $tag['key'] : 'i';
        $pos = !empty($tag['pos']) ? $tag['pos'] : '';
        $mod = isset($tag['mod']) ? $tag['mod'] : '2';
        $blockid = !empty($tag['blockid']) ? $tag['blockid'] : '';

        if ($blockid && !is_numeric($blockid)) {

            if (substr($blockid, 0, 2) == 'T[') {

                $T = $this->tpl->get('T');

                preg_match_all("/T\[(.*)\]$/", $blockid, $arr);

                $blockid = $T[$arr[1][0]];

            } else {
                $blockid = $this->tpl->get($blockid);
            }
        }

        $where = ' lang=' . LANG_ID;

        if ($pos) $where .= " and pos='$pos' ";

        if ($blockid) $where .= " and id=$blockid ";

        $r = M('Block')->where($where)->find();

        return $r['content'];
    }

    public function _db($attr, $content)
    {
        $tag = $this->parseXmlAttr($attr, 'db');
        $id = !empty($tag['id']) ? $tag['id'] : 'r';  //定义数据查询的结果存放变量
        $key = !empty($tag['key']) ? $tag['key'] : 'i';
        $sql = !empty($tag['sql']) ? $tag['sql'] : '';
        $dbname = isset($tag['dbname']) ? $tag['dbname'] : '';
        $mod = isset($tag['mod']) ? $tag['mod'] : '2';

        $dbsource = F('Dbsource');

        $db = $dbsource[$dbname];

        if (empty($db) || empty($sql)) return '';

        $sql = str_replace('{tablepre}', $db['dbtablepre'], $sql);

        $db = 'mysql://' . $db['username'] . ':' . $db['password'] . '@' . $db['host'] . ':' . $db['port'] . '/' . $db['dbname'];

        $sql = "M()->db(1,\"{$db}\")->query(\"{$sql}\");";

        //下面拼接输出语句
        $parsestr = '';
        $parsestr .= '<?php  $_result=' . $sql . ';
        if ($_result):
        $' . $key . '=0;
        foreach($_result as $key=>$' . $id . '):
        ++$' . $key . ';$mod = ($' . $key . ' % ' . $mod . ' );
        ?>';
        $parsestr .= $content;//解析在article标签中的内容
        $parsestr .= '<?php endforeach; endif;?>';

        return $parsestr;
    }

    public function _cat($attr, $content)
    {
        $tag = $this->parseXmlAttr($attr, 'cat');
        $id = !empty($tag['id']) ? $tag['id'] : 'r'; //定义数据查询的结果存放变量 result
        $type = !empty($tag['type']) ? $tag['type'] : '';
        $self = !empty($tag['self']) ? '1' : '';
        $key = !empty($tag['key']) ? $tag['key'] : 'n';
        $catid = !empty($tag['catid']) ? $tag['catid'] : '0';
        $count = !empty($tag['count']) ? $tag['count'] : 'count';

        $condition = $type ? ' &&  $' . $id . '["type"] == "' . $type . '"' : '';

        if ($catid && !is_numeric($catid)) {
            if (substr($catid, 0, 1) != '$') {
                $catid = $this->tpl->get($catid);
            }
        }

        $ifleft = $self ? '(' : '';
        $selfcondition = $self ? ') or (  $' . $id . '[\'ismenu\']==1 && intval(' . $catid . ')==$' . $id . '["id"])' : '';

        $parsestr = '<?php
            $' . $count . '=0;
            foreach ($Cats as $keyy=>$vy) {
                if($vy["ismenu"]==1 && intval(' . $catid . ')==$vy["parentid"]) {
                    $' . $count . '++;
                }
            }

            $' . $key . '=0;

            foreach ($Cats as $key=>$' . $id . ') {

                if('.$ifleft.' $'.$id.'[\'ismenu\']==1 && intval('.$catid.')==$'.$id.'["parentid"]'.$condition.$selfcondition.') {
                    ++$' . $key . ';
                    ?>'.$content.'<?php
                }
            }
            ?>';

        return  $parsestr;
    }


    public function _wapcat($attr, $content)
    {
        $tag = $this->parseXmlAttr($attr, 'cat');
        $id = !empty($tag['id']) ? $tag['id'] : 'r'; //定义数据查询的结果存放变量 result
        $type = !empty($tag['type']) ? $tag['type'] : '';
        $self = !empty($tag['self']) ? '1' : '';
        $key = !empty($tag['key']) ? $tag['key'] : 'n';
        $catid = !empty($tag['catid']) ? $tag['catid'] : '0';
        $count = !empty($tag['count']) ? $tag['count'] : 'count';

        $condition = $type ? ' &&  $' . $id . '["type"] == "' . $type . '"' : '';

        if ($catid && !is_numeric($catid)) {
            if (substr($catid, 0, 1) != '$') {
                $catid = $this->tpl->get($catid);
            }
        }

        $ifleft = $self ? '(' : '';
        $selfcondition = $self ? ') or (  $' . $id . '[\'is_wap_menu\']==1 && intval(' . $catid . ')==$' . $id . '["id"])' : '';

        $parsestr = '<?php
            $' . $count . '=0;
            foreach ($Cats as $keyy=>$vy) {
                if($vy["is_wap_menu"]==1 && intval(' . $catid . ')==$vy["parentid"]) {
                    $' . $count . '++;
                }
            }

            $' . $key . '=0;

            foreach ($Cats as $key=>$' . $id . ') {

                if('.$ifleft.' $'.$id.'[\'is_wap_menu\']==1 && intval('.$catid.')==$'.$id.'["parentid"]'.$condition.$selfcondition.') {
                    ++$' . $key . ';
                    ?>'.$content.'<?php
                }
            }
            ?>';

        return  $parsestr;
    }


    public function _catpos($attr)
    {
        $tag = $this->parseXmlAttr($attr,'catpos');
        $space = $tag['space'];

        if(is_numeric($tag['catid'])){

            $catid = intval($tag['catid']);
            $category_arr = $this->tpl->get('Cats');

            if(!isset($category_arr[$catid])) return '';

            $arrparentid = array_filter(explode(',', $category_arr[$catid]['arrparentid'].','.$catid));

            foreach($arrparentid as $cid) {
                $parsestr[] = '<a href="'.$category_arr[$cid]['url'].'">'.$category_arr[$cid]['catname'].'</a>';
            }

            return implode($space,$parsestr);

        }else{

            //下面拼接输出语句
            $parsestr = '<?php
            $ci=0;
            $arrparentid = array_filter(explode(\',\', $Cats[$'.$tag['catid'].'][\'arrparentid\'].\',\'.$'.$tag['catid'].'));
            foreach($arrparentid as $cid){
                $ci=$ci+1;
                if(count($arrparentid)==$ci){
                    $class="bc";
                }
                $parsestr[] = \'<a class="\'.$class.\'" href="\'.$Cats[$cid][\'url\'].\'">\'.$Cats[$cid][\'catname\'].\'</a>\';
            }
            echo implode("'.$space.'",$parsestr);
            ?>';

            return $parsestr;
        }
    }

    public function _link($attr,$content)
    {
        $tag = $this->parseXmlAttr($attr,'link');
        $id = !empty($tag['id'])?$tag['id']:'r';  //定义数据查询的结果存放变量
        $key = !empty($tag['key'])?$tag['key']:'i';
        $mod = isset($tag['mod'])?$tag['mod']:'2';

        //$typeid = isset($tag['$typeid'])?$tag['$typeid']:'';

        $linktype = isset($tag['linktype'])?$tag['linktype']:'';
        $order = isset($tag['order'])?$tag['order']:'id desc';
        $limit = isset($tag['limit'])?$tag['limit']: '10';
        $field = isset($tag['field'])?$tag['field']:'*';

        $where = ' lang=' . LANG_ID;

        if(substr($tag['typeid'],0,1)=='$') {
            $where .= ' and  typeid=$tag["typeid"]';
        }elseif(is_numeric($tag['typeid'])){
            $where .= " and typeid=".intval($tag['typeid']);
        }else{
            $typeid = $this->tpl->get($tag['typeid']);
            if($typeid)
                $where .= " and typeid=".intval($typeid);
        }

        if($linktype){
            $where .=  " and  linktype=".$linktype;
        }

        $sql = "M(\"Link\")->field(\"{$field}\")->where(\"{$where}\")->order(\"{$order}\")->limit(\"{$limit}\")->select();";

        //下面拼接输出语句
        $parsestr  = '';
        $parsestr .= '<?php
            $_result='.$sql.';$count=count($_result);
            if ($_result){
                $'.$key.'=0;
                foreach ($_result as $key=>$'.$id.') {
                    ++$'.$key.';
                    $mod = ($'.$key.' % '.$mod.' );
                    ?>'.$content.'<?php
                }
            }
            ?>';

        return  $parsestr;
    }

    //分类
    public function _type($attr,$content)
    {
        $tag = $this->parseXmlAttr($attr,'type');
        $id = !empty($tag['id']) ? $tag['id'] : 'r';  //定义数据查询的结果存放变量
        $key = !empty($tag['key']) ? $tag['key'] : 'i';
        $keyid = !empty($tag['keyid']) ? $tag['keyid'] : 0;
        $limit = isset($tag['limit']) ? $tag['limit']: '10';
        $field = isset($tag['field']) ? $tag['field'] : '*';

        $where = "parentid != 0 and keyid=".$keyid;

        $sql = "M(\"Type\")->field(\"{$field}\")->where(\"{$where}\")->limit(\"{$limit}\")->select()";

        //下面拼接输出语句
        $parsestr  = '';
        $parsestr .= '<?php
            $_result = '.$sql.';
            $count = count($_result);
            if ($_result) {
                $'.$key.'=0;
                foreach ($_result as $key=>$'.$id.') {
                    ++$'.$key.';
                    ?>'.$content.'<?php
                }
            }
            ?>';

        return  $parsestr;
    }

    //主营产品
    public function _plugin($attr,$content)
    {
        $tag = $this->parseXmlAttr($attr,'plugin');
        $id = !empty($tag['id']) ? $tag['id'] : 'r';  //定义数据查询的结果存放变量
        $name = !empty($tag['name']) ? ucfirst($tag['name']) : '';
        $key = !empty($tag['key']) ? $tag['key'] : 'i';

        if (!F($name.'_'.LANG_NAME)) {
            $result = M('Plugin')->where("name='$name' and lang=".LANG_ID)->find();
            F($name.'_'.LANG_NAME, $result);
        }

        //下面拼接输出语句
        $parsestr  = '';
        $parsestr .= '<?php
                $result = F(\''.$name.'_'.LANG_NAME.'\');
                $plugin_data = json_decode($result[\'config\'],true);
                $'.$key.'=0;
                foreach ($plugin_data as $key=>$'.$id.') {
                    ++$'.$key.';
                    ?>'.$content.'<?php
                }
            ?>';
        return  $parsestr;
    }

    public function _list($attr, $content)
    {
        $tag = $this->parseXmlAttr($attr, 'list');
        $id = !empty($tag['id']) ? $tag['id'] : 'r';  //定义数据查询的结果存放变量
        $key = !empty($tag['key']) ? $tag['key'] : 'i';
        $page = !empty($tag['page']) ? '1' : '0';
        $mod = isset($tag['mod']) ? $tag['mod'] : '2';
        $modelname = !empty($tag['name']) ? $tag['name'] : '';
        $catid = !empty($tag['catid']) ? $tag['catid'] : '';

        if (! empty($modelname) || ! empty($catid)) {

            $order = isset($tag['order']) ? $tag['order'] : 'id desc';
            $field = isset($tag['field']) ? $tag['field'] : 'id,catid,url,title,title_style,keywords,description,thumb,createtime';
            $limit = isset($tag['limit']) ? $tag['limit'] : '10';
            $status = isset($tag['status']) ? intval($tag['status']) : '1';
            $ids = isset($tag['ids']) ? $tag['ids'] : '';

            $where = isset($tag['where']) ? $tag['where'] : ' 1 ';
            $where .= ' AND createtime<='.time();
            $where .= " AND status=$status ";

            if ( ! empty($ids)) {
                if (strpos($ids, ',')) {
                    $where .= " AND id in($ids) ";
                } else {
                    $where .= " AND id=$ids ";
                }
            }

            if ( ! empty($catid)) {
                if (is_numeric($catid)) {
                    $category_arr = $this->tpl->get('Cats');
                    if (empty($modelname)) {
                        $modelname = $category_arr[$catid]['model'];
                    }
                    if ($category_arr[$catid]['child']) {
                        $where .= " AND catid in(" . $category_arr[$catid]['arrchildid'] . ")";
                    } else {
                        $where .= " AND catid=" . $catid;
                    }

                } else {
                    $firstLetter = substr($catid, 0, 1);
                    if ( ! is_numeric($firstLetter)) {
                        if ($firstLetter != '$') {
                            $catid = $this->tpl->get($catid);
                        }
                        $where .= ' AND catid in(".$Cats[' . $catid . '][\'arrchildid\'].")';
                    } else {
                        $where .= ' AND catid in(' . strip_tags($catid) . ')';
                    }
                }
            }

            if (!empty($tag['posid'])) {
                $posid = '-'.$tag['posid'].'-';
                $where .= ' AND posid like \'%' . $posid . '%\'';
            }

            if (!empty($tag['thumb'])) {
                $where .= ' AND  thumb !=\'\' ';
            }
            $sql = "M(\"{$modelname}\")->field(\"{$field}\")->where(\"{$where}\")->order(\"{$order}\")->limit(\"{$limit}\")->select()";
        } else {

            if ( ! $tag['sql']) {
                //排除没有指定model名称，也没有指定sql语句的情况
                return '';
            }

            $sql = "M()->query(\"{$tag['sql']}\")";
        }

        //下面拼接输出语句
        $parsestr = '<?php
            $_result=' . $sql . ';
            if ($_result) {
                $' . $key . '=0;
                $total = count($_result);
                foreach($_result as $key=>$' . $id . ') {
                    ++$' . $key . ';
                    $mod = ($' . $key . ' % ' . $mod . ' );
                    ?>'.$content.'<?php
                }
            }
         ?>';

        return  $parsestr;
    }
}