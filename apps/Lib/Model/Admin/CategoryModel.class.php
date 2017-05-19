<?php

class CategoryModel extends RelationModel
{

	protected $_link = array(
		'Model'=>array(
			'mapping_type'   => BELONGS_TO,
			'class_name'     => 'Model',
			'foreign_key'    => 'modelid',
			'mapping_fields' => 'tablename',
			'as_fields'      => 'tablename:model',
		),

	);

	/*
	 * 表单验证
	 */
	protected  $_validate = array(
		array('catname','require','{%error_catname_is_empty}',0,'regex',3),
	);


	/**
	 * 删除栏目，如果有子栏目，会删除对应的子目录
	 * @param type $catid 可以是数组，可以是栏目id
	 * @return boolean
	 */
	public function deleteCatid($catid) {
		if (!$catid) {
			return false;
		}
		$where = array();
		//取得子栏目
		if (is_array($catid)) {
			$where['catid'] = array("IN", $catid);
			$catList = $this->where($where)->select();
			foreach ($catList as $cat) {
				//是否存在子栏目
				if ($cat['child'] && $cat['type'] == 0) {
					$arrchildid = explode(",", $cat['arrchildid']);
					unset($arrchildid[0]);
					$catid = array_merge($catid, $arrchildid);
				}
			}
			$where['catid'] = array("IN", $catid);
		} else {
			$where['id'] = $catid;
			$catInfo = $this->where($where)->find();
			//是否存在子栏目
			if ($catInfo['child'] && $catInfo['type'] == 0) {
				$arrchildid = explode(",", $catInfo['arrchildid']);
				unset($arrchildid[0]);
				$catid = array_merge($arrchildid, array($catid));
				$where['id'] = array("IN", $catid);
			}
		}
		//检查是否存在数据，存在数据不执行删除
		if (is_array($catid)) {
			$modeid = array();
			foreach ($catid as $cid) {
				$catinfo = getCategory($cid);
				if ($catinfo['modelid'] && $catinfo['type'] == 0) {
					$modeid[$catinfo['modelid']] = $catinfo['modelid'];
				}
			}
			foreach ($modeid as $mid) {
				$tbname = ucwords(getModel($mid, 'tablename'));
				if (!$tbname) {
					return false;
				}
				if ($tbname && M($tbname)->where(array("catid" => array("IN", $catid)))->count()) {
					return false;
				}
			}
		} else {
			$catinfo = getCategory($catid);
			$tbname = ucwords(getModel($catInfo['modelid'], 'tablename'));
			if (!$tbname && $catinfo['type'] == 0) {
				return false;
			}
			if ($tbname && $catinfo['type'] == 0 && M($tbname)->where(array("catid" => $catid))->count()) {
				return false;
			}
		}
		$status = $this->where($where)->delete();
		//更新缓存
		savecache('Category');
		if (false !== $status) {
			$this->extendFieldDel($where);
			if (is_array($catid)) {
				//删除附件
				foreach ($catid as $cid) {
					api_delete('catid-' . $cid);
				}
			} else {
				api_delete('catid-' . $catid);
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 获取扩展字段
	 * @param type $catid 栏目ID
	 * @return boolean
	 */
	public function getExtendField($catid) {
		if (empty($catid)) {
			return false;
		}
		$extendFieldLisr = M('CategoryField')->where(array('catid' => $catid))->select();
		foreach ($extendFieldLisr as $k => $rs) {
			$extendFieldLisr[$k]['setting'] = unserialize($rs['setting']);
		}
		return $extendFieldLisr;
	}

	/**
	 * 删除某栏目下的扩展字段
	 * @param type $where 删除条件
	 * @return boolean
	 */
	protected function extendFieldDel($where) {
		if (empty($where)) {
			return false;
		}
		return M('CategoryField')->where($where)->delete() !== false ? true : false;
	}

	/**
	 * 扩展字段处理
	 * @param type $catid 栏目ID
	 * @param type $post 数据
	 * @return boolean
	 */
	public function extendField($catid, $post) {
		if (empty($catid) || intval($catid) < 1 || empty($post)) {
			return false;
		}
		C('TOKEN_ON', false);
		//时间
		$time = time();
		//栏目信息
		$info = $this->where(array('catid' => $catid))->find();
		if (empty($info)) {
			return false;
		}
		$info['setting'] = unserialize($info['setting']);
		//删除不存在的选项
		if (!empty($post['extenddelete'])) {
			$extenddelete = explode('|', $post['extenddelete']);
			M('CategoryField')->where(array('fid' => array('IN', $extenddelete)))->delete();
		}
		//查询出该栏目扩展字段列表
		$extendFieldLisr = array();
		foreach (M('CategoryField')->where(array('catid' => $catid))->field('fieldname')->select() as $rs) {
			$extendFieldLisr[] = $rs['fieldname'];
		}
		//检查是否有新怎字段
		if (!empty($post['extend_config']) && is_array($post['extend_config'])) {
			$validate = array(
				array('catid', 'require', '栏目ID不能为空！', 1, 'regex', 3),
				array('fieldname', 'require', '键名不能为空！', 1, 'regex', 3),
				array('type', 'require', '类型不能为空！', 1, 'regex', 3),
				array('fieldname', '/^[a-z_0-9]+$/i', '键名只支持英文、数字、下划线！', 0, 'regex', 3),
			);
			foreach ($post['extend_config'] as $field => $rs) {
				//如果已经存在则跳过
				if (in_array($field, $extendFieldLisr)) {
					continue;
				}
				$rs['catid'] = $catid;
				$data = M('CategoryField')->validate($validate)->create($rs);
				if ($data) {
					$data['createtime'] = $time;
					$setting = $data['setting'];
					if ($data['type'] == 'radio' || $data['type'] == 'checkbox') {
						$option = array();
						$optionList = explode("\n", $setting['option']);
						if (is_array($optionList)) {
							foreach ($optionList as $rs) {
								$rs = explode('|', $rs);
								if (!empty($rs)) {
									$option[] = array(
										'title' => $rs[0],
										'value' => $rs[1],
									);
								}
							}
							$setting['option'] = $option;
						}
					}
					$data['setting'] = serialize($setting);
					$fieldId = M('CategoryField')->add($data);
					if ($fieldId) {
						$extendFieldLisr[] = $field;
					}
				} else {
					continue;
				}
			}
		}
		//值更新
		$extend = array();
		if (!empty($post['extend']) || is_array($post['extend'])) {
			foreach ($post['extend'] as $field => $value) {
				if (in_array($field, $extendFieldLisr)) {
					$extend[$field] = $value;
				}
			}
			$info['setting']['extend'] = $extend;
		}
		//更新栏目
		$status = $this->where(array('catid' => $catid))->save(
			array(
				'setting' => serialize($info['setting']),
			)
		);
		//删除缓存
		getCategory($catid, '', true);
		return $status !== false ? true : false;
	}

}