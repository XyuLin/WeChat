<?php
/**
 * Created by PhpStorm.
 * User: L丶lin
 * Date: 2018/9/12
 * Time: 11:23
 */

namespace app\common\model;


use think\Model;

class BlockCategory extends Model
{
    protected $name = 'block_category';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    /**
     * @param array $ids
     * @param string $default
     * @param string $method 1=社区热门结果变化，2=通用数组结构
     *
     * @return array|false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getBlockCategoryList($ids = [],$default = '',$method = '1')
    {
        if(empty($ids)) {
            return [];
        } else {
            $data = $this->where('id','in',$ids)->select();
            $data = collection($data)->toArray();
            if(!empty($data)) {
                if($method == '1') {
                    if($default != '') {
                        $data = $this->default_in_array($data,$default);
                    }
                    $array['default'] = $data[0];
                    unset($data[0]);
                    $array['list'] = array_merge([],$data);
                    $data = $array;
                } else {
                    $data = $data;
                }
            } else {
                $data = [];
            }
            return $data;
        }
    }

    public function default_in_array($array,$default)
    {
        $key = '';
        $val = [];
        foreach($array as $key => $value) {
            if($value['id'] == $default) {
                $val = $value;
                $key = $key;
                break;
            }
        }

        $array[$key] = $array[0];
        $array[0] = $val;

        return $array;
    }

}