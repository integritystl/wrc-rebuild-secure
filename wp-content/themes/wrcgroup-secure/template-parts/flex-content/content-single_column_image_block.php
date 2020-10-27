<div class="flex_content_single_column_image_block">
  <div class="single_column_image_block_wrapper">
    <?php 
      $image_path = get_correct_image_path(get_sub_field('image'), !empty($display_pdf));
    ?>
    <img src="<?php echo($image_path); ?>" alt="Single Column Image">
  </div>
</div>