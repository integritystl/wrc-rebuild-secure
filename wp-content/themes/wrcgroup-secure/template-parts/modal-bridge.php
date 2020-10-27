<div class="modal" id="modalForm" role="dialog" aria-hidden="true">
  <div class="modal-fade-screen">
    <div class="modal__inner">
      <div class="modal__close">  &times; </div>
      <h2 class="modal__title">Access Policy</h2>
      <div class="modal__body">

        <?php if( get_field('access_policy_help_text', 'option') ): ?>
          <p class="policy_help_text"><?php the_field('access_policy_help_text', 'option'); ?></p>
        <?php endif; ?>

        <?php if( get_field('bill_pay_help_text', 'option') ): ?>
          <p class="bill_pay_help_text"><?php the_field('bill_pay_help_text', 'option'); ?></p>
        <?php endif;?>

          <div class="modal__error"></div>

          <form id="policy-number-check" class="modal__form" action="">
              <label>Policy Number</label>
              <input id="policy-num" type="text" autocomplete="off"
                data-parsley-required
                data-parsley-pattern="(^[a-zA-Z]{4}[-]\d{1,5}$|^[a-zA-Z]{4}\d{6}[-]\d{2}$)"
                data-parsley-trigger="change"
                data-parsley-validation-threshold="7"
                data-parsley-required-message="Please enter a policy number."
                data-parsley-pattern-message="Please enter a policy number that matches the format on the policy declarations page." />
              <input type="submit" value="Go" class="btn btn-primary"/>
          </form>

          <div class="modal__img-group">
            <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/WRC_modal_v3_06.gif" alt="" class="modal__img">
            <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/WRC_modal_v3_03.gif" alt="" class="modal__img">
          </div>
      </div>
    </div><!-- / .modal__inner -->
  </div>
</div>
