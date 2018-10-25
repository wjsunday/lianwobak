<?php

namespace Apps\Bonusevent\Controller;

defined('SITE_PATH') || exit('Forbidden');

use Apps\Bonusevent\Common;
use Apps\Bonusevent\Common\BaseController as Controller;
use Apps\Bonusevent\Model\Bonusevent;

/**
 * 发布活动控制器
 *
 * @package Apps\Bonusevent\Controller\Release
 * @author
 **/
class Release extends Controller
{
    /**
     * 编辑活动 - 只支持编辑详细内容
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function edit()
    {
        array_push($this->appJsList, '/js/release.edit.js');
        $eid = Common::getInput('id', 'get');
        $event = Bonusevent::getInstance()->get($eid); // 带缓存的，比去数据库比对高效
        /* 判断是否是活动作者 */
        if ($this->mid != $event['uid']) {
            $this->error('你没有权限编辑该活动');
        }
        $this->assign('event', $event);
        unset($event);
        $this->display();
    }

    /**
     * 编辑操作
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function doEdit()
    {
        list($eid, $content) = Common::getInput(array('eid', 'content'), 'post');
        if (Bonusevent::getInstance()->setId($eid)
                                ->setUid($this->mid)
                                ->setContent($content)
                                ->editContent()
        ) {
            $this->__JSON__(1, '编辑成功');
        }
        $this->__JSON__(0, Bonusevent::getInstance()->getError());
    }

    /**
     * 显示发布页面
     *
     * @author Seven Du <lovevipdsw2vip.qq.com>
     **/
    public function index()
    {
        /* 需要分类数据 */
        $this->__cate__();

        array_push($this->appJsList, '/js/release.index.js');
        $this->display();
    }

    /**
     * 发布活动提交事件
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function submit()
    {
        list($title, $sdate, $stime, $edate, $etime, $area, $city, $address, $place, $image, $mainNumber, $price, $tips, $cate, $audit, $content) = Common::getInput(array('title', 'sdate', 'stime', 'edate', 'etime', 'area', 'city', 'address', 'place', 'image', 'mainNumber', 'price', 'tips', 'cate', 'audit', 'content'), 'post');
        $audit != 1 and
        $audit = 0;
        /* 一些没办法在model里面封装的判断 被设计坑的不要不要的！！！ */
        /* 判断开始日期是否为空 */
        if (!$sdate) {
            $this->__JSON__(0, '请正确填写开始日期', $sdate);

        /* 判断开始日期 */
        } elseif (!$stime) {
            $this->__JSON__(0, '请正确填写开始时间');

        /* 结束日期 */
        } elseif (!$edate) {
            $this->__JSON__(0, '请正确填写结束日期');

        /* 结束时间 */
        } elseif (!$etime) {
            $this->__JSON__(0, '请正确填写结束时间');

        /* 添加操作，是否有错误 */
        } elseif (!($id = Bonusevent::getInstance()->setName($title) //活动标题
                                       ->setStime($sdate.' '.$stime) // 开始时间
                                       ->setEtime($edate.' '.$etime) // 结束时间
                                       ->setArea($area) // 地区
                                       ->setCity($city) // 城市
                                       ->setLocation($address) // 详细地址
                                       ->setPlace($place)  // 场所
                                       ->setImage($image) // 封面图片
                                       ->setManNumber($mainNumber)  // 活动人数
                                       ->setPrice($price)  // 价格
                                       ->setCid($cate) // 分类
                                       ->setAudit($audit)  // 是否需要权限审核
                                       ->setContent($content)  // 活动详情
                                       ->setUid($this->mid) // 发布活动的用户
                                       ->setTips($tips) // 费用说明
                                       ->add())
        ) {
            $this->__JSON__(0, Bonusevent::getInstance()->getError());
        }
        $this->__JSON__(1, '发布成功', U('Bonusevent/Info/index', array('id' => $id)));
    }

    /**
     * 获取地区信息
     *
     * @request pid 地区父ID 默认是0
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getArea()
    {
        $pid = intval(Common::getInput('pid'));
        $pid <= 0 and
        $pid = 0;
        $list = model('Area')->getAreaList($pid);
        echo json_encode($list);
        exit;
    }

    /**
     * 上传图片
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function uploadImage()
    {
        $data = $this->uploadFile('image', 'bonusevent_image', 'gif', 'jpg', 'png', 'jpeg');
        // ob_end_clean();
        // Common::setHeader('text/json');
        // ob_start();
        echo json_encode($data);
        // ob_end_flush();
        exit;
    }

    /**
     * 上传文件
     *
     * @param string $uploadType 上传文件的类型
     * @param string $attachType 保存文件的类型
     * @param string [$param, $param ...] 限制文件上传的类型
     * @return array
     * @author Medz Seven <lovevipdsw@vip.qq.com>
     **/
    protected function uploadFile($uploadType, $attachType)
    {
        $ext = func_get_args();
        array_shift($ext);
        array_shift($ext);

        $option = array(
            'attach_type' => $attachType,
            'app_name' => 'Bonusevent',
        );
        count($ext) and $option['allow_exts'] = implode(',', $ext);

        $info = model('Attach')->upload(array(
            'upload_type' => $uploadType,
        ), $option);

        // # 判断是否有上传
        if (count($info['info']) <= 0) {
            return array(
                'status' => '-1',
                'msg' => '没有上传的文件',
            );

        // # 判断是否上传成功
        } elseif ($info['status'] == false) {
            return array(
                'status' => '0',
                'msg' => $info['info'],
            );
        }

        return array(
            'status' => 1,
            'data' => array_pop($info['info']),
        );
    }
} // END class Release extends Controller
class_alias('Apps\Bonusevent\Controller\Release', 'ReleaseAction');
