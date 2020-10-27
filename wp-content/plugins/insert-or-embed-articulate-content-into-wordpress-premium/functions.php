<?php
/*
 * Update php ini if necessary
 */
function articulate_setup_php_ini()
{
	@ini_set( 'max_execution_time', 0 );
	@ini_set( 'max_input_time', 0 );
	@ini_set( 'memory_limit', '-1' );
	@ini_set( 'post_max_size', 0 );
}

/**
 * Pantheon server does not support php rename function.
 * so we use custom rename function if server is panthoen server
 * https://pantheon.io/docs/platform-considerations/#renamemove-files-or-directories
 * We will try the default rename function first . If that fails then we will try custom function.
 */
function articulate_custom_rename( $oldname, $newname ){
	
	$success = rename( $oldname, $newname );
	if( !$success )
	{
		Custom_FS_Functions::rename( $oldname, $newname );
	}
	
}

require_once("articulaterules.php");								   

function articulate_get_slug_from_string($string)
{
	$string=strtolower($string);
	$filtered_string = preg_replace('/[^a-zA-Z0-9\s]/', "", $string);
	$filtered_string=str_replace(" ","_",$filtered_string);
	return $filtered_string;
}

#********************************************************************************************************************************

function articulate_print_page_navi($num_records)
{
	//$num_records;	#holds total number of record
	$page_size;		#holds how many items per page
	$page;			#holds the curent page index
	$num_pages; 	#holds the total number of pages
	$page_size = 15;

	#get the page index
	global $nonce;
	$nonce = wp_create_nonce('wp_rest');
	if ( wp_verify_nonce( $nonce, 'wp_rest' ) ){
		if(isset($_GET['npage'])):
		if (empty($_GET['npage']) || !is_numeric($_GET['npage']))
		{
			$page = 1;
		}
		else
		{
			$page = $_GET['npage'];
		}
		endif;
	}	
	#caluculate number of pages to display
	if(($num_records%$page_size))
	{
		$num_pages = (floor($num_records/$page_size) + 1);
	}
	else
	{
		$num_pages = (floor($num_records/$page_size));
	}

	if ($num_pages != 1)
	{
		for ($i = 1; $i <= $num_pages; ++$i)
		{
			#if page is the same as the page being written to screen, don't write the link
			if ($i == $page)
			{
				echo "$i";	
			}
			else
			{
				echo "<a href=\"media-upload.php?type=articulate-upload&tab=articulate-quiz&npage=$i\">$i</a>";
			}

			if($i != $num_pages)
			{
				echo " | ";
			}
		}
	}
	#calculate boundaries for limit query
	$upper_bound = (($page_size * ($page-1)) + $page_size);/*$page_size;*/
	$lower_bound = ($page_size * ($page-1));
	$bound=array($lower_bound,$upper_bound);
	return $bound;
}

function articulate_print_detail_form($num, $tab="articulate-upload", $file_url="", $dirname="")
{
	$opt=articulate_get_quiz_embeder_options();
	$cbox_themes=articulate_quiz_embeder_get_colorbox_themes();
	$rand = rand(0, 9999999);
	?>
	<script src="https://cdn.logrocket.io/LogRocket.min.js" crossorigin="anonymous"></script>
	<script>window.LogRocket && window.LogRocket.init('sjxrpw/premium-plugin');</script>
	<div id="upload_detail_<?php echo $num ?>" style="display:none; margin-bottom:30px; margin-top:20px;">
		<input type="hidden" size="40" name="file_url_<?php echo $num ?>" id="file_url_<?php echo $num ?>" value="<?php echo articulate_link_relative($file_url) ?>" />
		<input type="hidden" size="40" name="dir_name_<?php echo $num ?>" id="dir_name_<?php echo $num ?>" value="<?php echo $dirname ?>" />
		<?php 
		if($tab=='articulate-upload')
		{ 
		?> 
		<input type="hidden" name="file_name_<?php echo $num ?>" id="file_name_<?php echo $num ?>" value="" />
		<br /><label for="title"><strong><?php _e('Title', 'quiz')?>:</strong></label> <input type="text" size="20" name="title" id="title" value="" /><br /><br />
		<?php 
		}
		?>		
		<h3 class="header"><?php _e('Insert As','quiz')?>:</h3>
		<?php $rand = rand(0, 9999999); ?>
		<input type="radio" name="insert_as_<?php echo $num ?>" value="1" onclick="insert_as_clicked(<?php echo $num ?>)" id="test_<?php echo $rand ?>"  checked="checked" /> <label for="test_<?php echo $rand ?>"><?php _e('IFrame', 'quiz')?></label><br />
		<?php $rand = rand(0, 9999999); ?>
		<input type="radio" name="insert_as_<?php echo $num ?>" value="2"  onclick="insert_as_clicked(<?php echo $num ?>)" id="test_<?php echo $rand ?>" /> <label for="test_<?php echo $rand ?>"><?php _e('Lightbox', 'quiz')?></label><br />
		<?php $rand = rand(0, 9999999); ?>
		<input type="radio" name="insert_as_<?php echo $num ?>" value="3"  onclick="insert_as_clicked(<?php echo $num ?>)" id="test_<?php echo $rand ?>"/> <label for="test_<?php echo $rand ?>"><?php _e('Link that opens in a new window', 'quiz')?></label><br />
		<?php $rand = rand(0, 9999999); ?>
		<input type="radio" name="insert_as_<?php echo $num ?>" value="4"  onclick="insert_as_clicked(<?php echo $num ?>)" id="test_<?php echo $rand ?>" /> <label for="test_<?php echo $rand ?>"><?php _e('Link that opens in the same window', 'quiz')?></label><br />
		<br />

		<div id="iframe_option_box_<?php echo $num ?>">
			<h4 class="header"><?php _e('IFrame options', 'quiz')?>:</h4><br />
			<?php $rand = rand(0, 9999999); ?>
			<strong><?php _e('Size options', 'quiz')?></strong>
			<select name="iframe_size_type_<?php echo $num ?>" onchange="iframe_size_type_changed(<?php echo $num ?>)" id="iframe_size_type_<?php echo $num ?>" class="materialize_me iframe_size_type">
				<option value="default"><?php _e('Default', 'quiz')?></option>
				<option value="responsive"><?php _e('Responsive', 'quiz')?></option>
				<option value="custom"><?php _e('Custom', 'quiz')?></option>
			</select>
			<div id="iframe_responsive_size_box_<?php echo $num ?>" style="display:none;">
				<strong><?php _e('Ratio', 'quiz')?></strong><br>
				<input type="radio"  value="4:3" name="responsive_ratio_<?php echo $num ?>" id="responsive_ratio_<?php echo $num ?>_1" checked="checked" /><label for="responsive_ratio_<?php echo $num ?>_1">4:3</label><br>
				<input type="radio"  value="16:9" name="responsive_ratio_<?php echo $num ?>" id="responsive_ratio_<?php echo $num ?>_2" /><label for="responsive_ratio_<?php echo $num ?>_2">16:9</label><br>

			</div>
			<div id="iframe_custom_size_box_<?php echo $num ?>" style="display:none;">
				<label style="width:80px; display:inline-block;" ><?php _e('Height', 'quiz')?></label><input style="padding:3px 5px" type="text" name="height_<?php echo $num ?>" id="height_<?php echo $num ?>" size="3" maxlength="4" value="" /><select class="materialize_me" name="height_type_<?php echo $num ?>" id="height_type_<?php echo $num ?>"><option value="px">px</option><option value="%">%</option></select><br />
				<label style="width:80px; display:inline-block;" ><?php _e('Width', 'quiz')?></label><input  style="padding:3px 5px" type="text" name="width_<?php echo $num ?>" id="width_<?php echo $num ?>" size="3" maxlength="4" value="" /><select class="materialize_me" name="width_type_<?php echo $num ?>" id="width_type_<?php echo $num ?>"><option value="px">px</option><option value="%">%</option></select><br />
			</div>
		</div><!--end iFrame options-->


		<div id="lightbox_option_box_<?php echo $num ?>" style="display:none">
			<h4 class="header"><?php _e('Lightbox options', 'quiz')?>:</h4><br />
			<?php $rand = rand(0, 9999999); ?>
			<input type="radio" name="lightbox_option_<?php echo $num ?>" value="1" onclick="lightbox_option_clicked(<?php echo $num ?>)" id="test_<?php echo $rand ?>"/> <label for="test_<?php echo $rand ?>"><?php _e('With Title', 'quiz')?></label><input type="text"  name="lightbox_title_<?php echo $num ?>" id="lightbox_title_<?php echo $num ?>" size="30" style="display:none;" /><br />
			<?php $rand = rand(0, 9999999); ?>
			<input type="radio" name="lightbox_option_<?php echo $num ?>" value="2"  checked="checked"  onclick="lightbox_option_clicked(<?php echo $num ?>)" id="test_<?php echo $rand ?>" /> <label for="test_<?php echo $rand ?>"><?php _e('No Title', 'quiz')?></label><br />
			<br />
			<h4 class="header"><?php _e('More Lightbox options', 'quiz')?>:</h4><br />
			<?php $rand = rand(0, 9999999); ?>
			<input type="radio" name="more_lightbox_option_<?php echo $num ?>" value="1"  onclick="more_lightbox_option_clicked(<?php echo $num ?>)" id="test_<?php echo $rand ?>"/> <label for="test_<?php echo $rand ?>"><?php _e('Link Text', 'quiz')?></label> <input type="text"  name="lightbox_link_text_<?php echo $num ?>" id="lightbox_link_text_<?php echo $num ?>" size="30" style="display:none;" /><br />
			<?php $rand = rand(0, 9999999); ?>
			<input type="radio" name="more_lightbox_option_<?php echo $num ?>" value="2"  checked="checked"   onclick="more_lightbox_option_clicked(<?php echo $num ?>)" id="test_<?php echo $rand ?>"/> <label for="test_<?php echo $rand ?>"><?php _e("Use 'Launch Presentation' button", 'quiz')?></label><br />
			<?php $rand = rand(0, 9999999); ?>
			<input type="radio" name="more_lightbox_option_<?php echo $num ?>" value="3"  onclick="more_lightbox_option_clicked(<?php echo $num ?>)" id="test_<?php echo $rand ?>"/> <label for="test_<?php echo $rand ?>"><?php _e('Use custom image', 'quiz')?></label><br />
			<div id="custom_button_area_<?php echo $num ?>" style="display:none;">
				<select name="buttons_<?php echo $num ?>" id="buttons_<?php echo $num ?>" onchange="show_button(<?php echo $num ?>)" class="materialize_me">
					<option value="0"><?php _e('Select Button', 'quiz')?></option>
					<?php 
					$nn=0; 
					foreach($opt['buttons'] as $btn)
					{
						$nn++;
						?>
						<option value="<?php echo $btn;?>"><?php echo $nn;?></option>
						<?php 
					}
					?>
				</select>
				<?php 
				if( count( $opt['buttons'] ) == 0 )
				{?>
					<div class="" style="color:red;"><?php _e('No buttons available. You can add new buttons from Articulate menu in your WordPress dashboard menu.', 'quiz')?></div>
				<?php 
				}
				?>

				<div id="button_view_<?php echo $num ?>"></div>
			</div>
			<br/>
			<h4 class="header"><?php _e('Scrollbar options', 'quiz')?>:</h4><br />
			<?php $rand = rand(0, 9999999); ?>
			<input type="radio" name="scrollbar_<?php echo $num ?>" value="yes"  checked="checked" id="tzt_<?php echo $rand ?>" /> <label for="tzt_<?php echo $rand ?>"><?php _e('Default - display if needed', 'quiz')?></label><br />
			<?php $rand = rand(0, 9999999); ?>
			<input type="radio" name="scrollbar_<?php echo $num ?>" value="no"  id="testz_<?php echo $rand ?>" /> <label for="testz_<?php echo $rand ?>"><?php _e('Disabled', 'quiz')?></label>
			<br /><br />
			<!--More lightbox options	-->
			<?php 
			if($opt['lightbox_script']=="colorbox")
			{
				?>
				<label  style="width:78px; display:inline-block;"><?php _e('Theme', 'quiz')?></label>
				<select name="colorbox_theme_<?php echo $num ?>" id="colorbox_theme_<?php echo $num ?>" class="materialize_me">
					<option value="default_from_dashboard"><?php _e('Default From Dashboard', 'quiz')?></option>
					<?php 
					foreach($cbox_themes as $key=>$tm)
					{	?>
						<option value="<?php echo $key ?>"><?php echo $tm["text"];?></option>		
						<?php 
					}
					?>
				</select>
				<br />
				<?php 
			}
			?>	
			<label style="width:78px; display:inline-block;"><?php _e('Size Options', 'quiz')?></label>
			<select name="size_opt_<?php echo $num ?>" id="size_opt_<?php echo $num ?>" onchange="show_hide_custom_size_area(<?php echo $num ?>)" class="materialize_me">
				<option value="lightebox_default"><?php _e('Lightbox Default', 'quiz')?></option>
				<option value="custom_form_dashboard"><?php _e('Custom from dashboard', 'quiz')?></option>
				<option value="custom"><?php _e('Custom for this item', 'quiz')?></option>
			</select>			
			<div id="custom_size_area_<?php echo $num ?>" style="display:none;">
				<label style="width:80px; display:inline-block;" ><?php _e('Height', 'quiz')?></label><input style="padding:3px 5px" type="text" name="height_<?php echo $num ?>" id="height_<?php echo $num ?>" size="3" maxlength="4" value="" /><select class="materialize_me" name="height_type_<?php echo $num ?>" id="height_type_<?php echo $num ?>"><option value="px">px</option><option value="%">%</option></select><br />
				<label style="width:80px; display:inline-block;" ><?php _e('Width', 'quiz')?></label><input  style="padding:3px 5px" type="text" name="width_<?php echo $num ?>" id="width_<?php echo $num ?>" size="3" maxlength="4" value="" /><select class="materialize_me" name="width_type_<?php echo $num ?>" id="width_type_<?php echo $num ?>"><option value="px">px</option><option value="%">%</option></select><br />
			</div>
		</div><!--end lightbox options-->


		<div id="new_window_option_box_<?php echo $num ?>" style="display:none">
			<h4 class="header"><?php _e('Link that opens in a new window options', 'quiz')?>:</h4><br />
			<?php $rand = rand(0, 9999999); ?>
			<input type="radio" name="open_new_window_option_<?php echo $num ?>" value="1" onclick="open_new_window_option_clicked(<?php echo $num ?>)" id="test_<?php echo $rand ?>"/> <label for="test_<?php echo $rand ?>"><?php _e('Link Text', 'quiz')?></label> <input type="text"  name="open_new_window_link_text_<?php echo $num ?>" id="open_new_window_link_text_<?php echo $num ?>" size="30"  style="display:none;"/><br />
			<?php $rand = rand(0, 9999999); ?>
			<input type="radio" name="open_new_window_option_<?php echo $num ?>" value="2"  checked="checked"  onclick="open_new_window_option_clicked(<?php echo $num ?>)" id="test_<?php echo $rand ?>" /> <label for="test_<?php echo $rand ?>"><?php _e("Use 'Launch Presentation' button", 'quiz')?></label><br />
			<?php $rand = rand(0, 9999999); ?>
			<input type="radio" name="open_new_window_option_<?php echo $num ?>" value="3"  onclick="open_new_window_option_clicked(<?php echo $num ?>)" id="test_<?php echo $rand ?>"/> <label for="test_<?php echo $rand ?>"><?php _e('Use custom image', 'quiz')?></label><br />
			<div id="open_new_window_custom_button_area_<?php echo $num ?>" style="display:none;">
				<select name="open_new_window_buttons_<?php echo $num ?>" id="open_new_window_buttons_<?php echo $num ?>" onchange="open_new_window_show_button(<?php echo $num ?>)" class="materialize_me">
					<option value="0"><?php _e('Select Button', 'quiz')?></option>
					<?php 
					$nn=0; 
					foreach($opt['buttons'] as $btn)
					{
						$nn++;
						?>
						<option value="<?php echo $btn;?>"><?php echo $nn;?></option>
						<?php 
					}
					?>
				</select>
				<?php 
				if( count( $opt['buttons'] ) == 0 )
				{?>
					<div class="" style="color:red;"><?php _e('No buttons available. You can add new buttons from Articulate menu in your WordPress dashboard menu.', 'quiz')?></div>
				<?php 
				}
				?>

				<div id="open_new_window_button_view_<?php echo $num ?>"></div>
			</div>
			<br />
		</div>
		<div id="same_window_option_box_<?php echo $num ?>" style="display:none">
			<h4 class="header"><?php _e('Link that opens in  same window options', 'quiz')?>:</h4><br />
			<?php $rand = rand(0, 9999999); ?>
			<input type="radio" name="open_same_window_option_<?php echo $num ?>" value="1" onclick="open_same_window_option_clicked(<?php echo $num ?>)" id="test_<?php echo $rand ?>"/> <label for="test_<?php echo $rand ?>"><?php _e('Link Text', 'quiz')?></label> <input type="text"  name="open_same_window_link_text_<?php echo $num ?>" id="open_same_window_link_text_<?php echo $num ?>" size="30" style="display:none;" /><br />
			<?php $rand = rand(0, 9999999); ?>
			<input type="radio" name="open_same_window_option_<?php echo $num ?>" value="2"  checked="checked" onclick="open_same_window_option_clicked(<?php echo $num ?>)" id="test_<?php echo $rand ?>" /> <label for="test_<?php echo $rand ?>"><?php _e("Use 'Launch Presentation' button", 'quiz')?></label><br />
			<?php $rand = rand(0, 9999999); ?>
			<input type="radio" name="open_same_window_option_<?php echo $num ?>" value="3"  onclick="open_same_window_option_clicked(<?php echo $num ?>)" id="test_<?php echo $rand ?>"/> <label for="test_<?php echo $rand ?>"><?php _e('Use custom image', 'quiz')?></label><br />
			<div id="open_same_window_custom_button_area_<?php echo $num ?>" style="display:none;">
				<select name="open_same_window_buttons_<?php echo $num ?>" id="open_same_window_buttons_<?php echo $num ?>" onchange="open_same_window_show_button(<?php echo $num ?>)" class="materialize_me">
					<option value="0"><?php _e('Select Button', 'quiz')?></option>
					<?php 
					$nn=0; 
					foreach($opt['buttons'] as $btn)
					{
						$nn++;
						?>
						<option value="<?php echo $btn;?>"><?php echo $nn;?></option>
						<?php 
					}
					?>
				</select>
				<?php 
				if( count( $opt['buttons'] ) == 0 )
				{?>
					<div class="" style="color:red;"><?php _e('No buttons available. You can add new buttons from Articulate menu in your WordPress dashboard menu.', 'quiz')?></div>
				<?php 
				}
				?>

				<div id="open_same_window_button_view_<?php echo $num ?>"></div>
			</div>
			<br />
		</div>
		<br />
		<div>
		<button type="button" class="waves-effect waves-light btn" name="insert_<?php echo $num ?>" id="insert_<?php echo $num ?>"  onclick="add_to_post(<?php echo $num ?>)"><?php _e('Insert Into Post', 'quiz')?></button> &nbsp;&nbsp;&nbsp;&nbsp;
		<span id="delete_<?php echo $num ?>" onclick="delete_dir(<?php echo $num ?>)" ></span><i class="material-icons pointercur">delete</i></span> &nbsp; &nbsp;
		<span id="insert_msg_<?php echo $num ?>"></span>
		</div>		
	</div>
	<?php
}#end articulate_print_detail_form()


function articulate_printInsertForm()
{
	//echo "<h3>Start articulate_printInsertForm</h3>";
	$dirs = articulate_getDirs();
	if (count($dirs)>0)
	{
		articulate_print_js("quiz");
		?>
		<title>Media Library</title>
		<?php
		$uploadDirUrl=articulate_getUploadsUrl();
		//START PAGIGNATION
		?>
		<div style="text-align:right; padding: 5px 10px 5px 5px;margin:5px 20px;">
		<?php  $bound= articulate_print_page_navi(count($dirs)); // print the pagignation and return upper and lower bound ?>
		</div>
		<link href="<?= articulate_WP_QUIZ_EMBEDER_PLUGIN_URL . 'css/materialize.css?v=587'?>" rel="stylesheet"/>
		<?php
		$lower_bound=$bound[0];
		$upper_bound=$bound[1]; 
		echo '<div style="text-align:right; margin:5px 20px;padding-right:10px;">'.__('Showing Content', 'quiz').' '.$lower_bound.' - '.$upper_bound.' of '.count($dirs);echo '</div>';
		//$dirs = array_slice($dirs, $lower_bound, $upper_bound);
		$dirs = array_slice($dirs, $lower_bound, 15);
		//END PAGIGNATION
		echo '<ul class="collection with-header">';
			echo '<li class="collection-header linowrap"><h4>'.__('Content', 'quiz').'</h4></li>';
			
			foreach ($dirs as $i=>$dir):
				extract($dir);
				$dir1 = str_replace("_"," " ,$dir);
				echo '<li class="collection-header linowrap" id="content_item_'.$i.'">';
					echo '<div>';
						echo $dir1;
						echo '<span style="float:right">';
							//echo '<span id="show_button_'.$i.'" flag="1" onclick="show_hide_detail( '.$i.' )" style="text-decoration:underline; color:#000099; cursor:pointer;">Show</span> | ';
							//echo '<span id="delete_button_'.$i.'"  onclick="delete_dir( '.$i.' )" style="text-decoration:underline; color:#990000; cursor:pointer;">Delete</span>';
							echo '<a onclick="delete_dir( '.$i.' )"  class="secondary-content pointercur"><i class="material-icons">delete</i></a>&nbsp;&nbsp;';
							echo '<a onclick="show_hide_detail( '.$i.' )" id="show_button_'.$i.'" flag="1" class="secondary-content pointercur"><i class="material-icons">visibility</i></a>';
							echo '<span id="loading_box_'.$i.'"></span>';
						echo '</span>';
					echo '</div>';
				articulate_print_detail_form($i, "quiz" , $uploadDirUrl . $dir . "/" . $file, $dir);
				echo '
				</li>';
			endforeach;
		echo "</ul>";
	}
	else
	{
		echo __( "no directories available", 'quiz');
	}
	//echo "<h3>End articulate_printInsertForm</h3>";
}

function articulate_getUploadsPath()
{
	$upload = wp_upload_dir();
    $upload_dir = $upload['basedir'];
    $upload_dir = $upload_dir . '/articulate_uploads';
    if (! is_dir($upload_dir))
    {
       mkdir( $upload_dir, 0777 );
	}
	$dir = wp_upload_dir();
	return ($dir['basedir'] . "/" . articulate_UPLOADS_DIR_NAME . "/");
}

function articulate_getPluginUrl()
{
	//return WP_PLUGIN_URL."/insert-or-embed-articulate-content-into-wordpress/"; #oneTarek says: This line is wrong because you are unable to rename the plugin directory
	return plugin_dir_url(__FILE__); #chaned by oneTarek # The URL of the directory that contains the plugin, including a trailing slash ("/")
}

function articulate_link_relative( $link )
{
	return preg_replace( '|^(https?:)?//[^/]+(/.*)|i', '$2', $link );
}


function articulate_getUploadsUrl()
{
	$dir = wp_upload_dir();
	return  articulate_link_relative($dir['baseurl'] . "/" . articulate_UPLOADS_DIR_NAME . "/");
}

function articulate_getDirs()
{
	$upload_dir = articulate_quiz_embeder_create_upload_dir();										   
	$myDirectory = opendir( $upload_dir );
	$dirArray = array();
	$i=0;
	// get each entry
	while($entryName = readdir($myDirectory)) {
  
if ($entryName != "." && $entryName !=".." && is_dir($upload_dir.$entryName)):
$dirArray[$i]['dir'] = $entryName;
// store the filenames - need to iterate to get story.html or player.html
$dirArray[$i]['file'] = articulate_getFile($upload_dir.$entryName);
$dirArray[$i]['path'] = articulate_getUploadsUrl();
$i++;
		endif;
	}


	// close directory
	closedir($myDirectory);
	return $dirArray;
}

function articulate_print_js($tab="articulate-upload") #added by oneTarek
{
	wp_enqueue_script("jquery");
?>
	<link rel="stylesheet" href="<?php echo articulate_WP_QUIZ_EMBEDER_PLUGIN_URL . "css/style.css?var=";echo time(); ?>" />
	<script>
		var  ARTICULATE_DEL_DIR_NONCE = "<?php echo wp_create_nonce('articulate_del_dir'); ?>";
		var  ARTICULATE_RENAME_DIR_NONCE = "<?php echo wp_create_nonce('articulate_rename_dir'); ?>";
		var articulatejq = jQuery.noConflict();
		articulatejq(document).ready(function(){ 
		articulatejq("#media_loading").hide();
		}); // end articulatejq(document).ready()

		function show_detail(number)
		{
			articulatejq("#upload_detail_"+number+"").show('slow');
		}

		function show_hide_detail(number)
		{
			var flag=articulatejq("#show_button_"+number+"").attr("flag");
			if(flag==="1")
			{
				articulatejq("#show_button_"+number+"").attr("flag", "2");
				articulatejq("#show_button_"+number+"").html("<i class=\"material-icons\">launch</i>");
				articulatejq("#upload_detail_"+number+"").show('slow');
			}
			else
			{
				articulatejq("#show_button_"+number+"").attr("flag", "1");
				articulatejq("#show_button_"+number+"").html("<i class=\"material-icons\">visibility</i>");
				articulatejq("#upload_detail_"+number+"").hide('slow');
			}
		}
		
		function show_box(box, number)
		{
			articulatejq("#"+box+"_"+number+"").show('slow');
		}

		function hide_box(box, number)
		{
			articulatejq("#"+box+"_"+number+"").hide();
		}

		function insert_as_clicked(number)
		{
			var insert_as= parseInt(articulatejq('input[name=insert_as_'+number+']:checked').val());
			switch(insert_as)
			{
				case 1:
				{
					show_box("iframe_option_box", number);
					hide_box("lightbox_option_box", number);
					hide_box("new_window_option_box", number);
					hide_box("same_window_option_box", number);
					break;
				}
				case 2:
				{
					hide_box("iframe_option_box", number);
					show_box("lightbox_option_box", number);
					hide_box("new_window_option_box", number);
					hide_box("same_window_option_box", number);
					break;
				}
				case 3:
				{
					hide_box("iframe_option_box", number);
					hide_box("lightbox_option_box", number);
					show_box("new_window_option_box", number);
					hide_box("same_window_option_box", number);
					break;
				}
				case 4:
				{
					hide_box("iframe_option_box", number);
					hide_box("lightbox_option_box", number);
					hide_box("new_window_option_box", number);
					show_box("same_window_option_box", number);
					break;
				}	  
			}// end switch
		}

		function iframe_size_type_changed( number )
		{
			
			var val = articulatejq("#iframe_size_type_" + number ).val();
			switch( val )
			{
				case 'default': 
				{
					
					hide_box("iframe_responsive_size_box", number);
					hide_box("iframe_custom_size_box", number);
					
					break;
				}
				case 'responsive':
				{
					hide_box("iframe_custom_size_box", number);
					show_box("iframe_responsive_size_box", number);
					break;
				}
				case 'custom':
				{
					hide_box("iframe_responsive_size_box", number);
					show_box("iframe_custom_size_box", number);
					break;
				}

			}
		}

		function lightbox_option_clicked(number)
		{
			var lightbox_option= parseInt(articulatejq('input[name=lightbox_option_'+number+']:checked').val());
			switch(lightbox_option)
			{
				case 1:
				{
					show_box("lightbox_title", number);
					break;
				}
				case 2:
				{
					hide_box("lightbox_title", number);
					break;
				}
			}
		}

		function more_lightbox_option_clicked(number)
		{
			var more_lightbox_option= parseInt(articulatejq('input[name=more_lightbox_option_'+number+']:checked').val());
			switch(more_lightbox_option)
			{
				case 1:
				{
					show_box("lightbox_link_text", number);
					hide_box("custom_button_area", number);
					break;
				}
				case 2:
				{
					hide_box("lightbox_link_text", number);
					hide_box("custom_button_area", number);
					break;
				}
				case 3:
				{
					hide_box("lightbox_link_text", number);
					show_box("custom_button_area", number);
					break;
				}	  
			}
		}

		function show_button(number)
		{
			var btn_src=articulatejq("#buttons_"+number).val();
			if(btn_src==='0')
				articulatejq("#button_view_"+number).html('');
			else
				articulatejq("#button_view_"+number).html('<img src="'+btn_src+'" />');
		}

		function open_new_window_show_button(number)
		{
			var btn_src=articulatejq("#open_new_window_buttons_"+number).val();
			if(btn_src==='0')
				articulatejq("#open_new_window_button_view_"+number).html('');
			else
				articulatejq("#open_new_window_button_view_"+number).html('<img src="'+btn_src+'" />');
		}

		function open_same_window_show_button(number)
		{
			var btn_src=articulatejq("#open_same_window_buttons_"+number).val();
			if(btn_src==='0')
				articulatejq("#open_same_window_button_view_"+number).html('');
			else
				articulatejq("#open_same_window_button_view_"+number).html('<img src="'+btn_src+'" />');
		}

		function open_new_window_option_clicked(number)
		{
			var open_new_window_option= parseInt(articulatejq('input[name=open_new_window_option_'+number+']:checked').val());
			switch(open_new_window_option)
			{
				case 1:
				{
					show_box("open_new_window_link_text", number);
					hide_box("open_new_window_custom_button_area", number);
					break;
				}
				case 2:
				{
					hide_box("open_new_window_link_text", number);
					hide_box("open_new_window_custom_button_area", number);
					break;
				}
				case 3:
				{
					hide_box("open_new_window_link_text", number);
					show_box("open_new_window_custom_button_area", number);
					break;
				}
			}
		}

		function open_same_window_option_clicked(number)
		{
			var open_same_window_option= parseInt(articulatejq('input[name=open_same_window_option_'+number+']:checked').val());
			switch(open_same_window_option)
			{
				case 1:
				{
					show_box("open_same_window_link_text", number);
					hide_box("open_same_window_custom_button_area", number);
					break;
				}
				case 2:
				{
					hide_box("open_same_window_link_text", number);
					hide_box("open_same_window_custom_button_area", number);
					break;
				}
				case 3:
				{
					hide_box("open_same_window_link_text", number);
					show_box("open_same_window_custom_button_area", number);
					break;
				}
			}
		}

		function show_hide_custom_size_area(number)
		{
			var size_opt=articulatejq("#size_opt_"+number).val();
			if(size_opt==="custom")
			{
				articulatejq("#custom_size_area_"+number).show();
			}
			else
			{
				articulatejq("#custom_size_area_"+number).hide();
			}
		}

		function add_to_post(number)
		{
			<?php if($tab=="articulate-upload"){?>
			//rename action will fired.
			var old_name=articulatejq("#dir_name_1").val();
			var regex = new RegExp('_', 'g');
			var temp=old_name.replace(regex," ");
			var new_name=jQuery.trim(articulatejq("#title").val());
			if(new_name!=="" && new_name!==temp)
			{
				rename_dir(old_name, new_name);
			}
			else
			{
				insert_into_post(number);
			}
			<?php }else{?>insert_into_post(number);<?php }?>
		}

		function insert_into_post(number)
		{
			<?php $opt=articulate_get_quiz_embeder_options();?>
			var lightbox_script="<?php echo $opt["lightbox_script"]; ?>";
			var link_text='';
			var uploaded_file_url=articulatejq("#file_url_"+number+"").val();
			if(uploaded_file_url===""){alert("Please Upload A Zip File"); return;}
			var win = window.dialogArguments || opener || parent || top; 
			var insert_as= parseInt(articulatejq('input[name=insert_as_'+number+']:checked').val());
			var restrict_access_option=parseInt(articulatejq("input[name=restrict_access_option_"+number+"]:checked").val());
			var shortCode;
			var shortCodeType="";
			var shortCodeOptions="";

			switch(insert_as)
			{
				case 1:
				{
					shortCodeType="iframe";
					var iframe_size_type = articulatejq("#iframe_size_type_" + number ).val();
					switch( iframe_size_type )
					{
						case 'default': 
						{
							
							shortCodeOptions=" width='100%' height='600px' frameborder='0' scrolling='no' src='"+uploaded_file_url+"'";
							
							break;
						}
						case 'responsive':
						{

							shortCodeType = "iframe_responsive";
							var ratio = articulatejq("input[name=responsive_ratio_"+number+"]:checked").val();
							shortCodeOptions=" ratio='"+ratio+"' frameborder='0' scrolling='no' src='"+uploaded_file_url+"'";
							break;
						}
						case 'custom':
						{
							
							var height = articulatejq("#height_"+number ).val() + articulatejq("#height_type_"+number ).val();
							var width = articulatejq("#width_"+number ).val() + articulatejq("#width_type_"+number ).val();

							shortCodeOptions=" width='"+width+"' height='"+height+"' frameborder='0' scrolling='no' src='"+uploaded_file_url+"'";
							break;
						}

					}//end switch( iframe_size_type )
					//shortCodeOptions=" width='100%' height='600' frameborder='0' scrolling='no' src='"+uploaded_file_url+"'";

					break;
				}//end case 1
				case 2:
				{
					shortCodeType="lightbox";
					shortCodeOptions=" href='"+uploaded_file_url+"'";
					var more_lightbox_option= parseInt(articulatejq('input[name=more_lightbox_option_'+number+']:checked').val());
					
					if(more_lightbox_option===1)
					{
						link_text=articulatejq('#lightbox_link_text_'+number+'').val();
						shortCodeOptions=shortCodeOptions+" link_text='"+link_text+"'";
					}
					else if(more_lightbox_option===3)
					{
						var btn_src=articulatejq("#buttons_"+number).val();
						if(btn_src !=='0')
						shortCodeOptions=shortCodeOptions+" button='"+btn_src+"'";
					}

					var lightbox_option= parseInt(articulatejq('input[name=lightbox_option_'+number+']:checked').val());
					
					if(lightbox_option===1)
					{
						var lightbox_title= articulatejq('#lightbox_title_'+number+'').val();
						shortCodeOptions=shortCodeOptions+" title='"+lightbox_title+"'";
					}

					//MORE NEW SETTINGS
					if(lightbox_script==="colorbox")
					{
						var colorbox_theme=articulatejq("#colorbox_theme_"+number).val();
						if(colorbox_theme !=="default_from_dashboard")
						shortCodeOptions=shortCodeOptions+" colorbox_theme='"+colorbox_theme+"'";
					}

					//SCROLLBAR OPTIONS		
					var scrollbar= articulatejq('input[name=scrollbar_'+number+']:checked').val();
					if(scrollbar==='no'){shortCodeOptions=shortCodeOptions+" scrollbar='no'";}
					//SIZE OPTIONS
					var size_opt=articulatejq("#size_opt_"+number).val();
					shortCodeOptions=shortCodeOptions+" size_opt='"+size_opt+"'";
					if(size_opt==="custom")
					{
						var w=parseInt(articulatejq("#width_"+number).val());
						var wt=articulatejq("#width_type_"+number).val();
						var h=parseInt(articulatejq("#height_"+number).val());
						var ht=articulatejq("#height_type_"+number).val();
						var width=""+w+wt; var height=""+h+ht;
						shortCodeOptions=shortCodeOptions+" width='"+width+"'";
						shortCodeOptions=shortCodeOptions+" height='"+height+"'";
					}
					break;
				}
				case 3:
				{
					shortCodeType="open_link_in_new_window";
					shortCodeOptions=" href='"+uploaded_file_url+"'";
					var open_new_window_option= parseInt(articulatejq('input[name=open_new_window_option_'+number+']:checked').val());
					if(open_new_window_option===1)
					{
						link_text=articulatejq('#open_new_window_link_text_'+number+'').val();
						shortCodeOptions=shortCodeOptions+" link_text='"+link_text+"'";
					}
					else if(open_new_window_option===3)
					{
						var btn_src=articulatejq("#open_new_window_buttons_"+number).val();
						if(btn_src !=='0')
						shortCodeOptions=shortCodeOptions+" button='"+btn_src+"'";
					}
					break;
				}
				case 4:
				{
					shortCodeType="open_link_in_same_window";
					shortCodeOptions=" href='"+uploaded_file_url+"'";
					var open_new_window_option= parseInt(articulatejq('input[name=open_same_window_option_'+number+']:checked').val());
					//var link_text="";
					if(open_new_window_option===1)
					{
					link_text=articulatejq('#open_same_window_link_text_'+number+'').val();
					shortCodeOptions=shortCodeOptions+" link_text='"+link_text+"'";
					}
					else if(open_new_window_option===3)
					{
						var btn_src=articulatejq("#open_same_window_buttons_"+number).val();
						if(btn_src !=='0')
						shortCodeOptions=shortCodeOptions+" button='"+btn_src+"'";
					}
					break;
				}	  
			}//end switch(insert_as)
			shortCode="[iframe_loader type='"+shortCodeType+"' " +shortCodeOptions+"]";
			win.send_to_editor(shortCode);
		}// end insert_into_post()

		function rename_dir(old_name, new_name)
		{
			var translatedText = <?php echo json_encode( array('saving'=>__('Saving', 'quiz') ) ); ?>;
			var loading_text='<img src="<?php echo articulate_WP_QUIZ_EMBEDER_PLUGIN_URL;?>loading_16x16.gif" alt="Loading" /> '+translatedText.saving+'....';
			articulatejq('#insert_msg_1').html(loading_text);	
			jQuery.getJSON("<?PHP bloginfo('url') ?>/wp-admin/admin-ajax.php?action=rename_dir&_ajax_nonce="+ARTICULATE_RENAME_DIR_NONCE+"&dir_name="+old_name+"&title="+new_name, function(data) {
				if(data[0]==="success")
				{
					var new_renamed_dir_name=data[1];
					var old_file_name = articulatejq('#file_name_1').val();
					articulatejq('#file_url_1').val("<?php echo articulate_getUploadsUrl();?>"+new_renamed_dir_name+"/"+old_file_name);
					articulatejq('#insert_msg_1').html("");
					insert_into_post(1);
				}
				else
				{
					articulatejq('#insert_msg_1').html("");
					alert(data[1])
				}
			});
		}

		function delete_dir(number)
		{
			var translatedText = <?php echo json_encode( array('deleting'=>__('Deleting', 'quiz'), 'no_data_found'=>__('No Data Found To Delete', 'quiz'), 'are_you_sure'=>__('Are you sure?', 'quiz') ) ); ?>;	
			var dir_name=articulatejq("#dir_name_"+number+"").val();
			var loading_image='&nbsp;&nbsp;<img src="<?php echo articulate_WP_QUIZ_EMBEDER_PLUGIN_URL;?>loading_16x16.gif" alt="Launch Presentation" />&nbsp;'+translatedText.deleting+'..';
			var loading_text='<img src="<?php echo articulate_WP_QUIZ_EMBEDER_PLUGIN_URL;?>loading_16x16.gif" alt="Loading" /> '+translatedText.deleting+'....';
			if(dir_name!=="")
			{			
				if (confirm(translatedText.are_you_sure))
				{
					articulatejq("#delete_button_"+number+"").hide();
					articulatejq("#loading_box_"+number+"").html(loading_image);
					articulatejq("#insert_msg_"+number+"").html(loading_text);
					jQuery.post("admin-ajax.php",{dir : dir_name,action:'articulate_del_dir', _ajax_nonce : ARTICULATE_DEL_DIR_NONCE },function(data){
					<?php if($tab=="articulate-upload"){?>
					articulatejq("#insert_msg_"+number+"").html("");
					articulatejq("#upload_detail_"+number+"").remove();
					location.reload();
					<?php }else{?>
					articulatejq("#content_item_"+number+"").remove();
					<?php }?>
					});
				}
			}
			else
			{
				alert(translatedText.no_data_found);
			}
		}// end delete_dir()

	</script>

<?php
}#end function articulate_print_js()

/*
If wp max upload size < 1024kb, use 100kb chunking
If wp max upload size is less than 20mb, use 1mb chunking
If wp max upload size is greater than 20mb, use 2mb chunking
If wp max upload size is greater than 50mb, use 3mb chunking
If wp max upload size is greater than 80mb, use 4mb chunking
If wp max upload size is greater than 100mb, use 5mb chunking
*/

function articulate_get_upload_chunk_size(){

	$max_upload_size = wp_max_upload_size();
	$chunk_size = '512kb';
	if( $max_upload_size <= 1024 * KB_IN_BYTES )
	{
		$chunk_size = '200kb';
	}
	elseif( $max_upload_size <= 20 * MB_IN_BYTES )
	{
		$chunk_size = '2mb';
	}
	elseif( $max_upload_size <= 50 * MB_IN_BYTES )
	{
		$chunk_size = '4mb';
	}
	elseif( $max_upload_size <= 80 * MB_IN_BYTES )
	{
		$chunk_size = '6mb';
	}	
	elseif( $max_upload_size <= 100 * MB_IN_BYTES )
	{
		$chunk_size = '8mb';
	}
	elseif( $max_upload_size > 100 * MB_IN_BYTES )
	{
		$chunk_size = '10mb';
	}
	return $chunk_size;
}

function articulate_print_upload()
{
	// echo "<h3>Start articulate_print_upload</h3>";
	articulate_print_js();
	?>
	<link href="<?= articulate_WP_QUIZ_EMBEDER_PLUGIN_URL . 'css/materialize.css?v=587'?>" rel="stylesheet"/>
	<?PHP 
	$chunk_size = articulate_get_upload_chunk_size();
	$dirs = articulate_getDirs();
	if( count($dirs) > articulate_quiz_embeder_count())
	{
		echo '<blockquote class="flow-text pink-text lineheighter">';
		echo sprintf(__('The trial version only supports three uploads. Please purchase the full version with unlimited uploads at %s - Already purchased? Go to Articulate > License on your Wordpress menu to activate your license key.', 'quiz' ) , '<a href="https://www.elearningfreak.com" target="_blank">www.elearningfreak.com</a>' );
		echo '</blockquote>';	
	}
	else
	{
		?>
		<form enctype="multipart/form-data" id="myForm1" action="admin-ajax.php" method="POST">
			<div id="container">
				<a id="pickfiles" href="javascript:;" class="waves-effect waves-light btn grey">Choose your zip file</a>
				<a id="uploadfiles" href="javascript:;" class="waves-effect waves-light btn"><i class="material-icons left">call_made</i> Upload!</a>
			</div>
			<div id="filelist"><?php _e("Your browser doesn't have Flash, Silverlight or HTML5 support.", 'quiz') ?></div>
			<div id="fileerror"></div>
			<br />
			<div id="console"></div>
		</form>
		<script type="text/javascript">
			// Custom example logic
			var articulate_uploader = new plupload.Uploader({
				
				runtimes : 'html5',
				url: '<?php echo admin_url('admin-ajax.php'); ?>',
				chunk_size: '<?php echo $chunk_size ?>',
				max_retries : 10,
				multi_selection: false,
				'file_data_name':  'async-upload' ,
				multipart_params : {
				"_ajax_nonce" : "<?php echo wp_create_nonce('articulate_upload_file'); ?>",
				"action" : "articulate_upload_file"
				},
				browse_button : 'pickfiles', // you can pass in id...
				container: document.getElementById('container'), // ... or DOM Element itself
				filters : {
					max_file_size : '0',
					mime_types: [
					{title : "Zip files", extensions : "zip"},
					{title : "MP4 files", extensions : "mp4"}
					]
					},

				// Flash settings
				flash_swf_url : '<?php echo articulate_WP_QUIZ_EMBEDER_PLUGIN_URL; ?>js/plupload/js/Moxie.swf',
				// Silverlight settings
				silverlight_xap_url : '<?php echo articulate_WP_QUIZ_EMBEDER_PLUGIN_URL; ?>js/plupload/js/Moxie.xap',
				init: {
					PostInit: function() {
						document.getElementById('filelist').innerHTML = '';
						document.getElementById('uploadfiles').onclick = function() {
						articulate_uploader.start();
						return false;
						};
					},
					FilesAdded: function(up, files) {
						plupload.each(files, function(file) {
						//do something
						});
					},
					UploadProgress: function(up, file) {
						if( up.total.percent == 100 ) {
							document.getElementById(file.id).getElementsByTagName('b')[0].innerHTML = '<span>File uploaded. Unzipping content.</span>';
					
						} else {
							document.getElementById(file.id).getElementsByTagName('b')[0].innerHTML = '<span>' + up.total.percent + "%</span>";}
					},
					Error: function(up, err) {
						var Msg = "";
						if( err.code == 200 ) {
							Msg = "\nError #" + err.code + ": " + err.message +' Upload failed. Please contact support at <a target="_blank" href="https://www.elearningfreak.com/contact-us/">https://www.elearningfreak.com/contact-us/</a>';
						} else {
							Msg = "\nError #" + err.code + ": " + err.message +' Upload failed. Please contact support at <a target="_blank" href="https://www.elearningfreak.com/contact-us/">https://www.elearningfreak.com/contact-us/</a>';						}
						document.getElementById('console').innerHTML += Msg;
					}
				}
			});
			
			articulate_uploader.init();

			articulatejq(function($){ 
			
				if(window.navigator.userAgent.indexOf("Edge") > -1){
					articulatejq('#pickfiles').removeClass('waves-effect');
					articulatejq('#uploadfiles').removeClass('waves-effect');
				}
				if(window.navigator.userAgent.indexOf("Vivaldi") > -1){
					articulatejq('#pickfiles').removeClass('waves-effect');
				}

				articulate_uploader.bind('FilesAdded', function(up, files) {
					articulatejq("#filelist").show();
					articulatejq("#filelist").removeClass('uploaded_file');
					if (articulate_uploader.files.length > 1)
					{
						articulate_uploader.removeFile(articulate_uploader.files[0]);
					}
					$.each(files, function(i, file) {
						articulatejq('#filelist').html(
						'<div id="' + file.id + '">' +
						file.name + ' (' + plupload.formatSize(file.size) + ') <b></b>' +
						'</div>');
					});
					console.log('articulate_uploader.files.length:',articulate_uploader.files.length);
					up.refresh(); // Reposition Flash/Silverlight
				});

				articulate_uploader.bind('FileUploaded', function(upldr, file, object) {
					var upload_response = articulatejq.parseJSON(object.response);
					console.log(upload_response);
					if(upload_response.OK === 1)
					{
						articulatejq("#filelist").addClass('uploaded_file');
						articulatejq("#filelist").append(' <span>'+upload_response.info+'</span>');
						dir = upload_response.path;
						var uploaded_dir_neme=upload_response.folder;
						var win = window.dialogArguments || opener || parent || top; 
						articulatejq("#file_url_1").val(dir);
						articulatejq("#dir_name_1").val(uploaded_dir_neme);
						articulatejq("#file_name_1").val(upload_response.name.file_name);
						var regex = new RegExp('_', 'g');
						articulatejq("#title").val(uploaded_dir_neme.replace(regex," "));
						show_detail(1);
					}
					else
					{
						articulatejq('#fileerror').show();
						articulatejq('#fileerror').html(upload_response.info);	
					}
				});
			});
		</script>
		<?php _e('Please choose a .zip file that you published with the software', 'quiz');?>
		<br />
		<?php 
		articulate_print_detail_form(1);

	}//end of if( count($dirs) > articulate_quiz_embeder_count())
	?>
	<p class="flow-text"><?php _e('Need help?  See the screencast below', 'quiz') ?>:</p>
	<iframe src="https://www.youtube.com/embed/AwcIsxpkvM4" width="600" height="366" frameborder="0"></iframe>
	<p></p>
	<p></p>
	<?php if( count($dirs) > articulate_quiz_embeder_count())
	{ 
		echo '<iframe src="https://www.elearningfreak.com/wordpresspluginlatesttrial500.html?v=587&editor=classic" width="600px" frameborder="0"></iframe>'; 
	}
	else
	{ 
		echo '<iframe src="https://www.elearningfreak.com/wordpresspluginlatestpremium500.html?v=587&editor=classic" width="600px" frameborder="0"></iframe>'; 
	}
	?>
	<p></p>
	<?php
	//echo "<h3>END articulate_print_upload</h3>";
}//END function articulate_print_upload

// handle uploaded file here
add_action('wp_ajax_articulate_upload_file','articulate_upload_ajax_file');

function articulate_upload_ajax_file()
{
	articulate_setup_php_ini();
	check_ajax_referer('articulate_upload_file');
	
	if( ! is_user_logged_in() || ! current_user_can( 'upload_files' ) ) 
	{
		die();
	}

	$count_dirs = articulate_getDirs();
	if ( count($count_dirs) <= articulate_quiz_embeder_count())
	{
		// you can use WP's wp_handle_upload() function:
		$file = $_FILES['async-upload'];
		$upload_dir = wp_upload_dir();
		$dir = '' . $upload_dir['basedir'] . '/articulate_uploads';
		$dir = untrailingslashit( articulate_getUploadsPath() );
		if (empty($_FILES) || $_FILES['async-upload']['error'])
		{
			die(json_encode( array( "OK"=> 0, "info"=> __( "Failed to move uploaded file.  Please check if the folder has write permissions.", 'quiz'))));
		}
		$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
		$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
		$fileName = isset($_REQUEST["name"]) ? sanitize_file_name($_REQUEST["name"]) : sanitize_file_name($_FILES["async-upload"]["name"]);
		$filePath = "".$dir."/".sanitize_file_name($fileName)."";
		// Open temp file
		$out = @fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
		if ($out)
		{
			// Read binary input stream and append it to temp file
			$in = @fopen($_FILES['async-upload']['tmp_name'], "rb");
			if ($in)
			{
				while ($buff = fread($in, 4096))
				fwrite($out, $buff);
			} 
			else
			die(json_encode( array( "OK"=> 0, "info"=> __( "Failed to open input stream. Please check if the folder has write permissions", 'quiz'))));
			
			@fclose($in);
			@fclose($out);
			@unlink($_FILES['async-upload']['tmp_name']);
		} 
		else
		die(json_encode( array( "OK"=> 0, "info"=> __( "Failed to open output stream.  Please check if the folder has write permissions", 'quiz'))));
		// Check if file has been uploaded
		if (!$chunks || $chunk == $chunks - 1)
		{
			// Strip the temp .part suffix off
			articulate_custom_rename("{$filePath}.part", $filePath);
			#start extracting
			#unzip file
			$dir = explode(".",$fileName);
			$dir[0] = str_replace(" ","_",$dir[0]);		
			$target = articulate_getUploadsPath() . $dir[0];
			$file = $filePath ;			
			while(file_exists($target))
			{
				$r = rand(1,10);
				$target .= $r;
				$dir[0] .= $r;
			}
			$ext = pathinfo ($filePath ,PATHINFO_EXTENSION);
			if( $ext == 'mp4')
			{
				$arr = articulate_process_single_mp4_file_upload($file,$target,$dir[0]);
			}
			else
			{
				$arr = articulate_extractZip($file,$target,$dir[0]);
			}
			unlink($filePath);
			do_action('iea/uploaded_quiz', $arr,$target);
			$ok = isset( $arr[4] ) ? $arr[4] : 0;
			$response = array(
				"OK" => $ok,
				"info" => $arr[0],
				"folder" => $arr[2],
				"path" => $arr[1],
				"name" => $arr[3],
				"target" => $target
			);
			die( json_encode($response ) );
		}
		else
		{
			die(json_encode( array( "OK"=> 1, "info"=> __("Uploading chunks!", 'quiz'))));
		}

	}
	else
	{
		$arr = array(
			"OK" => 0, 
			"info" => '<span style="color:#ff0000; font-size:15px;">'.sprintf(__("The trial version only supports three uploads. Please purchase the full version with unlimited uploads at %s - Already purchased? Go to articulate > License on your Wordpress menu to activate your license key.", 'quiz'), '<a href="https://www.elearningfreak.com/" target="_blank">www.elearningfreak.com</a>' ).'</span>',
		);
		die(json_encode($arr));
	}
	exit;
};



function articulate_wp_ajax_del_dir()
{	
	
	check_ajax_referer('articulate_del_dir');
	if( ! is_user_logged_in() || ! current_user_can( 'upload_files' ) ) 
	{
		die();
	}

	$dirname = sanitize_file_name( $_POST['dir'] );
	if( $dirname != "" )
	{
		$dir = articulate_getUploadsPath() . $dirname;
		articulate_rrmdir($dir);
	}
	die();
}

function articulate_wp_ajax_rename_dir()
{
	check_ajax_referer('articulate_rename_dir');
	if( ! is_user_logged_in() || ! current_user_can( 'upload_files' ) ) 
	{
		die();
	}
	$dir_name = ( isset($_REQUEST['dir_name']) ) ? $_REQUEST['dir_name'] : "";
	$dir_name = sanitize_file_name( $dir_name );

	$title = ( isset($_REQUEST['title']) ) ? $_REQUEST['title'] : "";
	$title = sanitize_file_name( $title );


	$arr=array();
	if($dir_name !="")
	{
		$target = articulate_getUploadsPath() . $dir_name;
		if(file_exists($target))
		{
					
			if($title)
			{   
				$title=str_replace(" ","_" , $title);
				$new_file= articulate_getUploadsPath() . $title;
				while(file_exists($new_file))
				{
					$r = rand(1,10);
					$new_file .= $r;
					$title .= $r;
				}
				articulate_custom_rename($target, $new_file);
				$arr[0]="success";
				$arr[1]=$title;
			}
			else
			{
				$arr[0]="error";
				$arr[1]=__("Failed: New Title Was Not Given", 'quiz');
			}
			
		}
		else
		{
			$arr[0]="error";
			$arr[1]=__("Failed: Given File is Not Exits", 'quiz');
		}
	}
	else
	{
		$arr[0]="error";
		$arr[1]=__("Failed: Targeted Directory Name Was Not Given", 'quiz');
	}
	echo json_encode($arr);	
	die();
}

#check if quiz is in a folder as many times as it takes    @anthonysbrown
function articulate_wp_ajax_quiz_check_folder($dir)
{
	$arr = preg_grep("/_macosx/i", scandir($dir), PREG_GREP_INVERT);
	foreach($arr as $key=>$folder)
	{
		if($folder != '.' && $folder != '..')
		{
			$structure[] = $folder;
		}
	}
	//Directory can contain only a single file( not another directory ) for mp4 type uploads.
	//So must check that is directory or not to avoid infinity loop of this recursive function.
	if(count($structure) == 1 && is_dir($dir.'/'.$structure[0]) ) 
	{
		$sub_folder = $dir.'/'.$structure[0].'/';
		articulate_custom_rename($dir, $dir.'_temp');
		articulate_custom_rename($dir.'_temp/'.$structure[0].'/', $dir);
		rmdir($dir.'_temp');
		articulate_wp_ajax_quiz_check_folder($dir);
	}
}

#copy temp mp4 file to perfect location and get info about the upload
function articulate_process_single_mp4_file_upload( $temp_file,$target_folder,$dir )
{
	require_once(ABSPATH . 'wp-admin/includes/file.php');
	$arr = array();
	WP_Filesystem();		
	if (!is_dir($target_folder))
	{
		mkdir($target_folder, 0777);
	}
	$success = copy($temp_file, $target_folder.'/'.$dir.'.mp4');
	if ($success) 
	{	
		$file = articulate_getFile($target_folder, true);// true to get return value as an array of detail
		$arr[0] = __('Upload Complete!', 'quiz');
		if( $file['status'] == "valid_mp4_file_found" )
		{
			$arr[0] = __('Upload Complete!', 'quiz');
			$arr[4] = 1;//OK = 1
		}
		else
		{
			$arr[0] = '<span style="color:red">'.sprintf(__('Mp4 upload successful, but we something went wrong. Contact support at %s', 'quiz'), '<a href="https://www.elearningfreak.com/upload-file/">www.elearningfreak.com</a>').'</span>';
			$arr[4] = 0;//OK = 0
		}
		 
		$arr[1] = articulate_getUploadsUrl().$dir."/".$file['file_name']; 
		$arr[2] = $dir;
		$arr[3] =$file;
	} 
	else 
	{
 

		$arr[0] =__('File upload failed', 'quiz');
		$arr[4] = 0;//OK = 0
	}
	return  $arr;

}

function articulate_has_php_file( $dir ){
  $dir = rtrim($dir, "/");
  if ( is_dir( $dir ) )
  {
     
    $dir_handle = opendir( $dir );
    if( $dir_handle )
    {       
      while( $file = readdir( $dir_handle ) ) 
      {
           if($file != "." && $file != "..") 
           {
                if( ! is_dir( $dir."/".$file ) && ( strpos($file, ".phtml") || ( strpos($file, ".php") && $file != "relay.php") ) )
                {
                  	return true;
                }
                else
                {
                  $found =  articulate_has_php_file($dir.'/'.$file);
                  if( $found ){ return true; }
                }
                      
           }
      }
      closedir( $dir_handle );
    }
  
    return false;
  }
  return false;
}

function articulate_run_admin_memory_limit_hook( $limit ){
	$limit = apply_filters('articulate_admin_memory_limit', $limit );
	return $limit;
}

#use wordpress unzip function instead
function articulate_extractZip($fileName,$target,$dir)
{
	add_filter('admin_memory_limit', 'articulate_run_admin_memory_limit_hook', 100, 1 );
	#admin_memory_limit hook is called in wp_raise_memory_limit function that is called in unzip_file function.
	$arr = array();
	$unzipper = new Quiz_Unzip( true );
	$unzip = $unzipper->unzip_file( $fileName, $target );
		
	if ($unzip) {
			articulate_wp_ajax_quiz_check_folder($target);
			if( articulate_has_php_file( $target ) )
			{
				$arr[0] = '<span style="color:red">'.sprintf(__('ZIP upload successful, but we found a PHP file that is not allowed in your content directory. Contact support at %s', 'quiz'),'<a style="color: black" href="https://www.elearningfreak.com/upload-file/">www.elearningfreak.com</a>').'</span>';
				articulate_rrmdir( $target );
				$arr[4] = 0;//OK = 0
				$arr[1] = "";
				$arr[2] = $dir;
				$arr[3] = "";
			}
			else
			{
				$file = articulate_getFile($target, true);// true to get return value as an array of detail
				$arr[0] = __('Upload Complete!', 'quiz');
				if( $file['status'] == "valid_html_file_found" || $file['status'] == "index_html_file_found" || $file['status'] == "other_html_file_found" )
				{
					$arr[0] = __('Upload Complete!', 'quiz');
					$arr[4] = 1;//OK = 1
				}
				elseif( $file['status'] == "no_html_file_found")
				{
					$arr[0] = '<span style="color:black">'.sprintf(__('ZIP upload successful, but we were unable to find an HTML file. Either increase your WP_MEMORY_LIMIT or contact support at %s', 'quiz'),'<a style="color: black" href="https://www.elearningfreak.com/upload-file/">www.elearningfreak.com</a>').'</span>';
					articulate_rrmdir( $target );
					$arr[4] = 0;//OK = 0
				}
				 
				$arr[1] = articulate_getUploadsUrl().$dir."/".$file['file_name']; 
				$arr[2] = $dir;
				$arr[3] =$file;
			}
		} else {

			$arr[0] =__('File upload failed', 'quiz');
			$arr[4] = 0;//OK = 0
		}
	return  $arr;
  
}

function articulate_rrmdir($dir)
{
	if (is_dir($dir))
	{
		$objects = scandir($dir);
		foreach ($objects as $object)
		{
			if ($object != "." && $object != "..")
			{
				if (filetype($dir."/".$object) == "dir") articulate_rrmdir( $dir . "/" . $object); else unlink( $dir . "/" . $object);
			}
		}
		reset($objects);
		rmdir($dir);
	}
} 

/*-----added by oneTarek-----*/
function articulate_quiz_embeder_wp_head()
{
	$opt=articulate_get_quiz_embeder_options();
	global $quiz_embeder_size_opt;
	global $quiz_embeder_width;
	global $quiz_embeder_height;
	global $quiz_embeder_scrollbar;
	if(isset($quiz_embeder_size_opt))
	{
		if($quiz_embeder_size_opt=="custom_form_dashboard"){$quiz_embeder_width=$opt['width'].$opt['width_type']; $quiz_embeder_height=$opt['height'].$opt['height_type'];}
	}
	?>
	<?php 	
	if($opt["lightbox_script"]=="nivo_lightbox")
	{
	?>
		<!--nivo lite box-->
		<link rel="stylesheet" href="<?php echo articulate_WP_QUIZ_EMBEDER_PLUGIN_URL?>nivo-lightbox/nivo-lightbox.css" type="text/css" />
		<link rel="stylesheet" href="<?php echo articulate_WP_QUIZ_EMBEDER_PLUGIN_URL?>nivo-lightbox/themes/default/default.css" type="text/css" />
		<?php 
		if($quiz_embeder_size_opt!="lightebox_default")
		{ 
			?>
		<style type="text/css">
			.nivo-lightbox-content{
			width: <?php echo $quiz_embeder_width; ?>;
			height: <?php echo $quiz_embeder_height; ?>;
			margin:auto;
			}
			<?php if(isset($quiz_embeder_scrollbar) && $quiz_embeder_scrollbar=="no")
			{?>
			.nivo-lightbox-content iframe{
			width: <?php echo $quiz_embeder_width; ?>;
			height: <?php echo $quiz_embeder_height; ?>;
			overflow:hidden;
			}
			<?php }?>
		</style>
		<?php 
		}
		?>
		<script src="<?php echo articulate_WP_QUIZ_EMBEDER_PLUGIN_URL?>nivo-lightbox/nivo-lightbox.min.js?v=587"></script>
		<!--end nivo lite box-->	
	<?php 
	} 
	else
	{
		global $quiz_embeder_colorbox_theme; if(!isset($quiz_embeder_colorbox_theme) || $quiz_embeder_colorbox_theme==""){$quiz_embeder_colorbox_theme=$opt["colorbox_theme"];}
		if($quiz_embeder_size_opt!="custom_form_dashboard" && $quiz_embeder_size_opt!="custom"){ $quiz_embeder_width="80%"; $quiz_embeder_height="80%";}//default
		?>
		<!--https://www.elearningfreak.com-->
		<link rel="stylesheet" href="<?php echo articulate_WP_QUIZ_EMBEDER_PLUGIN_URL . "colorbox/themes/" . $quiz_embeder_colorbox_theme . "/colorbox.css?v=587" ;?>" />
		<script type="text/javascript" src="<?php echo articulate_WP_QUIZ_EMBEDER_PLUGIN_URL . "colorbox/jquery.colorbox-min.js?v=587" ;?>" ></script>
		<?php 
	} ?>
	<!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous"> -->

	<style type="text/css">
	/* embed responsive CSS from bootstrap */
		.articulate-embed-responsive {
		  position: relative;
		  display: block;
		  width: 100%;
		  padding: 0;
		  overflow: hidden;
		}

		.articulate-embed-responsive::before {
		  display: block;
		  content: "";
		}

		.articulate-embed-responsive .articulate-embed-responsive-item,
		.articulate-embed-responsive iframe,
		.articulate-embed-responsive embed,
		.articulate-embed-responsive object,
		.articulate-embed-responsive video {
		  position: absolute;
		  top: 0;
		  bottom: 0;
		  left: 0;
		  width: 99.9%;
		  height: 100%;
		  border: 0;
		}

		.articulate-embed-responsive-21by9::before {
		  padding-top: 42.857143%;
		}

		.articulate-embed-responsive-16by9::before {
		  padding-top: 56.25%;
		}

		.articulate-embed-responsive-4by3::before {
		  padding-top: 75%;
		}

		.articulate-embed-responsive-1by1::before {
		  padding-top: 100%;
		}
	</style>

	<script type="text/javascript">

		var articulatejq = jQuery.noConflict();
		articulatejq(document).ready(function($){
		<?php if($opt["lightbox_script"]=="nivo_lightbox"){?>
		var nivoLightboxTranslate = <?php echo json_encode( array('errorMessage'=>__('The requested content cannot be loaded. Please try again later.', 'quiz') ) ); ?>;			
		articulatejq('.nivo_lightbox_iframe').nivoLightbox({
		effect: '<?php echo $opt['nivo_lightbox_effect'] ?>',                             // The effect to use when showing the lightbox
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
		<?php }else{?>
		//Examples of how to assign the ColorBox event to elements
		articulatejq(".colorbox_iframe").colorbox({iframe:true, transition:"<?php echo $opt['colorbox_transition']?>", width:"<?php echo $quiz_embeder_width ?>", height:"<?php echo $quiz_embeder_height ?>", scrolling:<?php echo($quiz_embeder_scrollbar=="no")?'false':'true'; ?>});
		<?php }?>			
		});
	</script>	
	<!--QUIZ_EMBEDER END-->
	<?php 
}


function articulate_quiz_embeder_get_colorbox_themes()
{
	$themes=array(
	"default"=>array("dir"=>"default", "text"=>"Default"),
	"vintage"=>array("dir"=>"vintage", "text"=>"Vintage"),
	"dark-rimmed"=>array("dir"=>"dark-rimmed", "text"=>"Dark Rimmed"),
	"fancy-overlay"=>array("dir"=>"fancy-overlay", "text"=>"Fancy Overlay"),
	"minimal"=>array("dir"=>"minimal", "text"=>"Minimal"),
	"minimal-circles"=>array("dir"=>"minimal-circles", "text"=>"Minimal Circles"),
	"sketch-toon"=>array("dir"=>"sketch-toon", "text"=>"Sketch / Toon"),
	"noimage"=>array("dir"=>"noimage", "text"=>"No Image"),
	"noimage-rounded"=>array("dir"=>"noimage-rounded", "text"=>"No Image Rounded"),
	"noimage-blue"=>array("dir"=>"noimage-blue", "text"=>"No Image Blue"),
	"noimage-polaroid"=>array("dir"=>"noimage-polaroid", "text"=>"No Image Polaroid"),
	"shadow"=>array("dir"=>"shadow", "text"=>"Shadow"),
	"wood-table"=>array("dir"=>"wood-table", "text"=>"Wood Table") 
	);
	return $themes;
}

function articulate_quiz_embeder_admin_scripts()
{
	if (isset($_GET['page']) && ($_GET['page'] == 'articulate_content' || $_GET['page'] == 'articulate-settings-button'))
	{
		wp_enqueue_script('jquery');
		wp_enqueue_media();
		wp_register_script('quiz_embeder_upload', articulate_WP_QUIZ_EMBEDER_PLUGIN_URL . 'js/admin.js?v=587');
		wp_enqueue_script('quiz_embeder_upload');
		wp_enqueue_style('materialize-css', articulate_WP_QUIZ_EMBEDER_PLUGIN_URL . 'css/materialize.css?v=587');
		wp_enqueue_script('materializejs', articulate_WP_QUIZ_EMBEDER_PLUGIN_URL . 'js/materialize.js?v=587' );
		wp_enqueue_script('jshelpers', articulate_WP_QUIZ_EMBEDER_PLUGIN_URL . 'js/jshelpers.js?v=587' );
	}
}
add_action( 'admin_enqueue_scripts', 'articulate_quiz_embeder_admin_scripts' );
function articulate_quiz_embeder_create_upload_dir(){
	$upload_path = articulate_getUploadsPath();
	$dir = untrailingslashit( $upload_path );
	if (!is_dir($dir)) 	 
	{
		mkdir($dir, 0777); 
	}
	return $upload_path;
}

function quiz_embeder_is_apache_2_4_or_grater(){
	$words = explode( " ", strtolower( $_SERVER['SERVER_SOFTWARE'] ) );
	$version = "";
	foreach( $words as $word )
	{
		if( strpos( $word, "apache/" ) !== false )
		{
			$parts = explode( "/", $word );
			$version = $parts[1];
			break;
		}
	}

	if( $version != "" )
	{
		return version_compare( $version, "2.4", ">=" );
	}
	else
	{
		return false;
	}
}

function quiz_embeder_get_htaccess_rules()
{
	$rules = "#allow articulate uploads\n";
	if( quiz_embeder_is_apache_2_4_or_grater() )
	{
		$rules .= "<Files ~ \"...\">\n";
			$rules .= "Require all granted\n";
		$rules .= "</Files>\n";
		$rules .= "<Files *.php>\n";
			$rules .= "Require all denied\n";
		$rules .= "</Files>\n";
	}
	else
	{
		$rules .= "<Files ~ \"...\">\n";
			$rules .= "Order Allow,Deny\n";
			$rules .= "Allow from all\n";
		$rules .= "</Files>\n";
		$rules .= "<Files *.php>\n";
			$rules .= "Order allow,deny\n";
			$rules .= "Deny from all\n";
		$rules .= "</Files>\n";
	}
	
	return $rules;
}

function articulate_quiz_embeder_create_protection_files( $force = false ) {
	if ( false === get_transient( 'articulate_quiz_embeder_create_protection_files' ) || $force ) {
		$upload_path = articulate_quiz_embeder_create_upload_dir();
		// Top level .htaccess file
		$rules = quiz_embeder_get_htaccess_rules();
		if ( file_exists( $upload_path . '.htaccess' ) ) {
			$contents = @file_get_contents( $upload_path . '.htaccess' );
			if ( $contents !== $rules || ! $contents ) {
				// Update the .htaccess rules if they don't match
				@file_put_contents( $upload_path . '.htaccess', $rules );
			}
		} elseif( wp_is_writable( $upload_path ) ) {
			// Create the file if it doesn't exist
			@file_put_contents( $upload_path . '.htaccess', $rules );
		}
		// Top level blank index.php
		if ( ! file_exists( $upload_path . 'index.php' ) && wp_is_writable( $upload_path ) ) {
			@file_put_contents( $upload_path . 'index.php', '<?php' . PHP_EOL . '// Silence is golden.' );
		}
		// Check for the files once per day
		set_transient( 'articulate_quiz_embeder_create_protection_files', true, 3600 * 24 );
					  
	} 
}
add_action( 'admin_init', 'articulate_quiz_embeder_create_protection_files' );

/**
 * Translate some texts those are being used in trial version of this plugin
 */
$some_text_from_trial_version = array(
	__('Premium only', 'quiz'),
	//add more items here
);
unset( $some_text_from_trial_version );

