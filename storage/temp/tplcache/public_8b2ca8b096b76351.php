<div id="check" style="height:90x;cursor:pointer;"  onclick="checkin();">
	<div class="<?php if($ischeck){ ?>sign-in-h<?php }else{ ?>sign-in<?php } ?>" id="checkdiv">
		<span class="datetime"><em class="date"><?php echo ($day); ?></em>
		<em class="week"><?php echo ($week); ?></em></span>
		<span class="days">
		<em class="day">DAYS</em><em class="num" id="con_num_day"><?php echo ($con_num); ?></em></span>
	<?php if($ischeck){ ?>
	<em id="checkin" class="btn-sign-h">已签到</em>
	<?php }else{ ?>
	<em href="javascript:void(0)" id="checkin" class="btn-sign">签到</em>
	<?php } ?>
	   <div class="sign-wrap" style="display:none" id="checkdetail">
	      <i class="arrow-y"></i>
	      <div class="sign-box">
		    <h3 id="checkinfo"><?php if($ischeck){ ?>签到成功，获取积分<?php echo ($credit["score"]); ?>分<?php }else{ ?>未签到<?php } ?></h3>
		    <div class="sign-info"><p>已连续签到<font id="con_num"><?php echo ($con_num); ?></font>天，累计签到<font id="total_num"><?php echo ($total_num); ?></font>天</p></div>
	      </div>
	   </div>
	</div>
</div>
<div class="clearfix mb20 pb20 border-b" style="display:none;">
	<a href="<?php echo U('public/Task/index');?>" class="btn-task left" target="_blank"><span>做任务</span></a>
	<a href="<?php echo U('public/Rank/index');?>" class="btn-account right" target="_blank"><span>比排名</span></a>
</div>

<script>
var isshow = 1;
$(function (){
	<?php if($ischeck){ ?>
	$('#check').hover(function (){
			$("#check").stop().animate({
				height:'170px'
			},500);
			$('#checkdetail').show();
			$('#checkdetail').stop().animate({
				opacity: 1
			},500);
	},function (){
			$("#check").stop().animate({
				height:'90px'
			});
			$('#checkdetail').stop().animate({
				opacity: 0
			},500);
	});
	<?php } ?>
});
function checkin(){
	//未登录
	if( MID == 0 ){
		ui.quicklogin();
		return;
	}
	var credit_score = "<?php echo ($credit["score"]); ?>";
	// $('#checkin').text('已签到');
	// $('#checkin').attr('onclick' , '');
	// $('#checkin').attr('class' , 'btn-sign-h');
	// $('#checkdiv').attr('class' , 'sign-in-h' )
	// $('#checkinfo').text('签到成功，获取积分<?php echo ($credit["score"]); ?>分');
	var totalnum = <?php echo ($total_num); ?> + 1;
	$.post(U('widget/CheckIn/check_in') , {} , function (res){
		if(res){
			$('#checkin').text('已签到');
			$('#checkin').attr('class' , 'btn-sign-h');
			$('#checkdiv').attr('class' , 'sign-in-h' );
			$('#checkinfo').text('签到成功，获取积分' + credit_score + '分');
			
			var connum = res;
			$('#con_num').text(connum);
			$('#con_num_day').text(connum);
			$('#total_num').text(totalnum);
			$('#check').hover(function (){
					$("#check").stop().animate({
						height:'170px'},500);
					$('#checkdetail').show();
					$('#checkdetail').stop().animate({
						opacity: 1
					},500);
			},function (){
					$("#check").stop().animate({
						height:'90px'
					},500);
					$('#checkdetail').stop().animate({
						opacity: 0
					},500);
			});
		}
	});
}
</script>