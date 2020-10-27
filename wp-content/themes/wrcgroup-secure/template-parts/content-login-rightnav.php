<?php
//Take field data of (###) ###-#### & output differently for tel attribute
$phoneNumber = get_field('tech_assistance_phone', 'option');

if (preg_match('/\((\d{3})\)\ (\d{3})\-(\d{4})$/', $phoneNumber, $matches)) {
  $formatedPhone = '1' . $matches[1] . '' .$matches[2] . '' .$matches[3];
} else {
  //fallback to what's in the field if it doesn't match
  $formatedPhone = $phoneNumber;
}?>

<h2>Technical Assistance</h2>
<p><i class="fa fa-envelope"></i><a href="mailto:<?php echo get_field('tech_assistance_email', 'option'); ?>"><?php echo get_field('tech_assistance_email', 'options'); ?></a></p>
<p><i class="fa fa-phone"></i><a href="tel:<?php echo $formatedPhone; ?>"><?php echo $phoneNumber; ?></a></p>
