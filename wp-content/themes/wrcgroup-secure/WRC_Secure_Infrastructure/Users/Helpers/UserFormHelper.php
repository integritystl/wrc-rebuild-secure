<?php
namespace WRCInfrastructure\Users\Helpers;

class UserFormHelper{

  public static function generateFormFields($is_registration){
    $form_fields = array(
      FormFieldNames::FIRST_NAME["NAME"] => array('field_text' => FormFieldNames::FIRST_NAME["TEXT"], 'field_value' => '', 'required' => true, 'error' => false, 'updated' => false),
      FormFieldNames::LAST_NAME["NAME"] => array('field_text' => FormFieldNames::LAST_NAME["TEXT"], 'field_value' => '', 'required' => true, 'error' => false, 'updated' => false),
      FormFieldNames::EMAIL["NAME"] => array('field_text' => FormFieldNames::EMAIL["TEXT"], 'field_value' => '', 'required' => true, 'error' => false, 'updated' => false),
      FormFieldNames::CONFIRM_EMAIL["NAME"] => array('field_text' => FormFieldNames::CONFIRM_EMAIL["TEXT"], 'field_value' => '', 'required' => true, 'error' => false, 'updated' => false),
      FormFieldNames::PASSWORD["NAME"] => array('field_text' => FormFieldNames::PASSWORD["TEXT"], 'field_value' => '', 'required' => $is_registration, 'error' => false, 'updated' => false),
      FormFieldNames::CONFIRM_PASSWORD["NAME"] => array('field_text' => FormFieldNames::CONFIRM_PASSWORD["TEXT"], 'field_value' => '', 'required' => $is_registration, 'error' => false, 'updated' => false),
      FormFieldNames::AGENCY["NAME"] => array('field_text' => FormFieldNames::AGENCY["TEXT"], 'field_value' => '', 'required' => $is_registration, 'error' => false, 'updated' => false),
      FormFieldNames::ADDRESS["NAME"] => array('field_text' => FormFieldNames::ADDRESS["TEXT"], 'field_value' => '', 'required' => true, 'error' => false, 'updated' => false),
      FormFieldNames::ADDRESS_2["NAME"] => array('field_text' => FormFieldNames::ADDRESS_2["TEXT"], 'field_value' => '', 'required' => false, 'error' => false, 'updated' => false),
      FormFieldNames::CITY["NAME"] => array('field_text' => FormFieldNames::CITY["TEXT"], 'field_value' => '', 'required' => true, 'error' => false, 'updated' => false),
      FormFieldNames::STATE["NAME"] => array('field_text' => FormFieldNames::STATE["TEXT"], 'field_value' => '', 'required' => true, 'error' => false, 'updated' => false),
      FormFieldNames::ZIP["NAME"] => array('field_text' => FormFieldNames::ZIP["TEXT"], 'field_value' => '', 'required' => true, 'error' => false, 'updated' => false),
      FormFieldNames::PHONE["NAME"] => array('field_text' => FormFieldNames::PHONE["TEXT"], 'field_value' => '', 'required' => true, 'error' => false, 'updated' => false),
    );

    return $form_fields;
  }

  public static function createSelectInputGroup($form_fields, $selected_field, $dropdown_data){
    $selected_value = empty($form_fields[$selected_field['NAME']]['field_value']) ? '' : $form_fields[$selected_field['NAME']]['field_value'] ;
    $required = $form_fields[$selected_field['NAME']]['required'];
    echo('<div class="input_group input_group-select');
    echo($form_fields[$selected_field['NAME']]['error'] ? ' has-error' : '');
    echo('">');
    echo('<label class="');
    echo($required ? 'required-label' : '');
    echo('" for="' . $selected_field['NAME'] . '">');
    echo($form_fields[$selected_field['NAME']]['field_text']);
    echo($required ? '<span>*</span>' : '');
    echo('</label>');
    echo('<select class="');
    echo($required ? 'required-field"' : '"');
    echo(' name="' . $selected_field['NAME'] . '" id="' . $selected_field['NAME'] . '-dropdown">');
    echo('<option value="">Select ' . $form_fields[$selected_field['NAME']]['field_text'] . '</option>');
    foreach($dropdown_data as $data_name => $data_value){
      echo('<option ');
      echo(strval($selected_value) === strval($data_name) ? 'selected' : '');
      echo(' value="' . $data_name . '">' . $data_value . '</option>');
    }

    echo('</select>');
    echo('</div>');
  }

  public static function createTextInputGroup($form_fields, $selected_field, $is_password = false, $includePasswordRequirements = false){
    $field_value = empty($form_fields[$selected_field['NAME']]['field_value']) ? '' : $form_fields[$selected_field['NAME']]['field_value'] ;
    $required = $form_fields[$selected_field['NAME']]['required'];
    echo('<div class="input_group');
    echo($form_fields[$selected_field['NAME']]['error'] ? ' has-error' : '');
    echo('">');
    echo('<label class="');
    echo($required ? 'required-label' : '');
    echo('" for="' . $selected_field['NAME'] . '">');
    echo($form_fields[$selected_field['NAME']]['field_text']);
    echo($required ? '<span>*</span>' : '');
    echo('</label>');

    if($includePasswordRequirements){
      self::buildPasswordRequirements();
    }

    echo('<input class="');
    echo($required ? 'required-field' : '');

    if($is_password){
      echo('" type="password" autocomplete="off" ');
    }else{
      echo('" type="text" ');
    }

    echo('name="' . $selected_field['NAME'] . '" value="' . ($is_password ? '' : $field_value) . '"/>');

    if($includePasswordRequirements){
      self::buildPasswordStrengthMeter();
    }

    echo('</div>');
  }

  public static function buildPasswordRequirements(){
    echo('<p>Passwords must contain:</p>');
    echo('<ul class="password_requirements">');
    echo('<li id="character-requirement" class="password-requirement">');
    echo('<i class="fa fa-check-circle" aria-hidden="true"></i>');
    echo('<i class="fa fa-times-circle" aria-hidden="true"></i>');
    echo('<span>at least 8 characters</span>');
    echo('</li>');
    echo('<li id="lowercase-requirement" class="password-requirement">');
    echo('<i class="fa fa-check-circle" aria-hidden="true"></i>');
    echo('<i class="fa fa-times-circle" aria-hidden="true"></i>');
    echo('<span>a lowercase letter</span>');
    echo('</li>');
    echo('<li id="uppercase-requirement" class="password-requirement">');
    echo('<i class="fa fa-check-circle" aria-hidden="true"></i>');
    echo('<i class="fa fa-times-circle" aria-hidden="true"></i>');
    echo('<span>an uppercase letter</span>');
    echo('</li>');
    echo('<li id="number-requirement" class="password-requirement">');
    echo('<i class="fa fa-check-circle" aria-hidden="true"></i>');
    echo('<i class="fa fa-times-circle" aria-hidden="true"></i>');
    echo('<span>a number</span>');
    echo('</li>');
    echo('<li id="symbol-requirement" class="password-requirement">');
    echo('<i class="fa fa-check-circle" aria-hidden="true"></i>');
    echo('<i class="fa fa-times-circle" aria-hidden="true"></i>');
    echo('<span>a symbol</span>');
    echo('</li>');
    echo('</ul>');
    echo('<p>Passwords must not contain:</p>');
    echo('<ul class="password_limitations">');
    echo('<li id="hash-limitation" class="password-limitation">');
    echo('<i class="fa fa-check-circle" aria-hidden="true"></i>');
    echo('<i class="fa fa-times-circle" aria-hidden="true"></i>');
    echo('<span>the "#" symbol (not allowed by Vertafore PL Rating Comparative Rater)</span>');
    echo('</li>');
    echo('</ul>');
  }

  private static function buildPasswordStrengthMeter(){
    echo('<div class="strength-meter"><span class="meter-value"></span></div><span class="meter-text">Weak</span>');
  }

}
