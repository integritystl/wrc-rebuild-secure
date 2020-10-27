<?php
function articulate_getFile($dir, $return_detail_array = false )
{
	$indexFileName = "";
	$otherHtmlFileName = "";
	$hasindex = FALSE;
	$hashtml = FALSE;
	$myDirectory = opendir($dir);
	$valid_file_found = false;
	//$fileArray = array();
	// get each entry
	/*
	If customer uploads a .zip file and it has multiple files in it, don't select the MP4.
	If a customer uploads a .zip file and we only find the MP4 file, use that.
	Allow the customer to upload just the .mp4 file.
	*/
	$file_count = 0;
	$mp4_file_name = "";
	while($entryName = readdir($myDirectory)) 
	{
		$f = articulate_getUploadsPath() . $entryName;
		if ($entryName != "." && $entryName !=".." && is_dir($f) == false )
		{
			$file_count++;
			
			// need to get the filename without the extension
			$fname = pathinfo ($f, PATHINFO_FILENAME);
			//echo $fname;
			// need the extension as well
			$ext = pathinfo ($f,PATHINFO_EXTENSION);
			// need to check the filename and only return Articulate
			//echo "<script type='text/javascript'>alert('$fname');</script>";
			//echo "<script type='text/javascript'>alert('$ext');</script>";
			if (($fname == "player" || $fname == "story" || $fname == "engage" || $fname =="quiz" || $fname =="presentation" || $fname =="interaction") && $ext == "html"):
					//closedir($myDirectory);
					//return $entryName;
					$valid_file_found = true;
					//echo "<script type='text/javascript'>alert('$elearningproduct');</script>";
				// check for Captivate
				elseif (($fname =="multiscreen" || $fname =="index_AICC" || $fname =="index_SCORM" || $fname =="index_scorm" || $fname =="index_aicc" || $fname =="index_tincan" || $fname =="index_TINCAN") && ($ext == "html" || $ext =="htm")):
					//closedir($myDirectory);
					//echo "<script type='text/javascript'>alert('$fname');</script>";
					//return $entryName;
					$valid_file_found = true;
				// check for Elucidat
				elseif (($fname =="launch") && ($ext == "html" || $ext =="htm")):
					//closedir($myDirectory);
					//return $entryName;
					$valid_file_found = true;
					//echo "<script type='text/javascript'>alert('$elearningproduct');</script>";
				// check for Pubcoder
				elseif (($fname =="content") && ($ext == "html" || $ext =="htm")):
					$valid_file_found = true;
				elseif (($fname =="index" || $fname =="INDEX") && ($ext == "html" || $ext =="htm")):
					$hasindex = true;
					$indexFileName = $entryName;
					
				elseif (($ext == "html" || $ext =="htm") && ($fname!=="Close")):
					$hashtml = true;
					$otherHtmlFileName = $entryName;
				elseif( $ext == "mp4" ):
					$mp4_file_name = $entryName;

			endif;
		}//end if ($entryName != "."....

		if( $valid_file_found ){ break;}

	}//end while($entryName...

	closedir($myDirectory);
	$returnArr = array();
	$returnArr["file_name"] = "";
	$returnArr["status"] = "no_html_file_found";

	if( $valid_file_found )
	{
		$returnArr['file_name'] = $entryName;
		$returnArr["status"] = "valid_html_file_found";
	}
	elseif( $hasindex==true )
	{
		$returnArr['file_name'] = $indexFileName;
		$returnArr["status"] = "index_html_file_found";
	}
	elseif ($hashtml==true)
	{
		$returnArr['file_name'] = $otherHtmlFileName;
		$returnArr["status"] = "other_html_file_found";
	}
	elseif( file_exists( $dir."/"."scormcontent/index.html") )//check for index.html from Rise product & chooses SCORM output.
	{
		$returnArr['file_name'] = "scormcontent/index.html";
		$returnArr["status"] = "valid_html_file_found";
	}
	elseif( file_exists( $dir."/"."res/index.html") )//check for index.html from iSpring product & choose Tin Can output.
	{
		$returnArr['file_name'] = "res/index.html";
		$returnArr["status"] = "valid_html_file_found";
	}
	elseif( $file_count == 1 && $mp4_file_name != "")
	{
		$returnArr['file_name'] = $mp4_file_name;
		$returnArr["status"] = "valid_mp4_file_found";
	}

	if( $return_detail_array )
	{
		return $returnArr;
	}
	else
	{
		return $returnArr['file_name'];
	}

}
