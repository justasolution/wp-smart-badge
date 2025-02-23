<?php
// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove database tables
global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}smart_badge_templates");

// Remove options
delete_option('wp_smart_badge_version');
delete_option('wp_smart_badge_settings');

// Remove uploaded files
$upload_dir = wp_upload_dir();
$badge_dir = $upload_dir['basedir'] . '/smart-badge';
if (file_exists($badge_dir)) {
    foreach (scandir($badge_dir) as $item) {
        if ($item == '.' || $item == '..') continue;
        unlink($badge_dir . '/' . $item);
    }
    rmdir($badge_dir);
}
