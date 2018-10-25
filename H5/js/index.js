// JavaScript Document

$(window).load(function(){	

	//首页红包
	$(".all").show()
	$(".redbg").hide()
	$(".right dd:nth-child(4) input").click(function(){
		$(".redbg").fadeIn(200);
	})
	$(".close").click(function(){
		$(".redbg").fadeOut(200);
	})
	$(".yuang2").click(function(){
		$(".yuang").hide();
		$(".yuang2").css({"opacity":"1"})
		$(".yuang2").css({"transform":"rotate(1800deg)"})
		 setTimeout(function(){//两秒后跳转  
         location.href = "hb_end.html"},3000);  
	})
	
	$(".indexhb_close").click(function(){
		$(".indexHbBg").fadeOut(200);
	})
	
	$(".indexhb_yuang2").click(function(){
		$(".indexhb_yuang").hide();
		$(".indexhb_yuang2").css({"opacity":"1"})
		$(".indexhb_yuang2").css({"transform":"rotate(1800deg)"})
		 setTimeout(function(){//两秒后跳转  
         location.href = "hb_end.html"},3000);  
	})	
	
	$(".sx").click(function(){
		window.location.search='?'+Math.random()
	})
	//首页即抢100--500
	$(".nav_mn a").eq(0).css({"color":"#dcbe85","text-decoration":"underline"});
	$(".nav_mn a").click(function(){
		$(".nav_mn a").css({"color":"#999e9f","text-decoration":"none"})
		$(this).css({"color":"#dcbe85","text-decoration":"underline"});
		// var tid = $(this).find('input').val();
		// $.ajax({
		// 		url:,
		// 		data:"tid="+tid,
		//         type:'post',
		//         dateType:'json',
		//         success:function(res){},
		// });
	})
	//首页焦点图
	timer = setInterval(roll,3000)
	function roll(){
		var num = $(".btn li").index($(".on"))
		var leftA = -1080*(num+1);
		if(num == 2){		  
		  $(".bigpic").animate({"left":0},500)
		  $(".btn li").eq(0).addClass("on").siblings().removeClass("on");
		}else{
		  $(".bigpic").animate({"left": leftA},500)	
		  $(".btn li").eq(num+1).addClass("on").siblings().removeClass("on") 
		  }
		}		
		
		$(".focus").mouseover(function(){
			clearInterval(timer)
			$(".leftBtn,.rightBtn").show()
			})
		$(".focus").mouseout(function(){
			timer = setInterval(roll,3000)
			$(".leftBtn,.rightBtn").hide()
			})
	  $(".btn li").hover(function(){
		  $(this).addClass("on").siblings().removeClass("on");
		  var i=$(this).index();
		  var leftB=-1080*i;
		  $(".bigpic").stop().animate({"left":leftB},500) 
	   })	
	  
	//左边菜单
	$(".top_left").click(function(){
		$(".leftmenu").animate({"left":"0"})
	})
	$(document).click(function(){
    	$(".leftmenu").animate({"left":"-800px"})
	});
	$(".top_left").click(function(event){
    event.stopPropagation();
	});
	$(".leftmenu").click(function(event){
    event.stopPropagation();
	});
	$(".leftmenu").on("swipeleft",function(){
  		$(".leftmenu").animate({"left":"-800px"})
	});
	
	
	$(".nav dl:nth-child(1) dd").css({"color":"#dcbe85"})
		$(".m1").children("dt").children("img").attr("src","images/nav_1_1.png")
		$(".m1").click(function(){
		$(".m1").children("dt").children("img").attr("src","images/nav_1_1.png")
		$(".m2").children("dt").children("img").attr("src","images/nav_2.png")
		$(".m3").children("dt").children("img").attr("src","images/nav_3.png")
		$(".m4").children("dt").children("img").attr("src","images/nav_4.png")
	})
	$(".m2").click(function(){
		$(".m2").children("dt").children("img").attr("src","images/nav_2_1.png")
		$(".m1").children("dt").children("img").attr("src","images/nav_1.png")
		$(".m3").children("dt").children("img").attr("src","images/nav_3.png")
		$(".m4").children("dt").children("img").attr("src","images/nav_4.png")
	})
	$(".m3").click(function(){
		$(".m3").children("dt").children("img").attr("src","images/nav_3_1.png")
		$(".m1").children("dt").children("img").attr("src","images/nav_1.png")
		$(".m2").children("dt").children("img").attr("src","images/nav_2.png")
		$(".m4").children("dt").children("img").attr("src","images/nav_4.png")
	})

	$(".m4").click(function(){
		$(".m4").children("dt").children("img").attr("src","images/nav_4_1.png")
		$(".m1").children("dt").children("img").attr("src","images/nav_1.png")
		$(".m3").children("dt").children("img").attr("src","images/nav_3.png")
		$(".m2").children("dt").children("img").attr("src","images/nav_2.png")
		$(".dhk").fadeIn(200)
	})

	$(".nav dl").click(function(){
		$(".nav dl dd").css({"color":"black"})
		$(this).children("dd").css({"color":"#dcbe85"})
	})
	
	
	$(".dhk").hide()
	$(".top_right").click(function(){
		$(".dhk").fadeIn(200)
	})
	$(".dhk_close").click(function(){
		$(".dhk").fadeOut(200)
	})
	$(".leftmenu").click(function(){
		$(".dhk").fadeIn(200)
	})

})