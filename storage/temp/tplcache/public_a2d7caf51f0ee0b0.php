<div id="feed-lists" class="feed_lists clearfix">
    <!-- <a class="notes" href="javascript:void(0)">有1条新分享</a> -->
    <?php echo ($list); ?>
    <?php if($type === 'channel' && !$channel): ?>
      <dl class="feed_list" style="min-height:500px">
	    <div style="padding-top:80px;text-align:center">
	    <p class="mb10"><i class="icon-bed"></i></p>
	    <p style="color:#333">抱歉，您还没有关注任何一个频道，立刻去&nbsp;<a style="color:#0096e6" href="<?php echo U('channel/Index/index');?>">频道&nbsp;</a>看看</p>
	    </div>
	  </dl>
    <?php endif; ?>
	
	<?php if(($loadmore)  !=  "1"): ?><div id="page" class="page"><?php echo ($html); ?></div><?php endif; ?>
</div>
	
<script type="text/javascript">
var firstId = '<?php echo ($firstId); ?>';
var loadId = '<?php echo ($lastId); ?>';
var maxId = '<?php echo ($firstId); ?>';
var feedType = '<?php echo ($type); ?>';
var loadmore = '<?php echo ($loadmore); ?>';
var loadnew = '<?php echo ($loadnew); ?>';
var feed_type = '<?php echo ($feed_type); ?>';
var feed_key = '<?php echo ($feed_key); ?>';
var fgid = '<?php echo ($fgid); ?>';
var topic_id = '<?php echo ($topic_id); ?>';
</script>