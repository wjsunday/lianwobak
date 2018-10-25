<p><?php echo (replaceurl(t($body))); ?></p>
<div class="feed_img_lists" >
<ul class="small">

<?php $attachCount=count($attachInfo); ?>
<?php if(is_array($attachInfo)): ?><?php $i = 0;?><?php $__LIST__ = $attachInfo?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><li rel="<?php echo ($vo["attach_id"]); ?>" <?php echo ($attachCount==1?'style="width:205px;height:auto"':''); ?>>
		<a href="javascript:void(0);" onclick="core.weibo.showBigImage(<?php echo ($feedid); ?>, <?php echo ($i); ?>)" >
		   <img <?php if($attachCount==1): ?>onload="/*仅标签上有效，待改进*/;var li=$(this).parents('li');if(li.height()>300){li.css('height','300px');li.find('.pic-btm').show();}" <?php endif; ?>class="imgicon" src='<?php echo ($attachCount==1?$vo['attach_medium']:$vo['attach_small']); ?>' title='点击放大' >
		   <!--共有<?php echo ($attachCount); ?>张图片-->
           <?php echo ($attachCount==1?'<span class="pic-btm hidden">点击查看完整图片</span>':''); ?>
		</a>
	</li><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
</ul>
</div>
<div class="feed_img_lists" rel='big' style='display:none'>
<ul class="feed_img_list big" >
<span class='tools'>
	<a href="javascript:;" event-node='img_big'><i class="ico-pack-up"></i>收起</a>
	<a target="_blank" href="<?php echo ($vo["attach_url"]); ?>"><i class="ico-show-big"></i>查看大图</a>
	<a href="javascript:;" onclick="revolving('left', <?php echo ($feedid); ?>)"><i class="ico-turn-l"></i>向左转</a>
	<a href="javascript:;" onclick="revolving('right', <?php echo ($feedid); ?>)"><i class="ico-turn-r"></i>向右转</a>
</span>
<?php if(is_array($attachInfo)): ?><?php $i = 0;?><?php $__LIST__ = $attachInfo?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><li title='<?php echo ($vo["attach_url"]); ?>'>
	<!-- <a onclick="core.weibo.showBigImage(<?php echo ($feedid); ?>);" target="_blank" class="ico-show-big" title="查看大图" ></a> -->
	<a href="javascript:void(0)" event-node='img_big'><img maxwidth="557" id="image_index_<?php echo ($feedid); ?>" class="imgsmall" src='<?php echo ($vo["attach_middle"]); ?>' title='点击缩小' ></a>
</li><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
</ul>
</div>