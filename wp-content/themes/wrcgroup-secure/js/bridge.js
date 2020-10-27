jQuery(document).ready(function($) {
  //Disable clicking on the encrypted link that we're going to use in the modal when Logged In by taking the link away and putting it in a variable
  var encryptURL = bolt_link.bolt_link;

  $('a.portal-link').attr('href', encryptURL);
  $('.portal-link').find('a').attr('href', encryptURL);

  // Disable the URL after we get it from the link with the bridge__url ID
  $('#bridge__url').attr('href', '#');

  //var iscsURL = 'https://uat-wrc.iscs.com';
  var iscsURL = bolt_link.ottomation_link;
  //Regex patterns to check
  var ottomationPattern = /^[a-zA-Z]{4}\d{6}[-]\d{2}$/;
  var boltPattern = /^[a-zA-Z]{4}[-]\d{1,5}$/;

  // Overwrite some of the default configurations for Parsley, like DOM placement of error messages
  var parsleyConfig = {
    errorsContainer: '.modal__error'
  }
http://portal.thewrcgroup.com/WRCView/Default.aspx?t=VUwzay9ww8lXeNKxFGp4TCRVlsTZGk5dS3sT4QCFXUG0QuRkrJGPTJGmRPlP6qEgLio9IHEJheqimU4EuTMsdw4xzbHzLvMU8%2BJu%2BlCwR9QqTHWLNDhOaHmWgMJiiX7QbQIfgQOn7vcYF4%2FiK5TAwV0yJuLAXGlkyDodp%2B7SPzY%3D

https://portal.thewrcgroup.com/WRCView/Default.aspx?t=ctMrUECjsPM61%2BHblzHU9FWOUnMxQ8ZSwAajDpX2O8OI7uNThdDHLMI%2BBdIIEcb1M6yync2ktRtOAddn3Ya8xEqmHABcUr7uVmwH7jkbWQ1FGNnx8nLEMgduSpwQic5g%2BRcBGZWYN3Uh3T4ejXJd19Oulzb6vIURbPgYyzmUfv4%3D
  //This functionality happens after login
  $('#policy-number-check').parsley(parsleyConfig);

  $('#policy-number-check').on('submit', function(){
    var form = $('#policy-number-check').parsley();
    form.validate();

    // Policy string patterns are one of the following and include the dash in the length count and regex check:
    // PAWI22222-01 for 1st Otto-mation : ^[a-zA-Z\d]{9}[-]\d{2}$ (only has 2 digits after the dash)
    // PAWI-2222 for Bolt w/ regex of : ^[a-zA-Z\d]{4}[-]\d{1,5}$   (can have up to 5 digits after the dash)

    if(form.isValid()){
      var inputVal = $('#policy-num').val();

      if( boltPattern.test(inputVal) ) {
        if ( $('#modalForm .modal__title').data("title") === "access-policy" )  {
          window.open(encryptURL, '_blank');
        } else {
          window.open('https://epayment.epymtservice.com/epay.jhtml?billerGroupId=AUT&billerId=AUT&productCode=BillerGroup-AUT&disallowLogin=N', '_blank');
        }
      } else {
        //Ottomation
        window.open(iscsURL, '_blank');
      }
      closeModal();
    }
    return false;

  });


  // Modal logic/general styles kinda pulled from
  // Ref http://refills.bourbon.io/components/

  //The header of the modal is different depending on the link clicked.
  $('.policy__modal-trigger > a, .bill__modal-trigger > a, .modal-public-trigger').on('click', function(){
      var title = "Pay Bill";
      var titleAttr = "pay-bill";
      var modalClass = "bill-pay-modal";

      if($(this).parent().hasClass('policy__modal-trigger')){
        title = "Access Policy";
        titleAttr = "access-policy";
        modalClass = "policy-modal";
      }

      $('#modalForm .modal__title').text(title);
      $('#modalForm .modal__title').data("title", titleAttr); // using DataAttributes to check which URL to use
      $('#modalForm').addClass(modalClass);
      $('body').addClass('modal-open');
  });

  //Add an extra class to the validation input so we can check if the modal needs the public-facing URL or not
  if ( $('.bill__modal-trigger').hasClass('modal-public-trigger') ) {
      $('.modal__form').addClass('modal__form-public');
  }

  $('.modal__inner').on('click', function(e) {
    e.stopPropagation();
  });

  $('.modal-fade-screen, .modal__close').on('click', closeModal);

  function closeModal(){
    $('body').removeClass('modal-open');
    $('#modalForm').removeClass('bill-pay-modal');
    $('#modalForm').removeClass('policy-modal');
    // reset Parsley & form field attributes so we don't have conflicts betwee Pay Bill and Access Policy
    $('#policy-number-check').parsley().reset();
    $('#policy-num').val('');
  }


  
  //Learning Library Feedback modal
  $('#ll__modal-trigger').on('click', function() {
    
    $('#ll-modalForm .modal-fade-screen').addClass('feedback-open');
    console.log('clicked');

  });

  $('#ll-modalForm .modal__close, #ll-modalForm .modal-fade-screen').on('click', function() {
    $('#ll-modalForm .modal-fade-screen').removeClass('feedback-open');
  });

});


