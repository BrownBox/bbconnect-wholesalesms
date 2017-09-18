<?php
/**
 * Plugin Name: Connexions WhoesaleSMS
 * Plugin URI: http://connexionscrm.com/
 * Description: Send SMS messages to your contacts direct from your CRM
 * Version: 0.1
 * Author: Brown Box
 * Author URI: http://brownbox.net.au
 * License: Proprietary Brown Box
 */
define('BBCONNECT_WHOLESALESMS_DIR', plugin_dir_path(__FILE__));
define('BBCONNECT_WHOLESALESMS_URL', plugin_dir_url(__FILE__));
define('BBCONNECT_WHOLESALESMS_WEBHOOK_URL', plugins_url('webhook.php', __FILE__));

require_once (BBCONNECT_WHOLESALESMS_DIR.'settings.php');
require_once (BBCONNECT_WHOLESALESMS_DIR.'classes/connector.class.php');

function bbconnect_wholesalesms_init() {
    if (!defined('BBCONNECT_VER')) {
        add_action('admin_init', 'bbconnect_wholesalesms_deactivate');
        add_action('admin_notices', 'bbconnect_wholesalesms_deactivate_notice');
        return;
    }
    if (is_admin()) {
        new BbConnectUpdates(__FILE__, 'BrownBox', 'bbconnect-wholesalesms');
    }
    $quicklinks_dir = BBCONNECT_WHOLESALESMS_DIR.'quicklinks/';
    bbconnect_quicklinks_recursive_include($quicklinks_dir);
}
add_action('plugins_loaded', 'bbconnect_wholesalesms_init');

function bbconnect_wholesalesms_deactivate() {
    deactivate_plugins(plugin_basename(__FILE__));
}

function bbconnect_wholesalesms_deactivate_notice() {
    echo '<div class="updated"><p><strong>Connexions WholesaleSMS</strong> has been <strong>deactivated</strong> as it requires Connexions.</p></div>';
    if (isset($_GET['activate'])) {
        unset($_GET['activate']);
    }
}
