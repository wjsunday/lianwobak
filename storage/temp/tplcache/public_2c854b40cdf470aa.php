<?php if($topic_list){ ?>
<!--推荐话题-->
<div class="hot-topic">
        <fieldset class="inter-line"><legend class="inter-txt"><?php echo ($title); ?></legend><a href="<?php echo U('public/Topic/topic_list');?>" class="right" style="font-size:12px;margin-top:-18px;">更多</a></fieldset>
        <ul>
        	<?php if(is_array($topic_list)): ?><?php $i = 0;?><?php $__LIST__ = $topic_list?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><li>
        			<p><a href="<?php echo U('public/Topic/index',array('k'=>urlencode($vo['topic_name'])));?>"><?php echo ($vo['topic_name']); ?></a>（<?php echo ($vo['count']); ?>）</p>
        		</li><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
        </ul>
</div>
<?php } ?>
<script>
/*$('.hot-topic .ico-refresh').click(function(){
	var url = "<?php echo U('widget/TopicList/refresh');?>";
	var obj = $(this).parent().parent().parent().find('ul');
	$.post(url,{id:1},function(html){
		obj.html(html);
	});	
});*/
</script>