<?php
/**
 * 获取幻灯片
 * @param $id
 * @return mixed
 */
function getSlide($id)
{
    $list = M('SlideData')->where('lang = '.LANG_ID.' and status=1 and fid='.$id)->order('listorder ASC ,id DESC')->select();
    return $list;
}

function stripslashesRecursive(array $array){
    foreach ($array as $k => $v) {
        if (is_string($v)){
            $array[$k] = stripslashes($v);
        } else if (is_array($v)){
            $array[$k] = stripslashesRecursive($v);
        }
    }
    return $array;
}

function fieldoption($fields, $value=null, $space='') {

    if (empty($fields['setup']['options'])) {
        return '';
    }

    $options = explode("\n",$fields['setup']['options']);

    foreach ($options as $r) {
        $v = explode("|",$r);
        $k = trim($v[1]);
        $optionsarr[$k] = $v[0];
    }

    if (isset($value)) {

        if (strpos($value,',')) {
            $value = explode(",",$value);
            $data = array();

            foreach ((array)$value as $val){
                $data[]= $optionsarr[$val];
            }

            if ($space!='') {
                return implode(stripcslashes($space),$data);
            } else {
                return $data;
            }
        } else {
          return $optionsarr[$value];
        }

    }else{
        return $optionsarr;
    }
}

function get_arrparentid($pid, $array=array(),$arrparentid='') {

    if (!is_array($array) || !isset($array[$pid])) {
        return $pid;
    }

    $parentid = $array[$pid]['parentid'];

    $arrparentid = $arrparentid ? $parentid.','.$arrparentid : $parentid;

    if($parentid) {
        $arrparentid = get_arrparentid($parentid,$array, $arrparentid);
    }else{
        $data = array();
        $data['bid'] = $pid;
        $data['arrparentid'] = $arrparentid;
    }
    return $arrparentid;
}

function getform($form,$info,$value=''){
    return $form->$info['type']($info,$value);
}


function shouji($string,$hz='******'){

    if ($n=strpos($string,'@')) {
        return str_replace(substr((string)$string,3,$n-3),$hz,$string);
    }

    return str_replace(substr((string)$string,2,6),$hz,$string);
}


function getvalidate($info) {

    $validate_data = array();
    $errormsg = '';

    if (isset($info['minlength']))
        $validate_data['minlength'] = ' minlength:'.$info['minlength'];

    if (isset($info['maxlength']))
        $validate_data['maxlength'] = ' maxlength:'.$info['maxlength'];

    if (isset($info['required']))
        $validate_data['required'] = ' required:true';

    if (isset($info['pattern']))
        $validate_data['pattern'] = ' '.$info['pattern'].':true';

    if (isset($info['errormsg']))
        $errormsg = ' title="'.$info['errormsg'].'"';

    $validate = implode(',',$validate_data);

    $validate = $validate ? 'validate="'.$validate.'" ' : '';

    $parseStr = $validate.$errormsg;

    return $parseStr;

}


/**
 * 系统邮件发送函数
 * @param string $to    接收邮件者邮箱
 * @param string $subject 邮件主题
 * @param string $body    邮件内容
 * @param string $attachment 附件列表
 * @return boolean
 */
function sendmail($tomail, $subject, $body, $attachment = null)
{
    $config = getCache('Sysconfig');

    import("@.ORG.PHPMailer");

    $mail = new PHPMailer();

    $mail->IsSMTP();

    $mail->PluginDir = LIB_PATH."ORG/";
    $mail->CharSet    = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
    $mail->SMTPDebug  = 0;                  // 关闭SMTP调试功能
    // 1 = errors and messages
    // 2 = messages only
    $mail->SMTPAuth   = true;               // 启用 SMTP 验证功能
    $mail->SMTPSecure = "ssl";              // 使用安全协议
    $mail->Host = $config['SMTP_HOST'];     // SMTP 服务器
    $mail->Port = $config['SMTP_PORT'];     // SMTP服务器的端口号
    $mail->Username = $config['SMTP_USER']; // SMTP服务器用户名
    $mail->Password = $config['SMTP_PASS']; // SMTP服务器密码

    //$mail->From ="";
    //$mail->FromName = "";
    $mail->SetFrom($config['mail_from'], $config['site_name']);     //发送者邮箱
    $mail->AddAddress($tomail); //可同时发多个
    //$mail->AddReplyTo('', ''); //回复到这个邮箱
    //$mail->WordWrap = 50; // 设定 word wrap
    $mail->Subject = $subject;
    $mail->IsHTML(true); // 以HTML发送
    $mail->Body = $body;
    if(is_array($attachment)){ // 添加附件
        foreach ($attachment as $file){
            is_file($file) && $mail->AddAttachment($file);
        }
    }

    return $mail->Send() ? true : $mail->ErrorInfo;
}

function template_file($model='',$path='',$ext='html')
{
    $path = $path ? $path : TMPL_PATH.'Home/'.C('DEFAULT_THEME').'/';

    $tempfiles = dir_list($path,$ext);

    $arr = array();
    foreach ($tempfiles as $key=>$file) {

        $dirname = basename($file);

        if ($model) {
            if(strstr($dirname,$model.'_')) {
                $arr[$key]['name'] =  substr($dirname,0,strrpos($dirname, '.'));
                $arr[$key]['value'] =  substr($arr[$key]['name'],strpos($arr[$key]['name'], '_')+1);
                $arr[$key]['filename'] = $dirname;
                $arr[$key]['filepath'] = $file;
            }
        } else {
          $arr[$key]['name'] = substr($dirname,0,strrpos($dirname, '.'));
          $arr[$key]['value'] = substr($arr[$key]['name'],strpos($arr[$key]['name'], '_')+1);
          $arr[$key]['filename'] = $dirname;
          $arr[$key]['filepath'] = $file;
        }
    }
    return  $arr;
}



function dir_path($path) {

    $path = str_replace('\\', '/', $path);
    if(substr($path, -1) != '/')
        $path = $path.'/';

    return $path;
}


/**
 * 创建目录
 *
 * @return void
 * @author
 **/
function dir_create($path, $mode = 0777) {

    if(is_dir($path)) return TRUE;

    $ftp_enable = 0;
    $path = dir_path($path);
    $temp = explode('/', $path);
    $cur_dir = '';
    $max = count($temp) - 1;
    for ($i=0; $i<$max; $i++) {

        $cur_dir .= $temp[$i].'/';

        if(@is_dir($cur_dir))
          continue;

        @mkdir($cur_dir, 0777,true);
        @chmod($cur_dir, 0777);
    }
    return is_dir($path);
}



function dir_copy($fromdir, $todir) {

    $fromdir = dir_path($fromdir);

    $todir = dir_path($todir);

    if (!is_dir($fromdir)) return FALSE;

    if (!is_dir($todir)) dir_create($todir);

    $list = glob($fromdir.'*');

    if (!empty($list)) {

        foreach($list as $v) {

            $path = $todir.basename($v);

            if(is_dir($v)) {
                dir_copy($v, $path);
            } else {
                copy($v, $path);
                @chmod($path, 0777);
            }
        }
    }
    return TRUE;
}



function dir_list($path, $exts = '', $list= array()) {

    $path = dir_path($path);

    $files = glob($path.'*');

    foreach($files as $v) {

        $fileext = get_extension($v);

        if (!$exts || preg_match("/\.($exts)/i", $v)) {
            $list[] = $v;
            if (is_dir($v)) {
                $list = dir_list($v, $exts, $list);
            }
        }
    }
    return $list;
}



function dir_tree($dir, $parentid = 0, $dirs = array())
{
    if ($parentid == 0) $id = 0;

    $list = glob($dir.'*');

    foreach($list as $v) {

        if (is_dir($v)) {
                $id++;
          $dirs[$id] = array('id'=>$id,'parentid'=>$parentid, 'name'=>basename($v), 'dir'=>$v.'/');
          $dirs = dir_tree($v.'/', $id, $dirs);
        }
    }
    return $dirs;
}



function dir_delete($dir) {

    $dir = dir_path($dir);

    if (!is_dir($dir)) return FALSE;

    $list = glob($dir.'*');

    foreach((array)$list as $v) {
        is_dir($v) ? dir_delete($v) : @unlink($v);
    }
    return @rmdir($dir);
}


/**
 * 格式化时间
 *
 * @return string
 * @author Mr.Weng
 **/
function toDate($time, $format = 'Y-m-d H:i:s')
{
    if (empty($time)) {
        return '';
    }

    $format = str_replace('#', ':', $format);

    return date($format, $time);
}

/**
 * 缓存文件
 *
 * @return void
 * @author Michael Weng
 **/
function savecache($name = '', $id='')
{
    $Model = M($name);

    switch ($name) {
        case 'Lang':
            $list = $Model->where('status=1')->order('listorder')->select ();

            $data = array ();

            foreach ($list as $key => $val) {
                $data[$val['mark']] = $val;
            }

            F($name,$data);
            break;

        case 'Model':
            $list = $Model->order('listorder')->select();
            $data = array ();

            foreach ($list as $key => $val) {
                $data[$val['id']] = $val;
            }

            F($name, $data);
            break;

        case 'Form':
            $list = $Model->order('listorder')->select();
            $data = array ();

            foreach ( $list as $key => $val ) {
                $data[$val['id']] = $val;
            }

            F($name, $data);
            break;

        case 'Config':

            $lang = F('Lang');

            foreach ((array)$lang as $key=>$r) {
                $data = $Model->where('lang='.$r['id'])->getField('varname,value');
                F('Config_'.$key, $data);
            }

            break;

        case 'Sysconfig':

            $data = $Model->getField('varname,value');

            F('Sysconfig',$data);

            break;

        case 'Category':
            $langs = M('Lang')->select();

            foreach ((array)$langs as $key=>$r) {

                $lang_id = $r['id'];

                if ($lang_id) {
                    $lang_name = $r['mark'];
                    $list = D('Admin/Category')->relation('Model')->where('lang='.$lang_id)->order('listorder desc,id asc')->select();
                    $data = array();

                    foreach ($list as $key => $val) {
                        $data[$val['id']] = $val;
                    }

                    F('Category_'.$lang_name, $data);
                }
            }
            break;

        case 'Field':

            if ($id) {

                $list = $Model->order('listorder')->where('modelid='.$id)->select ();
                $pkid = 'field';
                $data = array ();

                foreach ($list as $key => $val) {
                    $data[$val[$pkid]] = $val;
                }

                $name = $name.'_'.$id;

                F($name,$data);

            } else {
                $model = F('Model');
                foreach ($model as $key => $val) {
                    savecache($name,$key);
                }
            }
            break;
        case 'FormField':

            if ($id) {

                $list = $Model->order('listorder')->where('formid='.$id)->select ();
                $data = array ();

                foreach ($list as $key => $val) {
                    $data[$val['field']] = $val;
                }

                $name = $name.'_'.$id;

                F($name,$data);

            } else {
                $model = F('Form');
                foreach ($model as $key => $val) {
                    savecache($name,$key);
                }
            }
            break;

        case 'Dbsource':
            $list = $Model->select();
            $data = array ();

            foreach ($list as $key => $val) {
                $data[$val['name']] = $val;
            }

            F($name,$data);
            break;

        case 'Menu':
            $list = $Model->where('status=1')->order('listorder asc')->select();
            $data = array();
            foreach ($list as $key => $val) {
                $data[$val['id']] = $val;
            }

            F($name, $data);
            break;

        default:
            $list = $Model->order('listorder')->select();
            $pkid = $Model->getPk();
            $data = array();

            foreach ($list as $key => $val) {
                $data[$val[$pkid]] = $val;
            }

            F($name,$data);

            break;
    }
    return true;
}

//获取缓存数据
function getCache($name){

    $data = F($name);
    if (!$data) {

        $cachename = $name;
        if (strpos($name, '_')) {
            $temp_name = explode('_', $name);
            $cachename = $temp_name[0];
        }

        savecache($cachename);
        $data = F($name);
    }
    return $data;
}

function checkfield($fields,$post)
{
    foreach ($fields as $key => $val) {

        $setup = $fields[$key]['setup'];

        if(!empty($fields[$key]['required']) && empty($post[$key])){
            return '';
        }

        if($setup['multiple'] || $setup['inputtype']=='checkbox'){
            $post[$key] = implode(',',$post[$key]);
        }

        switch($fields[$key]['type']){
            case 'groupid':
            case 'checkbox':
                if (empty($post[$key])) {
                    $post[$key] = '';
                } else {
                    $post[$key] = implode(',', $post[$key]);
                }
                break;
            case 'posid':
                if (empty($post['posid'])) {
                    $post['posid'] = '';
                } else {
                    $post['posid'] = '-' . implode('-', $post['posid']) . '-';
                }
                break;
            case 'datetime':
                $post[$key] = strtotime($post[$key]);
                break;
            case 'relation':
                $post[$key] = !empty($post[$key]) ? json_encode(array_unique($post[$key])) : '';
                break;
            case 'textarea':
                $post[$key] = addslashes($post[$key]);
                break;
            case 'images':
            case 'files':
                $name = $key.'_name';
                $arrdata =array();

                foreach($post[$key] as $k=>$res){
                    if(!empty($_POST[$key][$k])) {
                        $arrdata[$k]['filepath'] = $post[$key][$k];
                        $arrdata[$k]['filename'] = $post[$name][$k];
                    }
                }

                $post[$key] = json_encode($arrdata);
                break;
            case 'editor':
                //自动提取摘要
                if(isset($post['add_description']) && $post['description'] == '' && isset($post['content'])) {
                    $content = stripslashes($post['content']);
                    $description_length = intval($post['description_length']);
                    $post['description'] = str_cut(str_replace(array("\r\n","\t",'[page]','[/page]','&ldquo;','&rdquo;'), '', strip_tags($content)),$description_length);
                    $post['description'] = addslashes($post['description']);
                }

                //自动提取缩略图
                if(isset($post['auto_thumb']) && $post['thumb'] == '' && isset($post['content'])) {
                    $content = $content ? $content : stripslashes($post['content']);
                    $auto_thumb_no = intval($post['auto_thumb_no']) * 3;
                    if(preg_match_all("/(src)=([\"|']?)([^ \"'>]+\.(gif|jpg|jpeg|bmp|png))\\2/i", $content, $matches)) {
                        $post['thumb'] = $matches[$auto_thumb_no][0];
                    }
                }
                break;
        }
    }
    return $post;
}


function string2array($info) {

    if($info == '') return array();
    $info = stripcslashes($info);

    eval("\$r = $info;");

    return $r;
}

function array2string($info) {

    if($info == '') return '';

    if(!is_array($info)) $string = stripslashes($info);

    foreach($info as $key => $val) $string[$key] = stripslashes($val);

    return addslashes(var_export($string, TRUE));
}



/**
 * 产生随机字串，可用来自动生成密码
 * 默认长度6位 字母和数字混合 支持中文
 * @param string $len 长度
 * @param string $type 字串类型
 * 0 字母 1 数字 其它 混合
 * @param string $addChars 额外字符
 * @return string
 */

function rand_string($len = 6, $type = '', $addChars = '') {

    $str = '';

    switch ($type) {

        case 0 :
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' . $addChars;
            break;
        case 1 :
            $chars = str_repeat ( '0123456789', 3 );
            break;
        case 2 :
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . $addChars;
            break;
        case 3 :
            $chars = 'abcdefghijklmnopqrstuvwxyz' . $addChars;
            break;
        default :
            // 默认去掉了容易混淆的字符oOLl和数字01，要添加请使用addChars参数
            $chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789' . $addChars;
            break;
    }

    if ($len > 10) { //位数过长重复字符串一定次数

        $chars = $type == 1 ? str_repeat ( $chars, $len ) : str_repeat ( $chars, 5 );

    }

    if ($type != 4) {
        $chars = str_shuffle ( $chars );
        $str = substr ( $chars, 0, $len );
    } else {
        // 中文随机字
        for($i = 0; $i < $len; $i ++) {
            $str .= msubstr ( $chars, floor ( mt_rand ( 0, mb_strlen ( $chars, 'utf-8' ) - 1 ) ), 1 );
        }
    }
    return $str;
}

//系统加密函数
function sysmd5($str, $key='', $type='sha1') {

    $key =  $key ? $key : C('ADMIN_ACCESS');

    return hash($type, $str.$key);
}

function pwdHash($password, $type = 'md5') {
    return hash($type, $password);
}


/**
* @param string $string 原文或者密文
* @param string $operation 操作(ENCODE | DECODE), 默认为 DECODE
* @param string $key 密钥
* @param int $expiry 密文有效期, 加密时候有效， 单位 秒，0 为永久有效
* @return string 处理后的 原文或者 经过 base64_encode 处理后的密文
*
* @example
*
*  $a = authcode('abc', 'ENCODE', 'key');
*  $b = authcode($a, 'DECODE', 'key');  // $b(abc)
*
*  $a = authcode('abc', 'ENCODE', 'key', 3600);
*  $b = authcode('abc', 'DECODE', 'key'); // 在一个小时内，$b(abc)，否则 $b 为空
*/

function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {

    if($operation == 'DECODE') {
        $string = str_replace('[url_a]','+',$string);
        $string = str_replace('[url_b]','&',$string);
        $string = str_replace('[url_c]','/',$string);
    }

    $ckey_length = 4;

    // 随机密钥长度 取值 0-32;
    // 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
    // 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
    // 当此值为 0 时，则不产生随机密钥

    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);

    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
    $string_length = strlen($string);

    $result = '';

    $box = range(0, 255);

    $rndkey = array();

    for($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for($j = $i = 0; $i < 256; $i++) {

        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for($a = $j = $i = 0; $i < $string_length; $i++) {

        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if($operation == 'DECODE') {
        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        $ustr = $keyc.str_replace('=', '', base64_encode($result));
        $ustr = str_replace('+','[url_a]',$ustr);
        $ustr = str_replace('&','[url_b]',$ustr);
        $ustr = str_replace('/','[url_c]',$ustr);

        return $ustr;
    }
}


//字符串截取
function str_cut($sourcestr,$cutlength,$suffix='...'){

    $sourcestr = strip_tags($sourcestr);

    $sourcestr = str_replace('&nbsp;','',$sourcestr);

    $str_length = strlen(strip_tags($sourcestr));

    if($str_length <= $cutlength) {
        return $sourcestr;
    }

    $returnstr='';

    $n = $i = $noc = 0;

    while($n < $str_length) {

        $t = ord($sourcestr[$n]);

        if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
            $i = 1; $n++; $noc++;
        } elseif(194 <= $t && $t <= 223) {
            $i = 2; $n += 2; $noc += 2;
        } elseif(224 <= $t && $t <= 239) {
            $i = 3; $n += 3; $noc += 2;

        } elseif(240 <= $t && $t <= 247) {

            $i = 4; $n += 4; $noc += 2;

        } elseif(248 <= $t && $t <= 251) {

            $i = 5; $n += 5; $noc += 2;

        } elseif($t == 252 || $t == 253) {

            $i = 6; $n += 6; $noc += 2;

        } else {
            $n++;
        }

        if($noc >= $cutlength) {
            break;
        }
    }

    if($noc > $cutlength) {
        $n -= $i;
    }

    $returnstr = substr($sourcestr, 0, $n);

    if ( substr($sourcestr, $n, 6)){
        $returnstr = $returnstr . $suffix;//超过长度时在尾处加上省略号
    }

    return $returnstr;
}

function IP($ip='',$file='UTFWry.dat') {

    import("@.ORG.IpLocation");

    $iplocation = new IpLocation($file);

    $location = $iplocation->getlocation($ip);

    return $location;
}

/**
 * 格式化字节大小
 * @param  number $size      字节数
 * @param  string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 * @author Mr.Weng <hlw@grwy.net>
 */
function byte_format($input, $dec=0){

    $prefix_arr = array("B", "K", "M", "G", "T");
    $value = round($input, $dec);
    $i=0;

    while ($value>1024) {
        $value /= 1024;
        $i++;
    }

    $return_str = round($value, $dec).$prefix_arr[$i];

    return $return_str;
}


function byteFormat($bytes, $unit = "", $decimals = 2) {
    $units = array('B' => 0, 'KB' => 1, 'MB' => 2, 'GB' => 3, 'TB' => 4, 'PB' => 5, 'EB' => 6, 'ZB' => 7, 'YB' => 8);

    $value = 0;
    if ($bytes > 0) {
        // Generate automatic prefix by bytes
        // If wrong prefix given
        if (!array_key_exists($unit, $units)) {
            $pow = floor(log($bytes)/log(1024));
            $unit = array_search($pow, $units);
        }

        // Calculate byte value by prefix
        $value = ($bytes/pow(1024,floor($units[$unit])));
    }

    // If decimals is not numeric or decimals is less than 0
    // then set default value
    if (!is_numeric($decimals) || $decimals < 0) {
        $decimals = 2;
    }

    // Format output
    return sprintf('%.' . $decimals . 'f'.$unit, $value);
}

/**
 * 获取登录验证码 默认为4位数字
 * @param string $fmode 文件名
 * @return string
 */

function build_verify($length=4,$mode=1) {

    return rand_string($length,$mode);
}


function make_urlrule($url,$lang,$action,$MOREREQUEST=''){

    preg_match_all ("/{([\w\$]+)}/",$url, $matches);

    if(strstr($url,'{$parentdir') && C('URL_PATHINFO_DEPR')=='/'){

        foreach((array)$lang as $r){

            $Category = getCache('Category_'.$r);

            foreach((array)$Category as $key =>$r){
                if($r['parentid']==0)
                    $pcatdir[] = $r['catdir'];
            }
        }

        unset($Category);
        $parent_rule = '('.implode('|',$pcatdir).')\/';
    }

    $REQUEST = str_replace(array('{$parentdir}','{$model}','{$model}','{$catdir}','{$year}','{$month}','{$day}','{$catid}','{$id}','{$page}'),array('','model','modelid','catdir','year','month','day','catid','id',C('VAR_PAGE')),$matches[0]);
    $rule = str_replace(array('{$parentdir}','{$model}','{$model}','{$catdir}','{$year}','{$month}','{$day}','{$catid}','{$id}','{$page}','/',C('URL_HTML_SUFFIX')),array('','([A-Z]{1}[a-z]+)','(\d+)','([\w^_]+)','(\d+)','(\d+)','(\d+)','(\d+)','(\d+)','(\d+)','\/',''),$url);

    $i=0;$j=1;$k=2;$n=3;
    $request = '';
    $request_lang = '';
    $request_lang_2 = '';
    $request_lang_3 = '';
    $langrequest = '';
    foreach($REQUEST as $key =>$r){

        if ($r) {
            $i=$i+1;
            $request .=$r.'=:'.$i.'&';
            $j=$j+1;
            $request_lang .=$r.'=:'.$j.'&';
            $k=$k+1;
            $request_lang_2 .=$r.'=:'.$k.'&'; //二级
            $n=$n+1;
            $request_lang_3 .=$r.'=:'.$n.'&'; //三级
        }
    }


    $langrule = '('.implode('|',$lang).')\/';

    if($parent_rule){

        $data[] = '\'/^'.$langrule.$parent_rule.'([\w^_]+)\/'.$rule.'$/\' => \'Content/'.$action.'?l=:1&parentdir=:2&'.$request_lang_3.$MOREREQUEST.$langrequest.'\'';
        $data[] = '\'/^'.$langrule.$parent_rule.$rule.'$/\' => \'Content/'.$action.'?l=:1&parentdir=:2&'.$request_lang_2.$MOREREQUEST.$langrequest.'\'';
        $data[] = '\'/^'.$parent_rule.'([\w^_]+)\/'.$rule.'$/\' => \'Content/'.$action.'?parentdir=:1&'.$request_lang_2.$MOREREQUEST.$langrequest.'\'';
        $data[] = '\'/^'.$parent_rule.$rule.'$/\' => \'Content/'.$action.'?parentdir=:1&'.$request_lang.$MOREREQUEST.$langrequest.'\'';

        if(strstr($url,'{$page')){
            $data[] = '\'/^'.$langrule.$parent_rule.'(\d+)$/\' => \'Content/'.$action.'?l=:1&catdir=:2&p=:3\'';
            $data[] = '\'/^'.$parent_rule.'(\d+)$/\' => \'Content/'.$action.'?catdir=:1&p=:2\'';
        }else{
            $data[] = '\'/^'.$langrule.$parent_rule.'$/\' => \'Content/'.$action.'?l=:1&catdir=:2\'';
            $data[] = '\'/^'.$parent_rule.'$/\' => \'Content/'.$action.'?catdir=:1\'';
        }

    }else{
        $data[] = '\'/^'.$langrule.$rule.'$/\' => \'Content/'.$action.'?l=:1&'.$request_lang.$MOREREQUEST.$langrequest.'\'';
        $data[]='\'/^'.$rule.'$/\' => \'Content/'.$action.'?'.$request.$MOREREQUEST.'\'';
    }

    $data = str_replace('\/$','$',$data);
    $data = implode(",\n",$data);
    
    return $data;
}

function HOMEURL($lang){

    $lang = C('DEFAULT_LANG')!=$lang ? $lang : '';

    $url = $lang ? __ROOT__.'/'.$lang.'/' :  __ROOT__.'/';

    return $url;
}

function URL($url='',$params=array()) {

    $lang = getlang();

    if(!empty($url)){

        list($path, $query) = explode('?',$url);

        list($group, $a) = explode('/',$path);

        list($g, $m) = explode('-',$group);

        $params= http_build_query($params);

        $params = !empty($params) ? '&' . $params : '';

        $query =  !empty($query) ? '&'.$query : '';

        //parse_str($_SERVER['QUERY_STRING'],$urlarr);

        if($lang) $langurl = '&l='.$lang;

        if (strcasecmp($g,'Home')== 0){
            $url = __ROOT__.'/index.php?m='.$m.'&a='.$a.$query.$params.$langurl;
        }else{
            $url = __ROOT__.'/index.php?g='.$g.'&m='.$m.'&a='.$a.$query.$params.$langurl;
        }

    }else{

        $url = $lang ? __ROOT__.'/'.$lang.'/' :  __ROOT__.'/';
    }

    return $url;
}


function TAGURL($data,$p=''){

    $lang=getlang();

    if($data['modelid'] > 0 && $data['modelid']!=2)
        $params['modelid'] = $data['modelid'] ;

    if($data['slug'])
        $params['tag'] = $data['slug'] ;

    if($lang)
        $params['l']=$lang;

    $url = URL('Home-tags/index',$params);

    if($p)
        $url = $url.'&p={$page}';

    return $url;
}



function getlang($have='')
{
    $conf = getCache('Sysconfig');
    if ($have) {
        if(strcasecmp(GROUP_NAME,'admin') == 0)
            $lang = LANG_NAME;
        else
            $lang = $_REQUEST['l'] ? $_REQUEST['l'] : $conf['DEFAULT_LANG'];
    } else {
        if(strcasecmp(GROUP_NAME,'admin') == 0)
            $lang = $conf['DEFAULT_LANG']!= LANG_NAME ? LANG_NAME : '';
        else
            $lang = !empty($_REQUEST['l']) && $conf['DEFAULT_LANG'] != $_REQUEST['l'] ? $_REQUEST['l'] : '';
    }

    return $lang;
}



function geturl($cat,$data='',$Urlrule='')
{
    $id = !empty($data['id']) ? $data['id'] : 0;

    $lang = getlang();

    $parentdir = $cat['parentdir'];

    $catdir = empty($cat['catdir']) ? Pinyin(strtolower($cat['catname'])):$cat['catdir'];
    $catdir = str_replace(" ","",$catdir);

    $data['createtime'] = !empty($data['createtime']) ? $data['createtime'] : 0;
    $year  = date('Y',$data['createtime']);
    $month = date('m',$data['createtime']);
    $day   = date('d',$data['createtime']);
    $model = $cat['model'];

    $modelid = $cat['modelid'];
    $catid = $cat['id'];

    $urlrule = explode(':::',C('URL_URLRULE'));
    $showurlrule = $urlrule[0];
    $listurlrule = $urlrule[1];

    if(empty($urls)){

        $langurl = $lang ? $lang.'/' : '';

        if($id){
            $urls = str_replace(array('{$parentdir}','{$model}','{$modelid}','{$catdir}','{$year}','{$month}','{$day}','{$catid}','{$id}'),array($parentdir,$model,$modelid,$catdir,$year,$month,$day,$catid,$id),$showurlrule);
        }else{
            $urls = str_replace(array('{$parentdir}','{$model}','{$modelid}','{$catdir}','{$year}','{$month}','{$day}','{$catid}','{$id}'),array($parentdir,$model,$modelid,$catdir,$year,$month,$day,$catid,$id),$listurlrule);
        }

        $urls = explode('|',$urls);
        $urls[0] = __ROOT__.'/'.$langurl.$urls[0];
        $urls[1] = __ROOT__.'/'.$langurl.$urls[1];
    }
    return $urls;
}

/**
 * 内容分页
 *
 * @return string
 * @author Mr.Weng
 **/
function content_pages($num, $p,$pageurls) {

    $multipage = '';
    $page = 11;
    $offset = 4;
    $pages = $num;
    $from = $p - $offset;
    $to = $p + $offset;
    $more = 0;

    if ($page >= $pages) {
        $from = 2;
        $to = $pages-1;
    } else {

        if ($from <= 1) {
            $to = $page-1;
            $from = 2;
        } elseif ($to >= $pages) {
            $from = $pages-($page-2);
            $to = $pages-1;
        }
        $more = 1;
    }

    if ($p>0) {

        $perpage = $p == 1 ? 1 : $p-1;

        if ($perpage==1) {
            $multipage .= '<a class="a1" href="'.$pageurls[$perpage][0].'">'.L('previous').'</a>';
        } else {
            $multipage .= '<a class="a1" href="'.$pageurls[$perpage][1].'">'.L('previous').'</a>';
        }

        if ($p==1) {
            $multipage .= ' <span>1</span>';
        } elseif($p>6 && $more) {
            $multipage .= ' <a href="'.$pageurls[1][0].'">1</a>..';
        } else {
            $multipage .= ' <a href="'.$pageurls[1][0].'">1</a>';
        }
    }

    for ($i = $from; $i <= $to; $i++) {

        if ($i != $p) {
            $multipage .= ' <a href="'.$pageurls[$i][1].'">'.$i.'</a>';
        } else {
            $multipage .= ' <span>'.$i.'</span>';
        }
    }

    if ($p<$pages) {
        if ($p<$pages-5 && $more) {
            $multipage .= ' ..<a href="'.$pageurls[$pages][1].'">'.$pages.'</a> <a class="a1" href="'.$pageurls[$p+1][1].'">'.L('next').'</a>';
        } else {
            $multipage .= ' <a href="'.$pageurls[$pages][1].'">'.$pages.'</a> <a class="a1" href="'.$pageurls[$p+1][1].'">'.L('next').'</a>';
        }
    } elseif ($p==$pages) {
        $multipage .= ' <span>'.$pages.'</span> <a class="a1" href="'.$pageurls[$p][1].'">'.L('next').'</a>';
    }
    return $multipage;
}


/**
 * 获取缩略图
 *
 * @return string
 * @author Mr.Weng
 **/
function thumb($file, $tw=300, $th=300 ,$autocat=0, $nopic = 'nopic.jpg',$t=''){

    if(strstr($file,'://'))
        return $file;
    if(empty($file))
        return __ROOT__.'/public/images/'.$nopic;

    $file = '.'.str_replace(__ROOT__,'',$file);

    // 获取图片的信息
    list($fw, $fh, $tmp) = getimagesize($file);

    if(empty($t)){

        if($fw>$tw && $fh>$th){

            $pathinfo = pathinfo($file);

            $t = $pathinfo['dirname'].'/thumb_'.$tw.'_'.$th.'_'.$pathinfo['basename'];

            if(is_file($t)){
                return  __ROOT__.substr($t,1);
            }
        }else{
            return  __ROOT__.substr($file,1);
        }
    }

    if($autocat){
        if($fw/$tw > $fh/$th){
            $fw = $tw * ($fh/$th);
        }else{
            $fh = $th * ($fw/$tw);
        }
    }else{
        $scale = min($tw/$fw, $th/$fh); // 计算缩放比例
        if($scale>=1) {
            // 超过原图大小不再缩略
            $tw = $fw;
            $th = $fh;
        }else{
            // 缩略图尺寸
            $tw = (int)($fw*$scale);
            $th = (int)($fh*$scale);
        }
    }

    // 创建图像资源 
    switch ($tmp) {
        case 1: //gif
            $fimg = imagecreatefromgif($file);
            break;
        case 2: //jpg
            $fimg = imagecreatefromjpeg($file);
            break;
        case 3: //png
            $fimg = imagecreatefrompng($file);
            break;
        default:
            return false;
            break;
    }

    $timg = imagecreatetruecolor($tw, $th);


    if($tmp==1 || $tmp==3) {
        $otsc = imagecolortransparent($fimg); //将某个颜色定义为透明色
        if( $otsc >= 0 && $otsc < imagecolorstotal($fimg)) {  //取得一幅图像的调色板中颜色的数目
             $transparentcolor = imagecolorsforindex( $fimg, $otsc ); //取得某索引的颜色
             $newtransparentcolor = imagecolorallocate(
                 $timg,
                 $transparentcolor['red'],
                 $transparentcolor['green'],
                 $transparentcolor['blue']
             );

             imagefill( $timg, 0, 0, $newtransparentcolor );
             imagecolortransparent( $timg, $newtransparentcolor );
        }

        /* 使用imagecopyresampled()函数对图片进行裁剪 */
        //imagecopyresampled($timg, $fimg, 0,0, 0,0, $tw,$th, $fw,$fh);

        imagecopyresized($timg, $fimg, 0, 0, 0, 0, $tw, $th, $fw, $fh);
    } else {
        imagecopyresized($timg, $fimg, 0, 0, 0, 0, $tw, $th, $fw, $fh);
    }

    switch ($tmp) {
        case 1: //gif
            imageGIF($timg, $t);
            break;
        case 2: //jpg
            imageJPEG($timg,$t);  
            break;
        case 3: //png
            imagePng($timg, $t);  
            break;
    }

    imagedestroy($timg);
    imagedestroy($fimg);

    return  __ROOT__.substr($t,1);
}


function Pinyin($_String) {

$_DataKey = "a|ai|an|ang|ao|ba|bai|ban|bang|bao|bei|ben|beng|bi|bian|biao|bie|bin|bing|bo|bu|ca|cai|can|cang|cao|ce|ceng|cha".
   "|chai|chan|chang|chao|che|chen|cheng|chi|chong|chou|chu|chuai|chuan|chuang|chui|chun|chuo|ci|cong|cou|cu|".
   "cuan|cui|cun|cuo|da|dai|dan|dang|dao|de|deng|di|dian|diao|die|ding|diu|dong|dou|du|duan|dui|dun|duo|e|en|er".
   "|fa|fan|fang|fei|fen|feng|fo|fou|fu|ga|gai|gan|gang|gao|ge|gei|gen|geng|gong|gou|gu|gua|guai|guan|guang|gui".
   "|gun|guo|ha|hai|han|hang|hao|he|hei|hen|heng|hong|hou|hu|hua|huai|huan|huang|hui|hun|huo|ji|jia|jian|jiang".
   "|jiao|jie|jin|jing|jiong|jiu|ju|juan|jue|jun|ka|kai|kan|kang|kao|ke|ken|keng|kong|kou|ku|kua|kuai|kuan|kuang".
   "|kui|kun|kuo|la|lai|lan|lang|lao|le|lei|leng|li|lia|lian|liang|liao|lie|lin|ling|liu|long|lou|lu|lv|luan|lue".
   "|lun|luo|ma|mai|man|mang|mao|me|mei|men|meng|mi|mian|miao|mie|min|ming|miu|mo|mou|mu|na|nai|nan|nang|nao|ne".
   "|nei|nen|neng|ni|nian|niang|niao|nie|nin|ning|niu|nong|nu|nv|nuan|nue|nuo|o|ou|pa|pai|pan|pang|pao|pei|pen".
   "|peng|pi|pian|piao|pie|pin|ping|po|pu|qi|qia|qian|qiang|qiao|qie|qin|qing|qiong|qiu|qu|quan|que|qun|ran|rang".
   "|rao|re|ren|reng|ri|rong|rou|ru|ruan|rui|run|ruo|sa|sai|san|sang|sao|se|sen|seng|sha|shai|shan|shang|shao|".
   "she|shen|sheng|shi|shou|shu|shua|shuai|shuan|shuang|shui|shun|shuo|si|song|sou|su|suan|sui|sun|suo|ta|tai|".
   "tan|tang|tao|te|teng|ti|tian|tiao|tie|ting|tong|tou|tu|tuan|tui|tun|tuo|wa|wai|wan|wang|wei|wen|weng|wo|wu".
   "|xi|xia|xian|xiang|xiao|xie|xin|xing|xiong|xiu|xu|xuan|xue|xun|ya|yan|yang|yao|ye|yi|yin|ying|yo|yong|you".
   "|yu|yuan|yue|yun|za|zai|zan|zang|zao|ze|zei|zen|zeng|zha|zhai|zhan|zhang|zhao|zhe|zhen|zheng|zhi|zhong|".
   "zhou|zhu|zhua|zhuai|zhuan|zhuang|zhui|zhun|zhuo|zi|zong|zou|zu|zuan|zui|zun|zuo";

$_DataValue = "-20319|-20317|-20304|-20295|-20292|-20283|-20265|-20257|-20242|-20230|-20051|-20036|-20032|-20026|-20002|-19990".
   "|-19986|-19982|-19976|-19805|-19784|-19775|-19774|-19763|-19756|-19751|-19746|-19741|-19739|-19728|-19725".
   "|-19715|-19540|-19531|-19525|-19515|-19500|-19484|-19479|-19467|-19289|-19288|-19281|-19275|-19270|-19263".
   "|-19261|-19249|-19243|-19242|-19238|-19235|-19227|-19224|-19218|-19212|-19038|-19023|-19018|-19006|-19003".
   "|-18996|-18977|-18961|-18952|-18783|-18774|-18773|-18763|-18756|-18741|-18735|-18731|-18722|-18710|-18697".
   "|-18696|-18526|-18518|-18501|-18490|-18478|-18463|-18448|-18447|-18446|-18239|-18237|-18231|-18220|-18211".
   "|-18201|-18184|-18183|-18181|-18012|-17997|-17988|-17970|-17964|-17961|-17950|-17947|-17931|-17928|-17922".
   "|-17759|-17752|-17733|-17730|-17721|-17703|-17701|-17697|-17692|-17683|-17676|-17496|-17487|-17482|-17468".
   "|-17454|-17433|-17427|-17417|-17202|-17185|-16983|-16970|-16942|-16915|-16733|-16708|-16706|-16689|-16664".
   "|-16657|-16647|-16474|-16470|-16465|-16459|-16452|-16448|-16433|-16429|-16427|-16423|-16419|-16412|-16407".
   "|-16403|-16401|-16393|-16220|-16216|-16212|-16205|-16202|-16187|-16180|-16171|-16169|-16158|-16155|-15959".
   "|-15958|-15944|-15933|-15920|-15915|-15903|-15889|-15878|-15707|-15701|-15681|-15667|-15661|-15659|-15652".
   "|-15640|-15631|-15625|-15454|-15448|-15436|-15435|-15419|-15416|-15408|-15394|-15385|-15377|-15375|-15369".
   "|-15363|-15362|-15183|-15180|-15165|-15158|-15153|-15150|-15149|-15144|-15143|-15141|-15140|-15139|-15128".
   "|-15121|-15119|-15117|-15110|-15109|-14941|-14937|-14933|-14930|-14929|-14928|-14926|-14922|-14921|-14914".
   "|-14908|-14902|-14894|-14889|-14882|-14873|-14871|-14857|-14678|-14674|-14670|-14668|-14663|-14654|-14645".
   "|-14630|-14594|-14429|-14407|-14399|-14384|-14379|-14368|-14355|-14353|-14345|-14170|-14159|-14151|-14149".
   "|-14145|-14140|-14137|-14135|-14125|-14123|-14122|-14112|-14109|-14099|-14097|-14094|-14092|-14090|-14087".
   "|-14083|-13917|-13914|-13910|-13907|-13906|-13905|-13896|-13894|-13878|-13870|-13859|-13847|-13831|-13658".
   "|-13611|-13601|-13406|-13404|-13400|-13398|-13395|-13391|-13387|-13383|-13367|-13359|-13356|-13343|-13340".
   "|-13329|-13326|-13318|-13147|-13138|-13120|-13107|-13096|-13095|-13091|-13076|-13068|-13063|-13060|-12888".
   "|-12875|-12871|-12860|-12858|-12852|-12849|-12838|-12831|-12829|-12812|-12802|-12607|-12597|-12594|-12585".
   "|-12556|-12359|-12346|-12320|-12300|-12120|-12099|-12089|-12074|-12067|-12058|-12039|-11867|-11861|-11847".
   "|-11831|-11798|-11781|-11604|-11589|-11536|-11358|-11340|-11339|-11324|-11303|-11097|-11077|-11067|-11055".
   "|-11052|-11045|-11041|-11038|-11024|-11020|-11019|-11018|-11014|-10838|-10832|-10815|-10800|-10790|-10780".
   "|-10764|-10587|-10544|-10533|-10519|-10331|-10329|-10328|-10322|-10315|-10309|-10307|-10296|-10281|-10274".
   "|-10270|-10262|-10260|-10256|-10254";

  $_TDataKey   = explode('|', $_DataKey);

  $_TDataValue = explode('|', $_DataValue);

  $_Data =  array_combine($_TDataKey, $_TDataValue);

  arsort($_Data);

  reset($_Data);

  $_String= auto_charset($_String,'utf-8','gbk');

  $_Res = '';

  for($i=0; $i<strlen($_String); $i++) {

    $_P = ord(substr($_String, $i, 1));

    if($_P>160) {
        $_Q = ord(substr($_String, ++$i, 1)); $_P = $_P*256 + $_Q - 65536;
    }

    $_Res .= _Pinyin($_P, $_Data);

  }

  return preg_replace("/[^a-z0-9]*/", '', $_Res);

}


// 自动转换字符集 支持数组转换
function auto_charset($fContents, $from='gbk', $to='utf-8') {

    $from = strtoupper($from) == 'UTF8' ? 'utf-8' : $from;

    $to = strtoupper($to) == 'UTF8' ? 'utf-8' : $to;

    if (strtoupper($from) === strtoupper($to) || empty($fContents) || (is_scalar($fContents) && !is_string($fContents))) {

      //如果编码相同或者非字符串标量则不转换
      return $fContents;

    }

    if (is_string($fContents)) {

      if (function_exists('mb_convert_encoding')) {

        return mb_convert_encoding($fContents, $to, $from);

      } elseif (function_exists('iconv')) {

        return iconv($from, $to, $fContents);

      } else {

        return $fContents;

      }

    } elseif (is_array($fContents)) {

      foreach ($fContents as $key => $val) {

          $_key = auto_charset($key, $from, $to);

          $fContents[$_key] = auto_charset($val, $from, $to);

          if ($key != $_key)

              unset($fContents[$key]);
      }

      return $fContents;

    }

    else {

      return $fContents;
    }
}

function _Pinyin($_Num, $_Data) {

    if ($_Num>0 && $_Num<160) {
        return chr($_Num);
    } elseif ($_Num<-20319 || $_Num>-10247) {
        return '';
    } else {
        foreach ($_Data as $k=>$v) {
            if ($v<=$_Num) break;
        }

        return $k;
    }
}

function return_url($code){

    $config = getCache('Sysconfig');

    return 'http://'.$config['SITE_DOMAIN'].'/index.php?g=user&m=pay&a=respond&code='.$code;
}

function order_pay_status($sn,$value){

    $cart['status'] =1;
    $cart['pay_status'] = $value;
    if($value==2)$cart['pay_time'] =time();

    $r = M('Order')->where("sn='{$sn}'")->save($cart);

    return $r;
}


//取回指定栏目最顶级父栏目
function get_max_parent_cat($id,$data){
    $cats='';

    while(1){

        foreach ($data as $row) {

            if ($row['id']==$id) {
                $id=$row['parentid'];
                $cats=$row['id'];
                break;
            }
        }
        if($id==0){
            break;
        }
    }
    return $cats;
}

function cn_substr_utf8($str, $length, $start=0) {

    $lgocl_str=$str;
    if(strlen($str) < $start+1) {
        return '';
    }

    preg_match_all("/./su", $str, $ar);

    $str = '';
    $tstr = '';

    //为了兼容mysql4.1以下版本,与数据库varchar一致,这里使用按字节截取
    for($i=0; isset($ar[0][$i]); $i++){

        if(strlen($tstr) < $start)  {
            $tstr .= $ar[0][$i];
        } else {
            if(strlen($str) < $length + strlen($ar[0][$i]) )  {
                $str .= $ar[0][$i];
            } else {
                break;
            }
        }
    }

    if(strlen($lgocl_str)<=$length){

    }else{
        $str.="...";
    }

    return deletehtml($str);
}


function deletehtml($str)
{
    $str = trim($str);
    $str = strip_tags($str,"");
    $str = preg_replace("{\t}","",$str);
    $str = preg_replace("{\r\n}","",$str);
    $str = preg_replace("{\r}","",$str);
    $str = preg_replace("{\n}","",$str);
    $str = preg_replace("{ }","",$str);
    return $str;
}


/**
 * 获取文件名后缀
 *
 * @return string
 * @author Mr.Weng
 **/
function get_extension($file)
{
    return pathinfo($file, PATHINFO_EXTENSION);
}

function get_yzh_auth($file_limit,$file_size,$modelid,$file_types='jpeg,jpg,png,gif')
{
    $attach['file_limit'] = $file_limit;
    $attach['file_size'] = $file_size;
    $attach['modelid'] = $modelid;
    $attach['file_types'] = $file_types;
    $attach['isthumb'] = 0;
    $yzh_auth_key = sysmd5(C('ADMIN_ACCESS').$_SERVER['HTTP_USER_AGENT']);
    $temp_str = authcode(json_encode($attach), 'ENCODE', $yzh_auth_key);
    $yzh_auth = urlencode($temp_str);

    return $yzh_auth;
}

/**
 * 根据用户ID获取用户昵称
 * @param  integer $uid 用户ID
 * @return string       用户昵称
 */
function get_nickname($uid = 0)
{
    $data = M('User')->field('username')->find($uid);
    $name = $data['username'];
    return $name;
}

/**
 * 检测用户是否登录
 * @return integer 0-未登录，大于0-当前登录用户ID
 */
function is_login()
{
    if (empty($_SESSION['userid'])) {
        return 0;
    } else {
        return $_SESSION['userid'] ? $_SESSION['userid'] : 0;
    }
}


/**
 * 时间戳格式化
 * @param int $time
 * @return string 完整的时间显示
 */
function time_format($time = NULL, $format = 'Y-m-d H:i')
{
    $time = $time === NULL ? NOW_TIME : intval($time);
    return date($format, $time);
}

/**
 * 记录行为日志，并执行该行为的规则
 * @param string $action 行为标识
 * @param string $model 触发行为的模型名
 * @param int    $record_id 触发行为的记录id
 * @param int    $user_id 执行行为的用户id
 * @return boolean
 */
function action_log($action = null, $model = null, $record_id = null, $user_id = null)
{

    //参数检查
    if (empty($action) || empty($model) || empty($record_id)) {
        return '参数不能为空';
    }
    if (empty($user_id)) {
        $user_id = is_login();
    }

    //查询行为,判断是否执行
    $action_info = M('Action')->getByName($action);
    if ($action_info['status'] != 1) {
        return '该行为被禁用或删除';
    }

    //插入行为日志
    $data['action_id'] = $action_info['id'];
    $data['user_id'] = $user_id;
    $data['action_ip'] = ip2long(get_client_ip());
    $data['model'] = $model;
    $data['record_id'] = $record_id;
    $data['create_time'] = NOW_TIME;

    //解析日志规则,生成日志备注
    if (!empty($action_info['log'])) {
        if (preg_match_all('/\[(\S+?)\]/', $action_info['log'], $match)) {
            $log['user'] = $user_id;
            $log['record'] = $record_id;
            $log['model'] = $model;
            $log['time'] = NOW_TIME;
            $log['data'] = array('user' => $user_id, 'model' => $model, 'record' => $record_id, 'time' => NOW_TIME);
            foreach ($match[1] as $value) {
                $param = explode('|', $value);
                if (isset($param[1])) {
                    $replace[] = call_user_func($param[1], $log[$param[0]]);
                } else {
                    $replace[] = $log[$param[0]];
                }
            }
            $data['remark'] = str_replace($match[0], $replace, $action_info['log']);
        } else {
            $data['remark'] = $action_info['log'];
        }
    } else {
        //未定义日志规则，记录操作url
        $data['remark'] = '操作url：' . $_SERVER['REQUEST_URI'];
    }

    M('ActionLog')->add($data);

    if (!empty($action_info['rule'])) {
        //解析行为
        $rules = parse_action($action, $user_id);

        //执行行为
        execute_action($rules, $action_info['id'], $user_id);
    }
}

/**
 * 解析行为规则
 * 规则定义  table:$table|field:$field|condition:$condition|rule:$rule[|cycle:$cycle|max:$max][;......]
 * 规则字段解释：table->要操作的数据表，不需要加表前缀；
 *              field->要操作的字段；
 *              condition->操作的条件，目前支持字符串，默认变量{$self}为执行行为的用户
 *              rule->对字段进行的具体操作，目前支持四则混合运算，如：1+score*2/2-3
 *              cycle->执行周期，单位（小时），表示$cycle小时内最多执行$max次
 *              max->单个周期内的最大执行次数（$cycle和$max必须同时定义，否则无效）
 * 单个行为后可加 ； 连接其他规则
 * @param string $action 行为id或者name
 * @param int    $self 替换规则里的变量为执行用户的id
 * @return boolean|array: false解析出错 ， 成功返回规则数组
 */
function parse_action($action = null, $self)
{
    if (empty($action)) {
        return false;
    }

    //参数支持id或者name
    if (is_numeric($action)) {
        $map = array('id' => $action);
    } else {
        $map = array('name' => $action);
    }

    //查询行为信息
    $info = M('Action')->where($map)->find();
    if (!$info || $info['status'] != 1) {
        return false;
    }

    //解析规则:table:$table|field:$field|condition:$condition|rule:$rule[|cycle:$cycle|max:$max][;......]
    $rules = $info['rule'];
    $rules = str_replace('{$self}', $self, $rules);
    $rules = explode(';', $rules);
    $return = array();
    foreach ($rules as $key => &$rule) {
        $rule = explode('|', $rule);
        foreach ($rule as $k => $fields) {
            $field = empty($fields) ? array() : explode(':', $fields);
            if (!empty($field)) {
                $return[$key][$field[0]] = $field[1];
            }
        }
        //cycle(检查周期)和max(周期内最大执行次数)必须同时存在，否则去掉这两个条件
        if (!array_key_exists('cycle', $return[$key]) || !array_key_exists('max', $return[$key])) {
            unset($return[$key]['cycle'], $return[$key]['max']);
        }
    }

    return $return;
}

/**
 * 执行行为
 * @param array $rules 解析后的规则数组
 * @param int   $action_id 行为id
 * @param array $user_id 执行的用户id
 * @return boolean false 失败 ， true 成功
 */
function execute_action($rules = false, $action_id = null, $user_id = null)
{
    if (!$rules || empty($action_id) || empty($user_id)) {
        return false;
    }

    $return = true;
    foreach ($rules as $rule) {

        //检查执行周期
        $map = array('action_id' => $action_id, 'user_id' => $user_id);
        $map['create_time'] = array('gt', NOW_TIME - intval($rule['cycle']) * 3600);
        $exec_count = M('ActionLog')->where($map)->count();
        if ($exec_count > $rule['max']) {
            continue;
        }

        //执行数据库操作
        $Model = M(ucfirst($rule['table']));
        /**
         * 判断是否加入了货币规则
         */
        if ($rule['tox_money_rule'] != '' && $rule['tox_money_rule'] != null) {
            $change = array($rule['field'] => array('exp', $rule['rule']), $rule['tox_money_field'] => array('exp', $rule['tox_money_rule']));
            $res = $Model->where($rule['condition'])->setField($change);
        } else {
            $field = $rule['field'];
            $res = $Model->where($rule['condition'])->setField($field, array('exp', $rule['rule']));
        }
        if (!$res) {
            $return = false;
        }
    }
    return $return;
}

/**
 * 根据ip获取城市地址
 * */
function getaddressbyip($ip=null){
    if(!$ip){
        $ip = get_client_ip();//14.127.247.38
    }
    if($ip=='127.0.0.1'){
        //return false;
    }
    $url = 'http://ip.taobao.com/service/getIpInfo.php?ip='.$ip;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回
    $location = curl_exec($ch);
    $location = json_decode($location);
    curl_close($ch);
    $loc = "";
    if($location===FALSE) return "";
    return $location->data->country;
}


/**
 * 获取栏目相关信息
 * @param type $catid 栏目id
 * @param type $field 返回的字段，默认返回全部，数组
 * @param type $newCache 是否强制刷新
 * @return boolean
 */
function getCategory($catid, $field = '', $newCache = false) {
    if (empty($catid)) {
        return false;
    }
    $key = 'getCategory_' . $catid;
    //强制刷新缓存
    if ($newCache) {
        S($key, NULL);
    }
    $cache = S($key);
    if ($cache === 'false') {
        return false;
    }
    if (empty($cache)) {
        //读取数据
        $cache = M('Category')->where(array('id' => $catid))->find();
        if (empty($cache)) {
            S($key, 'false', 60);
            return false;
        } else {
            //扩展配置
            $cache['setting'] = unserialize($cache['setting']);
            //栏目扩展字段
            $cache['extend'] = $cache['setting']['extend'];
            S($key, $cache, 3600);
        }
    }
    if ($field) {
        //支持var.property，不过只支持一维数组
        if (false !== strpos($field, '.')) {
            $vars = explode('.', $field);
            return $cache[$vars[0]][$vars[1]];
        } else {
            return $cache[$field];
        }
    } else {
        return $cache;
    }
}


/**
 * 获取模型数据
 * @param type $modelid 模型ID
 * @param type $field 返回的字段，默认返回全部，数组
 * @return boolean
 */
function getModel($modelid, $field = '') {
    if (empty($modelid)) {
        return false;
    }
    $key = 'getModel_' . $modelid;
    $cache = S($key);
    if ($cache === 'false') {
        return false;
    }
    if (empty($cache)) {
        //读取数据
        $cache = M('Model')->where(array('id' => $modelid))->find();
        if (empty($cache)) {
            S($key, 'false', 60);
            return false;
        } else {
            S($key, $cache, 3600);
        }
    }
    if ($field) {
        return $cache[$field];
    } else {
        return $cache;
    }
}

/**
 * 获取关联信息
 *
 * @return void
 * @author
 **/
function get_relation($relation, $modelname){
    $relation = json_decode($relation, true);
    $relation_data = M($modelname)->field('url,title,thumb')->where(array('id'=>array('in',$relation)))->select();
    return $relation_data;
}

//附件生成初始化
function attach_update_start()
{
    cookie('att_json',null);
}

//附件更新
function attach_update($keyid)
{
    if(cookie('att_json')) {
        $att_exist_arr = M('AttachmentIndex')->where("keyid='{$keyid}'")->getField('aid',true);
        $att_arr = explode('||', cookie('att_json'));
        foreach ($att_arr as $att_json) {
            $att = json_decode($att_json,true);
            if (!empty($att_exist_arr) && in_array($att['aid'], $att_exist_arr)){
                continue;
            }
            $data['aid']   = $att['aid'];
            $data['keyid'] = $keyid;
            M('Attachment')->where('aid='.$att['aid'])->save(array('module'=>'content','status'=>1));
            M('AttachmentIndex')->add($data);
        }
    }
}

/**
 * 通过附件关系删除附件
 * @param type $keyid 关联ID
 * @return boolean 布尔值
 */
function attach_delete($keyid) {
    if (empty($keyid)) {
        return false;
    }
    $db = M("AttachmentIndex");
    $data = $db->where(array("keyid" => $keyid))->select();
    if ($data) {
        foreach ($data as $aid) {
            //统计使用同一个附件的次数，如果大于一，表示还有其他地方使用，将不删除附件
            $count = $db->where(array("aid" => $aid['aid']))->count();
            if ($count > 1) {
                //只删除附件关系，不删除真实附件
                continue;
            } else {
                $att_db = M('Attachment');
                $att = $att_db->field('filepath')->where('aid='.$aid['aid'])->find();
                $att['filepath'] = substr($att['filepath'],1);
                @unlink($att['filepath']);
                $att_db->where('aid='.$aid['aid'])->delete();
            }
        }
    }
    $db->where(array("keyid" => $keyid))->delete();
    return true;
}

//获取浏览器类型
function getBrowser() {
    $user_OSagent = $_SERVER['HTTP_USER_AGENT'];
    if (strpos($user_OSagent, "Maxthon") && strpos($user_OSagent, "MSIE")) {
        $visitor_browser = "Maxthon(Microsoft IE)";
    } elseif (strpos($user_OSagent, "Maxthon 2.0")) {
        $visitor_browser = "Maxthon 2.0";
    } elseif (strpos($user_OSagent, "Maxthon")) {
        $visitor_browser = "Maxthon";
    } elseif (strpos($user_OSagent, "Edge")) {
        $visitor_browser = "Edge";
    } elseif (strpos($user_OSagent, "Trident")) {
        $visitor_browser = "IE";
    } elseif (strpos($user_OSagent, "MSIE")) {
        $visitor_browser = "IE";
    } elseif (strpos($user_OSagent, "MSIE")) {
        $visitor_browser = "MSIE 较高版本";
    } elseif (strpos($user_OSagent, "NetCaptor")) {
        $visitor_browser = "NetCaptor";
    } elseif (strpos($user_OSagent, "Netscape")) {
        $visitor_browser = "Netscape";
    } elseif (strpos($user_OSagent, "Chrome")) {
        $visitor_browser = "Chrome";
    } elseif (strpos($user_OSagent, "Lynx")) {
        $visitor_browser = "Lynx";
    } elseif (strpos($user_OSagent, "Opera")) {
        $visitor_browser = "Opera";
    } elseif (strpos($user_OSagent, "MicroMessenger")) {
        $visitor_browser = "微信浏览器";
    } elseif (strpos($user_OSagent, "Konqueror")) {
        $visitor_browser = "Konqueror";
    } elseif (strpos($user_OSagent, "Mozilla/5.0")) {
        $visitor_browser = "Mozilla";
    } elseif (strpos($user_OSagent, "Firefox")) {
        $visitor_browser = "Firefox";
    } elseif (strpos($user_OSagent, "U")) {
        $visitor_browser = "Firefox";
    } else {
        $visitor_browser = "其它";
    }
    return $visitor_browser;
}