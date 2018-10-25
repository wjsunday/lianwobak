<?php
/**
 * @author jason
 */
class FollowApi extends Api
{

    public function PersonalInformation(){

    	$uid['uid'] = $this->mid;
    	$uid['fid'] = $this->data['fid'];
    	$map['des'] = $this->data['des'];
        $map['phone'] = implode(',', $this->data['phone']);
        $map['email'] = implode(',', $this->data['email']);
        if(sizeof($map['phone']) > 5 || sizeof($map['email']) > 5){

            $res = array();
            $res['status'] = 0;
            $res['message'] = '最多只能存五条';
            return $res;
        }
    	if($map['phone'] != ''){

    		$dddd = M('user_follow')->where($uid)->save(array('phone' => $map['phone']));//保存电话 
    	}
        if($map['email'] != ''){

            $dddd = M('user_follow')->where($uid)->save(array('email' => $map['email']));//保存邮箱
        }
        if($map['des'] != ''){

            $dddd = M('user_follow')->where($uid)->save(array('des' => $map['des']));//保存描述
        }
    	if($dddd){
            $res = array();
            $res['status'] = 1;
            $res['message'] = '保存成功';
            return $res;
    	}else{
    		$res = array();
    		$res['status'] = 0;
    		$res['message'] = '保存失败';
    		return $res;
    	}

    }

//设置置顶聊天
    public function MessageTop(){

    	$add['state'] = $this->data['state'];
    	$add['ctime'] = time();
    	$sta['uid'] = $this->mid;
    	$sta['list_id'] = $this->data['roomid'];
    	$top = M('message_top')->where($sta)->save($add);
    	if($top == 0){
    		$add['uid'] = $sta['uid'];
    		$add['list_id'] = $sta['list_id'];
    		M('message_top')->add($add);
    	}
    	
    	$res = array();
    	if ($add['state'] == 0)
    	{
    	    $res['status'] = 0;
    	    $res['message'] = '取消置顶成功';
    	}else{
    	    $res['status'] = 1;
    	    $res['message'] = '置顶成功';
    	}

    	return $res;
    	
    }

//置顶聊天列表
    public function MessageTopList(){

        $sta['uid'] = $this->mid;
        $message = M('message_top')->field('list_id')->where("uid={$sta['uid']} and state=1")->select();
        if($message){
            foreach ($message as $key => $value) {
                $list[$key] = $value['list_id'];
            }
            $id = implode(',', $list);
            $where['list_id'] = array("in",$id);
            $top['top'] = M('message_list')->where($where)->order('mtime desc')->select();
            if($top){

                $top['status'] = 1;
                $top['message'] = '';
                return $top;
            }else{

                $top['status'] = 0;
                $top['message'] = '';
                return $top;
            }
        }
    }

//清除聊天记录
    public function no_message(){
    
    		$roomId = $this->data['roomid'];
            $webMessage = model('WebMessage');
            $res = array();
            if ($webMessage->clearMessage($roomId, 'all')) {
            	$res['status'] = 1;
        		$res['message'] = '清理成功';
        		
            } else {
                $res['status'] = 0;
        		$res['message'] = '已清理干净';
            }
            return $res;
     	
        }

//免打扰
        public function not_disturb(){

            $add['type'] = $this->data['type'];
            $wh['list_id'] = $this->data['roomid'];
            $wh['member_uid'] = $this->mid;
            $rus = M('message_member')->where($wh)->save($add);
            $res = array();
            if($add['type'] == 1){
                $res['status'] = 1;
                $res['message'] = '设置成功';
            }else{
                $res['status'] = 0;
                $res['message'] = '取消成功';
            }
            return $res;

        }

//黑名单
    public function blacklist(){

        $map['uid'] = $this->mid;
        $map['fid'] = $this->data['fid'];
    	
		$blacklist = M('user_blacklist')->where($map)->find();

		if($blacklist){

            $res['blacklist'] = 1;
		}else{

            $res['blacklist'] = 0;
		}
        return $res;
    }

//搜索好友
    public function SearchFriends(){

        $user = array();
    	$name['phone'] = $name['email'] = "'".$this->data['num']."'"; //邮箱或手机
    	$user_id = M('user')->field('uid')->where("phone={$name['phone']} or email={$name['email']}")->find();
        $user['avatar'] = model('Avatar')->init($user_id['uid'])->getUserAvatar()['avatar_middle'];
        $user['uname'] = getUserName($user_id['uid']);
        $user['uid'] = $user_id['uid'];
    	if(!$user_id){
    		
            $user['status'] = 0;
            $user['message'] = '该用户不存在';
            unset($user['avatar']);
            unset($user['uname']);
            unset($user['uid']);
    	}else{

            $user['status'] = 1;
            $user['message'] = '';
        }

    	 return $user;
    }
//添加好友
    public function AddFriends(){
    	$add['uid'] = $this->mid; 
    	$add['fid'] = $this->data['fid'];
        if($add['uid'] == $add['fid']){

            $res = array();
            $res['status'] = 0;
            $res['message'] = '不能添加自己为好友';
            return $res;
        }
        $cz = M('user_follow')->where($add)->find();
        $cz1 = M('user_follow')->where("uid={$add['fid']} and fid={$add['uid']}")->find();
        if(!empty($cz) && empty($cz1)){
            $map['ctime'] = time();
            $follow = M('user_follow')->where($add)->save($map);
            if($follow){
                $add2['from_uid'] = $this->mid;
                $add2['uid'] = $this->data['fid'];
                $exis = M('user_follow_addfriends')->where($add2)->find();
                if($exis){

                    $mxm['des'] = $this->data['des'];
                    $mxm['ctime'] = time();
                    $friend = M('user_follow_addfriends')->where($add2)->save($mxm);
                    if($friend){
                        //显示消息数量
                        $add3['uid'] = $add['fid'];
                        $num = M('user_follow_num')->where($add3)->find();
                        if(empty($num)){
                            $add3['num'] = 1;
                            M('user_follow_num')->add($add3);
                        }else{
                            $numm['num'] = $num['num'] + 1;
                            M('user_follow_num')->where($add3)->save($numm);
                        }
                        $res = array();
                        $res['status'] = 1;
                        $res['message'] = '添加成功，请等待对方确认！';
                        return $res;
                    }

                }
                $add2['des'] = $this->data['des'];
                $add2['ctime'] = time();
                $friend = M('user_follow_addfriends')->add($add2);
                if($friend){
                    //显示消息数量
                    $add3['uid'] = $add['fid'];
                    $num = M('user_follow_num')->where($add3)->find();
                    if(empty($num)){
                        $add3['num'] = 1;
                        M('user_follow_num')->add($add3);
                    }else{
                        $numm['num'] = $num['num'] + 1;
                        M('user_follow_num')->where($add3)->save($numm);
                    }
                    $res = array();
                    $res['status'] = 1;
                    $res['message'] = '添加成功，请等待对方确认！';
                    return $res;
                }
            }

        }elseif(!empty($cz1) && !empty($cz)){
            $res = array();
            $res['status'] = 0;
            $res['message'] = '你与该用户已是好友';
            return $res;
        }elseif(empty($cz)){
            $add['ctime'] = time();
            $follow = M('user_follow')->add($add);
            if($follow){
                $add2['from_uid'] = $this->mid;
                $add2['uid'] = $this->data['fid'];
                $add2['des'] = $this->data['des'];
                $add2['ctime'] = time();
                $friend = M('user_follow_addfriends')->add($add2);
                if($friend){
                    //显示消息数量
                    $add3['uid'] = $add['fid'];
                    $num = M('user_follow_num')->where($add3)->find();
                    if(empty($num)){
                        $add3['num'] = 1;
                        M('user_follow_num')->add($add3);
                    }else{
                        $numm['num'] = $num['num'] + 1;
                        M('user_follow_num')->where($add3)->save($numm);
                    }
                    $res = array();
                    $res['status'] = 1;
                    $res['message'] = '添加成功，请等待对方确认！';
                    return $res;
                }
            }

        }else{
            $res = array();
            $res['status'] = 0;
            $res['message'] = '添加失败';
            return $res;
        }


    }

//收到添加请求列表
	public function AgreeFriendsList(){

        $map['uid'] = $this->mid;
        $num['num'] = 0;

        //清除七天内记录
        $aa = M('user_follow_addfriends')->where('uid='.$map['uid'].'ctime<'.(time() - (86400 * 7)));

        M('user_follow_num')->where($map)->save($num);
        $friends = M('user_follow_addfriends')->where($map)->select();
        foreach ($friends as $key => $value) {
                $res[$key]['avatar'] = model('Avatar')->init($value['from_uid'])->getUserAvatar()['avatar_middle'];
                $res[$key]['uname'] = getUserName($value['from_uid']);
                $res[$key]['uid'] = $value['from_uid'];
                if($value['des'] == 'null'){
                    $res[$key]['des'] = '';
                }else{
                    $res[$key]['des'] = $value['des'];
                }
                $res[$key]['ctime'] = $value['ctime'];
                $res[$key]['is_add'] = $value['is_add'];
                $follower_info = $this->get_user_info($value['uid']);
                $res[$key]['intro'] = $follower_info['intro'] ? formatEmoji(false, $follower_info['intro']) : '';
                $res[$key]['user_group'] = $follower_info['user_group'] ? $follower_info['user_group'] : '';
        }
        if($friends){
            self::success(array(
                    'status' => 1,
                    'message' => '操作成功',
                    'data' => $res,
                ));
        }else{
            self::error(array(
                'status'  => 0,
                'message' => '暂无数据',
            ));
        }
        
    }    
 
 //接受请求   
 	public function accept(){

        $add['uid'] = $this->mid;
        $add['from_uid'] = $this->data['fid'];
        $data['is_add'] = 1;
        $accept = M('user_follow_addfriends')->where($add)->save($data);
        if($accept){
            $add2['uid'] = $this->mid;
            $add2['ctime'] = time();
            $add2['fid'] = $this->data['fid'];
            $follow = M('user_follow')->add($add2);
            if($follow){
                $add3['from_uid'] = $this->mid;
                $add3['type'] = 1;
                $add3['member_num'] = 2;
                $add3['mtime'] = time();
                
               
                if($add3['from_uid'] > $add['from_uid']){

                    $add3['min_max'] = $add['from_uid'].'_'.$add3['from_uid'];
                }else{

                    $add3['min_max'] = $add3['from_uid'].'_'.$add['from_uid'];
                }
                M('message_list')->add($add3);

                $room = M('message_list')->field('list_id,mtime')->where($add3)->find();

                
                $add6['list_id'] = $room['list_id'];
                $add6['member_uid'] = $this->mid;
                $add6['new'] = 1;
                $add6['message_num'] = 1;
                $add6['ctime'] = $add6['list_ctime'] = $add3['mtime'];
                M('message_member')->add($add6);
                $add7['list_id'] = $room['list_id'];
                $add7['member_uid'] = $this->data['fid'];
                $add7['new'] = 1;
                $add7['message_num'] = 1;
                $add7['ctime'] = $add7['list_ctime'] = $add3['mtime'];
                M('message_member')->add($add7);

                $add4['list_id'] = $room['list_id'];
                $add4['content'] = '我已接受你的请求，我们可以开始聊天了！';
                $add4['from_uid'] = $this->mid;
                $add4['mtime'] = time();
                $con = M('message_content')->add($add4);
                $con = M('message_content')->field('message_id')->where($add4)->find();
                $aa['list_id'] = $room['list_id'];
                $aa['ctime'] = $add3['mtime'];
                $aa['message_id'] = $con['message_id'];
                $aa['uid'] = $this->data['fid'];
                M('message_push')->add($aa);

                $last_message['last_message'] = 'a:6:{s:8:"from_uid";i:'.$this->mid.';s:4:"type";s:4:"text";s:7:"list_id";i:'.$room['list_id'].';s:5:"mtime";i:'.$add3['mtime'].';s:7:"content";s:57:"我已接受你的请求，我们可以开始聊天了！";s:10:"message_id";s:3:"'.$con['message_id'].'";}';

                M('message_list')->where("list_id={$room['list_id']}")->save($last_message);

                $res = array();
                $res['status'] = 1;
                $res['message'] = '添加成功';
                return $res;
            }else{

                $res = array();
                $res['status'] = 0;
                $res['message'] = '添加失败';
                return $res;
            }
            
        }else{

            $res = array();
            $res['status'] = 0;
            $res['message'] = '添加失败';
            return $res;
        }
    }

//消息提示
    public function num(){

        $num = M('user_follow_num')->field('num')->where("uid={$this->mid}")->find();
        if($num){
            $num['status'] = 1;
            $num['message'] = '';
            return $num;
        }else{
            $num['status'] = 0;
            $num['message'] = '';
            return $num;
        }

        
    }

    /**
     * 获取用户信息 --using.
     *
     * @param int $uid
     *                 用户UID
     *
     * @return array 用户信息
     */
    public function get_user_info($uid)
    {
        $user_info = model('Cache')->get('user_info_api_'.$uid);
        if (!$user_info) {
            $user_info = model('User')->where('uid='.$uid)->field('uid,uname,sex,location,province,city,area,intro')->find();
            // 头像
            $avatar = model('Avatar')->init($uid)->getUserAvatar();
            // $user_info ['avatar'] ['avatar_middle'] = $avatar ["avatar_big"];
            // $user_info ['avatar'] ['avatar_big'] = $avatar ["avatar_big"];
            $user_info['avatar'] = $avatar;
            // 用户组
            $user_group = model('UserGroupLink')->where('uid='.$uid)->field('user_group_id')->findAll();
            foreach ($user_group as $v) {
                $user_group_icon = D('user_group')->where('user_group_id='.$v['user_group_id'])->getField('user_group_icon');
                if ($user_group_icon != -1) {
                    $user_info['user_group'][] = THEME_PUBLIC_URL.'/image/usergroup/'.$user_group_icon;
                }
            }
            model('Cache')->set('user_info_api_'.$uid, $user_info);
        }
        // 积分、经验
        $user_info['user_credit'] = model('Credit')->getUserCredit($uid);
        $user_info['intro'] && $user_info['intro'] = formatEmoji(false, $user_info['intro']);
        // 用户统计
        $user_info['user_data'] = model('UserData')->getUserData($uid);
        // 用户备注
        $user_info['remark'] = model('UserRemark')->getRemark($this->mid, $uid);
        //个人空间隐私权限
        $privacy = model('UserPrivacy')->getPrivacy($this->mid, $uid);
        $user_info['space_privacy'] = $privacy['space'];

        return $user_info;
    }    

}
