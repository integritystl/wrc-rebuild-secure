<?php
namespace WRCInfrastructure\Users\Helpers;

/**
* This class holds the form field name cnstants so we don't have to go searching for names everywhere if we change one
**/

abstract class FormFieldNames{
  const FIRST_NAME = array('NAME' => "first_name", 'TEXT' => 'First Name');
  const LAST_NAME = array('NAME' => "last_name", 'TEXT' => 'Last Name');
  const EMAIL = array('NAME' => "email", 'TEXT' => 'Email');
  const CONFIRM_EMAIL = array('NAME' => "confirm_email", 'TEXT' => 'Confirm Email');
  const PASSWORD = array('NAME' => "password", 'TEXT' => 'Password');
  const CONFIRM_PASSWORD = array('NAME' => "confirm_password", 'TEXT' => 'Confirm Password');
  const AGENCY = array('NAME' => "agency", 'TEXT' => 'Agency');
  const ADDRESS = array('NAME' => "address", 'TEXT' => 'Address');
  const ADDRESS_2 = array('NAME' => "address_2", 'TEXT' => 'Address 2');
  const STATE = array('NAME' => "state", 'TEXT' => 'State');
  const CITY = array('NAME' => "city", 'TEXT' => 'City');
  const ZIP = array('NAME' => "zip", 'TEXT' => 'Zip');
  const PHONE = array('NAME' => "phone", 'TEXT' => 'Phone');
}