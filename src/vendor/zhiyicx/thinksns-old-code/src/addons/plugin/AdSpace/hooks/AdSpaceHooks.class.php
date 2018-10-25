<?php
/**
 * 广告位钩子.
 *
 * @author zivss <guolee226@gmail.com>
 *
 * @version TS3.0
 */
class AdSpaceHooks extends Hooks
{
    /**
     * 显示广告位钩子.
     *
     * @param array $param 钩子相关参数
     */
    public function show_ad_space($param)
    {
        // 获取位置广告信息
        $place = t($param['place']);
        $placeInfo = $this->_getPlaceKey($place);
        $data = $this->model('AdSpace')->getAdSpaceByPlace($placeInfo['id']);
        foreach ($data as &$value) {
            if ($value['display_type'] == 3) {
                $value['content'] = unserialize($value['content']);
                // 获取附件图片地址
                foreach ($value['content'] as &$val) {
                    $attachInfo = model('Attach')->getAttachById($val['banner']);
                    if ($placeInfo['width'] && $placeInfo['height']) {
                        $val['bannerpic'] = getImageUrl($attachInfo['save_path'].$attachInfo['save_name'], $placeInfo['width'], $placeInfo['height']);
                    } else {
                        $val['bannerpic'] = getImageUrl($attachInfo['save_path'].$attachInfo['save_name']);
                    }
                }
            }
        }
        $this->assign('data', $data);
        // 设置宽度
        $width = intval($placeInfo['width']);
        $this->assign('width', $width);
        // 设置高度
        $height = intval($placeInfo['height']);
        $this->assign('height', $height);
        // 设置距离顶端距离
        $top = intval($placeInfo['top']);
        $bottom = intval($placeInfo['bottom']);
        $this->assign('top', $top);
        $this->assign('bottom', $bottom);
        //增加自定义广告显示模板功能
        $tpl = $placeInfo['tpl'] ? $placeInfo['tpl'] : 'showAdSpace';
        $this->display($tpl);
    }

    /**
     * 广告位插件.
     */
    public function config()
    {
        // 位置数组
        $placeArr = $this->_getPlaceData();
        $placeArray = array();
        foreach ($placeArr as $value) {
            $placeArray[$value['id']] = $value['name'];
        }
        $this->assign('place_array', $placeArray);
        // 列表数据
        $list = $this->model('AdSpace')->getAdSpaceList();
        $this->assign('list', $list);

        $this->display('config');
    }

    /**
     * 添加广告位页面.
     */
    public function addAdSpace()
    {

        // $ucata = M('user_category')->field('user_category_id,title')->where('pid=0')->select();
        // // var_dump($ucata);
        // foreach ($ucata as $k => $v) {
        //     $aa[$k] = $v['user_category_id'];
        // }
        // var_dump($aa);
        // $uid = implode($aa, ',');
        // $where['pid'] = array("in","$uid");
        //标签
        $cata = M('user_category')->field('title,user_category_id')->where('pid = 128')->select();
        // var_dump($cata);exit;
        $this->assign('cata', $cata);

        //用户组
        $user_gro = M('user_group')->field('user_group_id,user_group_name')->where('user_group_id > 4')->select();
        $this->assign('user_gro', $user_gro);

        // 位置数组
        $placeArr = $this->_getPlaceData();
        $this->assign('placeArr', $placeArr);
        // 是否可编辑
        $this->assign('editPage', false);

        $previewUrl = Addons::createAddonUrl('AdSpace', 'previewPic');
        $this->assign('previewUrl', $previewUrl);

        $this->display('addAdSpace');
    }

    /**
     * 添加广告位操作.
     */
    public function doAddAdSpace()
    {
        // 组装数据
        $data['title'] = t($_POST['title']);
        $data['place'] = intval($_POST['place']);
        $data['is_active'] = intval($_POST['is_active']);
        $data['ctime'] = time();
        $data['display_type'] = intval($_POST['display_type']);
        $da = $_POST['lable'];
        if(!$da) $da = array(0);
        $data['lable'] = implode($da, ',');
        $uu = $_POST['user_gro'];
        if(!$uu) $uu = array(0);
        $data['user_gro'] = implode($uu, ',');
        switch ($data['display_type']) {
            case 1:
                $data['content'] = $_POST['html_form'];
                break;
            case 2:
                $data['content'] = $_POST['code_form'];
                break;
            case 3:
                $picData = array();
                for ($i = 0; $i < count($_POST['banner']); $i++) {
                    $picData[] = array('banner' => $_POST['banner'][$i], 'bannerurl' => $_POST['bannerurl'][$i]);
                }
                $data['content'] = serialize($picData);
                break;
        }
        $res = $this->model('AdSpace')->doAddAdSpace($data);

        return false;
    }

    /**
     * 删除广告位操作.
     *
     * @return json 是否删除成功
     */
    public function doDelAdSpace()
    {
        $result = array();
        $ids = t($_POST['ids']);
        if (empty($ids)) {
            $result['status'] = 0;
            $result['info'] = '参数不能为空';
            exit(json_encode($result));
        }
        $res = $this->model('AdSpace')->doDelAdSpace($ids);
        if ($res) {
            $result['status'] = 1;
            $result['info'] = '删除成功';
        } else {
            $result['status'] = 0;
            $result['info'] = '删除失败';
        }
        exit(json_encode($result));
    }

    /**
     * 编辑广告位页面.
     */
    public function editAdSpace()
    {
        //标签
        $cata = M('user_category')->field('title,user_category_id')->where('pid = 128')->select();
        // var_dump($cata);exit;
        $this->assign('cata', $cata);

        //用户组
        $user_gro = M('user_group')->field('user_group_id,user_group_name')->where('user_group_id > 4')->select();
        $this->assign('user_gro', $user_gro);

        // 位置数组
        $placeArr = $this->_getPlaceData();
        $this->assign('placeArr', $placeArr);
        // 获取广告位信息
        $id = intval($_GET['id']);
        $data = $this->model('AdSpace')->getAdSpace($id);
        // 轮播图片内容解析
        if ($data['display_type'] == 3) {
            $data['content'] = unserialize($data['content']);
            foreach ($data['content'] as &$value) {
                $attachInfo = model('Attach')->getAttachById($value['banner']);
                $value['bannerpic'] = getImageUrl($attachInfo['save_path'].$attachInfo['save_name']);
            }
        }
        $this->assign('data', $data);
        $this->assign('editPage', true);
        $placeInfo = $this->_getPlaceByID($data['place']);
        $this->assign('placeInfo', $placeInfo);
        $previewUrl = Addons::createAddonUrl('AdSpace', 'previewPic');
        $this->assign('previewUrl', $previewUrl);

        $this->display('addAdSpace');
    }

    /**
     * 编辑广告位操作.
     */
    public function doEditAdSpace()
    {
        // 数据组装
        $id = intval($_POST['ad_id']);
        $data['title'] = t($_POST['title']);
        $data['place'] = intval($_POST['place']);
        $data['is_active'] = intval($_POST['is_active']);
        $data['mtime'] = time();
        $data['display_type'] = intval($_POST['display_type']);
        $da = $_POST['lable'];
        if(!$da) $da = array(0);
        $data['lable'] = implode($da, ',');
        $uu = $_POST['user_gro'];
        if(!$uu) $uu = array(0);
        $data['user_gro'] = implode($uu, ',');
        switch ($data['display_type']) {
            case 1:
                $data['content'] = $_POST['html_form'];
                break;
            case 2:
                $data['content'] = $_POST['code_form'];
                break;
            case 3:
                $picData = array();
                for ($i = 0; $i < count($_POST['banner']); $i++) {
                    $picData[] = array('banner' => $_POST['banner'][$i], 'bannerurl' => $_POST['bannerurl'][$i]);
                }
                $data['content'] = serialize($picData);
                break;
        }
        $res = $this->model('AdSpace')->doEditAdSpace($id, $data);

        return false;
    }

    /**
     * 移动广告位操作.
     */
    public function doMvAdSpace()
    {
        $result = array();
        $id = intval($_POST['id']);
        $baseId = intval($_POST['baseId']);
        if ($id <= 0 || $baseId <= 0) {
            $result['status'] = 0;
            $result['info'] = '参数错误';
            exit(json_encode($result));
        }
        $res = $this->model('AdSpace')->doMvAdSpace($id, $baseId);
        if ($res) {
            $result['status'] = 1;
            $result['info'] = '操作成功';
        } else {
            $result['status'] = 0;
            $result['info'] = '操作失败';
        }

        exit(json_encode($result));
    }

    /**
     * 获取广告位配置信息.
     *
     * @return array 广告位配置信息
     */
    private function _getPlaceData()
    {
        $data = include ADDON_PATH.'/plugin/AdSpace/config/config.php';

        return $data;
    }

    /**
     * 获取用户广告位配置信息.
     *
     * @return array 广告位配置信息
     */
    private function _getUserPlaceData()
    {
        $data = include ADDON_PATH.'/plugin/AdSpace/config/user_config.php';

        return $data;
    }

    /**
     * 通过键值获取相应的ID.
     *
     * @return int 对应键值的ID
     */
    private function _getPlaceKey($key)
    {
        $data = $this->_getPlaceData();

        return $data[$key];
    }

    /**
     * 通过ID获取相应的广告位信息.
     *
     * @return array 对应的广告位信息
     */
    private function _getPlaceByID($id)
    {
        $data = $this->_getPlaceData();
        foreach ($data as $k => $v) {
            if ($v['id'] != $id) {
                continue;
            }

            return $v;
        }

        return array();
    }

    public function previewPic()
    {
        $params = t($_GET['params']);
        $params = explode(',', $params);
        $content = array();
        foreach ($params as $key => &$val) {
            $attachInfo = model('Attach')->getAttachById($val);
            $tmp['bannerpic'] = getImageUrl($attachInfo['save_path'].$attachInfo['save_name']);
            $content[] = $tmp;
            unset($tmp);
        }
        $this->assign('content', $content);
        $this->assign('width', t($_GET['width']));
        //自定义预览模板
        $previewTPL = t($_GET['preview']) ? t($_GET['preview']) : 'previewAd';
        //$previewTPL = 'previewAd';
        $this->display($previewTPL);
    }

    public function userAdSpace(){

        // 位置数组
        $placeArr = $this->_getUserPlaceData();
        $placeArray = array();
        foreach ($placeArr as $value) {
            $placeArray[$value['id']] = $value['name'];
        }
        // var_dump($placeArray);exit;
        $this->assign('place_array', $placeArray);
        // 列表数据
        $list = M('ad_user')->order('ad_id DESC')->findPage(20);
        // var_dump($list);exit;
        foreach ($list['data'] as $key => $value) {
            $data['img'] = unserialize($value['img']);
            $attachInfo = model('Attach')->getAttachById($data['img'][0]['banner']);
            $list['data'][$key]['img'] = getImageUrl($attachInfo['save_path'].$attachInfo['save_name']);
            
        }
        $this->assign('list', $list);

        $this->display('userAdSpace');
    }

    public function addUserAdSpace(){
        //位置数组
        $placeArr = $this->_getUserPlaceData();
        $this->assign('placeArr', $placeArr);
        // 是否可编辑
        $this->assign('editPage', false);

        $previewUrl = Addons::createAddonUrl('AdSpace', 'previewPic');
        $this->assign('previewUrl', $previewUrl);

        $this->display('addUserAdSpace');

    }

     /**
     * 编辑广告位页面.
     */
    public function editUserAdSpace()
    {
        // 位置数组
        $placeArr = $this->_getUserPlaceData();
        $this->assign('placeArr', $placeArr);

        // 获取广告位信息
        $id = intval($_GET['id']);
        $data = M('ad_user')->find($id);
        // 轮播图片内容解析
        $data['img'] = unserialize($data['img']);
        foreach ($data['img'] as &$value) {
            $attachInfo = model('Attach')->getAttachById($value['banner']);
            $value['bannerpic'] = getImageUrl($attachInfo['save_path'].$attachInfo['save_name']);
        }
        $data['adUrl'] = unserialize($data['adUrl']);
        $this->assign('data', $data);
        $this->assign('editPage', true);
        $previewUrl = Addons::createAddonUrl('AdSpace', 'previewPic');
        $this->assign('previewUrl', $previewUrl);

        $this->display('addUserAdSpace');
    }

    /**
     * 编辑广告位操作.
     */
    public function doEditUserAdSpace()
    {
        // 数据组装
        $id['ad_id'] = intval($_POST['ad_id']);
        $data['title'] = t($_POST['title']);
        $data['place'] = intval($_POST['place']);
        $data['is_active'] = intval($_POST['is_active']);
        $data['mtime'] = time();
        $data['content'] = $_POST['html_form'];
        $picData = array();
        for ($i = 0; $i < count($_POST['banner']); $i++) {
            $picData[] = array('banner' => $_POST['banner'][$i], 'bannerurl' => $_POST['bannerurl'][$i]);
        }
        $data['img'] = serialize($picData);

        $adUrlData = array();
        for ($i = 0; $i < count($_POST['adUrl']); $i++) {
            $adUrlData[] = array('adTitle' => $_POST['adTitle'][$i], 'explain' => $_POST['explain'][$i], 'url' => $_POST['url'][$i]);
        }
        $data['adUrl'] = serialize($adUrlData);

        $res = M('ad_user')->where($id)->save($data);

        return false;
    }

     /**
     * 添加广告位操作.
     */
    public function doAddUserAdSpace()
    {

        // 组装数据
        $data['title'] = t($_POST['title']);
        $data['place'] = $map['place'] = intval($_POST['place']);
        $map['uid'] = intval($_POST['uid']);
        $uid = M('user')->field('uid')->where("uid={$map['uid']}")->find();
        if(!$uid){
            $this->error('用户不存在');
        }else{
            $uname = M('ad_user')->where($map)->find();
            if($uname){
                $this->error('该用户已存在该广告位');
            }
            $data['uid'] = $uid['uid'];
        }
        $data['is_active'] = intval($_POST['is_active']);
        $data['ctime'] = time();
        
        $data['content'] = $_POST['html_form'];
        $picData = array();
        for ($i = 0; $i < count($_POST['banner']); $i++) {
            $picData[] = array('banner' => $_POST['banner'][$i], 'bannerurl' => $_POST['bannerurl'][$i]);
        }
        $data['img'] = serialize($picData);

        $adUrlData = array();
        for ($i = 0; $i < count($_POST['adUrl']); $i++) {
            $adUrlData[] = array('adTitle' => $_POST['adTitle'][$i], 'explain' => $_POST['explain'][$i], 'url' => $_POST['url'][$i]);
        }
        $data['adUrl'] = serialize($adUrlData);
        $res = M('ad_user')->add($data);

        return false;
    }

     /**
     * 删除广告位操作.
     *
     * @return json 是否删除成功
     */
    public function doDelUserAdSpace()
    {
        $result = array();
        $ids = t($_POST['ids']);
        if (empty($ids)) {
            $result['status'] = 0;
            $result['info'] = '参数不能为空';
            exit(json_encode($result));
        }
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        if (empty($ids)) {
            return false;
        }
        $map['ad_id'] = array('IN', $ids);
        $res = M('ad_user')->where($map)->delete();
        if ($res) {
            $result['status'] = 1;
            $result['info'] = '删除成功';
        } else {
            $result['status'] = 0;
            $result['info'] = '删除失败';
        }
        exit(json_encode($result));
    }
}
