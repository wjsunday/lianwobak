<?php if(is_array($data)): ?><?php $i = 0;?><?php $__LIST__ = $data?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><?php if($vo['display_type'] == 3): ?>
	<div id="ad_<?php echo ($vo["ad_id"]); ?>" class="flashNews" style="<?php if(($top)  !=  "0"): ?>margin-top:<?php echo ($top); ?>px;<?php endif; ?><?php if(($bottom)  !=  "0"): ?>margin-bottom:<?php echo ($bottom); ?>px;<?php endif; ?><?php if(($width)  !=  "0"): ?>width:<?php echo ($width); ?>px;<?php echo $height?'height:'.$height.'px;':''; ?>overflow:hidden;<?php endif; ?>">
		<?php if(is_array($vo["content"])): ?><?php $i = 0;?><?php $__LIST__ = $vo["content"]?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$v): ?><?php ++$i;?><?php $mod = ($i % 2 )?><div <?php if(($i)  !=  "1"): ?>style="display:none;"<?php endif; ?>>
			<p><a target="_blank" href="<?php echo ($v["bannerurl"]); ?>"><img width="<?php echo ($width); ?>"<?php echo $height?' height="'.$height.'"':''; ?> src="<?php echo ($v["bannerpic"]); ?>" /></a></p>
		</div><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
		<?php if(count($vo['content']) > 1): ?>
		<ul></ul>
		<script type="text/javascript">
		new fSwitchPic("ad_<?php echo ($vo["ad_id"]); ?>", 5000);
		</script>
		<?php endif; ?>
	</div>
	<?php else: ?>
	<div style="<?php if(($top)  !=  "0"): ?>margin-top:<?php echo ($top); ?>px;<?php endif; ?><?php if(($bottom)  !=  "0"): ?>margin-bottom:<?php echo ($bottom); ?>px;<?php endif; ?><?php if(($width)  !=  "0"): ?>width:<?php echo ($width); ?>px;<?php echo $height?'height:'.$height.'px;':''; ?>overflow:hidden;<?php endif; ?>"><?php echo str_ireplace('<img','<img width="'.$width.'"'.($height?' height="'.$height.'"':''),$vo['content']);?></div>
	<?php endif; ?><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>