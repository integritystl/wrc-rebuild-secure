<div class="flex_content_multi_column_image_block">
  <div class="multi_column_image_block_wrapper">
    <?php if( have_rows('images') ): ?>
      <?php while(have_rows('images')) : the_row();?>
        <div class="image_column">
          <?php
            $image_path = get_correct_image_path(get_sub_field('image'), !empty($display_pdf));
          ?>
          <img src="<?php echo($image_path); ?>" alt="<?php the_sub_field('title'); ?>">
        </div>
      <? endwhile; ?>
    <? endif; ?>
  </div>
</div>