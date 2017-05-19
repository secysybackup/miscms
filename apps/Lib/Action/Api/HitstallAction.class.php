<?php

class HitstallAction extends Action
{

    protected $db;

    function _initialize()
    {
        $Lang = getCache('Lang');
        $sysconf = getCache('Sysconfig');
        $this->assign('Lang',$Lang);

        $l = isset($_GET['l']) ? $_GET['l'] : $sysconf['DEFAULT_LANG'];

        define('LANG_ID', $Lang[$l]['id']);

        if (!empty($_GET['ismobile'])) {
            define('IS_MOBILE', 1);
        }
    }

    //详细获取点击数
    public function index()
    {
		//type 1:pc ip访问数量 2:pc pv数量  3:mobile ip访问数量 4:mobile pv访问数量
		if(defined('IS_MOBILE')){
			$this->hits(3);
			$this->hits(4);
		}else{
            $this->hits(1);
			$this->hits(2);
		}

		exit;
    }

    /**
     * 增加点击数
     * @param type $r 点击相关数据
     * @return boolean
     */
    private function hits($type)
    {
        if (empty($type)) {
            return false;
        }
        //删除今天之外的ip记录
        //今日起始时间戳
        $beginToday = mktime(0,0,0,date('m'),date('d'),date('Y'));
        //今天结束时间戳
        $endToday = mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $hits_info_db = M("HitsInfo");
        $hits_iplog_db = M("HitsIplog");

        //年 月 日
        $y = date('Y');
        $m = date('Ym');
        $d = date('Ymd');
        $hits_info = $hits_info_db->where(array('type' => $type,'y'=>$y,'m'=>$m,'d'=>$d, 'lang'=>LANG_ID))->find();

        //pc ip记录和mobile ip记录
        if($type ==1 || $type ==3){
        	$ip = get_client_ip();
        	//记录访问详细
        	$log_type = ($type==1) ? 1 : 2;

        	$hits_iplog_data = $hits_iplog_db->where(array('ip'=>$ip, 'type'=>$log_type, 'lang'=>LANG_ID, 'inputtime'=>array('BETWEEN', array($beginToday, $endToday))))->find();

            if(!$hits_iplog_data){
        		//查询当天点击量log
        		$address = getaddressbyip();
                $hits_iplog_db->add(array('ip'=>$ip,'y'=>$y,'m'=>$m,'d'=>$d,'address'=>$address,'inputtime'=>time(),'type'=>$log_type, 'lang'=>LANG_ID));

        		$hits_iplogcount =  $hits_iplog_db->where(array('lang'=>LANG_ID, 'inputtime'=>array('BETWEEN', array($beginToday, $endToday))))->count();
        		if($hits_info){
        			$hits_info['hits'] = $hits_iplogcount;
        			$status = $hits_info_db->where(array('id'=>$hits_info['id']))->save($hits_info);
        		}else{
        			$status = $hits_info_db->add(array('y'=>$y,'m'=>$m,'d'=>$d,'hits'=>1,'type'=>$type,'inputtime'=>time(),'lang'=>LANG_ID));
        		}
        	}else{
        		return false;
        	}
        }else if($type ==2 || $type ==4){
        	if($hits_info){
        		$hits_info['hits'] = $hits_info['hits']+1;
        		$status = $hits_info_db->where(array('id'=>$hits_info['id']))->save($hits_info);
        	}else{
        		$status = $hits_info_db->add(array('y'=>$y,'m'=>$m,'d'=>$d,'hits'=>1,'type'=>$type,'inputtime'=>time(),'lang'=>LANG_ID));
        	}
        }

        return false !== $status ? true : false;
    }

}
