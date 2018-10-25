<?php

namespace Apps\Information\Model;

use Model;

/**
 * 资讯分类模型
 *
 * @package Apps\Information\Model\Cate
 * @author Seven Du <lovevipdsw@vip.qq.com>
 **/
class Cate extends Model
{
    /**
     * 主键
     *
     * @var string
     **/
    protected $pk = 'id';

    /**
     * 表名
     *
     * @var string
     **/
    protected $tableName = 'information_cate';

    /**
     * 数据表字段
     *
     * @var array
     **/
    protected $fields = array('id', 'name', 'isDel', 'rank');

    /**
     * 分类名称
     *
     * @var string
     **/
    protected $_name;

    /**
     * 分类ID
     *
     * @var int
     **/
    protected $_id;

    /**
     * 排序等级
     *
     * @var int
     **/
    protected $_rank;

    /**
     * 储存单例的数据字段
     *
     * @var object
     **/
    private static $_instance;

    /**
     * 获取单例
     *
     * @return object
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public static function getInstance()
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self('Information\CateModel');
        }

        return self::$_instance;
    }

    /**
     * 设置分类名称到类属性
     *
     * @param  string $name 分类名称
     * @return object self
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setName($name)
    {
        $this->_name = t($name);

        return $this;
    }

    /**
     * 获取属性Name的值
     *
     * @return string
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getName()
    {
        return $this->_name;
    }

    /**
     * 设置分类ID到类属性
     *
     * @param  int    $id 分类ID
     * @return object Self
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setId($id)
    {
        $this->_id = intval($id);

        return $this;
    }

    /**
     * 获取类属性ID的值
     *
     * @return int
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getId()
    {
        return $this->_id;
    }

    /**
     * 设置排序等级
     *
     * @param  int    $rank 排序等级
     * @return object self
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setRank($rank)
    {
        $this->_rank = intval($rank);

        return $this;
    }

    /**
     * 获取类字段等级
     *
     * @return int
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getRank()
    {
        return $this->_rank;
    }

    /**
     * 根据分类名称判断是否存在
     *
     * @param  int  $del 是否标记删除
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function hasByName($del = 0)
    {
        $del = intval($del);

        return $this->where(array(
            'name' => array('LIKE', $this->getName()),
            'isDel' => array('eq', $del),
        ))->field('`id`')->count() > 0;
    }

    /**
     * 根据ID判断分类是否存在
     *
     * @param  int  $del 是否标记删除
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function hasById($del = 0)
    {
        $del = intval($del);

        return $this->where(array(
            'id' => array('eq', $this->getId()),
            'isDel' => array('eq', $del),
        ))->field('`id`')->count() > 0;
    }

    /**
     * 设置错误消息
     *
     * @param  string $message 错误消息
     * @return object
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setError($message)
    {
        $this->error = $message;

        return $this;
    }

    /**
     * 恢复已经删除的分类，
     *
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function reByName()
    {
        return $this->where(array(
            'name' => array('LIKE', $this->getName()),
        ))->save(array(
            'isDel' => '0',
        ));
    }

    /**
     * 以ID方式恢复已经删除的分类
     *
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function reById()
    {
        return $this->where(array(
            'id' => array('eq', $this->getId()),
        ))->save(array(
            'isDel' => '0',
        ));
    }

    /**
     * 以name改变分类的排序
     *
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function changeRankByName()
    {
        return $this->where(array(
            'name' => array('LIKE', $this->getName()),
        ))->save(array(
            'rank' => $this->getRank(),
        ));
    }

    /**
     * 以ID方式改变分类等级
     *
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function changeRankById()
    {
        return $this->where(array(
            'id' => array('eq', $this->getId()),
        ))->save(array(
            'rank' => $this->getRank(),
        ));
    }

    /**
     * 添加分类
     *
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function add()
    {
        /* # 判断分类是否存在 */
        if ($this->hasByName()) {
            $this->setError('分类已经存在！');

            return false;

        /* # 判断是否是伪删除并恢复 */
        } elseif ($this->reByName()) {
            $this->changeRankByName();

            return true;
        }
        /* # 实际添加记录 */
        return parent::add(array(
            'name' => $this->getName(),
        ));
    }

    /**
     * 获取分类信息
     *
     * @return array
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getInfoById()
    {
        return $this->where(array(
            'id' => array('eq', $this->getId()),
        ))->field('`id`, `name`, `rank`')->find();
    }

    /**
     * 分类名称
     *
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function changeName()
    {
        /* # 获取详情 */
        $info = $this->getInfoById();

        /* # 判断修改的名称是否是之前的名称，如果是，直接返回真 */
        if ($info['name'] == $this->getName()) {
            return true;

        /* # 判断是否存在其他分类中 */
        } elseif ($this->hasByName()) {
            $this->setError('修改的分类名称已经存在');

            return false;
        }

        return $this->where(array(
            'id' => array('eq', $this->getId()),
        ))->save(array(
            'name' => $this->getName(),
        ));
    }

    /**
     * 按照等级排序获取分类列表
     *
     * @param $asc 是否按照等级正序排序
     * @return array
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function get4Rank($asc = true)
    {
        $asc = (boolean) $asc;
        if ($asc) {
            $asc = 'ASC';
        } else {
            $asc = 'DESC';
        }

        return $this->where('`isDel` != 1')->order('`rank` '.$asc.',`id` ASC')->select();
    }

    /**
     * 后台等级排序分类列表专用
     *
     * @param  int    $id   搜索条件的分类ID
     * @param  string $name 分类名称包含字段
     * @return array
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getAdmin4Rank($id = 0, $name = null)
    {
        $id = intval($id);
        $sql = '`isDel` != 1';
        $SQL = '';
        if ($id || $name) {
            $sql .= ' AND (';
            $id && $sql .= '`id` = '.$id;
            $id && $name && $sql .= ' OR ';
            $name && $sql .= '`name` LIKE %'.$name.'%';
            $sql .= ')';
        }

        return $this->where($sql)->order('`rank` ASC, `id` ASC')->findPage(999);
    }

    /**
     * 以ID方式删除分类
     *
     * @param  array $ids 批量删除的ID
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function delete(array $ids)
    {
        $ids = array_map(function ($int) {
            return intval($int);
        }, $ids);

        return $this->where(array('id' => array('IN', $ids)))->save(array('isDel' => 1));
    }
} // END class Cate extends Model
