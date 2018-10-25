<?php

namespace Apps\Bonusevent\Model;

use Model;
use Apps\Bonusevent\Common;
use Apps\Bonusevent\Model\Cate as CateModel;

/**
 * 活动模型
 *
 * @package Apps\Bonusevent\Model\Bonusevent
 * @author Seven Du <lovevipdsw@vip.qq.com>
 **/
class Bonusevent extends Model
{
    /**
     * 储存单例
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
    public function getInstance()
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    /* =========================================== */
    /* 静态 End */

    /**
     * 主键名称
     *
     * @var string
     **/
    protected $pk = 'eid';

    /**
     * 活动列表表名
     *
     * @var string
     **/
    protected $tableName = 'bonusevent_list';

    /**
     * 模型限制字段
     *
     * @var array
     **/
    protected $fields = array('eid', 'name', 'stime', 'etime', 'area', 'city', 'location', 'longitude', 'latitude', 'place', 'image', 'manNumber', 'remainder', 'price', 'cid', 'audit', 'content', 'uid', 'del', 'tips');

    /**
     * 保存临时数据的字段
     *
     * @var array
     **/
    protected $data = array();

    /**
     * 获取临时储存字段的单个字段
     *
     * @param  string $name 临时数据名称
     * @return unknow
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getDataField($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }

        return null;
    }

    /**
     * 获取数据
     *
     * @return array
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getData()
    {
        return $this->data;
    }

    /**
     * 设置主键ID
     *
     * @return object self
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setId($eid)
    {
        $this->data['eid'] = intval($eid);

        return $this;
    }

    /**
     * 设置活动名称
     *
     * @param  string $name 活动名称
     * @return object self
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setName($name)
    {
        $this->data['name'] = t($name);

        return $this;
    }

    /**
     * 设置活动开始时间
     *
     * @param  int|string $stime 活动开始时间
     * @return object     self
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setStime($stime)
    {
        $stime = $stime ? is_numeric($stime) ? $stime : strtotime($stime) : time();
        $this->data['stime'] = $stime;

        return $this;
    }

    /**
     * 设置活动结束时间
     *
     * @param  int|string $etime 活动结束时间
     * @return object     self
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setEtime($etime)
    {
        $etime = $etime ? is_numeric($etime) ? $etime : strtotime($etime) : time() + 2592000;
        $this->data['etime'] = $etime;

        return $this;
    }

    /**
     * 设置地区
     *
     * @param  int    $area 地区ID （省）
     * @return object
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setArea($area)
    {
        $this->data['area'] = intval($area);

        return $this;
    }

    /**
     * 设置地区
     *
     * @param  int    $city 地区ID (市)
     * @return object
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setCity($city)
    {
        $this->data['city'] = intval($city);

        return $this;
    }

    /**
     * 设置详细地址
     *
     * @param  string $location 详细地址
     * @return object
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setLocation($location)
    {
        $this->data['location'] = t($location);

        return $this;
    }

    /**
     * 设置经度
     *
     * @param  float  $longitude 经度
     * @return object
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setLongitude($longitude)
    {
        $this->data['longitude'] = floatval($longitude);

        return $this;
    }

    /**
     * 设置纬度
     *
     * @param  float  $latitude 纬度
     * @return object
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setLatitude($latitude)
    {
        $this->data['latitude'] = floatval($latitude);

        return $this;
    }

    /**
     * 设置场所名称
     *
     * @param  string $place 场所名称
     * @return objetc
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setPlace($place)
    {
        $this->data['place'] = t($place);

        return $this;
    }

    /**
     * 设置活动图片
     *
     * @param  int    $image 活动图片附件ID
     * @return object
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setImage($image)
    {
        $this->data['image'] = intval($image);

        return $this;
    }

    /**
     * 设置活动需要人数
     *
     * @param  int    $manNumber 活动人数
     * @return object
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setManNumber($manNumber)
    {
        $manNumber = max(intval($manNumber), 0); // 最小0;
        $this->data['manNumber'] = $manNumber;

        return $this;
    }

    /**
     * 活动剩余人数
     *
     * @param  int    $remainder 活动剩余可参加的人数
     * @return object
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setRemainder($remainder)
    {
        $this->data['remainder'] = intval($remainder);

        return $this;
    }

    /**
     * 设置活动参加价格
     *
     * @param  float  $price 价格
     * @return object
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setPrice($price)
    {
        $this->data['price'] = floatval($price);

        return $this;
    }

    /**
     * 设置活动分类ID
     *
     * @param  int    $cid 分类ID
     * @return object
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setCid($cid)
    {
        $this->data['cid'] = intval($cid);

        return $this;
    }

    /**
     * 设置是否需要参加审核
     *
     * @param  bool   $audit 是否需要参与审核
     * @return object
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setAudit($audit = false)
    {
        $audit = $audit ? '1' : '0';
        $this->data['audit'] = $audit;

        return $this;
    }

    /**
     * 设置活动内容
     *
     * @param  string $content 活动内容
     * @return object
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setContent($content)
    {
        $this->data['content'] = h($content);

        return $this;
    }

    /**
     * 设置发布活动的用户ID
     *
     * @return object
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setUid($uid)
    {
        $this->data['uid'] = intval($uid);

        return $this;
    }

    /**
     * 设置是否标记删除
     *
     * @param  bool   $del 是否删除
     * @return object
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setDel($del = false)
    {
        $del = $del ? '1' : '0';
        $this->data['del'] = $del;

        return $this;
    }

    /**
     * 费用说明
     *
     * @param  string $tips 费用说明
     * @return object
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setTips($tips)
    {
        $this->data['tips'] = t($tips);

        return $this;
    }

    /**
     * 添加活动
     *
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function add($addMiniType=true)
    {
        /* # 清理ID，避免指定主键插入数据 */
        if ($this->getDataField('eid')) {
            unset($this->data['eid']);
        }
        /* 判断活动名称 */
        if (Common::strlen($this->getDataField('name')) <= 0) {
            $this->error = '活动名称不能为空';

            return false;

        /* 判断活动是否超出了长度限制 */
        } elseif (Common::strlen($this->getDataField('name')) > 150) {
            $this->error = '活动名称不能超过150字';

            return false;

        /* # 判断内容是否为空 */
        } 
        // elseif (Common::strlen($this->getDataField('content')) <= 0) {
        //     $this->error = '活动内容不能为空';

        //     return false;

        // /* 判断内容是否超出最大限制 */
        // } elseif (Common::strlen($this->getDataField('content')) > 5000) {
        //     $this->error = '活动内容不能超过5000字';

        //     return false;

        // /* 判断开始时间是否不存在 */
        // } elseif (!$this->getDataField('stime') && $addMiniType == false) {
        //     $this->error = '开始时间不能为空';

        //     return false;

        // /* # 判断结束时间是否小于当前时间 */
        // } elseif ($this->getDataField('etime') <= time() && $addMiniType == false) {
        //     $this->error = '结束时间不能小于当前时间';

        //     return false;

        // /* 判断结束时间是否小于开始时间 */
        // } elseif ($this->getDataField('etime') <= $this->getDataField('stime') && $addMiniType == false) {
        //     $this->error = '结束时间不能小于开始时间';

        //     return false;

        // /* 判断是否存在省 */
        // } elseif (!$this->getDataField('area')) {
        //     $this->error = '请正确选择地区';

        // /* 判断市是否存在 */
        // } elseif (!$this->getDataField('city')) {
        //     $this->error = '请正确选择地区';

        //     return false;

        // /* 判断是否没有输入详细地址 */
        // } elseif (!$this->getDataField('location')) {
        //     $this->error = '请正确的输入详细地址';

        //     return false;

        // /* 判断是否正确的选择了经纬度 */
        // // } elseif (!$this->getDataField('longitude') or !$this->getDataField('latitude')) {
        //     // $this->error = '请正确的标记处地图位置';
        //     // return false;

        // /* 活动场所 */
        // } elseif (Common::strlen($this->getDataField('place')) <= 0) {
        //     $this->error = '请填写活动场所';

        //     return false;

        // /* 分类 */
        // } 
        elseif (!CateModel::getInstance()->hasById($this->getDataField('cid'))) {
            $this->error = '请正确的性选择分类';

            return false;

        /* 检查用户是否为空 */
        }
        // elseif (!$this->getDataField('cid')) {
        //     $this->error = '请正确的性选择分类';

        //     return false;

        // /* 检查用户是否为空 */
        // } 
        elseif (!$this->getDataField('uid')) {
            $this->error = '没有正确的用户信息';

            return false;

        /* 同步名额 */
        } elseif ($this->getDataField('manNumber') >= 1) {
            $this->setRemainder($this->getDataField('manNumber'));
        }

        return parent::add($this->getData());
    }

    /**
     * 根据ID获取活动资料
     *
     * @param  int   $id 活动ID
     * @return array
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function get($id)
    {
        $id = intval($id);
        $data = S('Apps_Bonusevent_List_'.$id);
        if (!$data) {
            $data = $this->where(array(
                'eid' => array('eq', $id),
                // 'del' => array('neq', '1')
            ))->find();
            S('Apps_Bonusevent_List_'.$id, $data);
        }

        return $data;
    }

    /**
     * 获取数据表中不重复的地区
     *
     * @return array
     * @author Seven Du <lovevipdsw2vip.qq.com>
     **/
    public function getArea()
    {
        $citys = $this->where(array(
            'city' => array('neq', 0),
            'del' => array('neq', '1'),
        ))->field('city')->group('city')->select();
        foreach ($citys as $key => $value) {
            $citys[$key] = model('Area')->getAreaById($value['city']);
        }

        return $citys;
    }

    /**
     * 更具条件，按照最新返回活动列表数据
     *
     * @param int $cid  分类id,
     * @param int $area 地区ID，
     * @param string 搜索的关键词
     * @param  int   $time 时间
     * @return array
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getList($cid = null, $area = null, $wd = null, $time = null)
    {
        $whereSql = array(sprintf('%s != \'1\'', 'del'));

        /* 基本条件 */
        // $where = array(
        // 	'del' => array('neq', '1')
        // );

        /* 分类 */
        $cid = intval($cid);
        $cid and
        array_push($whereSql, sprintf('%s = %d', 'cid', $cid));
        // $where['cid'] = array('eq', $cid);

        /* 地区 */
        $area = intval($area);
        $area and
        array_push($whereSql, sprintf('%s = %d', 'city', $area));
        // $where['city'] = array('eq', $area);

        /* 关键词 */
        $wd = t($wd);
        $wd and
        array_push($whereSql, sprintf('%s LIKE \'%s%s%s\'', 'name', '%', $wd, '%'));
        // $where['name'] = array('LIKE', '%' . $wd . '%');

        /* 时间 */
        $time = $time ? is_numeric($time) ? $time : strtotime($time) : null;
        if ($time) {
            $stime = date('Y-m-d 00:00:00', $time);
            $stime = strtotime($stime);
            $etime = date('Y-m-d 23:59:59', $time);
            $etime = strtotime($etime);
            array_push($whereSql, sprintf('%s >= %d', 'stime', $stime));
            array_push($whereSql, sprintf('%s <= %d', 'stime', $etime));
        }

        /* 组装 */
        $whereSql = implode(' AND ', $whereSql);

        return $this->where($whereSql)->order('`eid` DESC')->findPage(20); // 每页20条
    }

    /**
     * 更具时间，获取这个月内的有活动的日期
     *
     * @param  string $time 时间
     * @return array
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getMonthBonuseventDay($cid = null, $area = null, $wd = null, $time = null)
    {
        $whereSql = array(sprintf('%s != \'1\'', 'del'));

        /* 分类 */
        $cid = intval($cid);
        $cid and
        array_push($whereSql, sprintf('%s = %d', 'cid', $cid));
        // $where['cid'] = array('eq', $cid);

        /* 地区 */
        $area = intval($area);
        $area and
        array_push($whereSql, sprintf('%s = %d', 'city', $area));
        // $where['city'] = array('eq', $area);

        /* 关键词 */
        $wd = t($wd);
        $wd and
        array_push($whereSql, sprintf('%s LIKE \'%s%s%s\'', 'name', '%', $wd, '%'));
        // $where['name'] = array('LIKE', '%' . $wd . '%');

        $time = $time ? is_numeric($time) ? $time : strtotime($time) : time();
        /* 开始时间 */
        $stime = date('Y-m-01 00:00:00', $time);
        $stime = strtotime($stime);
        /* 结束时间 */
        $etime = strtotime(date('Y-m-01 00:00:00', $time).' +1 month -1 day');
        array_push($whereSql, sprintf('%s >= %d AND %s <= %s', 'stime', $stime, 'stime', $etime));

        /* 组装 */
        $whereSql = implode(' AND ', $whereSql);

        unset($time);
        $days = $this->where($whereSql)
                    ->field('stime')
                    ->order('stime')
                    ->select();

        /* 计算日期 */
        foreach ($days as $key => $day) {
            $day = date('d', $day['stime']);
            $day = intval($day);
            $days[$day] = $day;
            unset($days[$key]);
        }

        return $days;
    }

    /**
     * 推荐的活动
     *
     * @param  int   $limit 默认五条
     * @return array
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getRightBonusevent($limit = 5)
    {
        /* 正在进行的活动，随机显示出来 */
        $where = array(
            'del' => array('neq', '1'),
            'stime' => array('elt', time()),
            'etime' => array('egt', time()),
        );

        return $this->where($where)->limit(intval($limit))->order('RAND()')->select();
    }

    /**
     * 更新报名人数
     *
     * @param  int  $num 需要减少的人数
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function changeMan($num = 1)
    {
        $eid = $this->getDataField('eid');
        if ($this->setDec('remainder', array('eid' => array('eq', $eid)), intval($num))) {
            S('Apps_Bonusevent_List_'.$eid, null);

            return true;
        }

        return false;
    }

    /**
     * 更新报名人数 增加
     *
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function change2Man($num)
    {
        $eid = $this->getDataField('eid');
        if ($this->setInc('remainder', array('eid' => array('eq', $eid)), intval($num))) {
            S('Apps_Bonusevent_List_'.$eid, null);

            return true;
        }

        return false;
    }

    /**
     * 获取我发起的活动，并取得待审核人数
     *
     * @return array
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getMyBonusevent($uid)
    {
        $uid = intval($uid);
        $events = $this->where(array(
            'del' => array('neq', '1'),
            'uid' => array('eq', $uid),
        ))->order('`eid` DESC')->findPage(20); // 每页20条数据
        foreach ($events['data'] as $key => $value) {
            $value['notAduit'] = Enrollment::getInstance()->getNotAduit($value['eid']);
            $events['data'][$key] = $value;
        }

        return $events;
    }

    /**
     * 编辑内容
     *
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function editContent()
    {
        $eid = $this->getDataField('eid');
        $content = $this->getDataField('content');
        $uid = $this->getDataField('uid');
        $info = $this->get($eid);
        /* 判断是否存在活动 */
        if (!$info) {
            $this->error = '活动不存在，或者已经删除！';

            return false;

        /* 判断是否传递了UID */
        } elseif (!$uid) {
            $this->error = '编辑作者的UID不能为空';

            return false;

        /* 判断编辑内容是否为空 */
        } elseif (Common::strlen($content) <= 0) {
            $this->error = '编辑内容不能为空';

            return false;

        /* 判断是否不是作者 */
        } elseif ($uid != $info['uid']) {
            $this->error = '不是作者本人，无法编辑';

            return false;

        /* 如果编辑成功，清理缓存 */
        } elseif ($this->where(array(
            'eid' => array('eq', $eid),
        ))->save(array(
            'content' => $content,
        ))) {
            S('Apps_Bonusevent_List_'.$eid, null);

            return true;
        }
        $this->error = '内容没有改变';

        return false;
    }

    /**
     * 删除活动
     *
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function delete()
    {
        /* 判断是否存在 */
        if (!($info = $this->get($this->getDataField('eid')))) {
            $this->error = '该活动不存在或者已经被删除';

            return false;

        /* 判断是否有权限 */
        } elseif ($info['uid'] != $this->getDataField('uid')) {
            $this->error = '你没有权限执行该操作';

            return false;

        /* 如果成功，就清理缓存 */
        } elseif ($this->where(array(
            'eid' => array('eq', $this->getDataField('eid')),
        ))->save(array(
            'del' => '1',
        ))) {
            S('Apps_Bonusevent_List_'.$this->getDataField('eid'), null);

            return true;
        }

        return false;
    }

    /**
     * 提前结束活动
     *
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function over()
    {
        $eid = $this->getDataField('eid');
        $uid = $this->getDataField('uid');
        /* 检查是否存在 */
        if (!($info = $this->get($eid)) or $info['del']) {
            $this->error = '该活动不存在或者已经被删除';

            return false;

        /* 判断是否有权限 */
        } elseif ($info['uid'] != $uid) {
            $this->error = '你没有权限执行该操作';

            return false;

        /* 判断活动是否已经结束 ，总有一些傻逼喜欢这样干 */
        } elseif ($info['etime'] <= time()) {
            $this->error = '该活动已经结束，无需执行该操作';

            return false;

        /* 判断活动是否开始 */
        } elseif ($info['stime'] > time()) {
            $this->error = '活动还未开始，无法执行结束操作~';

            return false;

        /* 如果成功，就清理缓存 */
        } elseif ($this->where(array(
            'eid' => array('eq', $eid),
        ))->save(array(
            'etime' => time(),
        ))) {
            S('Apps_Bonusevent_List_'.$eid, null);

            return true;
        }

        return false;
    }

    /**
     * 管理员删除活动
     *
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function adminDel(array $ids)
    {
        $ids = array_map('intval', $ids);
        if ($this->where(array('eid' => array('in', $ids)))->save(array('del' => '1'))) {
            foreach ($ids as $id) {
                $data = $this->get($id);
                $data and Common::sendMessage($data['uid'], '你的活动《'.$data['name'].'》被管理员删除！');
                S('Apps_Bonusevent_List_'.$id);
            }

            return true;
        }

        return false;
    }

    /**
     * 获取后台专用数据
     *
     * @return array
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getAdminList()
    {
        $where = array(
            sprintf('%s != "%d"', 'del', 1),
        );

        $where = implode(' AND ', $where);

        $events = $this->where($where)->order('`eid` DESC')->findPage(20);

        /* 组装数据 */
        foreach ($events['data'] as $key => $value) {
            /* 时间 */
            $value['time'] = date('Y-m-d H:i', $value['stime']).'至'.date('Y-m-d H:i', $value['etime']);

            /* 地址 */
            $value['area'] = model('Area')->getAreaById($value['area']);
            $value['area'] = $value['area']['title'];
            $value['city'] = model('Area')->getAreaById($value['city']);
            $value['city'] = $value['city']['title'];
            $value['location'] = $value['area'].'&nbsp;'.$value['city'].'&nbsp;'.$value['location'];

            /* 分类 */
            $value['cate'] = CateModel::getInstance()->getById($value['cid']);
            $value['cate'] = $value['cate']['name'];

            /* 用户名 */
            $value['user'] = getUserName($value['uid']);

            /* 人数 */
            $value['manNumber'] = $value['manNumber'] ? $value['manNumber'] : '不限制';

            /* 操作 */
            $value['action'] = '<a href="'.U('Bonusevent/Info/index', array('id' => $value['eid'])).'" target="_blank">[查看]</a>&nbsp;-&nbsp;<a href="'.U('Bonusevent/Admin/delBonusevent', array('ids' => $value['eid'])).'">[删除]</a>';

            $events['data'][$key] = $value;
        }

        // var_dump($this);exit;

        return $events;
    }
} // END class Bonusevent extends Model
