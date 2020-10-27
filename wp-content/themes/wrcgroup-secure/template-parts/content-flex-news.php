<?php if(have_rows('news_flexible_content', get_the_ID())) : ?>
  <?php while ( have_rows('news_flexible_content') ) : the_row(); ?>
    <?php //flex content template sections will have same name as the layouts ?>
		<?php //using locate template so the template recognizes our display pdf flag ?>
    <?php include(locate_template('template-parts/flex-content/content-' . get_row_layout() . '.php'));?>
  <?php endwhile; ?>
<?php endif; ?>
