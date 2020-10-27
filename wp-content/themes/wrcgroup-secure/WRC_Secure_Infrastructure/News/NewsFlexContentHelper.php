<?php
namespace WRCInfrastructure\News;
/**
*
* This class handles flex content nonsense.
**/
class NewsFlexContentHelper
{
  public static function getCorrectImagePath($assumed_path, $display_pdf){
    $image_path = '';
    $uploads_directory_structure = wp_upload_dir();
    //check if we're coming from the dkpdf plugin
    if($display_pdf){
      //need to use image path instead of url so the pdf generator will grab the images. super annoying
      $the_image = $assumed_path;
      if(!empty($the_image)){
        $image_url = explode("://", $the_image )[1];
        $base_url = explode("://", $uploads_directory_structure['baseurl'])[1];
        $image_path = str_replace( $base_url, $uploads_directory_structure['basedir'], $image_url );
      }
    }else{
      $image_path = $assumed_path;
    }

    return $image_path;
  }
}

