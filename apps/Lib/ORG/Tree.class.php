<?php
class Tree extends Think {
    /**
    * 生成树型结构所需要的2维数组
    * @var array
    */
    public $arr = array();

    /**
    * 生成树型结构所需修饰符号，可以换成图片
    * @var array
    */
    public $icon = array('│','├','└');

    /**
    * @access private
    */
    public $ret = '';

    public $nbsp = "&nbsp;";
    public $level = 0;

    /**
     * 构造函数，初始化类
     * @param array 2维数组，例如：
     * array(
     *      1 => array('id'=>'1','parentid'=>0,'name'=>'一级栏目一'),
     *      2 => array('id'=>'2','parentid'=>0,'name'=>'一级栏目二'),
     *      3 => array('id'=>'3','parentid'=>1,'name'=>'二级栏目一'),
     *      4 => array('id'=>'4','parentid'=>1,'name'=>'二级栏目二'),
     *      5 => array('id'=>'5','parentid'=>2,'name'=>'二级栏目三'),
     *      6 => array('id'=>'6','parentid'=>3,'name'=>'三级栏目一'),
     *      7 => array('id'=>'7','parentid'=>3,'name'=>'三级栏目二')
     *      )
     */
    public function __construct($arr=array()) {
        $this->arr = $arr;
        $this->ret = '';
    }



    public function get_child($bid){
        $newarr = array();
        if(is_array($this->arr)){
            foreach($this->arr as $key => $val){
                if($val['parentid'] == $bid)
                $newarr[$key] = $val;
            }
        }
        return $newarr ? $newarr : false;
    }

    /**
     * -------------------------------------
     *  得到树型结构
     * -------------------------------------
     * @param $myid 表示获得这个ID下的所有子级
     * @param $str 生成树形结构基本代码, 例如: "<option value=\$id \$select>\$spacer\$name</option>"
     * @param $sid 被选中的ID, 比如在做树形下拉框的时候需要用到
     * @param $adds
     * @param $str_group
     * @return unknown_type
     */
    function get_tree($myid, $str, $sid = 0, $adds = '', $str_group = '')
    {
        $number = 1;
        $child = $this->get_child($myid);
        if (is_array($child)) {
            $total = count($child);
            foreach ($child as $id=>$a) {
                $j = $k = '';
                if ($number == $total) {
                  $j .= $this->icon[2];
                } else {
                  $j .= $this->icon[1];
                  $k = $adds ? $this->icon[0] : '';
                }
                $class_css = "distinguish_".$a['parentid'];
                $spacer = $adds ? $adds.$j : '';
                @extract($a);
                if(empty($a['selected'])){
                  $selected = $id == $sid ? 'selected' : '';
                }
                $nstr = '';
                $a['parentid'] == 0 && $str_group ? eval("\$nstr = \"$str_group\";") : eval("\$nstr = \"$str\";");
                $this->ret .= $nstr;
                $this->get_tree($id, $str, $sid, $adds.$k.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',$str_group);
                $number++;
            }
        }
        return $this->ret;
    }

    /**
     * 将格式数组转换为基于标题的树（实际还是列表，只是通过在相应字段加前缀实现类似树状结构）
     * @param array $list
     * @param integer $level 进行递归时传递用的参数
     */
    private function _toFormatTree($list, $level = 0, $title = 'title'){
        foreach($list as $key=>$val){
            $title_prefix = str_repeat('<span class="indent"></span>', $level);
            $title_prefix .= "┝ ";
            $val['level'] = $level;
            $val['title_prefix'] = $level == 0 ? '' : $title_prefix;
            $val['title_show'] = $level == 0 ? $val[$title] : $title_prefix.$val[$title];
            if(!array_key_exists('_child', $val)){
                array_push($this->formatTree, $val);
            }else{
                $child = $val['_child'];
                unset($val['_child']);
                array_push($this->formatTree, $val);
                $this->_toFormatTree($child, $level+1, $title); //进行下一层递归
            }
        }
        return;
    }

  /**
   * 将格式数组转换为树
   * @param array $list
   * @param integer $level 进行递归时传递用的参数
   */
    public function toFormatTree($list, $title = 'title', $pk='id', $pid = 'pid', $root = 0, $strict = true){
        $list = $this->list_to_tree($list, $pk, $pid, '_child', $root, $strict);
        $this->formatTree = array();
        $this->_toFormatTree($list, 0, $title);
        return $this->formatTree;
    }

    /**
     * 将数据集转换成Tree（真正的Tree结构）
     * @param array $list 要转换的数据集
     * @param string $pk ID标记字段
     * @param string $pid parent标记字段
     * @param string $child 子代key名称
     * @param string $root 返回的根节点ID
     * @param string $strict 默认严格模式
     * @return array
     */
    public function list_to_tree($list, $pk='id', $pid = 'pid', $child = '_child', $root = 0, $strict = true){
        // 创建Tree
        $tree = array();
        if(is_array($list)){
            // 创建基于主键的数组引用
            $refer = array();
                foreach($list as $key => $data){
                    $refer[$data[$pk]] =& $list[$key];
                }
                foreach($list as $key => $data){
                    // 判断是否存在parent
                    $parent_id = $data[$pid];
                    if($parent_id === null || (String)$root === $parent_id){
                      $tree[] =& $list[$key];
                    }else{
                    if(isset($refer[$parent_id])){
                        $parent =& $refer[$parent_id];
                        $parent[$child][] =& $list[$key];
                    }else{
                        if($strict === false){
                          $tree[] =& $list[$key];
                        }
                    }
                }
            }
        }
        return $tree;
    }
}