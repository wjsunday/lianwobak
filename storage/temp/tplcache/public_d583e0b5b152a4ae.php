<?php if(($body)  ==  ""): ?>分享分享<?php endif; ?> 
<?php echo (replaceurl(t($body))); ?>
<dl class="comment">
	<?php if($sourceInfo['is_del'] == 0 && $sourceInfo['source_user_info'] != false): ?>
	<dd class="com-info clearfix">
		<?php if(!empty($sourceInfo['attach'])): ?>

		<?php echo constant(" 附件分享 *");?>
		<?php if(($sourceInfo["feedType"])  ==  "postfile"): ?><ul class="feed_file_list">
			<?php if(is_array($sourceInfo["attach"])): ?><?php $i = 0;?><?php $__LIST__ = $sourceInfo["attach"]?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><li>
				<a href="<?php echo U('widget/Upload/down',array('attach_id'=>$vo['attach_id']));?>" class="current right" target="_blank"><i class="ico-down"></i></a>
				<i class="ico-<?php echo ($vo["extension"]); ?>-small"></i>
				<a href="<?php echo U('widget/Upload/down',array('attach_id'=>$vo['attach_id']));?>"><?php echo ($vo["attach_name"]); ?></a>
				<span class="tips">(<?php echo (byte_format($vo["size"])); ?>)</span>
			</li><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>			
		</ul><?php endif; ?>

		<?php echo constant(" 图片分享 *");?>
		<?php if(($sourceInfo["feedType"])  ==  "postimage"): ?><div class="feed_img" rel='small' >
			<ul class="small">
				<?php 
                $attachCount = count($sourceInfo['attach']);
                $sourceInfo['attach'] = array($sourceInfo['attach'][0]);
                ?>
                <?php if(is_array($sourceInfo["attach"])): ?><?php $i = 0;?><?php $__LIST__ = $sourceInfo["attach"]?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><li class="m0">
                    <a href="javascript:void(0)" onclick="core.weibo.showBigImage(<?php echo ($sourceInfo['feed_id']); ?>, <?php echo ($i); ?>);">
                        <img class="imgicon" src='<?php echo ($vo["attach_small"]); ?>' title='点击放大' width="100" height="100">
                        <?php if($attachCount>1){ ?><span class="pic-more"><?php echo ($attachCount); ?> photos</span><?php } ?>
                    </a>
                </li><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
			</ul>
		</div>
		<div class="feed_txt">
	       <?php echo constant(" 转发原文 *");?>
           <span class="source_info"><?php echo ($sourceInfo['source_user_info']['space_link']); ?><em>&nbsp;&nbsp;<?php echo (friendlydate($sourceInfo['publish_time'])); ?><!--&nbsp;<?php echo getFromClient($sourceInfo['from']);?>--></em></span>
		   <p class="txt-mt" onclick="core.weibo.clickRepost(this);" href="javascript:core.weibo.showBigImage(<?php echo ($sourceInfo['feed_id']); ?>, <?php echo ($i); ?>);"><?php echo msubstr(t($sourceInfo['source_content']),0,100);?></p>
		</div><?php endif; ?>

		<?php else: ?>

			<?php echo constant(" 视频分享 *");?>
			<?php if(($sourceInfo["feedType"])  ==  "postvideo"): ?><div class="feed_img" id="video_mini_show_<?php echo ($feedid); ?>_<?php echo ($sourceInfo['feed_id']); ?>">
					  <a href="javascript:void(0);" <?php if(!$sourceInfo['transfering']){ ?>onclick="switchVideo(<?php echo ($sourceInfo['feed_id']); ?>,<?php echo ($feedid); ?>,'open','<?php echo ($sourceInfo["host"]); ?>','<?php echo ($sourceInfo["flashvar"]); ?>','<?php echo strpos($sourceInfo['flashimg'], '://')?$sourceInfo['flashimg']:getImageUrl($sourceInfo['flashimg'], 150, 100);?>')"<?php } ?> >
					    <img src="<?php echo strpos($sourceInfo['flashimg'], '://')?$sourceInfo['flashimg']:getImageUrl($sourceInfo['flashimg'], 120, 120, true);?>" style="width:100px;height:100px;overflow:hidden;" data-medz-name="user-outside-video"  onerror="javascript:var default_img = THEME_URL + '/image/video_bk.png';$(this).attr('src',default_img);">
					  </a>
					  <div class="video_play" ><a href="javascript:void(0);" <?php if(!$sourceInfo['transfering']){ ?>onclick="switchVideo(<?php echo ($sourceInfo['feed_id']); ?>,<?php echo ($feedid); ?>,'open','<?php echo ($sourceInfo["host"]); ?>','<?php echo ($sourceInfo["flashvar"]); ?>','<?php echo ($sourceInfo["flashimg"]); ?>')"<?php } ?> ></a>
					  </div>
				</div>
                <div class="feed_txt feed_txt_video">
                   <?php echo constant(" 转发原文 *");?>
                   <span class="source_info"><?php echo ($sourceInfo['source_user_info']['space_link']); ?><em>&nbsp;&nbsp;<?php echo (friendlydate($sourceInfo['publish_time'])); ?><!--&nbsp;<?php echo getFromClient($sourceInfo['from']);?>--></em></span>
                   <p class="txt-mt" onclick="core.weibo.clickRepost(this);" href="<?php echo U('public/Profile/feed',array('uid'=>$sourceInfo['uid'],'feed_id'=>$sourceInfo['feed_id']));?>"><?php echo msubstr(t($sourceInfo['source_content']),0,100);?></p>
                </div>
				<div class="feed_quote" style="display:none;" id="video_show_<?php echo ($feedid); ?>_<?php echo ($sourceInfo['feed_id']); ?>">
				  <div class="q_tit">
				    <img class="q_tit_l" onclick="switchVideo(<?php echo ($sourceInfo['feed_id']); ?>,<?php echo ($feedid); ?>,'open','<?php echo ($sourceInfo["host"]); ?>','<?php echo ($sourceInfo["flashvar"]); ?>','<?php echo ($sourceInfo["flashimg"]); ?>')" src="__THEME__/image/zw_img.gif" />
				  </div>
				  <div class="q_con"> 
				    <p style="margin:0;margin-bottom:5px" class="cGray2 f12">
				    <a href="javascript:void(0)" onclick="switchVideo(<?php echo ($sourceInfo['feed_id']); ?>,<?php echo ($feedid); ?>,'close')"><i class="ico-pack-up"></i>收起</a>
				    
				    </p>
				    <div id="video_content_<?php echo ($feedid); ?>_<?php echo ($sourceInfo['feed_id']); ?>"></div>
				  </div>
				  <!--<div class="q_btm"><img class="q_btm_l" src="__THEME__/image/zw_img.gif" /></div>-->
				</div><?php endif; ?>

			<?php if(($sourceInfo["feedType"])  ==  "post"): ?><div class="feed_txt feed_txt_default">
	        <?php echo constant(" 转发原文 *");?>
            <span class="source_info"><?php echo ($sourceInfo['source_user_info']['space_link']); ?><em>&nbsp;&nbsp;<?php echo (friendlydate($sourceInfo['publish_time'])); ?><!--&nbsp;<?php echo getFromClient($sourceInfo['from']);?>--></em></span>
		    <p class="txt-mt" onclick="core.weibo.clickRepost(this);" href="<?php echo U('public/Profile/feed',array('uid'=>$sourceInfo['uid'],'feed_id'=>$sourceInfo['feed_id']));?>"><?php echo msubstr(t($sourceInfo['source_content']),0,128);?></p>
			</div><?php endif; ?>

		<?php endif; ?>
	</dd>
	<?php else: ?>
	<dd class="name">内容已被删除</dd>
	<?php endif; ?>
</dl>