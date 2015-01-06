<?php 
class Area{

    protected $result = null;
    
    public function __construct($db)
    {
        $sql = 'SELECT s.area_id as area_id, s.title as title, s.pid as pid, p.title as p_title FROM ts_area as s, ts_area as p WHERE s.pid = p.area_id';
        $this->result = $db->query($sql);
    }
    
    /**
     * 根据id查询地区对象
     * @param unknown $id
     * @return unknown
     */
    public function getAreaById($id)
    {
        foreach ($this->result as $area){
            if($id == $area['area_id']){
                return $area;
            }
        }
    }
    
    /**
     * 根据地区名称及父级地区名称得到地区id
     * @param unknown $title
     * @param unknown $p_title
     * @return unknown|number
     */
    public function getAreaId($title, $p_title)
    {
        foreach ($this->result as $area){
            if($title == $area['title'] && $p_title == $area['p_title']){
                return $area['area_id'];
            }
        }
        return 0;
    }
    
    /**
     * 根据省份名称得到id
     * @param unknown $title
     * @return unknown|number
     */
    public function getProvinceId($title)
    {
        foreach ($this->result as $area){
            if($title == $area['p_title']){
                return $area['pid'];
            }
        } 
        return 0; 
    }
}
?>

