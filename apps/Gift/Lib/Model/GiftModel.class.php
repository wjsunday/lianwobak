<?php

namespace Apps\Gift\Model;

use Model;

/**
 * 礼品记录模型
 *
 * @package Apps\Gift\Model\Gift
 * @author Seven Du <lovevipdsw@vip.qq.com>
 **/
class Gift extends Model
{
    /**
     * 主键名称
     *
     * @var string
     **/
    protected $pk = 'id';

    /**
     * 数据表名称
     *
     * @var string
     **/
    protected $tableName = 'gift';

    /**
     * 字段
     *
     * @var array
     **/
    protected $fields = array('id', 'name', 'brief', 'info', 'image', 'score', 'stock', 'max', 'time', 'cate', 'isDel');

    /**
     * 根据礼物ID获取礼物信息
     *
     * @param  int   $id 礼物ID
     * @return array 礼物信息
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getById($id)
    {
        $id = intval($id);

        return $this->where('`id` = '.$id)->find();
    }

    /**
     * 以分页形式获取数据
     *
     * @param  int    $limie 每页显示的数量
     * @param  int    $id    搜索的ID
     * @param  string $name  礼物名称
     * @param  bool   $asc   是否按照正序排序
     * @return array
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function findByPage($limit = 10, $id = null, $name = '', $asc = false)
    {
        $where = '`isDel` != 1';

        if ($id && intval($id)) {
            $where .= ' AND (`id` = '.intval($id);
        }

        if (intval($id) && $name) {
            $where .= ' OR `name` LIKE "%'.$name.'%")';
        } elseif ($name) {
            $where .= ' AND `name` LIKE "%'.$name.'%"';
        }

        if ($where) {
            $this->where($where);
        }

        $order = 'DESC';
        if ($asc) {
            $order = 'ASC';
        }

        return $this->order('`id` '.$order)->findPage(intval($limit));
    }

    /**
     * 验证是否存在礼物数据
     *
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function hasById($id)
    {
        $id = intval($id);

        return (boolean) $this->where('`id` = '.$id.' AND `isDel` != 1')->field('id')->count();
    }

    /**
     * 添加礼品
     *
     * @param  string $name  礼品名称
     * @param  string $brief 简介
     * @param  string $info  详细
     * @param  int    $image 图片附件ID
     * @param  int    $score 积分数量
     * @param  int    $stock 库存量
     * @param  int    $cate  分类
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function add($name, $brief, $info, $image, $score, $stock, $max, $cate)
    {
        return parent::add(array(
            'time' => time(),
            'name' => $name,
            'brief' => $brief,
            'info' => $info,
            'image' => intval($image),
            'score' => intval($score),
            'stock' => intval($stock),
            'max' => intval($max),
            'cate' => intval($cate),
        ));
    }

    /**
     * 更新礼品数据
     *
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function update($id, $name = '', $brief = '', $info = '', $image = '', $score = '', $stock = '', $max = 0, $cate = '')
    {
        $where = '`id` = '.intval($id);
        $data = array();

        $name && $data['name'] = $name;
        $brief && $data['brief'] = $brief;
        $info && $data['info'] = $info;
        $image && $data['image'] = intval($image);
        $score && $data['score'] = intval($score);
        $stock && $data['stock'] = intval($stock);
        $max && $data['max'] = intval($max);
        $cate && $data['cate'] = intval($cate);

        return $this->where($where)->save($data);
    }

    /**
     * 礼物库存改变
     *
     * @param  int  $gid 礼物ID
     * @param  int  $num 减少的数量
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function changeStock($gid, $num)
    {
        return $this->setDec('stock', '`id` = '.intval($gid), $num);
    }

    /**
     * 删除礼物（覆盖父类数据删除，数据做假删除处理）
     *
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function delete()
    {
        $this->save(array(
            'isDel' => 1,
        ));
    }

    /**
     * 储存单例数据的变量
     *
     * @var object
     **/
    private static $_instance;

    /**
     * 获取单例对象
     *
     * @return object self
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }
} // END class Gift extends Model
class_alias('Apps\Gift\Model\Gift', 'GiftModel');
