<?php

namespace Apps\Gift\Model;

use Model;
use Apps\Gift\Model\Gift as GiftModel;

/**
 * 礼物赠送记录模型
 *
 * @package Apps\Gift\Model\Log
 * @author Seven Du <lovevipdsw@vip.qq.com>
 **/
class GiftLog extends Model
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
    protected $tableName = 'gift_log';

    /**
     * 字段
     *
     * @var array
     **/
    protected $fields = array('id', 'inUid', 'outUid', 'gid', 'type', 'say', 'name', 'phone', 'addres', 'time', 'num', 'status', 'content', 'notIn', 'notOut');

    /**
     * 转送给其他用户 - 只支持虚拟礼物
     *
     * @param  int    $id    记录ID
     * @param  int    $toUid 被赠送人UID
     * @param  string $say,  祝福语
     * @param  int    $num   赠送数量，不得多于已经收到的数量
     * @param  int    $type  类型，只有三个，1：匿名赠送 2：公开赠送 3：私密赠送
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function transfer($id, $toUid, $say, $num, $type)
    {
        $id = intval($id);
        $toUid = intval($toUid);
        $say = t($say);
        $num = intval($num);
        $type = intval($type);
        $data = $this->getById($id);

        /* # 先判断是否是全部赠送 */
        if ($data['num'] <= $num) {
            $num = $data['num'];

        /* # 只是赠送部分 */
        } else {
            $id2 = $this->add($data['inUid'], $data['outUid'], $data['gid'], $data['type'], $data['say'], $data['num'] - $num, '', '', '', $data['time']);
            $this->where('`id` = '.$id2)->save(array(
                'notOut' => '1',
            ));
        }

        $this->where('`id` = '.$id)->save(array(
            'notIn' => '1',
        ));

        return $this->add($toUid, $data['inUid'], $data['gid'], $type, $say, $num);
    }

    /**
     * 添加赠送记录
     *
     * @param  int          $inUid  被赠送的用户UID
     * @param  int          $outUid 赠送人UID
     * @param  int          $gid    礼物ID
     * @param  int          $type   类型，只有三个，1：匿名赠送 2：公开赠送 3：私密赠送
     * @param  string       $say    祝福语
     * @param  int          $num    赠送数量
     * @param  string       $addres 实体礼物邮寄地址
     * @param  string       $name   用户真实姓名
     * @param  string       $phone  用户联系方式
     * @param  string|float $time   时间
     * @return int          记录ID
     * @author Seevn Du <lovevipdsw@vip.qq.com>
     **/
    public function add($inUid, $outUid, $gid, $type, $say, $num, $addres = '', $name = '', $phone = '', $time = null)
    {
        $logId = parent::add(array(
            'inUid' => intval($inUid),
            'outUid' => intval($outUid),
            'gid' => intval($gid),
            'type' => intval($type),
            'num' => intval($num),
            'say' => $say,
            'addres' => $addres,
            'name' => $name,
            'phone' => $phone,
            'time' => is_numeric($time) ? $time : ($time ? strtotime($time) : time()),
        ));
        if ($logId) {
            /* # 礼物库存减少相关数量 */
            GiftModel::getInstance()->changeStock($gid, $num);

            /* # 赠送者积分减去 */
            $score = GiftModel::getInstance()->where('`id` = '.intval($gid))->field('score')->getField('score');
            $score *= $num;
            D('credit_user')->setDec('score', '`uid` = '.intval($outUid), $score);
            D('Credit')->cleanCache(intval($outUid));

            /* # 积分记录 */
            D('credit_record')->add(array(
                'uid' => intval($outUid),
                'change' => '积分<font color="red">-'.$score.'</font>',
                'action' => '兑换礼物',
                'ctime' => time(),
                'des' => '给好友赠送礼物消耗',
                'type' => 1,
            ));
        }

        return $logId;
    }

    /**
     * 判断该礼物，是否赠送过
     *
     * @param  int  $gid    礼物ID
     * @param  int  $inUid  被赠送人UID
     * @param  int  $outUid 赠送人UID
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function hasGive($gid, $inUid, $outUid)
    {
        $where = '`gid` = %d AND `inUid` = %d AND `outUid` = %d';
        $where = sprintf($where, intval($gid), intval($inUid), intval($outUid));

        return $this->where($where)->field('`id`')->count() > 0;
    }

    /**
     * 验证订单记录是否存在
     *
     * @param  int  $id 记录ID
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function has($id)
    {
        $id = intval($id);

        return $this->where('`id` = '.$id)->field('`id`')->count() > 0;
    }

    /**
     * 发货
     *
     * @param  int    $id      记录ID
     * @param  string $content 发货的内容
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function delivery($id, $content)
    {
        return $this->where('`id` = '.intval($id))->save(array(
            'status' => 1,
            'content' => $content,
        ));
    }

    /**
     * 按照ID获取记录
     *
     * @param  int   $id 记录ID
     * @return array
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getById($id)
    {
        $id = intval($id);

        return $this->where('`id` = '.$id)->find();
    }

    /**
     * 获取送出的礼物记录+分页
     *
     * @param  int   $uid  查询的用户UID
     * @param  int   $type 查询的类型 只有三个，1：匿名赠送 2：公开赠送 3：私密赠送 0：全部
     * @param  int   $num  每页的条数
     * @return array
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getOutLog($uid, $type = 0, $num = 20)
    {
        list($uid, $type, $num) = array(intval($uid), intval($type), intval($num));

        $where = '`outUid` = %d AND `notOut` != 1';
        $where = sprintf($where, $uid);

        if (in_array($type, array(1, 2, 3))) {
            $where .= ' AND `type` = %d';
            $where = sprintf($where, $type);
        }

        // var_dump($where);exit;

        $list = $this->where($where)->order('`id` DESC')->findPage($num);
        foreach ($list['data'] as $key => $value) {
            $value['date'] = date('Y年m月d日', $value['time']);
            $value['inUserName'] = getUserName($value['inUid']);
            $value['outUserName'] = getUserName($value['outUid']);
            $list['data'][$key] = array_merge($value, $this->_getGiftInfo($value['gid']));
            $list['data'][$key]['name'] = $list['data'][$key]['name'].'('.$list['data'][$key]['num'].')';
        }

        return $list;
    }

    /**
     * 获取获得的礼物记录+分页
     *
     * @param  int   $uid  查询的用户UID
     * @param  int   $type 查询的类型 只有三个，1：匿名赠送 2：公开赠送 3：私密赠送 0：全部
     * @param  int   $num  每页的条数
     * @return array
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getInLog($uid, $type = 0, $num = 20)
    {
        list($uid, $type, $num) = array(intval($uid), intval($type), intval($num));

        $where = '`inUid` = %d AND `notIn` != 1';
        $where = sprintf($where, $uid);

        if (in_array($type, array(1, 2, 3))) {
            $where .= ' AND `type` = %d';
            $where = sprintf($where, $type);
        }

        $list = $this->where($where)->order('`id` DESC')->findPage($num);

        foreach ($list['data'] as $key => $value) {
            $value['date'] = date('Y年m月d日', $value['time']);
            $value['inUserName'] = getUserName($value['inUid']);
            $value['outUserName'] = getUserName($value['outUid']);
            $value['logId'] = $value['id'];

            if ($value['type'] == 1) {
                $value['outUserName'] = '匿名';
            }

            $list['data'][$key] = array_merge($value, $this->_getGiftInfo($value['gid']));
            $list['data'][$key]['name'] = $list['data'][$key]['name'].'('.$list['data'][$key]['num'].')';
        }

        return $list;
    }

    /**
     * 取得礼物信息
     *
     * @param  int   $gid 礼物ID
     * @return array
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function _getGiftInfo($gid)
    {
        $gid = intval($gid);
        $data = GiftModel::getInstance()->getById($gid);
        $data['image'] && $data['image_src'] = getImageUrlByAttachId($data['image']);

        return $data;
    }

    /**
     * 取得兑换排行榜
     *
     * @param  int   $num 分页每页的条数
     * @return array
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getTops($num = 6)
    {
        // SELECT gid FROM `ts_gift_log` group by gid order by count(gid) desc
        $data = $this->field('`gid`')->group('`gid`')->order('COUNT(`gid`) DESC')->findPage($num);

        foreach ($data['data'] as $key => $value) {
            $value = $this->_getGiftInfo($value['gid']);
            $data['data'][$key] = $value;
        }
        $data['data'] = array_filter($data['data']);

        return $data;
    }

    /**
     * 根据礼物ID计算兑换人数
     *
     * @param  int $gid 礼物ID
     * @return int
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getUserCount($gid)
    {
        $gid = intval($gid);

        return $this->where('`gid` = '.$gid)->field('`id`')->count();
    }

    /**
     * 按照各种条件获取订单数据
     *
     * @param  int            $gid       礼物ID
     * @param  string         $giftName  礼物名称
     * @param  array          $inUid     被赠送人UID列表
     * @param  array          $outUid    赠送人UID列表
     * @param  string         $name      联系人名称
     * @param  string         $phone     联系方式
     * @param  string         $addres    联系人地址
     * @param  float | string $startTime 开始搜索的时间范围
     * @param  fload | string $endTime   搜索结束的时间范围
     * @return array
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getOrderData($gid = 0, $giftName = '', $inUid = array(), $outUid = array(), $name = '', $phone = '', $addres = '', $startTime = 0, $endTime = 0)
    {
        // SELECT `log`.*, `gift`.`name` FROM `ts_gift_log` AS `log` INNER JOIN `ts_gift` AS `gift` ON `log`.`gid` = `gift`.`id` WHERE `gift`.`isDel` != 1 AND `gift`.`cate` = 2 GROUP BY `log`.`id` ORDER BY `log`.`time`
        is_numeric($startTime) || $startTime = strtotime($startTime);
        is_numeric($endTime) || $endTime = strtotime($endTime);

        $arrOStr2Int = function ($str) {
            is_array($str) || $str = explode(',', $str);
            foreach ($str as $key => $value) {
                $value = intval($value);
                $str[$key] = $value;
                if (!$value) {
                    unset($str[$key]);
                }
            }

            return $str;
        };

        /* # 过滤 */
        $inUid = $arrOStr2Int($inUid);
        $outUid = $arrOStr2Int($outUid);
        $gid = intval($gid);
        $giftName = t($giftName);
        // $inUid    = intval($inUid);
        // $outUid   = intval($outUid);
        $name = t($name);
        $phone = t($phone);
        $addres = t($addres);
        $startTime = floatval($startTime);
        $endTime = floatval($endTime);

        /* # 内联 */
        $join = 'INNER JOIN `%s` AS `gift` ON `%s`.`gid` = `gift`.`id`';
        $join = sprintf($join, GiftModel::getInstance()->getTableName(), $this->getTableName());

        /* # 字段 */
        $field = '`%s`.*, `gift`.`name` AS `giftName`';
        $field = sprintf($field, $this->getTableName());

        /* # 排序 */
        $order = '`%s`.`time` DESC';
        $order = sprintf($order, $this->getTableName());

        /* # 条件 */
        $where = '`gift`.`isDel` != 1 AND `gift`.`cate` = 2 AND `__TABLE__`.`status` = 0';
        $gid && $where .= ' AND `__TABLE__`.`gid` = '.$gid;
        $giftName && $where .= ' AND `gift`.`name` LIKE \'%'.$giftName.'%\'';
        $inUid && $where .= ' AND `__TABLE__`.`inUid` = '.$inUid;
        $outUid && $where .= ' AND `__TABLE__`.`outUid` = '.$outUid;
        $name && $where .= ' AND `__TABLE__`.`name` LIKE \'%'.$name.'%\'';
        $phone && $where .= ' AND `__TABLE__`.`phone` LIKE \'%'.$phone.'%\'';
        $addres && $where .= ' AND `__TABLE__`.`addres` LIKE \'%'.$addres.'%\'';
        $startTime && $where .= ' AND `__TABLE__`.`time` >= \''.$startTime.'\'';
        $endTime && $where .= ' AND `__TABLE__`.`time` <= \''.$endTime.'\'';
        $where = str_replace('__TABLE__', $this->getTableName(), $where);

        $data = $this->where($where)->join($join)->order($order)->field($field)->findPage(10);

        return $data;
        var_dump($data, $this);
        exit;
    }

    /**
     * 发送通知
     *
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function sendMessage($uid, $content, $formUid = 0)
    {
        $message = D('notify_message');

        $messageData = array();
        $messageData['uid'] = $uid;
        $messageData['from_uid'] = $formUid;
        $messageData['appname'] = 'Gift';
        $messageData['title'] = '礼物通知';
        $messageData['ctime'] = time();
        $messageData['is_read'] = '0';
        $messageData['body'] = $content;

        return $message->add($messageData);
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
} // END class Log extends Model
class_alias('Apps\Gift\Model\Log', 'GiftLogModel');
