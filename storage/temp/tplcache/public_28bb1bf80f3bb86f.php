<?php $cancomment = intval(CheckPermission('core_normal','feed_comment')); 
$canfeedshare = CheckPermission('core_normal','feed_share');
$canfeeddel = CheckPermission('core_normal','feed_del');
$adminfeeddel = CheckPermission('core_admin','feed_del');
$canfeedreport = CheckPermission('core_normal','feed_report');
$adminchannelrecom = CheckPermission('channel_admin','channel_recommend');
$admintaskrecom = CheckPermission('vtask_admin','vtask_recommend'); ?>
<?php if($data){ ?>
  <?php if(is_array($data)): ?><?php $i = 0;?><?php $__LIST__ = $data?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vl): ?><?php ++$i;?><?php $mod = ($i % 2 )?><?php $cancomment_old = empty($vl['app_row_id'])  ? 0 : 1; ?>
    <dl class="feed_list" id="feed<?php echo ($vl["feed_id"]); ?>" model-node="feed_list">
      <dt class="face">
      <a href="<?php echo ($vl['user_info']['space_url']); ?>"><img src="<?php echo ($vl['user_info']['avatar_middle']); ?>" event-node="face_card" uid="<?php echo ($vl['user_info']['uid']); ?>"></a><?php if($vl['user_info']['group_icon_only']): ?><a href="javascript:;" title="<?php echo ($vl['user_info']['group_icon_only']['user_group_name']); ?>" class="group_icon_only"><img alt="<?php echo ($vl['user_info']['group_icon_only']['user_group_name']); ?>" src="<?php echo ($vl['user_info']['group_icon_only']['user_group_icon_url']); ?>" ></a><?php endif; ?>
    </dt>
  	<dd class="content">
      <?php echo W('FeedManage', array('feed_id'=>$vl['feed_id'], 'feed_uid'=>$vl['user_info']['uid'], 'is_recommend'=>$vl['is_recommend']));?>
      <em class="hover right">
            <?php if(($vl["actions"]["delete"])  ==  "true"): ?><?php if($adminfeeddel || ($vl['user_info']['uid'] == $GLOBALS['ts']['mid'] && $canfeeddel)): ?>
            <a href="javascript:;" event-node="delFeed" event-args="feed_id=<?php echo ($vl["feed_id"]); ?>&uid=<?php echo ($vl["user_info"]["uid"]); ?>&type=<?php echo ($vl["type"]); ?>"><?php echo L('PUBLIC_STREAM_DELETE');?></a>&nbsp;&nbsp;
            <?php endif; ?><?php endif; ?>

            <?php if($canfeedreport && ($vl['user_info']['uid'] != $GLOBALS['ts']['mid']) ): ?>
            <a href="javascript:;" event-node="denounce" event-args="aid=<?php echo ($vl["feed_id"]); ?>&type=feed&uid=<?php echo ($vl["user_info"]["uid"]); ?>"><?php echo L('PUBLIC_STREAM_REPORT');?></a>&nbsp;&nbsp;
            <?php endif; ?>
      </em>
      <?php if(($vl["is_del"])  ==  "0"): ?><p class="hd">
        <?php echo ($vl['user_info']['space_link']); ?>
        <?php if(in_array($vl['user_info']['uid'], $followUids)): ?>
        <?php echo W('Remark',array('uid'=>$vl['user_info']['uid'],'remark'=>$remarkHash[$vl['user_info']['uid']],'showonly'=>1));?>
        <?php endif; ?>
        <span class="title-from hidden"><i class="ico-weiba"></i>发表在 </span>
      </p>
      <div class="contents clearfix"><?php echo (format($vl["body"],true)); ?></div>
      <p class="info">
        <span class="right">
          <?php if(in_array('comment',$weibo_premission)): ?>
          <?php if(($vl["actions"]["comment"])  ==  "true"): ?><a event-node="comment" href="javascript:void(0)" event-args='row_id=<?php echo ($vl["feed_id"]); ?>&app_uid=<?php echo ($vl["api_source"]["source_user_info"]["uid"]); ?>&app_row_id=<?php echo ($vl["app_row_id"]); ?>&app_row_table=<?php echo ($vl["app_row_table"]); ?>&to_comment_id=0&to_uid=0&app_name=<?php echo ($vl["app"]); ?>&table=feed&cancomment=<?php echo ($cancomment); ?>&cancomment_old=<?php echo ($cancomment_old); ?>'><?php echo L('PUBLIC_STREAM_COMMENT');?><?php if(($vl["comment_count"])  !=  "0"): ?>(<?php echo ($vl["comment_count"]); ?>)<?php endif; ?></a>
          <i class="vline">|</i><?php endif; ?>
          <?php endif; ?>

          <?php if( $GLOBALS['ts']['mid'] != 0 ): ?><?php if(in_array('repost', $weibo_premission) && $canfeedshare): ?>
            <?php if(($vl["actions"]["repost"])  ==  "true"): ?><?php $sid = !empty($vl['app_row_id'])? $vl['app_row_id'] : $vl['feed_id'];
            $cancomment_old = in_array($vl['type'],$cancomment_old_type) ? 1 : 0; ?>
            <?php echo W('Share',array('sid'=>$sid,'stable'=>$vl['app_row_table'],'initHTML'=>'','current_table'=>'feed','current_id'=>$vl['feed_id'],'nums'=>$vl['repost_count'],'appname'=>$vl['app'],'cancomment'=>$cancomment_old,'feed_type'=>$vl['type'],'is_repost'=>$vl['is_repost'],'enode'=>'open_share'));?>
            <i class="vline">|</i>
            <?php endif; ?><?php endif; ?>
            <?php else: ?>
            <?php echo W('Share',array('sid'=>$sid,'stable'=>$vl['app_row_table'],'initHTML'=>'','current_table'=>'feed','current_id'=>$vl['feed_id'],'nums'=>$vl['repost_count'],'appname'=>$vl['app'],'cancomment'=>$cancomment_old,'feed_type'=>$vl['type'],'is_repost'=>$vl['is_repost'],'enode'=>'open_share'));?>
            <i class="vline">|</i><?php endif; ?>

          <?php if(($vl["actions"]["favor"])  ==  "true"): ?><?php echo W('Collection',array('type'=>$type,'sid'=>$vl['feed_id'],'stable'=>'feed','sapp'=>$vl['app']));?><?php endif; ?>
          <i class="vline">|</i>

          <?php echo W('Digg',array('feed_id'=>$vl['feed_id'],'digg_count'=>$vl['digg_count'],'diggArr'=>$diggArr));?>
        </span>
        <span>
          <a class="date" date="<?php echo ($vl["publish_time"]); ?>" href="<?php echo U('public/Profile/feed',array('feed_id'=>$vl['feed_id'],'uid'=>$vl['uid']));?>"><em><?php echo (friendlydate($vl["publish_time"])); ?></em><em style="display:none;">查看详情</em></a>
          <span><?php echo ($vl['from']); ?></span>
        </span>
      </p>
      <div class="infopen"><div class="trigon"></div></div>
      <div class="forward_box">
      	<div class="bdsharebuttonbox share_feedlist clearfix" data-tag="share_feedlist">
              <a href="javascript:;" class="bds_tsina" data-cmd="tsina" title="分享到新浪微博">新浪微博</a>
              <a href="javascript:;" class="bds_weixin" data-cmd="weixin" title="分享到微信">微信</a>
              <a href="javascript:;" class="bds_tqq" data-cmd="tqq" title="分享到腾讯微博">腾讯微博</a>
              <a href="javascript:;" class="bds_qzone" data-cmd="qzone" title="分享到QQ空间">QQ空间</a>
              <a href="javascript:;" class="bds_renren" data-cmd="renren" title="分享到人人网">人人网</a>
              <a href="javascript:;" class="bds_douban" data-cmd="douban" title="分享到豆瓣网">豆瓣网</a>
              <?php echo W('Share',array('sid'=>$sid,'stable'=>$vl['app_row_table'],'initHTML'=>'','current_table'=>'feed','current_id'=>$vl['feed_id'],'nums'=>$vl['repost_count'],'appname'=>$vl['app'],'cancomment'=>$cancomment_old,'feed_type'=>$vl['type'],'is_repost'=>$vl['is_repost'], 'text'=>'我的主页', 'title'=> '分享到我的主页', 'class'=>'repost'));?>
              <a href="javascript:;" class="bds_count" data-cmd="count"></a>
        </div>
      </div>
      <div model-node="comment_detail" class="repeat clearfix" style="display:none;"></div>
      <!--
      <div class="praise-list clearfix" style="display:none;">
        <i class="arrow arrow-t"></i>
        <ul>
          <li><a href=""><img src="<?php echo ($vl['user_info']['avatar_small']); ?>" width="30" height="30"/></a><a href="" class="ico-close1"></a></li>
          <li><a href=""><img src="<?php echo ($vl['user_info']['avatar_small']); ?>" width="30" height="30"/></a></li>
          <li><a href=""><img src="<?php echo ($vl['user_info']['avatar_small']); ?>" width="30" height="30"/></a></li>
          <li><a href=""><img src="<?php echo ($vl['user_info']['avatar_small']); ?>" width="30" height="30"/></a></li>
          <li><a href=""><i class="arrow-next-page"></i></a></li>
        </ul>
      </div>
      -->
     	<?php else: ?>
        <p><?php echo L('PUBLIC_INFO_ALREADY_DELETE_TIPS');?></p>
        <p class="info">
        <?php if(($vl["actions"]["favor"])  ==  "true"): ?><?php echo W('Collection',array('type'=>$type,'sid'=>$vl['feed_id'],'stable'=>'feed','sapp'=>$vl['app']));?><?php endif; ?>
        </p><?php endif; ?> 
      </dd>
      <dt class="xline"></dt>
    </dl><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
<?php }else{ ?>

<?php if($load_count != ''): ?><dl class="feed_list" style="text-align:center"><p class="p20">暂时没有更多可显示的内容哟~</p></dl>
<?php else: ?>
  <dl class="feed_list" style="min-height:500px">
    <div style="padding-top:80px;text-align:center">
    <p class="mb10"><i class="icon-bed"></i></p>
    <p style="color:#333">暂时没有更多可显示的内容哟~</p>
    </div>
  </dl><?php endif; ?>
<?php } ?>
<script>
function doHighlight(a,b){
    highlightStartTag="<span style='color:red'>";
    highlightEndTag="</span>";
    var c="";
    var i=-1;
    var d=b.toLowerCase();
    var e=a.toLowerCase();
    while(a.length>0){
        i=e.indexOf(d,i+1);
        if(i<0){
            c+=a;
            a="";
        }else{
            if(a.lastIndexOf(">",i)>=a.lastIndexOf("<",i)){
                if(e.lastIndexOf("/script>",i)>=e.lastIndexOf("<script",i)){
                    c+=a.substring(0,i)+highlightStartTag+a.substr(i,b.length)+highlightEndTag;
                    a=a.substr(i+b.length);e=a.toLowerCase();
                    i=-1;
                }
            }
        }
    }
    return c;
};

$.fn.highlight=function(z){
    $(this).each(
        function(){
            if (z !== null) {
              $(this).html(doHighlight($(this).html(),z));
            }
        });
    return this;
}

$(document).ready(function(){
  if(!'<?php echo ($topic_id); ?>' && '<?php echo ($feed_key); ?>'){
  	var key3 = '<?php echo ($feed_key); ?>';
    $('.contents').highlight(key3);
    M($('#feed-lists')[0]);
  }
});
/**
 * 时间更新效果
 * return void
 */
$(document).ready(function() {
  var formatTime = '';
  $('.date').live({
    mouseover: function() {
      var width = $(this).find('em').first().width();
      $(this).find('em').first().hide();
      $(this).find('em').last().css({display:'inline-block', width: width});
      $(this).find('em').last().css({minWidth:'50px'});
    },
    mouseout: function() {
      $(this).find('em').first().show();
      $(this).find('em').last().hide();
    }
  });

	var wTime = parseInt("<?php echo time();?>");
	var updateTime = function()
	{
		$('.date').each(function(i, n) {
			var date = $(this).attr('date');
			if(typeof date !== 'undefined') {
				$(this).find('em').first().html(core.weibo.friendlyDate(date, wTime));
			}
		});	
	};
	//updateTime();
	setInterval(function() {
		wTime += 10;
		updateTime();
	}, 10000);
	//
	$('.title-from').each(function(index, element) {
		var html = $(element).parents('dd').find('.title-from-source').html();
		if(html) {
			$(element).html(html).show();
		}else{
			$(element).hide();
		}
    });
});
//解决异步加载分享无效的问题
if(core.bdshare) core.bdshare.init();
</script>