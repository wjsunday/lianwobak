<?php if(is_array($user)): ?><?php $i = 0;?><?php $__LIST__ = $user?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><li model-node="related_li" class="mb20">
        <div class="user left">
          <a event-node="face_card" uid="<?php echo ($vo["userInfo"]["uid"]); ?>" href="<?php echo ($vo["userInfo"]["space_url"]); ?>" title="<?php echo ($vo["userInfo"]["uname"]); ?>" class="face">
            <img  src="<?php echo ($vo["userInfo"]["avatar_middle"]); ?>"/>
          </a>
        </div>
        <div class="user-prof left">
          <a class="mb10 name" href="<?php echo ($vo["userInfo"]["space_url"]); ?>"><?php echo ($vo["userInfo"]["uname"]); ?></a>
          <p>
          <?php echo ($vo["info"]["msg"]); ?>
          </p>
        </div> 
        
        <?php echo W('FollowBtn',array('fid'=>$vo['userInfo']['uid'],'uname'=>$vo['userInfo']['uname'],'follow_state'=>$vo['followState'],'refer'=>'following_right', 'type'=>'related'));?>
        

<!--        <div class="left"><a class="btns-red mt10"><i class="ico-add"></i>关注</a></div>-->
       
	</li><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>