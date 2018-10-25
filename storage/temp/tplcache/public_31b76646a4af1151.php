<div id="weibo_admin_box" class="layer-list" style="display:none;z-index:1;">
	<ul>
    	<?php if($feed_recommend == 1 && $is_recommend){ ?>
        <li><a href="javascript:;" onclick="feed_recommend('<?php echo ($feed_id); ?>', 0);">取消推荐</a></li>
        <?php }elseif($feed_recommend == 1){ ?>
        <li><a href="javascript:;" onclick="feed_recommend('<?php echo ($feed_id); ?>', 1);">推荐动态</a></li>
		<?php } ?>

		<?php if($feed_del){ ?>
		<li><a href="javascript:void(0)" event-node ='delFeed' event-args='feed_id=<?php echo ($feed_id); ?>&uid=<?php echo ($uid); ?>'>删除动态</a></li>
		<?php } ?>

		<?php if($channel_recommend == 1): ?>
		<li><a href="javascript:;" onclick="getAdminBox('<?php echo ($feed_id); ?>', '<?php echo ($channel_id); ?>', '<?php echo ($clear); ?>');">推荐到频道</a></li>
		<?php endif; ?>
		<?php if($vtask_recommend == 1): ?>
		<li><a href="javascript:;" onclick="addToVtask('<?php echo ($feed_id); ?>');">添加到事务</a></li>
		<?php endif; ?>
		<?php if($feed_del == 1): ?>
		<?php endif; ?>
		<?php echo Addons::hook('feed_manage_li', array('feed_id'=>$feed_id, 'uid'=>$uid));?>
	</ul>
</div>