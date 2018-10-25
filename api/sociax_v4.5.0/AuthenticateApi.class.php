<?php
/**
 * @author jason
 */
class AuthenticateApi extends Api
{

    /**
     * 认证分类/附件限制
     */
    public function ApplicationCertification(){

        $data['auth_info'] = M('user_verified')->field('*')->where("uid={$this->mid} and usergroup_id={$this->data['usergroup_id']}")->find();
        if($data['auth_info'] != ''){
            switch ($data['auth_info']['verified']) {
                case '0':
                    $data['authentication_status'] = 2;
                    $data['auth_info']['user_verified_category_naem'] = $this->category_title($data['auth_info']['user_verified_category_id']);
                    $data['auth_info']['usergroup_name'] = $this->user_group_name($data['auth_info']['usergroup_id']);
                    $data['auth_info']['idcard'] = substr($data['auth_info']['idcard'], 0, 4).'****'.substr($data['auth_info']['idcard'], -4);
                    $data['auth_info']['phone'] = substr($data['auth_info']['phone'], 0, 3).'****'.substr($data['auth_info']['phone'], -4);
                    break;
                case '1':
                    $data['authentication_status'] = 1;
                    $data['auth_info']['user_verified_category_naem'] = $this->category_title($data['auth_info']['user_verified_category_id']);
                    $data['auth_info']['usergroup_name'] = $this->user_group_name($data['auth_info']['usergroup_id']);
                    $data['auth_info']['idcard'] = substr($data['auth_info']['idcard'], 0, 4).'****'.substr($data['auth_info']['idcard'], -4);
                    $data['auth_info']['phone'] = substr($data['auth_info']['phone'], 0, 3).'****'.substr($data['auth_info']['phone'], -4);
                    break;
                case '-1':
                    $data['authentication_status'] = -1;
                    $data['auth_info']['user_verified_category_naem'] = $this->category_title($data['auth_info']['user_verified_category_id']);
                    $data['auth_info']['usergroup_name'] = $this->user_group_name($data['auth_info']['usergroup_id']);
                    $data['auth_info']['idcard'] = substr($data['auth_info']['idcard'], 0, 4).'****'.substr($data['auth_info']['idcard'], -4);
                    $data['auth_info']['phone'] = substr($data['auth_info']['phone'], 0, 3).'****'.substr($data['auth_info']['phone'], -4);
                    $data['reason'] = $data['auth_info']['reason'];
                    break;
            }
            unset($data['auth_info']['verified']);
            unset($data['auth_info']['attach_id_front']);
            unset($data['auth_info']['attach_id_back']);
            unset($data['auth_info']['attach_id_hands']);
            unset($data['auth_info']['attach_id']);
            $data['status'] = 1;
            $data['message'] = '操作成功';
            return $data;
        }else{

            $pid['user_group_id'] = $this->data['usergroup_id'];
            $apCf['data'] = M('user_verified_category')->field('user_verified_category_id,title')->where('pid='.$pid['user_group_id'])->select();

            // 附件限制
            $attach = model('Xdata')->get('admin_Config:attachimage');
            $imageArr = array(
                'gif',
                'jpg',
                'jpeg',
                'png',
                'bmp',
            );
            foreach ($imageArr as $v) {
                if (strstr($attach['attach_allow_extension'], $v)) {
                    $imageAllow[] = $v;
                }
            }
            $apCf['attach_allow_extension'] = implode(', ', $imageAllow);
            $apCf['attach_max_size'] = $attach['attach_max_size'];
            $apCf['authentication_status'] = 0;
            $apCf['status'] = 1;
            $apCf['message'] = '操作成功';
            return $apCf;
        }
            
    }

    public function category_title($user_verified_category_id){

        $data = M('user_verified_category')->field('title')->where("user_verified_category_id={$user_verified_category_id}")->find();
        return $data['title'];
    }

    public function user_group_name($user_group_id){

        $data = M('user_group_')->field('user_group_name')->where("user_group_id={$user_group_id}")->find();
        return $data['user_group_name'];
    }

    /**
     * 提交申请认证
     */
    public function doAuthenticate()
    {
        //检查认证类型
        $data['usergroup_id'] = intval($this->data['usergroup_id']);
        $hasUserGroup = model('UserGroup')->where(array('user_group_id' => $data['usergroup_id'], 'is_authenticate' => 1))->count() > 0;
        if (!$hasUserGroup) {
            return array('status' => 0, 'message' => '认证的分类不存在');
        }
        //检查认证分类
        $data['user_verified_category_id'] = intval($this->data['verifiedCategory']);
        $hasVCatId = D('user_verified_category')->where("pid={$data['usergroup_id']} and user_verified_category_id={$data['user_verified_category_id']}")->count() > 0;
        if (!$hasVCatId) {
            $data['user_verified_category_id'] = 0;
        }
        

        //取得认证信息
        $data['company'] = trim(t($this->data['company']));//公司名称
        $data['realname'] = trim(t($this->data['realname']));//真实姓名
        $data['idcard'] = trim(t($this->data['idcard']));//身份证号
        $data['phone'] = trim(t($this->data['phone']));
        $data['credit_code_number'] = trim(t($this->data['credit_code_number']));//信用代码号
        $data['business_address'] = trim(t($this->data['business_address']));//经营地址
        $data['reason'] = trim(t($this->data['reason']));//认证理由
        $data['info'] = trim(t($this->data['info']));//认证信息
        $data['attach_id_front'] = trim(t($this->data['attach_id_front']));//
        $data['attach_id_back'] = trim(t($this->data['attach_id_back']));//
        $data['attach_id_hands'] = trim(t($this->data['attach_id_hands']));//认证资料，存储用户上传的ID
        $data['attach_id'] = '|'.$data['attach_id_front'].'|'.$data['attach_id_back'].'|'.$data['attach_id_hands'].'|';//

        $Regx1 = '/^[0-9]*$/';
        $Regx2 = '/^[A-Za-z0-9]*$/';
        $Regx3 = '/^[A-Za-z|\x{4e00}-\x{9fa5}]+$/u';

        if ($data['usergroup_id'] == 6) {
            if (!$data['company']) {
                return array('status' => 0, 'message' => '机构名称不能为空');
            }
            if (!$data['credit_code_number']) {
                return array('status' => 0, 'message' => '信用代码号不能为空');
            }
            if (!$data['business_address']) {
                return array('status' => 0, 'message' => '经营地址不能为空');
            }
        }
        if (!$data['realname']) {
            if($data['usergroup_id'] == 5){

                return array('status' => 0, 'message' => '负责人姓名不能为空');
            }else{

                return array('status' => 0, 'message' => '真实姓名不能为空');

            }
        }
        if (!$data['idcard']) {
            return array('status' => 0, 'message' => '身份证号码不能为空');
        }
        if (!$data['phone']) {
            return array('status' => 0, 'message' => '联系方式不能为空');
        }
        if (preg_match($Regx3, $data['realname']) == 0 || strlen($data['realname']) > 30) {
            return array('status' => 0, 'message' => '请输入正确的姓名格式');
        }
        if (preg_match($Regx2, $data['idcard']) == 0 || preg_match($Regx1, substr($data['idcard'], 0, 17)) == 0 || strlen($data['idcard']) !== 18) {
            return array('status' => 0, 'message' => '请输入正确的身份证号码');
        }
        if (preg_match($Regx1, $data['phone']) == 0) {
            return array('status' => 0, 'message' => '请输入正确的手机号码格式');
        }
        preg_match_all('/./us', $data['reason'], $matchs); // 一个汉字也为一个字符
        if (count($matchs[0]) > 255) {
            return array('status' => 0, 'message' => '认证补充不能超过255个字符');
        }
        preg_match_all('/./us', $data['info'], $match); //一个汉字也为一个字符
        if (count($match[0]) > 140) {
            return array('status' => 0, 'message' => '认证资料不能超过255个字符');
        }

        $data['verified'] = 0; //认证状态为未认证
        $verifyInfo = D('user_verified')->where('uid='.$this->mid)->count() > 0;
        if ($verifyInfo) {
            $res = D('user_verified')->where('uid='.$this->mid)->save($data);
        } else {
            $data['uid'] = $this->mid;
            $res = D('user_verified')->add($data);
        }
        if (false !== $res) {
            model('Notify')->sendNotify($this->mid, 'public_account_doAuthenticate');
            $touid = D('user_group_link')->where('user_group_id=1')->field('uid')->findAll();
            foreach ($touid as $k => $v) {
                model('Notify')->sendNotify($v['uid'], 'verify_audit');
            }
            return array('status' => 1, 'message' => '提交成功，等待审核');
        } else {
            return array('status' => 0, 'message' => '认证信息提交失败');
        }
    }

    public function upload(){
        $d['attach_type'] = 'authentication_image';
        
        $d['upload_type'] = 'image';
        $GLOBALS['fromMobile'] = true;
        $info = model('Attach')->upload($d, $d);
        if ($info['status']){
            $data['attach_id'] = implode(',',getSubByKey($info['info'], 'attach_id'));

            $data['status'] = 1;
            $data['message']="上传成功";
        }else{
            $data['status'] = 0;
            $data['message']="上传失败，请重新上传"  ;
        }

        return $data;
    }

    /**
     * 注销认证
     *
     * @return bool 操作是否成功 1:成功 0:失败
     */
    public function delverify()
    {
        $verified_group_id = D('user_verified')->where('uid='.$this->mid)->getField('usergroup_id');
        $res = D('user_verified')->where('uid='.$this->mid)->delete();
        $res2 = D('user_group_link')->where('uid='.$this->mid.' and user_group_id='.$verified_group_id)->delete();
        if ($res && $res2) {
            if($verified_group_id == 5 && model('VipPay')->isPersonalVip($this->mid))
            {
                $vip_group_id = M('vip')->where('u_id='.$this->mid.' and status=1')->getField('p_id');
                M('vip')->where('u_id='.$this->mid)->delete();
                D('user_group_link')->where('uid='.$this->mid.' and user_group_id='.$vip_group_id)->delete();
            }
            // 清除权限组 用户组缓存
            model('Cache')->rm('perm_user_'.$this->mid);
            model('Cache')->rm('user_group_'.$this->mid); 
            model('Notify')->sendNotify($this->mid, 'public_account_delverify');
            return array('status' => 1, 'message' => '成功');
        } else {
            return array('status' => 0, 'message' => '失败');
        }
    }

}
