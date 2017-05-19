<?php

class MenuModel extends Model
{
	/*
	 * 表单验证
	 */
	protected  $_validate = array(
		array('name','require','{%menu_user_is_empty}',1,'regex',3),
	);

	/*
     * 自动完成
     */
	protected $_auto = array(
		array('model','ucfirst',3,'function'),
	);


	/**
	 * 根据id获取当前菜单的顶级菜单
	 * @author Mr.Weng
	 */
	public function getRootMenuById($id){
		if(empty($id)){
			return false;
		}

		$map['id'] = array('eq', $id);
		$map['status'] = array('eq', 1);

		$main_menu = array();
		do{
			$main_menu = $this->where($map)->find();
			$map['id'] = array('eq', $main_menu['parentid']);
		}while($main_menu['parentid'] > 0);

		return $main_menu;
	}

	/**
	 * 获取当前菜单
	 * @author Mr.Weng
	 */
	public function getCurrentMenu(){

		//$map['id'] = array('eq', $menuId);
		//$result = $this->where($map)->order('parentid desc')->find();

		$map['status'] = array('eq', 1);
		$map['group'] = GROUP_NAME;
		$map['model'] = MODULE_NAME;
		$map['action'] = ACTION_NAME;

		$result = $this->where($map)->order('parentid desc')->find();
		return $result;
	}

	/**
	 * 根据菜单ID的获取其所有父级菜单
	 * @param array $current 当前菜单信息
	 * @return array 父级菜单集合
	 * @author Mr.Weng
	 */
	public function getParentMenu($current){
		if(empty($current)){
			return false;
		}

		$map['status'] = array('eq', 1);
		$menus = $this->where($map)->select();
		$parentid   = $current['parentid'];
		$res[] = $current;
		while(true){
			foreach ($menus as $key => $val){
				if($val['id'] == $parentid){
					$parentid = $val['parentid'];
					array_unshift($res, $val); //将父菜单插入到数组第一个元素前
				}
			}
			if($parentid == 0){
				break;
			}
		}

		return $res;
	}

}