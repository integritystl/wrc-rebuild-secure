(function($){
  $(document).ready(function(){

    $('#print_terms_and_conditions').on('click', function(){
      toprint();
		});

		//using the masked input plugin
		$('input[name="phone"]').mask("(999) 999-9999");

		$('input[name="password"], input[name="pass1"]').on('keyup', function(){
			var password = $(this).val();
			checkPasswordRequirements(password);
			setPasswordStrengthMeter(password);
		});

		function checkPasswordRequirements(password){
			var successClass = 'met';
			var lengthSelector = '.password_requirements #character-requirement';
			var lowercaseSelector = '.password_requirements #lowercase-requirement';
			var uppercaseSelector = '.password_requirements #uppercase-requirement';
			var numberSelector = '.password_requirements #number-requirement';
			var symbolSelector = '.password_requirements #symbol-requirement';
			var hashSelector = '.password_limitations #hash-limitation';

			if(password.length >= 8){
				$(lengthSelector).addClass(successClass);
			}else{
				$(lengthSelector).removeClass(successClass);
			}
			if(/[a-z]/.test(password)){
				$(lowercaseSelector).addClass(successClass);
			}else{
				$(lowercaseSelector).removeClass(successClass);
			}
			if(/[A-Z]/.test(password)){
				$(uppercaseSelector).addClass(successClass);
			}else{
				$(uppercaseSelector).removeClass(successClass);
			}
			if(/\d/.test(password)){
				$(numberSelector).addClass(successClass);
			}else{
				$(numberSelector).removeClass(successClass);
			}
			if(/\W/.test(password)){
				$(symbolSelector).addClass(successClass);
			}else{
				$(symbolSelector).removeClass(successClass);
			}
			if(/#/.test(password)){
				$(hashSelector).addClass('failed');
			}else{
				$(hashSelector).removeClass('failed');
			}
		}

		function setPasswordStrengthMeter(password){
			var score = scorePassword(password);
			if(score >= 80){
				$('.meter-text').text('Strong');
				removePasswordRatingClass();
				$('.meter-value').addClass('strong-password');
			}else if(score >= 60){
				$('.meter-text').text('Good');
				removePasswordRatingClass();
				$('.meter-value').addClass('good-password');
			}else{
				$('.meter-text').text('Weak');
				removePasswordRatingClass();
				$('.meter-value').addClass('weak-password');
			}

			if(score > 100){score = 100;}
			if(score <= 0){score = 1;}

			$('.meter-value').css('width', score + '%');
		}

		function removePasswordRatingClass(){
			$('.meter-value').removeClass('weak-password');
			$('.meter-value').removeClass('good-password');
			$('.meter-value').removeClass('strong-password');
		}

		//pretty solid password rating algorithm found here: https://stackoverflow.com/a/11268104
		function scorePassword(password){
			var score = 0;
			if (!password){
				return score;
			}

			// award every unique letter until 5 repetitions
			var letters = new Object();
			for (var i=0; i<password.length; i++) {
					letters[password[i]] = (letters[password[i]] || 0) + 1;
					score += 5.0 / letters[password[i]];
			}

			// bonus points for mixing it up
			var variations = {
					digits: /\d/.test(password),
					lower: /[a-z]/.test(password),
					upper: /[A-Z]/.test(password),
					nonWords: /\W/.test(password),
			}

			variationCount = 0;
			for (var check in variations) {
					variationCount += (variations[check] == true) ? 1 : 0;
			}
			score += (variationCount - 1) * 10;

			return parseInt(score);
		}

    function toprint() {
			var data = $('#terms_and_conditions_container').html();
			var mywindow = window.open('', 'print', '');
			mywindow.document.write('<html><head><title>Terms and Conditions</title>');
			mywindow.document.write('</head><body >');
			mywindow.document.write('<h1 style="text-align:center;">Terms and Conditions</h1>');
			mywindow.document.write(data);
			mywindow.document.close();
			mywindow.focus();
			mywindow.print();
			mywindow.close();
			return true;
		}
  });
})(jQuery);
