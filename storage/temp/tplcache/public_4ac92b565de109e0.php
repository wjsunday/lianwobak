<link href="__THEME__/js/um/themes/default/css/umeditor.css" type="text/css" rel="stylesheet">

<script type="text/plain" id="<?php echo ($type); ?>" style="width:100%;height:290px;" name="<?php echo ($contentName); ?>"><?php echo ($content); ?></script>

<script type="text/javascript" charset="utf-8" src="__THEME__/js/um/umeditor.config.js"></script>
<script type="text/javascript" charset="utf-8" src="__THEME__/js/um/umeditor.min.js"></script>
<script type="text/javascript" src="__THEME__/js/um/lang/zh-cn/zh-cn.js"></script>
<script type="text/javascript">
var initialFrameWidth = '<?php echo ($width); ?>';
var EditorList = EditorList || {};
EditorList['<?php echo $type; ?>'] = UM.getEditor('<?php echo $type; ?>',{initialFrameWidth:initialFrameWidth,initialStyle:'.edui-editor-body .edui-body-container img{padding:5px 0px 5px 0px;}'});
</script>