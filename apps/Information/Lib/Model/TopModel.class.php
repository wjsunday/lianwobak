<?php

namespace Apps\Information\Model;

use Apps\Information\Common;
use Model;
use Apps\Information\Model\Subject as SubjectModel;

/**
 * 资讯主题推荐列表
 *
 * @package Apps\Information\Model\Top
 * @author
 **/
class Top extends Model
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
    protected $tableName = 'information_top';

    /**
     * 表字段
     *
     * @var array
     **/
    protected $field = array('id', 'title', 'image', 'sid');

    /**
     * 数据字段
     *
     * @var array
     **/
    protected $_data = array();

    /**
     * 单例对象
     *
     * @var obejct
     **/
    protected static $_instance;

    /**
     * 获取单例对象
     *
     * @return object
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public static function getInstance()
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self('Information\TopModel');
        }

        return self::$_instance;
    }

    /**
     * 获取临时数据
     *
     * @param  string $name 字段名称
     * @return unknow
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getFieldValue($name)
    {
        if (isset($this->_data[$name])) {
            return $this->_data[$name];
        }

        return null;
    }

    /**
     * 设置记录D
     *
     * @param  int    $id 记录ID
     * @return object self
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setId($id)
    {
        $this->_data['id'] = intval($id);

        return $this;
    }

    /**
     * 设置推荐标题
     *
     * @param  string $title 标题
     * @return object self
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setTitle($title)
    {
        $this->_data['title'] = t($title);

        return $this;
    }

    /**
     * 设置图片附件ID
     *
     * @param  int    $aid 图片附件ID
     * @return object self
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setImage($aid)
    {
        $this->_data['image'] = intval($aid);

        return $this;
    }

    /**
     * 设置主题ID
     *
     * @param  int    $sid 主题ID
     * @return object self
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setSid($sid)
    {
        $this->_data['sid'] = intval($sid);

        return $this;
    }

    /**
     * 设置错误消息
     *
     * @param  string $message 错误消息
     * @return false
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setError($message)
    {
        $this->error = $message;

        return false;
    }

    /**
     * 清理临时数据
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function cleanData()
    {
        $this->_data = array();
    }

    /**
     * 获取临时数据
     *
     * @return array
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getData()
    {
        return $this->_data;
    }

    /**
     * 清理推荐表中不存在的主题或者数据
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function cleanTable()
    {
        $sql = 'DELETE `ts_information_top` FROM `ts_information_top` INNER JOIN `ts_information_list` ON `ts_information_top`.`sid` = `ts_information_list`.`id` WHERE `ts_information_list`.`isTop` != 1';
        $sql = str_replace('ts_information_list', SubjectModel::getInstance()->getTableName(), $sql);
        $sql = str_replace('ts_information_top', $this->getTableName(), $sql);
        $this->query($sql);
    }

    /**
     * 添加推荐数据
     *
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function add()
    {
        $this->setId(null);
        /* # 判断是否么有设置主题ID或者主题不存在 */
        if (!SubjectModel::getInstance()->setId($this->getFieldValue('sid'))->has()) {
            return $this->setError('主题不存在，请重新选择');

        /* # 判断是否是预发布 */
        } elseif (SubjectModel::getInstance()->hasIsPre()) {
            return $this->serError('当前主题资讯是投稿预发布状态，无法推荐，请先正是发布后推荐！');

        /* # 判断是否填写了标题 */
        } elseif (($tleng = Common::strlen($this->getFieldValue('title'))) < 1) {
            return $this->setError('推荐标题不能为空');

        /* # 判断推荐标题是否超出限制 */
        } elseif (200 < $tleng) {
            return $this->setError('推荐标题不得超出200字符');

        /* # 判断是否没有上传附件图片 */
        } elseif (!$this->getFieldValue('image')) {
            return $this->setError('请上传推荐图片');
        }

        if (($id = parent::add($this->getData()))) {
            return SubjectModel::getInstance()->setIsTop(true)->update();
        } elseif ($id) {
            $this->setId($id)->delete();
        }

        return false;
    }

    /**
     * 删除推荐
     *
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function delete()
    {
        $sid = $this->where('`id` = '.$this->getFieldValue('id'))->field('`sid`')->getField('sid');
        if (SubjectModel::getInstance()->setId($sid)->setIsTop(false)->update()) {
            $this->where('`id` = '.$this->getFieldValue('id'));

            return parent::delete();
        }

        return false;
    }

    /**
     * 根据SID删除推荐
     *
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function delBySid()
    {
        if (SubjectModel::getInstance()->setId($this->getFieldValue('sid'))->setIsTop(false)->update()) {
            $this->where('`sid` = '.$this->getFieldValue('sid'));

            return parent::delete();
        }

        return false;
    }

    /**
     * 获取首页幻灯片列表
     *
     * @param  int   $num 获取的数量，默认五条，不足则返回全部
     * @return array
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getIndexList($num = 5)
    {
        return $this->limit($num)->order('`id` DESC')->select();
    }
} // END class Top extends Model
