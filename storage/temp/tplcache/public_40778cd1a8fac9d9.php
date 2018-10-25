<div class="contents clearfix">
			<div class="mb10 clearfix"><?php echo (replaceurl(t($body))); ?></div>
			<div class="feed_img" id="video_mini_show_<?php echo ($feedid); ?>">
          <a href="javascript:void(0);" <?php if(!$transfering){ ?>onclick="switchVideo(<?php echo ($feedid); ?>,0,'open','<?php echo ($host); ?>','<?php echo ($flashvar); ?>','<?php if (strpos($flashimg, '://')) {
    echo $flashimg;
} else {
    echo getImageUrl($flashimg);
} ?>')"<?php } ?> >
            <img src="<?php if (strpos($flashimg, '://')) {
    echo $flashimg;
} else {
    echo getImageUrl($flashimg, 205);
} ?>" style="width:205px;height:auto;overflow:hidden" data-medz-name="outside-video" onerror="javascript:var default_img = THEME_URL + '/image/video_bk.png';$(this).attr('src',default_img);">
          </a>
          <div class="video_play" ><a href="javascript:void(0);" <?php if(!$transfering){ ?>onclick="switchVideo(<?php echo ($feedid); ?>,0,'open','<?php echo ($host); ?>','<?php echo ($flashvar); ?>','<?php echo ($flashimg); ?>')"<?php } ?> ></a>
          </div>
      </div>
    <div class="feed_quote" style="display:none;" id="video_show_<?php echo ($feedid); ?>"> 
      <div class="q_tit">
        <img class="q_tit_l" onclick="switchVideo(<?php echo ($feedid); ?>,0,'open','<?php echo ($host); ?>','<?php echo ($flashvar); ?>','<?php echo ($flashimg); ?>')" src="__THEME__/image/zw_img.gif" />
      </div>
      <div class="q_con">
        <p style="margin:0;margin-bottom:5px" class="cGray2 f12">
        <a href="javascript:void(0)" onclick="switchVideo(<?php echo ($feedid); ?>,0,'close')"><i class="ico-pack-up"></i>收起</a>
        <?php if($source){ ?>
          &nbsp;&nbsp;|&nbsp;&nbsp;<a href="<?php echo ($source); ?>" target="_blank"><i class="ico-show-all"></i><?php echo ($title); ?></a>
        <?php } ?>
        </p>
        <div id="video_content_<?php echo ($feedid); ?>"></div>
      </div>
      <div class="q_btm"><img class="q_btm_l" src="__THEME__/image/zw_img.gif" /></div>
    </div>
  </div>