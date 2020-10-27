<?php
namespace WRCInfrastructure\Events;
/**
*
* This class handles grabbing Events posts for setting a cookie for New Events.
* Pulled from NewsHelper to be the same as how News makes a cookie for New Posts
**/
class EventsHelper
{
  const COOKIE_NAME = "new_event_ids";

  public static function setNewEventsCookie($new_event_IDs, $site_id){
    //original: cookie format "post_id|view_flag,post_id|view_flag,..."
		//changed to: "site_id|post_id|view_flag" so we can determine which site the post is for
		//ex: "1|123|0,2|124|1"
		$new_events_cookie = (!empty($_COOKIE[self::COOKIE_NAME]) ? $_COOKIE[self::COOKIE_NAME] : '');
		//if a new_posts cookie has not been set
		if(!empty($new_events_cookie)){
			//if there have been new posts created since the last time the user logged in
			if(count($new_event_IDs) > 0){
				//check to see if there are new posts not already included in the cookie
				//add them to tempt array if there are
				$new_events_cookie_array = explode(",", $new_events_cookie);
				$new_values = array();
				foreach($new_event_IDs as $id){
					//check to see if cookie with this site/post combo exists
					if(self::isNewCookieValue("$site_id|$id", $new_events_cookie_array)){
						//view flag starts as a 0 for not viewed
						$cookie_value = "$site_id|$id|0";
						//var_dump($cookie_value);
						array_push($new_values, $cookie_value);
					}
				}
				//if there are new values, merge them into the cookie array and set the cookie
				if(count($new_values) > 0){
					$new_events_cookie_array = array_merge($new_events_cookie_array, $new_values);
					//set cookie and make sure it's immediately accessible
					$cookie_val = implode(",", $new_events_cookie_array);
					setcookie(self::COOKIE_NAME, $cookie_val, 0, '/');
					$_COOKIE[self::COOKIE_NAME] = $cookie_val;
				}
			}
		}else{
			//var_dump($new_event_IDs); die;
			//if the cookie has not been set
			if(count($new_event_IDs) > 0){
				$new_events_cookie_array = array();
				//create cookie values from post IDs and set in cookie
				foreach($new_event_IDs as $id){
						$cookie_value = "$site_id|$id|0";
						array_push($new_events_cookie_array, $cookie_value);
				}
				$cookie_val = implode(",", $new_events_cookie_array);
				setcookie(self::COOKIE_NAME, $cookie_val, 0, '/');
				$_COOKIE[self::COOKIE_NAME] = $cookie_val;
			}
		}
	}
	
	private static function isNewCookieValue($value, $cookie_array){
		foreach($cookie_array as $cookie){
			if(strpos($cookie, $value) !== false){
				return false;
			}
		}

		return true;
	}

  public static function isEventNew($post_id){
    //check to see if we should mark the post as new
    $new_post = false;
    if(!empty($_COOKIE[self::COOKIE_NAME])){
      $cookie_array = explode(",", $_COOKIE[self::COOKIE_NAME]);
      if(count($cookie_array) > 0){
        foreach($cookie_array as $cookie_value){
          $value_array = explode("|", $cookie_value);
          if(intval($value_array[1]) === $post_id){
            $new_post = empty($value_array[2]);
          }
        }
      }
    }

    return $new_post;
  }

  public static function deleteEventPostsCookie(){
    //destroy that cookie!
    unset($_COOKIE[self::COOKIE_NAME]);
    setcookie(self::COOKIE_NAME, '', time() - 3600, '/');
  }

}
