<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php if(($_title)  !=  ""): ?><?php echo ($_title); ?> - <?php echo ($site["site_name"]); ?><?php else: ?><?php echo ($site["site_name"]); ?> - <?php echo ($site["site_slogan"]); ?><?php endif; ?></title>
<meta content="<?php if(($_keywords)  !=  ""): ?><?php echo ($_keywords); ?><?php else: ?><?php echo ($site["site_header_keywords"]); ?><?php endif; ?>" name="keywords">
<?php if (isset($_description)) { ?>
<meta content="<?php echo $_description; ?>" name="description">
<?php } elseif(isset($site['site_header_keywords'])) { ?>
<meta content="<?php echo $site['site_header_keywords']; ?>" name="description">
<?php } ?>
<meta property="qc:admins" content="345471037076401633636375" />
<?php echo Addons::hook('public_meta');?>
<link href="favicon.ico?v=<?php echo ($site["sys_version"]); ?>" type="image/x-icon" rel="shortcut icon">
<link href="__THEME__/css/css.php?t=css&f=global.css,module.css,menu.css,form.css,message.css,jquery.atwho.css&v=<?php echo ($site["sys_version"]); ?>.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="__THEME__/js/uploadify/uploadify.css?v=<?php echo ($site["sys_version"]); ?>" type="text/css">

<?php if(!empty($appCssList)): ?>
<?php if(is_array($appCssList)): ?><?php $i = 0;?><?php $__LIST__ = $appCssList?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$cl): ?><?php ++$i;?><?php $mod = ($i % 2 )?><link href="<?php echo APP_PUBLIC_URL;?>/<?php echo ($cl); ?>?v=<?php echo ($site["sys_version"]); ?>" rel="stylesheet" type="text/css"/><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
<?php endif; ?>
<script>
/**
 * 全局变量
 */
var SITE_URL  = '<?php echo SITE_URL; ?>';
var UPLOAD_URL= '<?php echo UPLOAD_URL; ?>';
var THEME_URL = '__THEME__';
var APPNAME   = '<?php echo APP_NAME; ?>';
var MID		  = '<?php echo $mid; ?>';
var UID		  = '<?php echo $uid; ?>';
var initNums  =  '<?php echo $initNums; ?>';
var SYS_VERSION = '<?php echo $site["sys_version"]; ?>';
var UMEDITOR_HOME_URL = '__THEME__/js/um/';
var _CP       = '<?php echo C("COOKIE_PREFIX");?>';
// Js语言变量
var LANG = new Array();
</script>
<?php if(!empty($langJsList)) { ?>
<?php if(is_array($langJsList)): ?><?php $i = 0;?><?php $__LIST__ = $langJsList?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><script src="<?php echo ($vo); ?>?v=<?php echo ($site["sys_version"]); ?>"></script><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
<?php } ?>

<script src="__THEME__/js/jquery.js?v=<?php echo ($site["sys_version"]); ?>"></script>
<script src="__THEME__/js/jquery.form.js?v=<?php echo ($site["sys_version"]); ?>"></script>
<script src="__THEME__/js/common.js?v=<?php echo ($site["sys_version"]); ?>"></script>
<script src="__THEME__/js/core.js?v=<?php echo ($site["sys_version"]); ?>"></script>
<script src="__THEME__/js/module.js?v=<?php echo ($site["sys_version"]); ?>"></script>
<script src="__THEME__/js/module.common.js?v=<?php echo ($site["sys_version"]); ?>"></script>
<script src="__THEME__/js/jwidget_1.0.0.js?v=<?php echo ($site["sys_version"]); ?>"></script>
<script src="__THEME__/js/jquery.atwho.js?v=<?php echo ($site["sys_version"]); ?>"></script>
<script src="__THEME__/js/jquery.caret.js?v=<?php echo ($site["sys_version"]); ?>"></script>
<script src="__THEME__/js/ui.core.js?v=<?php echo ($site["sys_version"]); ?>"></script>
<script src="__THEME__/js/ui.draggable.js?v=<?php echo ($site["sys_version"]); ?>"></script>
<script src="__THEME__/js/plugins/core.digg.js?v=<?php echo ($site["sys_version"]); ?>"></script>
<script src="__THEME__/js/plugins/core.comment.js?v=<?php echo ($site["sys_version"]); ?>"></script>
<script src="__THEME__/js/plugins/core.digg.js?v=<?php echo ($site["sys_version"]); ?>"></script>

<?php if (empty($mid)) { ?>
<style type="text/css">
body, #header { padding-right: 0; }
</style>
<?php } ?>
<?php echo Addons::hook('public_head',array('uid'=>$uid));?>
</head>
<body>
<script>
    core.plugFunc('message', function(){
        core.message.init();
        
    });
</script>

<!--手机APP下载-->
<div id="app_download">
  <a class="app_download" href="<?php echo U('weiba/Index/postDetail', array('post_id'=>2699));?>"></a>
</div>
<div id="body_page" name='body_page'>
<div id="body-bg">
<?php $act = strtolower(APP_NAME).'_'.strtolower(MODULE_NAME); $navClass[$act]='current';$aClass[$act]='app'; ?>
<?php $act = strtolower(APP_NAME); $navClass[$act]='current';$aClass[$act]='app'; ?>
<div id="header" name="header">
  <!-- 未登录时 -->
  <?php if( !isset($_SESSION["mid"])): ?><div class="header-wrap">
      <div class="head-bd"> 
        <!-- logo -->
        <div class="logo" style="background:url('<?php echo ($ts["site"]["logo"]); ?>') no-repeat;"><a href="<?php echo SITE_URL;?>"></a></div>
        <!-- logo -->
        <div class="nav">
          <ul>
            <?php if(is_array($site_guest_nav)): ?><?php $i = 0;?><?php $__LIST__ = $site_guest_nav?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$st): ?><?php ++$i;?><?php $mod = ($i % 2 )?><li id="sec-nav-<?php echo ($st["navi_id"]); ?>" <?php if(APP_NAME == $st['app_name'] || $_GET['page'] == $st['app_name']): ?> class="current" <?php endif; ?>>
              <a href="<?php echo ($st["url"]); ?>" target="<?php echo ($st["target"]); ?>"  <?php if(APP_NAME == $st['app_name'] || $_GET['page'] == $st['app_name']): ?> class="app" <?php endif; ?> ><?php echo ($st["navi_name"]); ?></a>
            </li><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
          </ul>
        </div>
      </div>
    </div><?php endif; ?>
  <!-- 登录后 -->
  <?php if(isset($_SESSION["mid"])): ?><div class="header-wrap">
      <div class="head-bd"> 
        <!-- logo -->
        <div class="logo" style="background:url('<?php echo ($site["logo"]); ?>') no-repeat;"><a href="<?php echo SITE_URL;?>"></a></div>
        <!-- logo -->
        <div class="nav">
          <ul>
          <?php if(is_array($site_top_nav)): ?><?php $i = 0;?><?php $__LIST__ = $site_top_nav?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$st): ?><?php ++$i;?><?php $mod = ($i % 2 )?><?php if($st['app_name'] != 'jipu'): ?>
            <li _nav="sec-nav-<?php echo ($st["navi_id"]); ?>" <?php if(APP_NAME == $st['app_name'] || $_GET['page'] == $st['app_name']): ?> class="current" <?php endif; ?>>
              <a href="<?php echo ($st["url"]); ?>" target="<?php echo ($st["target"]); ?>"  <?php if(APP_NAME == $st['app_name'] || $_GET['page'] == $st['app_name']): ?> class="app" <?php endif; ?> ><?php echo ($st["navi_name"]); ?></a>
            </li>
            <?php else: ?>
            <!-- 极铺带参数 -->
            <li>
              <a href="<?php echo ($st["url"]); ?>/oauth_token/<?php echo ($login_token['oauth_token']); ?>/oauth_token_secret/<?php echo ($login_token['oauth_token_secret']); ?>/uid/<?php echo ($mid); ?>" target="<?php echo ($st["target"]); ?>"><?php echo ($st["navi_name"]); ?></a>
            </li> 
            <?php endif; ?><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
          </ul>
        </div>
        <?php if(($user["is_init"])  ==  "0"): ?><div class="person">
            <ul>
              <li class="dorp-right"><a href="<?php echo U('public/Passport/logout');?>" class="app name">退出</a></li>
              <li model-node="person" class="dorp-right"><a href="javascript:void(0);" class="app name" style="cursor:default">欢迎，<?php echo ($user['uname']); ?></a></li>
            </ul>
          </div>
          <?php else: ?>
          <div class="person">
            <ul class="clearfix">
              <li model-node="search" class="dorp-right"> 
                  <a href="javascript:void(0);" class="app search-btn"><img src="__THEME__/image/ico-search.png" /></a>
                  <!--<div class="search" model-node="drop_menu_list">-->
                  <div class="search">
                  <div id="mod-search" model-node="drop_search">
                    <form name="search_feed" id="search_feed" method="get" action="<?php echo U('public/Search/index');?>">
                      <input name="app" value="public" type="hidden"/>
                      <input name="mod" value="Search" type="hidden"/>
                      <input type="hidden" name="t" value="2"/>
                      <input type="hidden" name="a" value="public"/>
                      <dl>
                        <dt class="clearfix">
                          <input id="search_input" class="s-txt left"  type="text" placeholder="搜人/分享/微吧/帖子" value="" event-node="searchKey" name='k'  autocomplete="off">
                        </dt>
                      </dl>
                    </form>
                  </div>
                </div>
                <script type="text/javascript">
                       // $('#search_input').keydown(function(e) {
                       //         if (e.keyCode == 8) {
                       //             var len = $(this).val().length;
                       //             if (len == 1) {
                       //                 $(this).focus();
                       //                 $(this).val('');
                       //                 return false;
                       //             }
                       //         } else {
                       //             var val = $(this).val();
                       //             if (val == '搜人/分享/微吧/知识/帖子') {
                       //                 $(this).val('');
                       //             }
                       //         }
                       //     });
                       //  var valSearchChange = function(e) {
                       //         var ev = e || window.event;
                       //         ev.keyCode == 8;
                       //         alert(ev);
                       //         var text = $('#search_input').val();
                       //         alert(text);
                       //         if (text == '') {
                       //             $('#search_input').val('');
                       //         }
                       //     };
                       //      浏览器的输入的兼容性
                       //     if($.browser.msie && parseInt($.browser.version) < 9) {
                       //         $('#search_input').bind("propertychange", function(e) {
                       //             valSearchChange(e);
                       //         });
                       //     } else {
                       //         $('#search_input').bind("input", function() {
                       //             valSearchChange(e);
                       //         });
                       //     }
                       //     var searchSubmit = function() {
                       //         var val = $('#search_input').val();
                       //         if (getLength(val)) {
                       //             $('#search_feed').submit();
                       //             return false;
                       //         }
                       //     };
                </script> 
              </li>
              <!-- <li model-node="person" class="dorp-right"> <a href="<?php echo ($user['space_url']); ?>" class="username"><?php echo (getshort($user['uname'],6)); ?></a> </li> -->
              <li model-node="account" class="dorp-right"><a href="javascript:void(0);" class="app" rel="account-btn"><img rel="account-btn" src="__THEME__/image/ico-set.png" /></a>
                <div model-node="drop_menu_list" class="dropmenu" style="opacity:0;display:none;width:100px"> <i class="arrow-mes"></i>
                  <dl class="acc-list">
                    <dd><a href="<?php echo U('public/Profile/index',array('uid'=>$mid));?>"><i class="ico-myhome"></i>我的主页</a></dd>
                    <dd><a href="<?php echo U('public/Account/index');?>"><i class="ico-mysetup"></i>个人设置</a></dd>
                    <dd><a href="<?php echo U('public/Rank/index','type=2');?>"><i class="ico-mypoints"></i>排行榜</a></dd>
                    <?php if(CheckTaskSwitch()): ?>
                      <dd><a href="<?php echo U('public/Task/index');?>"><i class="ico-mytask"></i>任务中心</a></dd>
                      <dd><a href="<?php echo U('public/Medal/index');?>"><i class="ico-mymedal"></i>勋章馆</a></dd>
                    <?php endif; ?> 
                    <?php if(!isVerified($user['uid'])): ?>
                    <dd><a href="<?php echo U('public/Account/authenticate');?>"><i class="ico-certification"></i>申请认证</a></dd>
                    <?php endif; ?>
                    <?php if(isInvite() && CheckPermission('core_normal','invite_user')): ?>
                    <dd><a href="<?php echo U('public/Invite/invite');?>"><i class="ico-invite"></i><?php echo L('PUBLIC_INVITE_COLLEAGUE');?></a></dd>
                    <?php endif; ?>
                    
                    <!-- <dd><a href="<?php echo U('public/SmallTools/weiboShare');?>">小工具</a></dd> --> 
                    
                    <!-- 个人设置菜单钩子 --> 
                    <?php echo Addons::hook('header_account_dropmenu');?>
                    <?php if(CheckPermission('core_admin','admin_login')){ ?>
                    <dd><a href="<?php echo U('admin');?>"><i class="ico-systemmanage"></i><?php echo L('PUBLIC_SYSTEM_MANAGEMENT');?></a></dd>
                    <?php } ?>
                    <dd class="border"><a href="<?php echo U('public/Passport/logout');?>"><i class="ico-layout"></i><?php echo L('PUBLIC_LOGOUT');?>>></a></dd>
                    <dd></dd>
                  </dl>
                </div>
              </li>
              <!--<li model-node="notice" class="dorp-right" style="border-right:none;"><a href="javascript:void(0);" class="app" rel="notice-btn"><img rel="notice-btn" src="__THEME__/image/ico-mes.png" /></a>
                <div  class="dropmenu" model-node="drop_menu_list" style="height:322px;">
                </div>
              </li>-->
              
            </ul>
          </div>
          <?php if(MODULE_NAME !='Register'): ?>
          <div id="message_container" class="layer-massage-box" style="display:none">
            <ul class="message_list_container" >
              <li rel="new_folower_count" style="display:none"><span></span>，<a href="<?php echo U('public/Index/follower',array('uid'=>$mid));?>"><?php echo L('PUBLIC_FOLLOWERS_REMIND');?></a></li>
              <li rel="unread_comment" style="display:none"><span></span>，<a href="<?php echo U('public/Comment/index',array('type'=>'receive'));?>"><?php echo L('PUBLIS_MESSAGE_REMIND');?></a></li>
              <li rel="unread_message" style="display:none"><span></span>，<a href="<?php echo U('public/Message');?>" ><?php echo L('PUBLIS_MESSAGE_REMIND');?></a></li>
              <li rel="unread_atme" style="display:none"><span></span>，<a href="<?php echo U('public/Mention');?>"><?php echo L('PUBLIS_MESSAGE_REMIND');?></a></li>
              <li rel="unread_notify" style="display:none"><span></span>，<a href="<?php echo U('public/Message/notify');?>"><?php echo L('PUBLIS_MESSAGE_REMIND');?></a></li>
              <li rel="unread_group_atme" style="display:none"><span></span>，<a href="<?php echo U('group/SomeOne/index');?>"><?php echo L('PUBLIS_MESSAGE_REMIND');?></a></li>
              <li rel="unread_group_comment" style="display:none"><span></span>，<a href="<?php echo U('group/SomeOne/index');?>"><?php echo L('PUBLIS_MESSAGE_REMIND');?></a></li>
            </ul>
            <a href="javascript:void(0)" onclick="core.dropnotify.closeParentObj()" class="ico-close1"></a> </div>
          <?php endif; ?><?php endif; ?>
      </div>
    </div>
    <?php if(MODULE_NAME != 'Search'): ?>
      <div id="search"  class="mod-at-wrap search_footer" model-node='search_footer' style="display:none;z-index:-1">
        <div class="search-wrap">
          <div class="input">
            <form id="search_form" action="<?php echo U('public/Search/index');?>" method="GET">
              <div class="search-menu" model-node='search_menu' model-args='a=<?php echo ($curApp); ?>&t=<?php echo ($curType); ?>'> <a href="javascript:;" id='search_cur_menu'><?php echo (($curTypeName)?($curTypeName):"全站"); ?><i class="ico-more"></i></a> </div>
              <input name="app" value="public" type="hidden" />
              <input name="mod" value="Search" type="hidden" />
              <input name="a" value="<?php echo ($curApp); ?>" id='search_a' type="hidden"/>
              <input name="t" value="<?php echo ($curType); ?>" id='search_t' type="hidden"/>
              <input name="k" value="<?php echo (t($_GET['k'])); ?>" type="text" class="s-txt" onblur="this.className='s-txt'" onfocus="this.className='s-txt-focus'" autocomplete="off">
              <a class="btn-red left" href="javascript:void(0);" onclick="$('#search_form').submit();"><span class="ico-search"></span></a>
            </form>
          </div>
        </div>
      </div>
      <div class="mod-at-wrap" id="search_menu" ison='no' style="display:none" model-node="search_menu_ul">
        <div class="mod-at">
          <div class="mod-at-list">
            <ul class="at-user-list">
              <li onclick="core.search.doShowCurMenu(this)" a='public' t='' typename='<?php echo L('PUBLIC_ALL_WEBSITE');?>'><?php echo L('PUBLIC_ALL_WEBSITE');?></li>
              <?php if(is_array($menuList)): ?><?php $i = 0;?><?php $__LIST__ = $menuList?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$m): ?><?php ++$i;?><?php $mod = ($i % 2 )?><?php if($m['app_name'] == $curApp && $m['type_id'] == $curType){
                  $curTypeName = $m['type'];
                  } ?>
                <li onclick="core.search.doShowCurMenu(this)" a='<?php echo ($m["app_name"]); ?>' t='<?php echo ($m["type_id"]); ?>' typename='<?php echo ($m["type"]); ?>'><?php echo ($m["type"]); ?></li><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
            </ul>
          </div>
        </div>
      </div>
    <?php endif; ?>
    <script type="text/javascript">
    $(document).ready(function(){
        $("#mod-product dd").hover(function() {
            $(this).addClass("hover");
        },function() {
            $(this).removeClass("hover");
        });
        core.plugInit('search');

        //二级导航
        var qcloud={};
        $("[_nav]").hover(function(){
          var _nav = $(this).attr('_nav');
          var _li = $("li[_nav="+_nav+"]");
          var _menu = $('#'+_nav);

          clearTimeout( qcloud[ _nav + '_timer' ] );

          //边框
          if(_li.hasClass("current") && _menu.has("ul").length) _li.find("a").css("border-bottom","none");

          //箭头
          if(_menu.has("ul").length){
            _li.find(".arrow-mes").remove();
            var l_width = _li.css("width");
            var i_width = "16px";
            var left = (parseInt(l_width)-parseInt(i_width))/2+"px";
            // var arrow_mes = "<i class="arrow-mes" style="top:51px;left:"+left+"";display:none;opacity:0;></i>";
            var arrow_mes = '<i class="arrow-mes" style="top:51px; left: ' + left + '; display:none; opacity:0;"></i>';
            _li.append(arrow_mes);}

          //调整位置
          var l_left = _li.offset().left;

          //显示
          qcloud[ _nav + '_timer' ] = setTimeout(function(){
            if(_menu.has("ul").length){
              _menu.find("ul").css("left",l_left+17);
              _menu.stop(true,true).fadeIn("fast");
              _li.find(".arrow-mes").fadeIn("fast");
            }
          }, 150);
        },function(){
          var _nav = $(this).attr('_nav');
          var _li = $("li[_nav="+_nav+"]");
          var _menu = $('#'+_nav);
          clearTimeout( qcloud[ _nav + '_timer' ] );

          
          //边框
          if(_li.hasClass("current") && _menu.has("ul").length) $("li[_nav="+_nav+"]").find("a").css("border-bottom","#0096e6 4px solid");
          

          //隐藏
          qcloud[ _nav + '_timer' ] = setTimeout(function(){
            _menu.stop(true,true).fadeOut("fast");
            _li.find(".arrow-mes").fadeOut("fast");
          }, 150);

        })


        // $(".nav ul li a").each(function(){
        //   //判断是否有二级导航
        //   if($(this).parent().has('ul.sec-nav-detail').length){
        //     $(this).mouseover(function(){
        //       //边框
        //       if($(this).parent().hasClass("current")) $(this).css("border-bottom","none");
        //       //箭头
        //       var l_width = $(this).parent().css("width");
        //       var i_width = $(this).parent().find('i').css("width");1
        //       var left = (parseInt(l_width)-parseInt(i_width))/2+"px";
        //       $(this).parent().find('i').css("left",left);
        //       $(this).parent().find('i').show();

        //       //二级导航
        //       $(".sec-nav").remove();
        //       var sec_nav = "<div class="sec-nav" class="sec-nav"></div>";
        //       $("body").append(sec_nav);
        //       _dd = $(this).parent().find("ul.sec-nav-detail").clone();

        //       //获取li位置
        //       var l_left = $(this).parent().offset().left; 

        //       $(_dd).css("marginLeft",l_left);
        //       $(".sec-nav").html(_dd);
        //       $(".sec-nav").show();
        //     });

        //     $(this).mouseout(function(event){

        //       $(".sec-nav").bind("mouseover",function(){
        //         status = true;
        //       });

        //       setTimeout(function(){
        //         if(status == false){
        //           $(".sec-nav").hide();
        //         }else{
        //           return false;
        //         }
        //       },100);
        //     });
        //   }
        // })



    });
    // core.plugFunc('dropnotify',function(){
    //     setTimeout(function(){
    //         core.dropnotify.init('message_list_container','message_container');
    //     },320);   
    // });
    </script><?php endif; ?>
</div>
<!--二级导航-->
<div class="navigation-down">
  <?php if(is_array($site_top_nav)): ?><?php $i = 0;?><?php $__LIST__ = $site_top_nav?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$st): ?><?php ++$i;?><?php $mod = ($i % 2 )?><div class="nav-down-menu" id="sec-nav-<?php echo ($st["navi_id"]); ?>" _nav="sec-nav-<?php echo ($st["navi_id"]); ?>">
    <div class="navigation-down-inner">
    <?php if( $st["child"] != '' ): ?><ul>
      <?php if(is_array($st["child"])): ?><?php $i = 0;?><?php $__LIST__ = $st["child"]?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$stc): ?><?php ++$i;?><?php $mod = ($i % 2 )?><li><a href="<?php echo ($stc["url"]); ?>" target="<?php echo ($stc["target"]); ?>"><?php echo (getshort($stc["navi_name"],6)); ?></a></li><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
      </ul><?php endif; ?>
    </div>
  </div><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
</div>
<?php //出现注册提示的页面
  $show_register_tips = array('public/Profile','public/Topic','weiba/Index','channel/Index','public/Index','people/Index');
  if(!$mid && in_array(APP_NAME.'/'.MODULE_NAME,$show_register_tips)){ ?>
<?php $registerConf = model('Xdata')->get('admin_Config:register'); ?>
<!--未登录前-->
<div class="login-no-bg">
  <div class="login-no-box clearfix">
    <div class="login-reg right">
      <?php if($registerConf['register_type'] == 'open'){ ?>
      <a href="<?php echo U('public/Register/index');?>" class="btn-reg">立即注册</a>
      <?php } ?>
      <span>已有帐号？<a href="javascript:ui.quicklogin()">立即登录</a></span> </div>
    <p class="left"><span>欢迎来到<?php echo ($site["site_name"]); ?></span>赶紧注册与朋友们分享快乐点滴吧！</p>
  </div>
</div>
<?php } ?>
<div id="page-wrap">
  <div id="feedback" class="feedback-fixed">
    <a href="<?php echo U('public/Index/feedback');?>" target="_blank"><?php echo L('PUBLIC_FEEDBACK');?></a>
  </div>
  <div id="main-wrap">
    <div id="st-index-grid">
      <!--左边 -->
      <div id="col1" class="st-index-left">
<div class="left-wrap">
<!--个人信息-->
<div class="mod-person">
<a href="<?php echo U('public/Profile/index',array('uid'=>$mid));?>" class="face"><img src="<?php echo ($user["avatar_small"]); ?>" width="48" /></a>
<a href="<?php echo U('public/Profile/index',array('uid'=>$mid));?>"><span class="name"><?php echo ($user["uname"]); ?></span></a>
</div>
<!--左导航菜单-->
<div class="mod-sub-nav">
<ul class="basic-list">
	<li <?php if(MODULE_NAME =='Index' && APP_NAME=='public'){ ?> class="current"<?php } ?> ><a href="<?php echo U('public/Index/index');?>" class="app"><i class="arrow-current"></i><i class="icon ico-home"></i><?php echo L('PUBLIC_MY_HOME');?></a></li>
	<li <?php if(MODULE_NAME =='Mention'){ ?> class="current"<?php } ?>><a href="<?php echo U('public/Mention');?>" class="app"><i class="arrow-current"></i><i class="icon ico-at"></i><?php echo L('PUBLIC_MY_MENTIONS');?></a></li>
	<li <?php if(MODULE_NAME =='Comment'){ ?> class="current"<?php } ?>><a href="<?php echo U('public/Comment');?>" class="app"><i class="arrow-current"></i><i class="icon ico-comment"></i><?php echo L('PUBLIC_MY_COMMENTS');?></a></li>
	<li <?php if(MODULE_NAME =='Collection'){ ?> class="current"<?php } ?>><a href="<?php echo U('public/Collection');?>" class="app"><i class="arrow-current"></i><i class="icon ico-favorites"></i><?php echo L('PUBLIC_MY_FAVORITES');?></a></li>
	<li><a target="_blank" href="<?php echo U('public/Profile/index',array('uid'=>$uid));?>" class="app"><i class="arrow-current"></i><i class="icon ico-wbo"></i><?php echo L('PUBLIC_MY_WEIBO');?></a></li>		
	<li><a target="_blank" href="<?php echo U('public/Profile/data',array('uid'=>$uid));?>" class="app"><i class="arrow-current"></i><i class="icon ico-record"></i><?php echo L('PUBLIC_MY_PROFILE');?></a></li>

</ul>
</div>
<!--应用-->
<div class="mod-app">
<h3 class="hd"><?php echo L('PUBLIC_APPLICATION');?></h3>
<ul class="app-list">
<?php if(is_array($_userApp)): ?><?php $i = 0;?><?php $__LIST__ = $_userApp?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$ua): ?><?php ++$i;?><?php $mod = ($i % 2 )?><li <?php if($ua['app_name'] == APP_NAME ): ?> class="current" <?php endif; ?> >
	<a href="<?php echo ($ua['app_entry']); ?>" class="app" id='leftApp<?php echo ($ua["app_id"]); ?>'>
	<i class="arrow-current"></i><img src="<?php echo ($ua["icon_url"]); ?>" width='16'> 
	<?php echo L('PUBLIC_APPNAME_'.$ua['app_name']);?>
	</a>
</li><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
</ul>
<div class="app-add"><a href="<?php echo U('public/App/index');?>" class="app"><i class="icon ico-add"></i><?php echo L('PUBLIC_APP_INEX');?></a></div>
</div>
</div>
</div>
      <div id="col8" class="st-section">
        <!--右边-->
        <div id="col3" class="st-index-right">
	
	<!-- 个人信息 -->
	<?php if(isset($_SESSION["mid"])): ?><div class="right-wrap">
    	   <?php echo W('UserInformation', array('uid'=>$mid, 'tpl'=>'right', 'isReturn'=>$isReturn));?>
        </div><?php endif; ?>
    <!-- 签到 -->
	<?php echo W('CheckIn');?>
	<!-- 插件位 -->
    <div class="right-wrap">
    <?php echo W('RelatedUser', array('title'=>'可能感兴趣的人', 'limit'=>3, 'max'=> 3));?>
    </div>
    <div class="right-wrap">
    <?php echo W('TopicList',array('type'=>1, 'limit'=>5));?><!--推荐话题-->
    </div>
	<!-- 插件位 -->
    <!-- 首页右下广告位 -->
    <div class="right-wrap">
    <fieldset class="inter-line" style="padding-bottom:20px;"><legend class="inter-txt">推荐</legend></fieldset>
	<!--<?php echo W('ShowAds');?>-->
    <?php echo Addons::hook('show_ad_space', array('place'=>'home_right_bottom'));?>
    </div>
</div>
        <div id="col5" class="st-index-main">
          <?php echo Addons::hook('home_index_left_top');?>
          <div> 
            <!-- 发布框 分享 文章微吧 -->
            <div class="diy-share">
              <ul class="diy-share-cont" id="medz-share-box" <?php if(($user["is_fixed"])  ==  "1"): ?>style="display:none;"<?php endif; ?>>
                <li> <a href="javascript:void(0);" class="sharing"> <i class="i-sharing"></i><span>分享</span> </a>
                  <p>快速分享文字、图片、视频</p>
                </li>
                <?php if($weibaIfOpen) { ?>
                <li> <a href="javascript:void(0);" class="circle"> <i class="i-circle"></i><span>微吧</span> </a>
                  <p>在自己关注的微吧分享内容</p>
                </li>
                <?php } ?>
                <?php if($channelIfOpen) { ?>
                <li style="border-right:none;"> <a href="javascript:void(0);" class="article"> <i class="i-article"></i><span>频道</span> </a>
                  <p>在频道里发布感兴趣的内容</p>
                </li>
                <?php } ?>
              </ul>

              <!--分享-->
              <div class="sharing-cont post" <?php if(($user["is_fixed"])  ==  "1"): ?>style="display:block;"<?php endif; ?>>
                <div class="title">
                  <i class="ico-sharing"></i>
                  <span>发布分享</span>
                  <a href="javascript:void(0);" class="cancel-share btn-cancel right" title="取消">取消</a>
                  <?php if($GLOBALS['ts']['mid'] != 0): ?><?php if(($user["is_fixed"])  ==  "1"): ?><a href="javascript:;" class="right font12 mr10 f9" title="取消锁定" onclick="fixed(<?php echo ($user['is_fixed']); ?>,this)">取消锁定</a>
                    <?php else: ?>
                    <a href="javascript:;" class="right font12 mr10 f9" title="锁定" onclick="fixed(<?php echo ($user['is_fixed']); ?>,this)">锁定</a><?php endif; ?><?php endif; ?>
                </div>
                <?php echo W('SendWeibo',array('title'=>$title, 'topicHtml'=>$initHtml));?> 
              </div>
              <!--微吧-->
              <div class="circle-cont clearfix post">
                <form id="weiba_form" action="<?php echo U('weiba/Index/doPost','post_type=index');?>" method="post" enctype="multipart/form-data" model-node='event_post'>
                <div class="title">
                  <i class="ico-circle"></i><span>发布微吧帖子</span>
                  <a href="javascript:void(0);" class="cancel-share btn-cancel right" title="取消">取消</a>
                </div>
                <div>
                  <div class="share-kind">
                    <ul>
                      <li><a id="weiba_name" name="1" href="javascript:void(0);">选择微吧<i class="ico-arrow-down right mt15"></i></a></li>
                      <input id="weiba_id" type="hidden" value="" name="weiba_id" />
                    </ul>
                    <div class="input-title">
                      <input type="text" name="title" value="输入微吧标题" style="width:467px;" onfocus="if(this.value=='输入微吧标题') this.value = ''" onblur="if(this.value=='') this.value = '输入微吧标题'"/>
                    </div>
                  </div>
                  <div class="kind-list kind-1" id="kind-1">
                    <ul class="clearfix">
                    <?php foreach( $weiba_category as $v ){ ?>
                    <li id="weiba_id_<?php echo ($v["weiba_id"]); ?>" onclick="weibaName(<?php echo ($v["weiba_id"]); ?>)"><?php echo (getshort($v['weiba_name'],8)); ?></li>
                    <?php } ?>
                    </ul>
                  </div>
                </div>
                <?php if( $GLOBALS['ts']['mid'] != 0 ): ?><?php echo W('UM', array('type'=>'weiba','contentName'=>'content','content'=>'',width=>'656',height=>'290'));?>
                  <input type="hidden" value="0" name="private" />
                  <div class="mt15"> 
                  <input name='button' type="submit" class="btn-green-big right" style="margin-left:140px;" event-node="submit_btn" value="发布" editor="true"/></div>
                  </form>
                <?php else: ?>
                <!--没有登录-->
                <div class="send_weibo">
                  <div class="box-purview"><div class="nologin"><i class="ico-error"></i>您还未登录，请<a class="nologin-a" href="javascript:ui.quicklogin();">&nbsp;登录</a>&nbsp;or&nbsp;<a class="nologin-a" href="<?php echo U('public/Register/index');?>">注册</a></div></div>
                </div><?php endif; ?>
              </div>
              <!--频道-->
              <div class="article-cont clearfix post">
                <div class="title"><i class="ico-article"></i><span>发布频道</span><a href="javascript:void(0);" class="cancel-share btn-cancel right" title="取消">取消</a> </div>
                <div>
                  <div class="share-kind">
                    <ul>
                      <li><a id="blog_fname" name="1" href="javascript:void(0);" id="level-one"><span>频道</span><i class="ico-arrow-down right mt15"></i></a></li>
                   </ul>
                  </div>
                  <div class="kind-list kind-1">
                    <ul class="clearfix">
                    <?php foreach( $channel_category as $value ){ ?>
                    <li id="blog_id_<?php echo ($value['channel_category_id']); ?>" onclick="getcid(<?php echo ($value['channel_category_id']); ?>)"><?php echo (getshort($value['title'],8)); ?></li>
                    <?php } ?>
                    </ul>
                  </div>
                </div>
                <input type="hidden" autocomplete="off" value="" id="contribute" />
                <?php echo W('SendWeibo',array('title'=>$title,'tpl'=>'channel','channel'=>'channel','initHtml'=>$initHtml,'prompt'=>$prompt,'actions'=>$actions));?>
                
        <script type="text/javascript">
				var boxmodule = function() {
					M(document.getElementById('tsbox'));
				};
				
				if("undefined" == typeof(loadmore) || loadmore == 0) {
					var loadmore = '0';
					var loadnew = '0';	 
					core.loadFile(THEME_URL+'/js/module.weibo.js',boxmodule);
				} else {
					boxmodule();
				}
				</script>
              </div>
            </div>
            <?php echo Addons::hook('show_ad_space', array('place'=>'home_middle'));?>
            <!--feednav-->
            <div class="st-bg">
              <div class="tab-menu line-b-animate"> 
              <?php $nowClass[$type] = 'current'; ?>
                <ul>
                  <li class="<?php echo ($nowClass['all']); ?>"><span><a href="<?php echo U('public/Index/index',array('type'=>'all'));?>">全部</a></span></li>
                  <li class="<?php echo ($nowClass['following']); ?>"><span><a href="<?php echo U('public/Index/index',array('type'=>'following'));?>">关注</a></span></li>
                  <?php if($isChannelOpen): ?><li class="<?php echo ($nowClass['channel']); ?>"><span><a href="<?php echo U('public/Index/index',array('type'=>'channel'));?>">频道</a></span></li><?php endif; ?>
                  <?php if($weibaIfOpen) { ?>
                  <li class="<?php echo ($nowClass['weiba']); ?>"><span><a href="<?php echo U('public/Index/index',array('type'=>'weiba'));?>">帖子</a></span></li>
                  <?php } ?>
                  <li class="<?php echo ($nowClass['recommend']); ?>"><span><a href="<?php echo U('public/Index/index',array('type'=>'recommend'));?>">推荐</a></span></li>
                </ul>
              </div>
              <!--<div class="feed-nav clearfix"> 
                <div class="mod-feed-tab" id="mod-feed-tab">
                  <ul class="inner-feed-nav">
                    <li 
                    
                    <?php if(($feed_type)  ==  ""): ?>class="current"<?php endif; ?>
                    > <a href="<?php echo U('public/Index/index',array('type'=>$type,'feed_type'=>''));?>"><?php echo L('PUBLIC_ALL_STREAM');?></a>
                    </li>
                    <li 
                    
                    <?php if(($feed_type)  ==  "post"): ?>class="current"<?php endif; ?>
                    > <a href="<?php echo U('public/Index/index',array('type'=>$type,'feed_type'=>'post'));?>"><?php echo L('PUBLIC_ORIGINAL_STREAM');?></a>
                    </li>
                    <li 
                    
                    <?php if(($feed_type)  ==  "repost"): ?>class="current"<?php endif; ?>
                    > <a href="<?php echo U('public/Index/index',array('type'=>$type,'feed_type'=>'repost'));?>"><?php echo L('PUBLIC_SHARE_STREAM');?></a>
                    </li>
                    <li 
                    
                    <?php if(($feed_type)  ==  "postimage"): ?>class="current"<?php endif; ?>
                    > <a href="<?php echo U('public/Index/index',array('type'=>$type,'feed_type'=>'postimage'));?>"><?php echo L('PUBLIC_IMAGE_STREAM');?></a>
                    </li>
                    <li 
                    
                    <?php if(($feed_type)  ==  "postvideo"): ?>class="current"<?php endif; ?>
                    > <a href="<?php echo U('public/Index/index',array('type'=>$type,'feed_type'=>'postvideo'));?>">视频</a>
                    </li>
                  </ul>
                </div>
              </div>-->
              <?php echo Addons::hook('home_index_left_feedtop');?>
              <!--feed list-->
              <?php echo W('FeedList',array('type'=>$type,'feed_type'=>$feed_type,'feed_key'=>$feed_key,'fgid'=>t($_GET['fgid'])));?>
              </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="layer-channel-group-list" style="display:none" model-node="layer_channel_group_list">
  <div class="inner">
    <ul>
      <li><a href="<?php echo U('public/Index/index',array('type'=>'channel'));?>">我关注的频道</a></li>
      <?php if(is_array($channelGroup)): ?><?php $i = 0;?><?php $__LIST__ = $channelGroup?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$f): ?><?php ++$i;?><?php $mod = ($i % 2 )?><li>
        <a href="<?php echo U('public/Index/index',array('type'=>'channel','fgid'=>$f['channel_category_id']));?>" class="group_title" gid="<?php echo ($f['channel_category_id']); ?>"><?php echo ($f["title"]); ?></a>
      </li><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
    </ul>
  </div>
</div>

<script type="text/javascript">
//分享页面自动收缩
$(".diy-share-cont li").each(function(){
$(this).click(function(){
	$(".diy-share-cont").slideUp("fast");
	var ids = $(this).find("a").attr("class");
	$("."+ids+"-cont").show();
})

//取消当前
$(".cancel-share").click(function(){
  $(this).parents().find(".post").hide();
  $(".kind-list").hide();//关闭当前下拉选项
  $(".diy-share-cont").slideDown("fast");
  $("#contribute").val('');
  })
})
	
function getcid (cid){
	var sdata = $('#blog_id_'+cid).text()+"<i class='ico-arrow-down right mt15'></i>";
	$('#blog_fid').val(cid);
	$('#blog_fname').html(sdata);
	$(".kind-1").hide();
	$('#contribute').val(cid);
};

function setcid (cid){
	var sdata = $('#blog_'+cid).text()+"<i class='ico-arrow-down right mt15'></i>";
	$('#blog_id').val(cid);
	$(".kind-2").hide();//当前级别隐藏
	$('#blog_name').html(sdata);
};

function weibaName(weiba_id){
	var data = $('#weiba_id_'+weiba_id).text()+"<i class='ico-arrow-down right mt15'></i>";
	$('#weiba_id').val(weiba_id);
	$(".kind-1").hide();//当前级别隐藏
	$('#weiba_name').html(data);
};

//如果没有可选择微吧，添加提示
function nocircleTip(obj,tips){
	var tipContent = ''+'<p class="f8 p20">您没有可选择的微吧，<a href="<?php echo U('weiba/Index/weibaList',array('type'=>$type));?>"class="f-red">赶紧去加入吧！</a></p>'
	if($(obj).length<1){
		$(tips).html(tipContent);
	}
}
//锁定，取消锁定
function fixed(fixed,_this){
  var url = "<?php echo U('public/Index/fixed');?>";
  var text = fixed == 1 ? "锁定" : "取消锁定";
  var _fixed = fixed == 1 ? 0 : 1;
  $.post(url,{fixed:fixed},function(res){
    if(res.status){
      $(_this).text(text);
      $(_this).attr("onclick","fixed("+_fixed+",this)");
    }else{
      $(_this).text(text);
      $(_this).attr("onclick","fixed("+_fixed+",this)");
    }
  },'json');
}
nocircleTip("#kind-1 li","#kind-1");
//分类标签选择		
$(".share-kind li a").each(function(){
	var index = $(this).attr("name");
    var currentKind = $(".kind-" + index);
	$(this).click(function(){
		currentKind.siblings(".kind-list").hide();
		currentKind.toggle();
	});
})
</script> 
<script type="text/javascript" src="__THEME__/js/home/module.home.js"></script>
<script type="text/javascript" src="__THEME__/js/module.weibo.js"></script>
<script type="text/javascript">
$(function() {
  $('#medz-share-box').find('li').last().css('border-right', 'none');
  $('#medz-share-box').find('li').css('width', (700 - 1 - $('#medz-share-box').find('li').size()) / $('#medz-share-box').find('li').size());
});
</script>
<div class="footer-wrap">
  <div class="footer">
    <div class="login-footer">
      <div class="attend-official clearfix" style="display:none">
        <dl>
          <dt>
            <div class="mb15">关注我们</div>
            <a href="<?php echo ($GLOBALS["ts"]["site"]["sina_weibo_link"]); ?>" target="_blank" rel="nofollow me"><i class="ico-weibo"></i>加关注</a>
            <!--<a><i class="ico-weixin"></i>加关注</a>-->
          </dt>
          <dd><img src="<?php echo getAttachUrlByAttachId($GLOBALS['ts']['site']['site_qr_code']);?>"></dd>
        </dl>
      </div>
      <?php if(!empty($site_bottom_nav) && $site_bottom_child_nav){ ?>
      <div class="foot clearfix right">
        <?php if(is_array($site_bottom_nav)): ?><?php $i = 0;?><?php $__LIST__ = $site_bottom_nav?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$nv): ?><?php ++$i;?><?php $mod = ($i % 2 )?><dl>
            <dt><a href="<?php echo ($nv["url"]); ?>" target="<?php echo ($nv["target"]); ?>"><?php echo ($nv['navi_name']); ?></a></dt>
            <?php if(is_array($nv["child"])): ?><?php $i = 0;?><?php $__LIST__ = $nv["child"]?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$cv): ?><?php ++$i;?><?php $mod = ($i % 2 )?><dd><a href="<?php echo ($cv["url"]); ?>" target="<?php echo ($cv["target"]); ?>"><?php echo ($cv['navi_name']); ?></a></dd><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
          </dl><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
      </div>
      <?php } else if(!empty($site_bottom_nav)) { ?>
      <div class="foot foot1 right clearfix">
        <?php if(is_array($site_bottom_nav)): ?><?php $i = 0;?><?php $__LIST__ = $site_bottom_nav?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$nv): ?><?php ++$i;?><?php $mod = ($i % 2 )?><dl>
            <dt><a href="<?php echo ($nv["url"]); ?>" target="<?php echo ($nv["target"]); ?>"><?php echo ($nv['navi_name']); ?></a></dt>
          </dl><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
      </div>
      <?php } ?>
      <div class="copy-right">
      <p><?php echo ($GLOBALS["ts"]["site"]["site_footer_des"]); ?></p>
      <p class="f8">Powered by <a href="http://www.lianwo.tv">lianwo.tv</a>&nbsp;<?php echo ($GLOBALS["ts"]["site"]["site_footer"]); ?> </p>
      </div>
    </div>
  </div>
  <!--footer end--> 
  
</div>
<!--page end--> 
<?php echo Addons::hook('public_footer');?> 
<!-- 统计代码-->
<div id="site_analytics_code" style="display:none;"> <?php echo (base64_decode($site["site_analytics_code"])); ?> </div>
<?php if(($site["site_online_count"])  ==  "1"): ?><script src="<?php echo SITE_URL;?>/online_check.php?uid=<?php echo ($mid); ?>&uname=<?php echo ($user["uname"]); ?>&mod=<?php echo MODULE_NAME;?>&app=<?php echo APP_NAME;?>&act=<?php echo ACTION_NAME;?>&action=trace"></script><?php endif; ?>
<script>
  //底部置底
  var head_height = $("#body-bg").css("padding-top");
  var footer_height = $(".footer-wrap").height();
  var footer_margin_top = $(".footer-wrap").css("margin-top");
  var content_height = $("#page-wrap").height();

  var window_height = $(window).height();
  var min_height = Number(window_height)-parseInt(head_height)-Number(footer_height)-parseInt(footer_margin_top)-1;
  if(content_height < min_height){
    if($("#main-wrap").length > 0){
      $("#main-wrap").css("min-height",min_height);
    }else{
      $("#page-wrap").css("min-height",min_height);
    }
  }
</script>
<?php if(!empty($appJsList) && is_array($appJsList)) { ?>
<?php foreach ($appJsList as $jsUrl) { ?>
<script type="text/javascript" src="<?php echo APP_PUBLIC_URL;?>/<?php echo ($jsUrl); ?>?v=<?php echo ($site["sys_version"]); ?>"></script>
<?php } ?>
<?php } ?>
</body>
</html>