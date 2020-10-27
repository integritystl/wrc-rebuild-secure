<?php 
$articulate_iea_admin_buttons = new articulate_iea_admin_buttons;
add_action("iea_admin_menu", array($articulate_iea_admin_buttons  , 'menu'));
add_action("admin_init", array($articulate_iea_admin_buttons  , 'save'));
class articulate_iea_admin_buttons{


	
	
function menu(){
	
add_submenu_page ( 'articulate_content', __('Custom Buttons', 'quiz'),  __('Custom Buttons', 'quiz'), "manage_options", 'articulate-settings-button',array($this, 'view') );	
	
}
function save(){

if(isset($_POST['iea_save'])){

if (wp_verify_nonce( $_POST['articulate_nonce_field'], 'articulate_nonce_action' ) ){
	if($_POST['iea_save'] == 'buttons')
	{
		$opt=articulate_get_quiz_embeder_options();
		$cbox_themes=articulate_quiz_embeder_get_colorbox_themes();
		//echo "<pre>"; print_r($_POST); echo "</pre>";
		/*$opt['lightbox_script']=$_POST['lightbox_script'];
		$opt['colorbox_transition']=$_POST['colorbox_transition'];
		$opt['colorbox_theme']=$_POST['colorbox_theme'];
		$opt['nivo_lightbox_effect']=$_POST['nivo_lightbox_effect'];
		$opt['nivo_lightbox_theme']=$_POST['nivo_lightbox_theme'];	
		$opt['size_opt']=$_POST['size_opt'];

		$opt['height']=intval($_POST['height']);
		$opt['width']=intval($_POST['width']);

		$opt['height_type']=$_POST['height_type'];
		$opt['width_type']=$_POST['width_type'];*/
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
	
	?>
 	
    <h2 class="header"><?php _e('Button Settings', 'quiz')?></h2>
    <div style="background-color:#FFF;padding:10px;margin:10pxmax-width:700px">
     <form action="" method="post" >
	<input type="hidden" name="iea_save" value="buttons">
		
		<h4 class="header"><?php _e('Custom Buttons', 'quiz')?></h4> 
		<div id="button_area">
		<?php if(is_array($opt['buttons'])){
		
			foreach($opt['buttons'] as $btn){
		 ?>
			<div class="button_box">
			<div class="close_button" title="Delete"><i class="material-icons">delete</i></div>
			<div class="imgbox"><img src="<?php echo $btn?>"  /></div>
			<input type="text" size="20" name="buttons[]" class="image_source" value="<?php echo $btn ?>">
			</div>	
			<?php }}?>
		</div>
		<?php wp_nonce_field( 'articulate_nonce_action', 'articulate_nonce_field' ); ?>
		<div><input type="button" id="add_new_button_btn" class="waves-effect waves-light btn" value="Add Button" /> &nbsp;&nbsp;
		<input type="submit" value="Save Changes" name="save" class="waves-effect waves-light btn" /></div>
	</form>
    
  <style type="text/css">
	.button_box{ margin-bottom:5px; padding:2px; border:1px solid #FFFFFF;}
	.button_box .close_button{ width:16px; height:16px; position:absolute; color:#26a69a; margin-left:369px; cursor:pointer; }
	.button_box:hover{background:#fff;}
	.imgbox{min-height:18px;}
	.imgbox img{max-width:300px;}

	.image_source{width:277px;}
	
	</style>
    
    
    <?php
	
}
	
	
	
	
	
}
