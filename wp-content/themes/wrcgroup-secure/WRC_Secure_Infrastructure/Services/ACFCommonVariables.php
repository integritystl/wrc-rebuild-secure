<?php
namespace WRCInfrastructure\Services;

/**
* Class to hold common variables for use throughout the site.
**/

class ACFCommonVariables
{
  public static function ACFSelectAllStates() {
    return array(
      'AL'=>'Alabama',
      'AK'=>'Alaska',
      'AZ'=>'Arizona',
      'AR'=>'Arkansas',
      'CA'=>'California',
      'CO'=>'Colorado',
      'CT'=>'Connecticut',
      'DE'=>'Delaware',
      'DC'=>'District of Columbia',
      'FL'=>'Florida',
      'GA'=>'Georgia',
      'HI'=>'Hawaii',
      'ID'=>'Idaho',
      'IL'=>'Illinois',
      'IN'=>'Indiana',
      'IA'=>'Iowa',
      'KS'=>'Kansas',
      'KY'=>'Kentucky',
      'LA'=>'Louisiana',
      'ME'=>'Maine',
      'MD'=>'Maryland',
      'MA'=>'Massachusetts',
      'MI'=>'Michigan',
      'MN'=>'Minnesota',
      'MS'=>'Mississippi',
      'MO'=>'Missouri',
      'MT'=>'Montana',
      'NE'=>'Nebraska',
      'NV'=>'Nevada',
      'NH'=>'New Hampshire',
      'NJ'=>'New Jersey',
      'NM'=>'New Mexico',
      'NY'=>'New York',
      'NC'=>'North Carolina',
      'ND'=>'North Dakota',
      'OH'=>'Ohio',
      'OK'=>'Oklahoma',
      'OR'=>'Oregon',
      'PA'=>'Pennsylvania',
      'RI'=>'Rhode Island',
      'SC'=>'South Carolina',
      'SD'=>'South Dakota',
      'TN'=>'Tennessee',
      'TX'=>'Texas',
      'UT'=>'Utah',
      'VT'=>'Vermont',
      'VA'=>'Virginia',
      'WA'=>'Washington',
      'WV'=>'West Virginia',
      'WI'=>'Wisconsin',
      'WY'=>'Wyoming',
    );
  }

  public static function ACFSelectStatesOfBusiness($all = true) {
      
      if($all){
            return array (
                  'AR' => 'Arkansas',
                  'IL' => 'Illinois',
                  'IA' => 'Iowa',
                  'MO' => 'Missouri',
                  'SD' => 'South Dakota',
                  'WI' => 'Wisconsin',
                  'Senior Staff' => 'Senior Staff',
                  'Manager' => 'Manager',
                  'All Staff' => 'All Staff'
            );
      }
      else {
            return array (
                  'AR' => 'Arkansas',
                  'IL' => 'Illinois',
                  'IA' => 'Iowa',
                  'MO' => 'Missouri',
                  'SD' => 'South Dakota',
                  'WI' => 'Wisconsin'
            );
      }
  }
}
