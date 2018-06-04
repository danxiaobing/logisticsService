<?php
/**
 * Created by PhpStorm.
 * User: Jeff
 * Date: 2016/8/11 0011
 * Time: 18:32
 */
class App_CartypeModel
{
    public $dbh = null;

    /**
     * Constructor
     * @param   object $dbh
     * @return  void
     */
    public function __construct($dbh, $mch = null)
    {
        $this->dbh = $dbh;
    }
    
    /**
     * 根据条件检索人工找货
     * @return 数组
     * @author Tina
     */
    public function getList($params)
    {
        $filter = array();
        $filter[] = " `is_del` = 0";
        $orders = "order by id desc";
        $where = " WHERE `is_del` = 0  ";
        $filter = array();
        if (isset($params['name']) && $params['name'] != '' && $params['name'] != '0') {
            $filter[] = " `name` LIKE '%{$params['name']}%' ";
        }

        if (1 <= count($filter)) {
            $where .= ' AND ' . implode(' AND ', $filter);
        }

        
        $sql = "SELECT COUNT(*) FROM gl_cars_type {$where}";
        //
        $result = $params;
        $result['totalRow'] = $this->dbh->select_one($sql);
        //
        $result['list'] = array();

        if ($result['totalRow']) {
            $result['totalPage'] = ceil($result['totalRow'] / $params['pageSize']);
            $this->dbh->set_page_num($params['pageCurrent']);
            $this->dbh->set_page_rows($params['pageSize']);

            $sql = "SELECT * FROM gl_cars_type {$where} {$orders}";
            //print_r($sql);die; 
            $arr = $this->dbh->select_page($sql);
            
            $result['list'] = $arr;
        }
        return $result;
    }

    /**
     * 获取所有的车辆类型
     * @return data
     * @author Daley
     */
    public function getAll(){

        $sql = "SELECT `id`,`name`,`load` FROM gl_cars_type WHERE `is_del`= 0";
        $result = $this->dbh->select($sql);
        return $result;

    }
    /**
     * 数据添加
     * @return boolean
     * @author Tina
     */
    public function add($data)
    {
        //print_r($data);die;
        return $this->dbh->insert('gl_cars_type', $data);
    }


    /**
     * 根据id获得细节
     * id: 权限id
     * @return 数组
     */
    public function getInfo($id = 0)
    {
        $sql = "SELECT * FROM gl_cars_type WHERE id=".$id;
        return $this->dbh->select_row($sql);
    }

    /**
     * 更新
     */

    public function update($id = 0, $data = array())
    {
        //print_r($data);die;
        $res = $this->dbh->update('gl_cars_type', $data, 'id = ' . intval($id));
        return $res;
    }

    /**
     * 删除
     */
    public function del($id,$data){
        $res = $this->dbh->update('gl_cars_type',$data,'id = ' . intval($id));
        return $res;
    }

}