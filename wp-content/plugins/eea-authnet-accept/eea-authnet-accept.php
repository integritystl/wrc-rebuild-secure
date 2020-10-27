<?php

/*
  Plugin Name: Event Espresso - Authorize.net Accept Gateway (EE 4.9.59+)
  Plugin URI: https://eventespresso.com
  Description: Authorize.net Accept is an off-site payment method for Event Espresso for accepting credit and debit card payments and is available to event organizers in the United States, Canada, United Kingdom, and Australia. An account with Authorize.net is required to accept payments. Requires HTTPS.
  Version: 1.0.3.p

  Author: Event Espresso
  Author URI: https://eventespresso.com
  Copyright 2014 Event Espresso (email : support@eventespresso.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA02110-1301USA
 *
 * ------------------------------------------------------------------------
 *
 * Event Espresso
 *
 * Event Registration and Management Plugin for WordPress
 *
 * @ package		Event Espresso
 * @ author		Event Espresso
 * @ copyright	(c) 2008-2015 Event Espresso  All Rights Reserved.
 * @ license		https://eventespresso.com/support/terms-conditions/   * see Plugin Licensing *
 * @ link			https://eventespresso.com
 * @ version	 	EE4
 *
 * ------------------------------------------------------------------------
 */

define('EEA_AUTHORIZENET_ACCEPT_VERSION', '1.0.3.p');
define('EEA_AUTHORIZENET_ACCEPT_PLUGIN_FILE', __FILE__);


function load_eea_authorizenet_accept()
{
    if (class_exists('EE_Addon')) {
        require_once ( plugin_dir_path(__FILE__) . 'EE_AuthorizeNet_Accept.class.php' );
        EE_AuthorizeNet_Accept::register_addon();
    }
}
add_action('AHEE__EE_System__load_espresso_addons', 'load_eea_authorizenet_accept');

// End of file eea-authorize-net-pm.php
// Location: wp-content/plugins/eea-authnet-accept/eea-authnet-accept.php
