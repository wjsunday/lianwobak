<?php

namespace Apps\Event\Model;

use Model;

/**
 * 活动分类模型
 *
 * @package Apps\Event\Model\Cate
 * @author Seven Du <lovevipdsw@vip.qq.com>
 **/
class Cate extends Model
{
    /**
     * 表主键名称
     *
     * @var string
     **/
    protected $pk = 'cid';

    /**
     * 表名称
     *
     * @var string
     **/
    protected $tableName = 'event_cate';

    /**
     * 模型保护字段成员
     *
     * @var array
     **/
    protected $fields = array('cid', 'name', 'leval', 'del');

    /**
     * 添加分类
     *
     * @param  string $name  分类名称
     * @param  int    $leval 默认值 100 分类排序等级
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function add($name, $leval = 100)
    {
        $name = t($name);
        /* 判断是否为空 */
        if (!$name) {
            $this->error = '添加的分类名称不能为空';

            return false;

        /* 判断是否存在 */
        } elseif ($this->hasByName($name)) {
            $this->error = '该分类已经存在！';

            return false;
        } elseif (!parent::add(array('name' => $name, 'leval' => intval($leval)))) {
            $this->error = '添加分类失败';

            return false;
        }
        $this->clear();

        return true;
    }

    /**
     * 改变分类信息
     *
     * @param  int    $cid   分类id
     * @param  string $name  分类名称
     * @param  int    $leval 等级
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function update($cid, $name, $leval = null)
    {
        $cid = intval($cid);
        $name2 = $this->getById($cid);
        $name2 = $name2['name'];
        $data = array();
        if (!$cid) {
            $this->error = 'Id不能为空';

            return false;
        } elseif (!$name) {
            $this->error = '分类名称不能为空';

            return false;
        } elseif (!$this->hasById($cid)) {
            $this->error = '该分类不存在';

            return false;
        } elseif ($this->hasByName($name) and $name2 != $name) {
            $this->error = '该分类已经存在！';

            return false;
        } elseif (!is_null($leval)) {
            $data['leval'] = intval($leval);
        }
        $data['name'] = $name;
        $this->where(array('cid' => array('eq', $cid)))->save($data) and
        $this->clear($cid);

        return true;
    }

    /**
     * 用id删除分类
     *
     * @param int $cid 分类id
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function delCate($cid)
    {
        if (is_numeric($cid)) {
            $cid = intval($cid);
        } elseif (is_array($cid)) {
            $cid = array_map('intval', $cid);
        }
        if ($cid) {
            $this->clear();

            return $this->where(array('cid' => array('in', $cid)))->delete();
        }
        $this->error = '删除的ID不存在';

        return false;
    }

    /**
     * 根据ID获取
     *
     * @param  int   $cid 分类ID
     * @return array
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getById($cid)
    {
        $cid = intval($cid);
        $data = S('Apps_Event_Cate_'.$cid);
        if ($data) {
            return $data;
        }
        $data = $this->where(array(
            'cid' => array('eq', $cid),
            'del' => array('neq', '1'),
        ))->find();
        S('Apps_Event_Cate_'.$cid, $data);

        return $data;
    }

    /**
     * 根据Name判断是否存在分类
     *
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function hasByName($name)
    {
        $name = t($name);

        return $this->where(array(
            'name' => array('like', $name),
            'del' => array('neq', '1'),
        ))->field('cid')->count() > 0;
    }

    /**
     * 更具cid判断是否存在分类
     *
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function hasById($cid)
    {
        $cid = intval($cid);

        return $this->where(array(
            'del' => array('neq', '1'),
            'cid' => array('eq', $cid),
        ))->field('id')->count() > 0;
    }

    /**
     * 获取所有分类
     *
     * @return Array
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getAll()
    {
        $data = S('Apps_Event_Cate_All');
        if (is_array($data) and $data) {
            return $data;
        }
        $data = $this->where(array('del' => array('neq', '1')))->order('`leval` ASC, `cid` ASC')->select();
        S('Apps_Event_Cate_All', $data);

        return $data;
    }

    /**
     * 后台专用
     *
     * @return array
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getAdminList()
    {
        $list = $this->where(array('del' => array('neq', '1')))->order('`leval` ASC')->findPage(20);
        foreach ($list['data'] as $key => $value) {
            $value['action'] = '<a href="'.U('Event/Admin/AddCate', array('tabHash' => 'index', 'id' => $value['cid'])).'">[编辑]</a>';
            $value['action'] .= '&nbsp;-&nbsp;';
            $value['action'] .= '<a href="'.U('Event/Admin/delCate', array('ids' => $value['cid'])).'">[删除]</a>';
            $list['data'][$key] = $value;
        }

        return $list;
    }

    /**
     * 清理缓存
     *
     * @param  int      $cid 要清理的id
     * @return booleana
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function clear($cid = null)
    {
        $cid and S('Apps_Event_Cate_'.$cid, null);

        return S('Apps_Event_Cate_All', null);
    }

    /**
     * 储存单例对象
     *
     * @var object self
     **/
    private static $_instance;

    /**
     * 获取单例
     *
     * @return object self
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public static function getInstance()
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }
} // END class Cate extends Model
