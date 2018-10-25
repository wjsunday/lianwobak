<div class="right-box clearfix">
  <fieldset class="inter-line mb20">
    <legend class="inter-txt">热门活动</legend>
  </fieldset>
  <ul class="adslist">
    <?php if(is_array($user)): ?><?php $i = 0;?><?php $__LIST__ = $user?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><li><a href="<?php echo U('event/Index/eventDetail','id='.$vo['id'].'&uid='.$vo['uid']);?>"><img src="<?php echo (getimageurlbyattachid($vo["coverId"])); ?>" /></a></li><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
  </ul>
</div>

<script src="__APP__/login.js" type="text/javascript"></script>