<?php 
global $articulate_iea_admin_lightbox;
$articulate_iea_admin_lightbox = new articulate_iea_admin_lightbox;
add_action("iea_admin_menu", array($articulate_iea_admin_lightbox  , 'menu'));
add_action("admin_init", array($articulate_iea_admin_lightbox  , 'save'));
class articulate_iea_admin_lightbox{
	
function menu(){
	
add_submenu_page ( 'articulate_content', __('Lightbox Settings', 'quiz'),  __('Lightbox Settings', 'quiz'), "manage_options", 'articulate-settings-lightbox',array($this, 'view') );	
	
}

function save(){
if(isset($_POST['iea_save'])){
	if ( wp_verify_nonce( $_POST['articulate_nonce_field'], 'articulate_nonce_action' ) ){
		if($_POST['iea_save'] == 'lightbox')
		{	
			$opt=articulate_get_quiz_embeder_options();
			$cbox_themes=articulate_quiz_embeder_get_colorbox_themes();
			//echo "<pre>"; print_r($_POST); echo "</pre>";
			$opt['lightbox_script']=$_POST['lightbox_script'];
			$opt['colorbox_transition']=$_POST['colorbox_transition'];
			$opt['colorbox_theme']=$_POST['colorbox_theme'];
			$opt['nivo_lightbox_effect']=$_POST['nivo_lightbox_effect'];
			$opt['nivo_lightbox_theme']=$_POST['nivo_lightbox_theme'];	
			$opt['size_opt']=$_POST['size_opt'];

			$opt['height']=intval($_POST['height']);
			$opt['width']=intval($_POST['width']);

			$opt['height_type']=$_POST['height_type'];
			$opt['width_type']=$_POST['width_type'];
			$buttons=array();
			if(isset($_POST['buttons']) && is_array($_POST['buttons']))
			{
			foreach($_POST['buttons'] as $btn){$btn=trim($btn); if($btn!=""){$buttons[]=$btn;}}
			}
			$opt['buttons']=$buttons;
			
			update_option('quiz_embeder_option', $opt);

		}	
}

}
}
function view(){
	$opt=articulate_get_quiz_embeder_options();
	$cbox_themes=articulate_quiz_embeder_get_colorbox_themes();
	echo '<link href="' . articulate_WP_QUIZ_EMBEDER_PLUGIN_URL . 'css/materialize.css?v=587" rel="stylesheet"></link>';
    echo '<script src="' . articulate_WP_QUIZ_EMBEDER_PLUGIN_URL . 'js/materialize.js?v=587"></script>';
    echo '<script src="' . articulate_WP_QUIZ_EMBEDER_PLUGIN_URL . 'js/jshelpers.js?v=587"></script>';
	?>
    <script type="text/javascript">
		function show_lightbox_settings()
		{  

			var val=articulatejq("#lightbox_script").val();
			if(val=="colorbox")
			{  
				articulatejq("#nivo_lightbox_settings").hide();
				articulatejq("#colorbox_settings").show();
			}
			else
			{	
				articulatejq("#colorbox_settings").hide();
				articulatejq("#nivo_lightbox_settings").show();
			}
		
		}
		
		function show_custom_size_options()	{ articulatejq("#custom_size_options").show(); }
		function hide_custom_size_options()	{ articulatejq("#custom_size_options").hide(); }
		
	</script>
	<link rel="stylesheet" href="<?php echo articulate_WP_QUIZ_EMBEDER_PLUGIN_URL; ?>nivo-lightbox/nivo-lightbox.css?v=587" type="text/css" />
	<link rel="stylesheet" href="<?php echo articulate_WP_QUIZ_EMBEDER_PLUGIN_URL; ?>nivo-lightbox/themes/default/default.css?v=587" type="text/css" />

	<script src="<?php echo articulate_WP_QUIZ_EMBEDER_PLUGIN_URL?>nivo-lightbox/nivo-lightbox.min.js?v=587"></script>
	<!--end nivo lite box-->	
	<!--QUIZ_EMBEDER START-->
	
	<link rel="stylesheet" id="colorbox_css" href="<?php echo articulate_WP_QUIZ_EMBEDER_PLUGIN_URL . "colorbox/themes/default/colorbox.css?v=587" ;?>" />
	<script type="text/javascript" src="<?php echo articulate_WP_QUIZ_EMBEDER_PLUGIN_URL . "colorbox/jquery.colorbox-min.js?v=587" ;?>" ></script>
	<script type="text/javascript">
		function get_colorbox_css_url(theme)
		{
			var url="<?php echo articulate_WP_QUIZ_EMBEDER_PLUGIN_URL?>colorbox/themes/"+theme+"/colorbox.css?v=587";
			return url;
		}
		//articulatejq(document).ready(function($){
		var articulatejq = jQuery.noConflict();
		articulatejq(document).ready(function() { 	
			var nivoLightboxTranslate = <?php echo json_encode( array('errorMessage'=>__('The requested content cannot be loaded. Please try again later.', 'quiz') ) ); ?>;		
			articulatejq('#nivo_lightbox_preview').nivoLightbox({
				effect: 'fall',                             // The effect to use when showing the lightbox
				theme: 'default',                           // The lightbox theme to use
				keyboardNav: true,                          // Enable/Disable keyboard navigation (left/right/escape)
				clickOverlayToClose: true,                  // If false clicking the "close" button will be the only way to close the lightbox
				onInit: function(){},                       // Callback when lightbox has loaded
				beforeShowLightbox: function(){},           // Callback before the lightbox is shown
				afterShowLightbox: function(lightbox){},    // Callback after the lightbox is shown
				beforeHideLightbox: function(){},           // Callback before the lightbox is hidden
				afterHideLightbox: function(){},            // Callback after the lightbox is hidden
				onPrev: function(element){},                // Callback when the lightbox gallery goes to previous item
				onNext: function(element){},                // Callback when the lightbox gallery goes to next item
				errorMessage: nivoLightboxTranslate.errorMessage // Error message when content can't be loaded
			});
			//Examples of how to assign the ColorBox event to elements
			articulatejq("#colorbox_preview").colorbox({iframe:true, width:"80%", height:"80%"});	
			
			
			articulatejq("#colorbox_theme").click(function(){
				var val=articulatejq(this).val();
				articulatejq("#colorbox_css").remove();
				var url=get_colorbox_css_url(val);
				articulatejq("head").append('<link rel="stylesheet" id="colorbox_css" href="'+url+'" />');
			});
						
		});
		
		
		function preview_lightbox()
		{
			
			var val=articulatejq("#lightbox_script").val();
			if(val=="colorbox")
			{
				var theme=articulatejq("#colorbox_theme").val();
				
				//articulatejq("#colorbox_css").attr("href", get_colorbox_css_url(theme)); 
				articulatejq("#colorbox_preview").trigger('click');
			}
			else
			{
				articulatejq("#nivo_lightbox_preview").trigger('click');

			}
			//articulatejq.colorbox({href:"http://www.articulate.com"}); //This method allows you to call Colorbox without having to assign it to an element.
		
		}
		
		
	</script>
<style type="text/css">
	.button_box{ margin-bottom:5px; padding:2px; border:1px solid #FFFFFF;}
	.button_box .close_button{ width:16px; height:16px; position:absolute; background:url(<?php echo articulate_WP_QUIZ_EMBEDER_PLUGIN_URL?>/close_16.png) no-repeat; margin-left:369px; cursor:pointer; }
	.button_box:hover{background:#fff;}
	.imgbox{min-height:18px;}
	.imgbox img{max-width:300px;}

	.image_source{width:277px;}
	
</style>

     <div style="background-color:#FFF;padding:10px;margin:10px;max-width:700px">
      <h2 class="header"><?php _e('Lightbox Settings', 'quiz')?></h2>
     <form action="" method="post" >
		<input type="hidden" name="iea_save" value="lightbox">
		<?php wp_nonce_field( 'articulate_nonce_action', 'articulate_nonce_field' ); ?>
		<table class="widefat"><tr><td>

    <select onchange="show_lightbox_settings()" id="lightbox_script" name="lightbox_script" class="materialize_me">
      <option value="colorbox" <?php echo ($opt["lightbox_script"]=="colorbox")?' selected="selected"':""?> >Color Box</option>
		<option value="nivo_lightbox" <?php echo ($opt["lightbox_script"]=="nivo_lightbox")?' selected="selected"':""?>>Nivo Lightbox</option>
    </select>
    <label><?php _e('Lightbox Style', 'quiz')?></label>
  
	<h4 class="header"><?php _e('Lightbox Settings', 'quiz')?></h4>
	<div id="colorbox_settings" <?php echo ($opt["lightbox_script"]=="colorbox")?' style="display:block"':'style="display:none"'?>>
	<label style="width:90px; display:inline-block;" ><?php _e('Transition', 'quiz')?></label>
	<select name="colorbox_transition" class="materialize_me">
		<option value="elastic" <?php echo ($opt["colorbox_transition"]=="elastic")?' selected="selected"':""?>>Elastic</option>
		<option value="fade" <?php echo ($opt["colorbox_transition"]=="fade")?' selected="selected"':""?>>Fade</option>
		<option value="none" <?php echo ($opt["colorbox_transition"]=="none")?' selected="selected"':""?>>None</option>
	</select>	
	<br />
	<label  style="width:90px; display:inline-block;" ><?php _e('Theme', 'quiz')?></label>

	<select name="colorbox_theme" id="colorbox_theme" class="materialize_me">
		<?php 
		foreach($cbox_themes as $key=>$tm)
		{?>
		<option value="<?php echo $key ?>" <?php echo ($opt["colorbox_theme"]==$key)?' selected="selected"':""?>><?php echo $tm["text"];?></option>		
		<?php }?>
	</select>
	</div>
	<div id="nivo_lightbox_settings" <?php echo ($opt["lightbox_script"]=="nivo_lightbox")?' style="display:block"':'style="display:none"'?>>
	<label style="width:90px; display:inline-block;" ><?php _e('Effect', 'quiz')?></label>
	<select name="nivo_lightbox_effect" class="materialize_me">
		<option value="fade" <?php echo ($opt["nivo_lightbox_effect"]=="fade")?' selected="selected"':""?>>Fade</option>
		<option value="fadeScale" <?php echo ($opt["nivo_lightbox_effect"]=="fadeScale")?' selected="selected"':""?>>FadeScale</option>
		<option value="slideLeft" <?php echo ($opt["nivo_lightbox_effect"]=="slideLeft")?' selected="selected"':""?>>SlideLeft</option>
		<option value="slideRight" <?php echo ($opt["nivo_lightbox_effect"]=="slideRight")?' selected="selected"':""?>>SlideRight</option>
		<option value="slideUp" <?php echo ($opt["nivo_lightbox_effect"]=="slideUp")?' selected="selected"':""?>>SlideUp</option>
		<option value="slideDown" <?php echo ($opt["nivo_lightbox_effect"]=="slideDown")?' selected="selected"':""?>>SlideDown</option>
		<option value="fall" <?php echo ($opt["nivo_lightbox_effect"]=="fall")?' selected="selected"':""?>>Fall</option>
	</select>	
	<br />
	<label  style="width:90px; display:inline-block;" ><?php _e('Theme', 'quiz')?></label>
	<select name="nivo_lightbox_theme" class="materialize_me">
		<option value="default" <?php echo ($opt["nivo_lightbox_theme"]=="default")?' selected="selected"':""?>>Default</option>
	</select>	
	</div>
	
		
	<h4 class="header"><?php _e('Size Options', 'quiz')?></h4>
	<input type="radio" name="size_opt" id="size_opt_default" value="default" <?php echo ($opt["size_opt"]=="default")?' checked="checked"':""?> onclick="hide_custom_size_options()" /><label for="size_opt_default" >Default</label><br />
	<input type="radio" name="size_opt" id="size_opt_custom"  value="custom" <?php echo ($opt["size_opt"]=="custom")?' checked="checked"':""?> onclick="show_custom_size_options()" /><label for="size_opt_custom" >Custom</label><br />
	<div id="custom_size_options" <?php echo ($opt["size_opt"]=="custom")?' style="display:block"':'style="display:none"'?>>
	<br/>
	<label  style="width:90px; display:inline-block;"class="active" ><?php _e('Height', 'quiz')?></label><input type="text" class="validate"name="height" size="3" maxlength="4" value="<?php echo (intval($opt["height"]))?>" /><select style="margin-top:-2px;" name="height_type" class="materialize_me"><option value="px" <?php echo ($opt["height_type"]=="px")?' selected="selected"':""?>>px</option><option value="%" <?php echo ($opt["height_type"]=="%")?' selected="selected"':""?> >%</option></select><br />
	<label  style="width:90px; display:inline-block;" class="active"><?php _e('Width', 'quiz')?></label><input type="text" class="validate" name="width" size="3" maxlength="4" value="<?php echo (intval($opt["width"]))?>" /><select style="margin-top:-2px;" name="width_type" class="materialize_me"><option value="px" <?php echo ($opt["width_type"]=="px")?' selected="selected"':""?>>px</option><option value="%" <?php echo ($opt["width_type"]=="%")?' selected="selected"':""?>>%</option></select><br />
	</div>
	<br /><br />
	<a href="https://community.articulate.com/hubs/360" id="nivo_lightbox_preview" data-lightbox-type="iframe" title="<?php echo esc_attr(__('Lightbox Preview: Sample Title (optional)') )?>" style="display:none;"><?php _e('Nivo Preview', 'quiz')?></a>
	<a href="https://community.articulate.com/hubs/360" id="colorbox_preview"  style="display:none;"  title="<?php echo esc_attr(__('Lightbox Preview: Sample Title (optional)') )?>"><?php _e('Colorbox Preview', 'quiz')?></a>
	<input type="button" value="<?php echo esc_attr(__('Preview Lightbox') )?>" class="waves-effect waves-light btn" onclick="preview_lightbox()" /> &nbsp;&nbsp;
    <input type="submit" value="<?php echo esc_attr(__('Save', 'quiz') )?>" name="save" class="waves-effect waves-light btn" />    
	</td></tr></table>
    


    
    </form>
    </div>
     
    <?php
	
}
	
	
	
	
	
}
