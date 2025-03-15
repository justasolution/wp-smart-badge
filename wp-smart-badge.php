<?php
/*
Plugin Name: WP Smart Badge
Description: Professional ID Badge Generator for WordPress
Version: 1.0.0
Author: Mohammad Mushrath
License: GPL v2 or later
Text Domain: wp-smart-badge
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

if (!defined('WP_SMART_BADGE_FILE')) {
    define('WP_SMART_BADGE_FILE', __FILE__);
}

// Define plugin constants
define('WP_SMART_BADGE_VERSION', '1.0.0');
define('WP_SMART_BADGE_PATH', plugin_dir_path(__FILE__));
define('WP_SMART_BADGE_URL', plugins_url('', __FILE__));
define('WP_SMART_BADGE_LOGO_URL', 'http://smartidportal.justasolutionaway.com/wp-content/uploads/2025/02/png-transparent-guntur-vijayawada-bus-nellore-andhra-pradesh-state-road-transport-corporation-bus-removebg-preview-e1740051277257.png');

// Add this near the top of the file, after defining constants
if (!defined('WP_DEBUG')) {
    define('WP_DEBUG', true);
}
if (!defined('WP_DEBUG_DISPLAY')) {
    define('WP_DEBUG_DISPLAY', true);
}
if (!defined('WP_DEBUG_LOG')) {
    define('WP_DEBUG_LOG', true);
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Add this at the top of the file after the plugin header
function wp_smart_badge_log($message, $data = null) {
    $log_file = WP_SMART_BADGE_PATH . 'debug.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[{$timestamp}] {$message}" . ($data ? ' Data: ' . print_r($data, true) : '') . "\n";
    error_log($log_message, 3, $log_file);
}

// Debug logging function
function wp_smart_badge_debug_log($message, $data = null) {
    if (WP_DEBUG === true) {
        $log_message = '[Smart Badge Debug] ' . $message;
        if ($data !== null) {
            $log_message .= ' Data: ' . print_r($data, true);
        }
        error_log($log_message);
    }
}

// Add activation logging
register_activation_hook(__FILE__, function() {
    wp_smart_badge_log('Plugin activated');
    try {
        // Create uploads directory if it doesn't exist
        $upload_dir = wp_upload_dir();
        $badge_dir = $upload_dir['basedir'] . '/badges';
        if (!file_exists($badge_dir)) {
            mkdir($badge_dir, 0755, true);
        }
        wp_smart_badge_log('Badge directory created at: ' . $badge_dir);
        
        // Check template files
        $template_files = array(
            'class-badge-template.php',
            'class-active-employee-template.php',
            'class-active-employee-horizontal-template.php',
            'class-retired-officer-template.php',
            'class-retired-medical-template.php',
            'class-retired-travel-template.php',
            'class-class-3-and-4-template.php',
            'class-vertical-card-template.php'
        );
        
        foreach ($template_files as $file) {
            $file_path = WP_SMART_BADGE_PATH . 'includes/templates/' . $file;
            if (file_exists($file_path)) {
                wp_smart_badge_log('Template file exists: ' . $file);
                wp_smart_badge_log('File contents:', file_get_contents($file_path));
            } else {
                wp_smart_badge_log('Template file missing: ' . $file);
            }
        }
        
        // Check autoloader
        if (file_exists(WP_SMART_BADGE_PATH . 'vendor/autoload.php')) {
            wp_smart_badge_log('Composer autoloader exists');
        } else {
            wp_smart_badge_log('Composer autoloader missing');
        }
        
        // List all declared classes
        wp_smart_badge_log('Declared classes:', get_declared_classes());
        
    } catch (Exception $e) {
        wp_smart_badge_log('Activation error: ' . $e->getMessage());
        wp_smart_badge_log('Error trace: ' . $e->getTraceAsString());
    }
});

// Initialize default template on activation
register_activation_hook(__FILE__, function() {
    wp_smart_badge_debug_log('Plugin activated, initializing default template');
    
    $default_template = array(
        'layout' => array(
            'front' => array(
                array('type' => 'photo', 'x' => 10, 'y' => 10),
                array('type' => 'name', 'x' => 10, 'y' => 45),
                array('type' => 'id', 'x' => 10, 'y' => 60),
                array('type' => 'designation', 'x' => 10, 'y' => 75)
            ),
            'back' => array(
                array('type' => 'department', 'x' => 10, 'y' => 10),
                array('type' => 'blood_group', 'x' => 10, 'y' => 30),
                array('type' => 'qr_code', 'x' => 60, 'y' => 60)
            )
        ),
        'styles' => array(
            'background' => array(
                'type' => 'gradient',
                'color' => '#ffffff',
                'gradient' => array(
                    'start' => '#ffffff',
                    'end' => '#f0f0f0',
                    'direction' => 'to right'
                )
            ),
            'text' => array(
                'color' => '#000000',
                'size' => 'medium'
            )
        )
    );

    update_option('smart_badge_template_active-employee', $default_template);
    wp_smart_badge_debug_log('Default template saved', $default_template);
});

// Add deactivation logging
register_deactivation_hook(__FILE__, function() {
    wp_smart_badge_log('Plugin deactivated');
});

// Load Composer autoloader
$composer_autoload = WP_SMART_BADGE_PATH . 'vendor/autoload.php';
if (file_exists($composer_autoload)) {
    try {
        wp_smart_badge_log('Loading composer autoloader');
        require_once $composer_autoload;
        wp_smart_badge_log('Composer autoloader loaded successfully');
    } catch (Exception $e) {
        wp_smart_badge_log('Error loading composer autoloader: ' . $e->getMessage());
        wp_smart_badge_log('Error trace: ' . $e->getTraceAsString());
    }
} else {
    wp_smart_badge_log('Composer autoloader not found');
    add_action('admin_notices', function() {
        echo '<div class="error"><p>WP Smart Badge: Composer dependencies are missing. Please run <code>composer install</code> in the plugin directory.</p></div>';
    });
    return;
}

// Load template classes
try {
    wp_smart_badge_log('Loading template classes');
    
    $template_base = WP_SMART_BADGE_PATH . 'includes/templates/class-badge-template.php';
    if (file_exists($template_base)) {
        require_once $template_base;
        wp_smart_badge_log('Base template class loaded');
    } else {
        throw new Exception('Base template class file not found at: ' . $template_base);
    }
    
    $active_template = WP_SMART_BADGE_PATH . 'includes/templates/class-active-employee-template.php';
    if (file_exists($active_template)) {
        require_once $active_template;
        wp_smart_badge_log('Active employee template class loaded');
    } else {
        throw new Exception('Active employee template class file not found at: ' . $active_template);
    }
    
    $retired_template = WP_SMART_BADGE_PATH . 'includes/templates/class-retired-officer-template.php';
    if (file_exists($retired_template)) {
        require_once $retired_template;
        wp_smart_badge_log('Retired officer template class loaded');
    } else {
        throw new Exception('Retired officer template class file not found at: ' . $retired_template);
    }
    
    // $vertical_card_template = WP_SMART_BADGE_PATH . 'includes/templates/class-vertical-card-template.php';
    // if (file_exists($vertical_card_template)) {
    //     require_once $vertical_card_template;
    //     wp_smart_badge_log('Vertical card template class loaded');
    // } else {
    //     throw new Exception('Vertical card template class file not found at: ' . $vertical_card_template);
    // }
    
    wp_smart_badge_log('All template classes loaded successfully');
} catch (Exception $e) {
    wp_smart_badge_log('Error loading template classes: ' . $e->getMessage());
    wp_smart_badge_log('Error trace: ' . $e->getTraceAsString());
    return;
}

use WpSmartBadge\Templates\BadgeTemplate;
use WpSmartBadge\Templates\ActiveEmployeeTemplate;
use WpSmartBadge\Templates\ActiveEmployeeHorizontalTemplate;
use WpSmartBadge\Templates\RetiredOfficerTemplate;
use WpSmartBadge\Templates\Class34Template;

use WpSmartBadge\Badge_Generator;

// Activation hook
register_activation_hook(__FILE__, 'wp_smart_badge_activate');
function wp_smart_badge_activate() {
    global $wpdb;
    
    // Create database tables
    $charset_collate = $wpdb->get_charset_collate();
    
    // Templates table
    $table_name = $wpdb->prefix . 'smart_badge_templates';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        data longtext NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Create upload directory
    $upload_dir = wp_upload_dir();
    $badge_dir = $upload_dir['basedir'] . '/smart-badge';
    if (!file_exists($badge_dir)) {
        wp_mkdir_p($badge_dir);
    }
    
    // Add version
    add_option('wp_smart_badge_version', WP_SMART_BADGE_VERSION);
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'wp_smart_badge_deactivate');
function wp_smart_badge_deactivate() {
    // Clear any transients
    delete_transient('smart_badge_cache');
}

// Admin menu
add_action('admin_menu', 'wp_smart_badge_admin_menu');
function wp_smart_badge_admin_menu() {
    add_menu_page(
        'Smart Badge',
        'Smart Badge',
        'manage_options',
        'wp-smart-badge',
        'wp_smart_badge_admin_page',
        'dashicons-id',
        30
    );
    
    add_submenu_page(
        'wp-smart-badge',
        'Generate Badge',
        'Generate Badge',
        'manage_options',
        'wp-smart-badge',
        'wp_smart_badge_admin_page'
    );
    
    add_submenu_page(
        'wp-smart-badge',
        'Templates',
        'Templates',
        'manage_options',
        'wp-smart-badge-templates',
        'wp_smart_badge_templates_page'
    );
    
    add_submenu_page(
        'wp-smart-badge',
        'Settings',
        'Settings',
        'manage_options',
        'wp-smart-badge-settings',
        'wp_smart_badge_settings_page'
    );
    
    // Hidden preview page
    add_submenu_page(
        null,
        'Preview Badge',
        'Preview Badge',
        'manage_options',
        'wp-smart-badge-preview',
        'wp_smart_badge_preview_page'
    );
}

// Admin assets
add_action('admin_enqueue_scripts', 'wp_smart_badge_admin_assets');
function wp_smart_badge_admin_assets($hook) {
    if (strpos($hook, 'wp-smart-badge') === false) {
        return;
    }
    
    // AG Grid
    wp_enqueue_style(
        'ag-grid',
        'https://cdn.jsdelivr.net/npm/ag-grid-community@30.2.1/styles/ag-grid.min.css',
        array(),
        '30.2.1'
    );
    
    wp_enqueue_style(
        'ag-grid-theme',
        'https://cdn.jsdelivr.net/npm/ag-grid-community@30.2.1/styles/ag-theme-alpine.min.css',
        array(),
        '30.2.1'
    );
    
    wp_enqueue_script(
        'ag-grid',
        'https://cdn.jsdelivr.net/npm/ag-grid-community@30.2.1/dist/ag-grid-community.min.js',
        array(),
        '30.2.1',
        true
    );
    
    // Plugin assets
    wp_enqueue_style(
        'wp-smart-badge-admin',
        plugins_url('/assets/css/admin.css', __FILE__),
        array('ag-grid', 'ag-grid-theme'),
        WP_SMART_BADGE_VERSION
    );
    
    wp_enqueue_script(
        'wp-smart-badge-admin',
        plugins_url('/assets/js/admin.js', __FILE__),
        array('jquery', 'ag-grid'),
        WP_SMART_BADGE_VERSION,
        true
    );
    
    // Localize script with user data endpoint
    wp_localize_script('wp-smart-badge-admin', 'wpSmartBadge', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wp_smart_badge_nonce'),
        'pluginUrl' => plugins_url('', __FILE__),
        'defaultAvatar' => plugins_url('/assets/images/default-avatar.jpg', __FILE__),
        'previewKey' => wp_create_nonce('preview_badge')
    ));
}

// AJAX endpoint for user data
add_action('wp_ajax_get_users_data', 'wp_smart_badge_get_users_data');
function wp_smart_badge_get_users_data() {
    check_ajax_referer('wp_smart_badge_nonce', 'nonce');
    
    $users = get_users(array(
        'fields' => array('ID', 'user_email', 'display_name'),
        'orderby' => 'display_name'
    ));
    
    $users_data = array();
    foreach ($users as $user) {
        // Get photo URL from attachment
        $photo_id = get_user_meta($user->ID, '_emp_photo_attachment_id', true);
        $photo_url = '';
        
        if ($photo_id) {
            $photo_url = wp_get_attachment_url($photo_id);
            // Verify if attachment still exists
            if (!$photo_url || !file_exists(get_attached_file($photo_id))) {
                $photo_url = '';
                delete_user_meta($user->ID, '_emp_photo_attachment_id');
            }
        }
        
        // Try getting from emp_photo meta if no attachment
        if (!$photo_url) {
            $photo_url = get_user_meta($user->ID, 'emp_photo', true);
            // Verify if it's a valid URL or base64
            if ($photo_url && !filter_var($photo_url, FILTER_VALIDATE_URL) && strpos($photo_url, 'data:image') !== 0) {
                $photo_url = '';
            }
        }
        
        // Use default avatar if no valid photo found
        if (!$photo_url) {
            $photo_url = plugins_url('assets/images/default-avatar.jpg', WP_SMART_BADGE_FILE);
        }

        $blood_group = get_user_meta($user->ID, 'emp_blood_group', true);
        if (empty($blood_group)) {
            $blood_group = get_user_meta($user->ID, 'blood_group', true); // Check old meta key
        }
        
        $user_data = array(
            'ID' => $user->ID,
            'user_email' => $user->user_email,
            'display_name' => $user->display_name,
            'first_name' => get_user_meta($user->ID, 'first_name', true),
            'last_name' => get_user_meta($user->ID, 'last_name', true),
            'emp_id' => get_user_meta($user->ID, 'emp_id', true),
            'emp_full_name' => get_user_meta($user->ID, 'emp_full_name', true),
            'emp_designation' => get_user_meta($user->ID, 'emp_designation', true),
            'emp_department' => get_user_meta($user->ID, 'emp_department', true),
            'emp_phone' => get_user_meta($user->ID, 'emp_phone', true),
            'emp_blood_group' => $blood_group,
            'emp_cfms_id' => get_user_meta($user->ID, 'emp_cfms_id', true),
            'emp_hrms_id' => get_user_meta($user->ID, 'emp_hrms_id', true),
            'emp_emergency_contact' => get_user_meta($user->ID, 'emp_emergency_contact', true),
            'emp_ehs_card' => get_user_meta($user->ID, 'emp_ehs_card', true),
            'emp_barcode' => get_user_meta($user->ID, 'emp_barcode', true),
            'emp_depot_location' => get_user_meta($user->ID, 'emp_depot_location', true),
            'emp_last_working' => get_user_meta($user->ID, 'emp_last_working', true),
            'emp_residential_address' => get_user_meta($user->ID, 'emp_residential_address', true),
            'emp_status' => get_user_meta($user->ID, 'emp_status', true),
            'emp_photo' => $photo_url,
            '_emp_photo_attachment_id' => $photo_id
        );
        $users_data[] = $user_data;
    }
    
    wp_send_json_success($users_data);
}

// AJAX endpoint for badge generation
add_action('wp_ajax_generate_badge', 'wp_smart_badge_generate_badge');
function wp_smart_badge_generate_badge() {
    check_ajax_referer('wp_smart_badge_nonce', 'nonce');
    
    $user_id = isset($_POST['user_id']) ? sanitize_text_field($_POST['user_id']) : '';
    $badge_type = isset($_POST['badge_type']) ? sanitize_text_field($_POST['badge_type']) : '';
    
    wp_smart_badge_log('Starting badge generation for user: ' . $user_id . ', badge type: ' . $badge_type);
    
    if (empty($user_id)) {
        wp_send_json_error('Invalid user ID');
    }
    
    try {
        // Get WordPress user data
        $user = get_userdata($user_id);
        if (!$user) {
            throw new Exception("User not found for ID: $user_id");
        }
        
        wp_smart_badge_log('Found user data', $user);
        
        // Get user meta data
        $user_data = array(
            'ID' => $user->ID,
            'emp_id' => get_user_meta($user->ID, 'emp_id', true) ?: $user->user_login,
            'emp_full_name' => get_user_meta($user->ID, 'emp_full_name', true) ?: $user->display_name,
            'emp_designation' => get_user_meta($user->ID, 'emp_designation', true) ?: $user->roles[0],
            'emp_department' => get_user_meta($user->ID, 'emp_department', true) ?: '',
            'emp_phone' => get_user_meta($user->ID, 'emp_phone', true) ?: '',
            'emp_blood_group' => get_user_meta($user->ID, 'emp_blood_group', true) ?: '',
            'emp_cfms_id' => get_user_meta($user->ID, 'emp_cfms_id', true) ?: '',
            'emp_hrms_id' => get_user_meta($user->ID, 'emp_hrms_id', true) ?: '',
            'emp_status' => get_user_meta($user->ID, 'emp_status', true) ?: 'active',
            'user_email' => $user->user_email,
            'display_name' => $user->display_name,
            'user_registered' => $user->user_registered,
            'emp_ehs_card' => get_user_meta($user->ID, 'emp_ehs_card', true) ?: '',
            'emp_emergency_contact' => get_user_meta($user->ID, 'emp_emergency_contact', true) ?: '',
            'emp_barcode' => get_user_meta($user->ID, 'emp_barcode', true) ?: '',
            'emp_depot_location' => get_user_meta($user->ID, 'emp_depot_location', true) ?: '',
            'emp_last_working' => get_user_meta($user->ID, 'emp_last_working', true) ?: '',
            'emp_residential_address' => get_user_meta($user->ID, 'emp_residential_address', true) ?: '',
            'emp_photo' => get_user_meta($user->ID, 'emp_photo', true) ?: ''
        );
        
        wp_smart_badge_log('Processed user data', $user_data);
        
        // Determine badge type if not provided
        if (empty($badge_type)) {
            $badge_type = !empty($user_data['emp_status']) && strtolower($user_data['emp_status']) === 'retired' 
                ? 'RetiredOfficer' 
                : 'ActiveEmployee';
        }
        
        wp_smart_badge_log('Using badge type: ' . $badge_type);
        
        // Load the appropriate template class
        $template_class = 'WpSmartBadge\\Templates\\' . $badge_type . 'Template';
        wp_smart_badge_log('Looking for template class: ' . $template_class);
        
        // Check if file exists
        $template_file = WP_SMART_BADGE_PATH . 'includes/templates/' . 'class-' . strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $badge_type)) . '-template.php';
        wp_smart_badge_log('Template file path: ' . $template_file);
        
        if (!file_exists($template_file)) {
            wp_smart_badge_log('Template file not found at: ' . $template_file);
            throw new Exception("Template file not found: $template_file");
        }
        
        if (!class_exists($template_class)) {
            wp_smart_badge_log('Template class not found: ' . $template_class);
            wp_smart_badge_log('Available classes: ' . print_r(get_declared_classes(), true));
            throw new Exception("Template class not found: $template_class. Please ensure all template files are properly loaded.");
        }
        
        // Create template instance with user data
        $template = new $template_class($user_data);
        wp_smart_badge_log('Template instance created successfully');
        
        // Generate a unique preview key
        $preview_key = wp_generate_password(32, false);
        
        // Store the template and user data in a transient
        $preview_data = array(
            'template' => $template_class,
            'user_data' => $user_data,
            'preview_key' => $preview_key  // Store the preview key with the data
        );
        
        // Store the preview data in a transient (expires in 1 hour)
        set_transient('badge_preview_' . $user_id, $preview_data, HOUR_IN_SECONDS);
        
        // Generate preview URL
        $preview_url = add_query_arg(array(
            'preview_key' => $preview_key,
            'user_id' => $user_id
        ), home_url('badge-preview'));
        
        wp_smart_badge_log('Badge generation completed successfully');
        
        wp_send_json_success(array(
            'message' => 'Badge ready for preview',
            'preview_url' => $preview_url
        ));
    } catch (Exception $e) {
        wp_smart_badge_log('Error in badge generation: ' . $e->getMessage());
        wp_send_json_error('Error generating badge: ' . $e->getMessage());
    }
}

// AJAX endpoint for bulk badge generation
add_action('wp_ajax_generate_bulk_badges', 'wp_smart_badge_generate_bulk_badges');
function wp_smart_badge_generate_bulk_badges() {
    check_ajax_referer('wp_smart_badge_nonce', 'nonce');
    
    $user_ids = isset($_POST['user_ids']) ? $_POST['user_ids'] : '';
    if (empty($user_ids)) {
        wp_send_json_error('No users selected');
    }
    
    try {
        // Generate a unique preview key
        $preview_key = wp_generate_password(32, false);
        
        // Store the preview key in a transient (expires in 1 hour)
        set_transient('badge_preview_' . $user_ids, $preview_key, HOUR_IN_SECONDS);
        
        // Generate preview URL
        $preview_url = add_query_arg(array(
            'preview_key' => $preview_key,
            'user_ids' => $user_ids
        ), plugin_dir_url(__FILE__) . 'templates/preview.php');
        
        wp_send_json_success(array(
            'message' => 'Badges ready for preview',
            'preview_url' => $preview_url
        ));
    } catch (Exception $e) {
        wp_send_json_error($e->getMessage());
    }
}

// AJAX endpoint for downloading badges as PDF
add_action('wp_ajax_download_badges', 'wp_smart_badge_download_badges');
function wp_smart_badge_download_badges() {
    check_ajax_referer('wp_smart_badge_download', 'nonce');
    
    $user_ids = isset($_POST['user_ids']) ? array_map('intval', explode(',', $_POST['user_ids'])) : array();
    if (empty($user_ids)) {
        wp_send_json_error('No users selected');
    }
    
    try {
        require_once WP_SMART_BADGE_PATH . 'includes/class-badge-generator.php';
        
        // Create temporary directory for PDFs
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/badges/temp';
        if (!file_exists($temp_dir)) {
            wp_mkdir_p($temp_dir);
        }
        
        $pdf_files = array();
        foreach ($user_ids as $user_id) {
            $generator = new Badge_Generator($user_id);
            $result = $generator->generate();
            if ($result['success']) {
                $pdf_files[] = $result['file_path'];
            }
        }
        
        if (empty($pdf_files)) {
            throw new Exception('No badges generated');
        }
        
        // If only one PDF, send it directly
        if (count($pdf_files) === 1) {
            $file_path = $pdf_files[0];
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="badge.pdf"');
            readfile($file_path);
            exit;
        }
        
        // For multiple PDFs, create a ZIP file
        $zip_file = $temp_dir . '/badges_' . time() . '.zip';
        $zip = new ZipArchive();
        if ($zip->open($zip_file, ZipArchive::CREATE) !== true) {
            throw new Exception('Could not create ZIP file');
        }
        
        foreach ($pdf_files as $pdf_file) {
            $zip->addFile($pdf_file, basename($pdf_file));
        }
        
        $zip->close();
        
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="badges.zip"');
        readfile($zip_file);
        
        // Cleanup
        unlink($zip_file);
        foreach ($pdf_files as $pdf_file) {
            unlink($pdf_file);
        }
        exit;
    } catch (Exception $e) {
        wp_send_json_error($e->getMessage());
    }
}

// Add preview endpoint
add_action('init', 'wp_smart_badge_add_preview_endpoint');
function wp_smart_badge_add_preview_endpoint() {
    add_rewrite_endpoint('badge-preview', EP_ROOT);
}

// Handle preview endpoint
add_action('template_redirect', 'wp_smart_badge_handle_preview');
function wp_smart_badge_handle_preview() {
    global $wp_query;
    
    if (!isset($wp_query->query_vars['badge-preview'])) {
        return;
    }
    
    wp_smart_badge_log('Preview request received');
    
    // Check preview key and user ID
    $preview_key = isset($_GET['preview_key']) ? sanitize_text_field($_GET['preview_key']) : '';
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    
    wp_smart_badge_log('Preview params:', array('key' => $preview_key, 'user_id' => $user_id));
    
    if (empty($preview_key) || empty($user_id)) {
        wp_smart_badge_log('Invalid preview request - missing parameters');
        wp_die('Invalid preview request - missing parameters');
    }
    
    try {
        // Get the preview data from transient
        $preview_data = get_transient('badge_preview_' . $user_id);
        wp_smart_badge_log('Preview data:', $preview_data);
        
        if (!$preview_data || !isset($preview_data['preview_key'])) {
            wp_smart_badge_log('Preview data not found');
            wp_die('Preview data not found or expired');
        }
        
        // Validate preview key
        if ($preview_key !== $preview_data['preview_key']) {
            wp_smart_badge_log('Invalid preview key');
            wp_die('Invalid preview key');
        }
        
        // Get user data
        $user = get_userdata($user_id);
        if (!$user) {
            wp_smart_badge_log('User not found: ' . $user_id);
            wp_die('User not found');
        }
        
        $user_data = array(
            'ID' => $user->ID,
            'emp_id' => get_user_meta($user->ID, 'emp_id', true) ?: $user->user_login,
            'emp_full_name' => get_user_meta($user->ID, 'emp_full_name', true) ?: $user->display_name,
            'emp_designation' => get_user_meta($user->ID, 'emp_designation', true) ?: $user->roles[0],
            'emp_department' => get_user_meta($user->ID, 'emp_department', true) ?: '',
            'emp_phone' => get_user_meta($user->ID, 'emp_phone', true) ?: '',
            'emp_blood_group' => get_user_meta($user->ID, 'emp_blood_group', true) ?: '',
            'emp_cfms_id' => get_user_meta($user->ID, 'emp_cfms_id', true) ?: '',
            'emp_hrms_id' => get_user_meta($user->ID, 'emp_hrms_id', true) ?: '',
            'emp_status' => get_user_meta($user->ID, 'emp_status', true) ?: 'active',
            'user_email' => $user->user_email,
            'display_name' => $user->display_name,
            'user_registered' => $user->user_registered,
            'emp_ehs_card' => get_user_meta($user->ID, 'emp_ehs_card', true) ?: '',
            'emp_emergency_contact' => get_user_meta($user->ID, 'emp_emergency_contact', true) ?: '',
            'emp_barcode' => get_user_meta($user->ID, 'emp_barcode', true) ?: '',
            'emp_depot_location' => get_user_meta($user->ID, 'emp_depot_location', true) ?: '',
            'emp_last_working' => get_user_meta($user->ID, 'emp_last_working', true) ?: '',
            'emp_residential_address' => get_user_meta($user->ID, 'emp_residential_address', true) ?: '',
            'emp_photo' => get_user_meta($user->ID, 'emp_photo', true) ?: ''
        );
        
        wp_smart_badge_log('User data:', $user_data);
        
        // Create template instance
        $template_class = $preview_data['template'];
        $template = new $template_class($user_data);
        
        wp_smart_badge_log('Template created:', $template_class);
        
        // Generate badge HTML
        $front_html = $template->generate_front();
        $back_html = $template->generate_back();
        
        wp_smart_badge_log('Badge HTML generated');
        
        // Output preview
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Badge Preview</title>
            <style>
                body {
                    margin: 0;
                    padding: 20px;
                    background: #f0f0f1;
                }
                .badge-sides {
                    display: flex;
                    justify-content: center;
                    gap: 40px;
                    flex-wrap: wrap;
                    padding: 20px;
                }
                .badge-side {
                    text-align: center;
                    perspective: 1000px;
                }
                .badge-front, .badge-back {
                    border-radius: 8px;
                    box-shadow: 0 4px 8px rgba(0,0,0,0.1), 0 6px 20px rgba(0,0,0,0.05);
                    background: white;
                    transition: transform 0.3s;
                    transform-style: preserve-3d;
                }
                .badge-front:hover, .badge-back:hover {
                    transform: translateY(-5px);
                }
                .badge-content {
                    border-radius: 8px;
                    overflow: hidden;
                }
                .badge-label {
                    margin-bottom: 15px;
                    font-weight: 500;
                    color: #666;
                }
                .preview-actions {
                    margin-top: 30px;
                    text-align: center;
                }
                button {
                    padding: 10px 20px;
                    margin: 0 5px;
                    cursor: pointer;
                    border: none;
                    border-radius: 4px;
                    background: #2271b1;
                    color: white;
                    font-weight: 500;
                    transition: background 0.3s;
                }
                button:hover {
                    background: #135e96;
                }
                @media print {
                    body {
                        margin: 0;
                        padding: 0;
                        background: none;
                    }
                    .badge-sides {
                        gap: 0;
                        padding: 0;
                        justify-content: space-between;
                    }
                    .badge-side {
                        margin: 0;
                        padding: 0;
                    }
                    .badge-label, .preview-actions {
                        display: none;
                    }
                    .badge-front, .badge-back {
                        box-shadow: none;
                        border-radius: 0;
                    }
                    .badge-content {
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                        color-adjust: exact !important;
                        forced-color-adjust: exact !important;
                    }
                    @page {
                        size: A4 landscape;
                        margin: 0;
                    }
                    * {
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                        color-adjust: exact !important;
                    }
                }
            </style>
        </head>
        <body>
            <div class="badge-sides">
                <div class="badge-side">
                    <div class="badge-label">Front</div>
                    <div class="badge-front">
                        <?php echo $front_html; ?>
                    </div>
                </div>
                <div class="badge-side">
                    <div class="badge-label">Back</div>
                    <div class="badge-back">
                        <?php echo $back_html; ?>
                    </div>
                </div>
            </div>
            <div class="preview-actions">
                <button onclick="window.print()">Print Badge</button>
                <button onclick="downloadBadge(<?php echo esc_js($user_id); ?>, '<?php echo esc_js($template_class); ?>')">Download Badge</button>
                <button onclick="window.close()">Close Preview</button>
            </div>

            <script>
            function downloadBadge(userId, templateType) {
                const data = new FormData();
                data.append('action', 'generate_badge');
                data.append('nonce', '<?php echo wp_create_nonce("wp_smart_badge_nonce"); ?>');
                data.append('user_id', userId);
                data.append('template_type', templateType);
                
                const downloadBtn = document.querySelector('button:nth-child(2)');
                if (downloadBtn) {
                    downloadBtn.disabled = true;
                    downloadBtn.textContent = 'Generating...';
                }
                
                fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                    method: 'POST',
                    body: data,
                    credentials: 'same-origin'
                })
                .then(response => response.blob())
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    a.download = `badge_${userId}.pdf`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                    
                    if (downloadBtn) {
                        downloadBtn.disabled = false;
                        downloadBtn.textContent = 'Download Badge';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error downloading badge. Please try again.');
                    
                    if (downloadBtn) {
                        downloadBtn.disabled = false;
                        downloadBtn.textContent = 'Download Badge';
                    }
                });
            }
            </script>
        </body>
        </html>
        <?php
        exit;
        
    } catch (Exception $e) {
        wp_smart_badge_log('Preview error: ' . $e->getMessage());
        wp_die('Error generating preview: ' . $e->getMessage());
    }
}

// AJAX endpoint for preview badge
add_action('wp_ajax_preview_badge', 'wp_smart_badge_preview_handler');

function wp_smart_badge_preview_handler() {
    // Verify nonce
    if (!isset($_REQUEST['preview_key']) || !wp_verify_nonce($_REQUEST['preview_key'], 'badge_preview')) {
        wp_die('Invalid preview key.');
    }

    // Get parameters
    $user_ids = isset($_REQUEST['user_ids']) ? explode(',', $_REQUEST['user_ids']) : [];
    $template_type = isset($_REQUEST['template_type']) ? sanitize_text_field($_REQUEST['template_type']) : 'ActiveEmployee';
    $is_bulk = isset($_REQUEST['bulk']) && $_REQUEST['bulk'] === '1';
    $debug = isset($_REQUEST['debug']) && $_REQUEST['debug'] === '1';

    // Verify user permissions
    if (!current_user_can('manage_options')) {
        wp_die('Sorry, you are not allowed to access this page.');
    }

    // Set headers for HTML output
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Badge Preview</title>
        <style>
            body {
                margin: 0;
                padding: 20px;
                background: #f0f0f1;
            }
            .badge-sides {
                display: flex;
                justify-content: center;
                gap: 40px;
                flex-wrap: wrap;
                padding: 20px;
            }
            .badge-side {
                text-align: center;
                perspective: 1000px;
            }
            .badge-front, .badge-back {
                border-radius: 8px;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1), 0 6px 20px rgba(0,0,0,0.05);
                background: white;
                transition: transform 0.3s;
                transform-style: preserve-3d;
            }
            .badge-front:hover, .badge-back:hover {
                transform: translateY(-5px);
            }
            .badge-content {
                border-radius: 8px;
                overflow: hidden;
            }
            .badge-label {
                margin-bottom: 15px;
                font-weight: 500;
                color: #666;
            }
            .preview-actions {
                margin-top: 30px;
                text-align: center;
            }
            button {
                padding: 10px 20px;
                margin: 0 5px;
                cursor: pointer;
                border: none;
                border-radius: 4px;
                background: #2271b1;
                color: white;
                font-weight: 500;
                transition: background 0.3s;
            }
            button:hover {
                background: #135e96;
            }
            @media print {
                body {
                    margin: 0;
                    padding: 0;
                    background: none;
                }
                .badge-sides {
                    gap: 0;
                    padding: 0;
                    justify-content: space-between;
                }
                .badge-side {
                    margin: 0;
                    padding: 0;
                }
                .badge-label, .preview-actions {
                    display: none;
                }
                .badge-front, .badge-back {
                    box-shadow: none;
                    border-radius: 0;
                }
                .badge-content {
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                    color-adjust: exact !important;
                    forced-color-adjust: exact !important;
                }
                @page {
                    size: A4 landscape;
                    margin: 0;
                }
                * {
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                    color-adjust: exact !important;
                }
            }
        </style>
    </head>
    <body>
        <div class="badge-sides">
            <?php
            foreach ($user_ids as $user_id) {
                $user = get_userdata($user_id);
                if ($user) {
                    // Get all user meta at once for debugging
                    $all_meta = get_user_meta($user_id);
                    wp_smart_badge_log('All meta data for user ' . $user_id, $all_meta);
                    
                    // Helper function to get meta value
                    $get_meta_value = function($key) use ($all_meta) {
                        return isset($all_meta[$key]) && !empty($all_meta[$key][0]) ? $all_meta[$key][0] : '';
                    };

                    // Prepare user data with consistent meta keys
                    $user_data = array(
                        'ID' => $user_id,
                        'user_email' => $user->user_email,
                        'display_name' => $user->display_name,
                        'emp_id' => $get_meta_value('emp_id'),
                        'emp_full_name' => $get_meta_value('emp_full_name'),
                        'emp_cfms_id' => $get_meta_value('emp_cfms_id'),
                        'emp_hrms_id' => $get_meta_value('emp_hrms_id'),
                        'emp_designation' => $get_meta_value('emp_designation'),
                        'emp_department' => $get_meta_value('emp_department'),
                        'emp_ehs_card' => $get_meta_value('emp_ehs_card'),
                        'emp_phone' => $get_meta_value('emp_phone'),
                        'emp_blood_group' => $get_meta_value('emp_blood_group'),
                        'emp_emergency_contact' => $get_meta_value('emp_emergency_contact'),
                        'emp_status' => $get_meta_value('emp_status'),
                        'emp_barcode' => $get_meta_value('emp_barcode'),
                        'emp_depot_location' => $get_meta_value('emp_depot_location'),
                        'emp_last_working' => $get_meta_value('emp_last_working'),
                        'emp_photo' => $get_meta_value('emp_photo')
                    );

                    // Log the formatted user data
                    wp_smart_badge_log('Formatted user data for preview', $user_data);
                    
                    // Generate badge preview
                    $template_class = "WpSmartBadge\\Templates\\{$template_type}Template";
                    if (class_exists($template_class)) {
                        try {
                            $template = new $template_class($user_data);
                            ?>
                            <div class="badge-side">
                                <div class="badge-label">Front</div>
                                <div class="badge-front">
                                    <?php echo $template->generate_front(); ?>
                                </div>
                            </div>
                            <div class="badge-side">
                                <div class="badge-label">Back</div>
                                <div class="badge-back">
                                    <?php echo $template->generate_back(); ?>
                                </div>
                            </div>
                            <?php
                        } catch (Exception $e) {
                            wp_smart_badge_log('Template generation error', array(
                                'error' => $e->getMessage(),
                                'template_class' => $template_class,
                                'user_data' => $user_data
                            ));
                            echo '<div class="error">Error generating preview: ' . esc_html($e->getMessage()) . '</div>';
                        }
                    } else {
                        wp_smart_badge_log('Template class not found', array(
                            'template_class' => $template_class
                        ));
                        echo '<div class="error">Error: Template class not found - ' . esc_html($template_class) . '</div>';
                    }
                }
            }
            ?>
        </div>
        <div class="preview-actions">
            <button onclick="window.print()">Print Badge</button>
            <button onclick="downloadBadge(<?php echo esc_js($user_ids[0]); ?>, '<?php echo esc_js($template_type); ?>')">Download Badge</button>
            <button onclick="window.close()">Close Preview</button>
        </div>

        <script>
        function downloadBadge(userId, templateType) {
            const data = new FormData();
            data.append('action', 'generate_badge');
            data.append('nonce', '<?php echo wp_create_nonce("wp_smart_badge_nonce"); ?>');
            data.append('user_id', userId);
            data.append('template_type', templateType);
            
            const downloadBtn = document.querySelector('button:nth-child(2)');
            if (downloadBtn) {
                downloadBtn.disabled = true;
                downloadBtn.textContent = 'Generating...';
            }
            
            fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                method: 'POST',
                body: data,
                credentials: 'same-origin'
            })
            .then(response => response.blob())
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                a.download = `badge_${userId}.pdf`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
                
                if (downloadBtn) {
                    downloadBtn.disabled = false;
                    downloadBtn.textContent = 'Download Badge';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error downloading badge. Please try again.');
                
                if (downloadBtn) {
                    downloadBtn.disabled = false;
                    downloadBtn.textContent = 'Download Badge';
                }
            });
        }
        </script>
    </body>
    </html>
    <?php
    exit;
}

// Add Badge Generation column to users table
add_filter('manage_users_columns', 'add_badge_generation_column');
function add_badge_generation_column($columns) {
    $columns['badge_generation'] = 'Badge Generation';
    return $columns;
}

// Add content to the Badge Generation column
add_filter('manage_users_custom_column', 'add_badge_generation_column_content', 10, 3);
function add_badge_generation_column_content($value, $column_name, $user_id) {
    if ($column_name !== 'badge_generation') {
        return $value;
    }

    // Add nonce for security
    $nonce = wp_create_nonce('generate_badge_nonce');
    
    // Get user status (you might need to adjust this based on your user meta)
    $user_status = get_user_meta($user_id, 'employment_status', true);
    $is_retired = $user_status === 'retired';

    // Create template selection dropdown
    $select = '<select class="badge-template-select" data-user-id="' . esc_attr($user_id) . '" data-nonce="' . esc_attr($nonce) . '">';
    $select .= '<option value="ActiveEmployee"' . (!$is_retired ? ' selected' : '') . '>Active Employee</option>';
    $select .= '<option value="RetiredOfficer"' . ($is_retired ? ' selected' : '') . '>Retired Officer</option>';
    $select .= '<option value="RetiredMedical">Retired Medical</option>';
    $select .= '<option value="RetiredTravel">Retired Travel</option>';
    $select .= '</select>';

    // Create action buttons
    $buttons = '<button class="button button-small generate-badge" data-user-id="' . esc_attr($user_id) . '">Generate</button>';
    $buttons .= '<button class="button button-small download-badge" data-user-id="' . esc_attr($user_id) . '">Download Badge</button>';

    return '<div class="badge-actions" style="display: flex; gap: 8px;">' . $select . $buttons . '</div>';
}

// Add necessary JavaScript to footer
add_action('admin_footer', 'add_badge_generation_scripts');
function add_badge_generation_scripts() {
    $screen = get_current_screen();
    if ($screen->base !== 'users') {
        return;
    }
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Handle Generate Badge click
        $('.generate-badge').on('click', function() {
            const userId = $(this).data('user-id');
            const template = $(this).closest('.badge-actions').find('.badge-template-select').val();
            const nonce = $(this).closest('.badge-actions').find('.badge-template-select').data('nonce');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'generate_badge',
                    user_id: userId,
                    template_type: template,
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('Badge generated successfully!');
                    } else {
                        alert('Error generating badge: ' + response.data);
                    }
                },
                error: function() {
                    alert('Error generating badge. Please try again.');
                }
            });
        });

        // Handle Download Badge click
        $('.download-badge').on('click', function() {
            const userId = $(this).data('user-id');
            const template = $(this).closest('.badge-actions').find('.badge-template-select').val();
            const nonce = $(this).closest('.badge-actions').find('.badge-template-select').data('nonce');
            
            window.location.href = ajaxurl + '?action=download_badge&user_id=' + userId + 
                                 '&template_type=' + template + '&nonce=' + nonce;
        });
    });
    </script>
    <?php
}

// Add some basic styles
add_action('admin_head', 'add_badge_generation_styles');
function add_badge_generation_styles() {
    $screen = get_current_screen();
    if ($screen->base !== 'users') {
        return;
    }
    ?>
    <style>
    .badge-actions {
        white-space: nowrap;
    }
    .badge-template-select {
        margin-right: 8px;
    }
    .generate-badge, .download-badge {
        margin: 0 4px !important;
    }
    </style>
    <?php
}

// AJAX endpoint for CSV import
add_action('wp_ajax_import_users_csv', 'handle_import_users_csv');
function handle_import_users_csv() {
    // Verify nonce for security
    if (!check_ajax_referer('wp_smart_badge_nonce', 'nonce', false)) {
        wp_send_json_error('Security check failed. Please refresh the page and try again.', 403);
        return;
    }

    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions. You do not have permission to import data.', 403);
        return;
    }

    // Log the import attempt for debugging
    wp_smart_badge_log('CSV import initiated', array('user' => wp_get_current_user()->user_login));

    // Validate input data
    if (!isset($_POST['users_data'])) {
        wp_send_json_error('No data provided for import.', 400);
        return;
    }

    // Decode and validate JSON data
    $users_data = json_decode(stripslashes($_POST['users_data']), true);
    
    // Log the received data for debugging
    wp_smart_badge_log('CSV import data received', array(
        'data_count' => is_array($users_data) ? count($users_data) : 'not_array',
        'data_sample' => is_array($users_data) && !empty($users_data) ? json_encode(array_slice($users_data, 0, 2)) : 'empty'
    ));
    
    if (!is_array($users_data)) {
        wp_send_json_error('Invalid data format. The data is not in the expected format.', 400);
        return;
    }
    
    if (empty($users_data)) {
        wp_send_json_error('No data records found in the CSV file. Please make sure your CSV file contains valid data rows.', 400);
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'users';
    
    // Check if table exists
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
    
    if (!$table_exists) {
        wp_smart_badge_log('Table does not exist', array('table_name' => $table_name));
        wp_send_json_error("Error: The database table '$table_name' does not exist. Please activate/deactivate the plugin to create the table.", 500);
        return;
    }
    
    // Log the table name for debugging
    wp_smart_badge_log('Using table', array('table_name' => $table_name));
    
    $success_count = 0;
    $update_count = 0;
    $insert_count = 0;
    $errors = array();

    // Begin transaction for better data integrity
    $wpdb->query('START TRANSACTION');
    
    // Initialize arrays to track processing details
    $processed_records = array();
    $skipped_records = array();
    $error_details = array();

    try {
        foreach ($users_data as $index => $user) {
            // Validate required fields - we need at least a name for WordPress users
            if (empty($user['emp_full_name'])) {
                $errors[] = "Row " . ($index + 1) . ": Missing required field (Full Name)";
                $skipped_records[] = array(
                    'row' => $index + 1,
                    'data' => $user,
                    'reason' => 'Missing required field (Full Name)'
                );
                continue;
            }
            
            // Generate email if not provided - required for WordPress users
            if (empty($user['emp_email'])) {
                // Create email from name: convert to lowercase, replace spaces with dots, add domain
                $name_for_email = strtolower(str_replace(' ', '.', trim($user['emp_full_name'])));
                $user['emp_email'] = $name_for_email . '@example.com';
            }
            
            // Map CSV fields to expected structure if they're in a different format
            // This handles cases where the CSV might have "Full Name" instead of "emp_full_name"
            if (isset($user['Full Name']) && empty($user['emp_full_name'])) {
                $user['emp_full_name'] = $user['Full Name'];
            }
            if (isset($user['Employee ID']) && empty($user['emp_id'])) {
                $user['emp_id'] = $user['Employee ID'];
            }
            if (isset($user['CFMS ID']) && empty($user['emp_cfms_id'])) {
                $user['emp_cfms_id'] = $user['CFMS ID'];
            }
            if (isset($user['HRMS ID']) && empty($user['emp_hrms_id'])) {
                $user['emp_hrms_id'] = $user['HRMS ID'];
            }
            if (isset($user['Designation']) && empty($user['emp_designation'])) {
                $user['emp_designation'] = $user['Designation'];
            }
            if (isset($user['Department']) && empty($user['emp_department'])) {
                $user['emp_department'] = $user['Department'];
            }
            if (isset($user['EHS Card']) && empty($user['emp_ehs_card'])) {
                $user['emp_ehs_card'] = $user['EHS Card'];
            }
            if (isset($user['Phone']) && empty($user['emp_phone'])) {
                $user['emp_phone'] = $user['Phone'];
            }
            if (isset($user['Blood Group']) && empty($user['emp_blood_group'])) {
                $user['emp_blood_group'] = $user['Blood Group'];
            }
            if (isset($user['Emergency Contact']) && empty($user['emp_emergency_contact'])) {
                $user['emp_emergency_contact'] = $user['Emergency Contact'];
            }
            if (isset($user['Status']) && empty($user['emp_status'])) {
                $user['emp_status'] = $user['Status'];
            }
            if (isset($user['QR/Barcode']) && empty($user['emp_barcode'])) {
                $user['emp_barcode'] = $user['QR/Barcode'];
            }
            if (isset($user['Depot Location']) && empty($user['emp_depot_location'])) {
                $user['emp_depot_location'] = $user['Depot Location'];
            }
            if (isset($user['Last Working Place']) && empty($user['emp_last_working'])) {
                $user['emp_last_working'] = $user['Last Working Place'];
            }
            if (isset($user['Residential Address']) && empty($user['emp_residential_address'])) {
                $user['emp_residential_address'] = $user['Residential Address'];
            }
            
            // Generate username if not provided - required for WordPress users
            if (empty($user['username'])) {
                // Create username from email (without domain) or from name
                $email_parts = explode('@', $user['emp_email']);
                $username = $email_parts[0];
                
                // Ensure username is unique
                $username_exists = username_exists($username);
                if ($username_exists) {
                    $i = 1;
                    $base_username = $username;
                    while (username_exists($username)) {
                        $username = $base_username . $i;
                        $i++;
                    }
                }
                
                $user['username'] = $username;
            }

            // Split full name into first and last name for WordPress users
            $name_parts = explode(' ', $user['emp_full_name']);
            $first_name = $name_parts[0];
            $last_name = '';
            if (count($name_parts) > 1) {
                $last_name = implode(' ', array_slice($name_parts, 1));
            }
            
            // Generate a random password
            $random_password = wp_generate_password(12, true, false);
            
            // Prepare WordPress user data
            $wp_user_data = array(
                'user_login' => $user['username'],
                'user_email' => $user['emp_email'],
                'first_name' => $first_name,
                'last_name' => $last_name,
                'display_name' => $user['emp_full_name'],
                'user_pass' => $random_password,
                'role' => 'subscriber' // Default role
            );
            
            // Prepare meta data for user meta table - keep the emp_ prefix for all fields
            $user_meta = array(
                'emp_id' => isset($user['emp_id']) ? sanitize_text_field($user['emp_id']) : '',
                'emp_cfms_id' => isset($user['emp_cfms_id']) ? sanitize_text_field($user['emp_cfms_id']) : '',
                'emp_hrms_id' => isset($user['emp_hrms_id']) ? sanitize_text_field($user['emp_hrms_id']) : '',
                'emp_designation' => isset($user['emp_designation']) ? sanitize_text_field($user['emp_designation']) : '',
                'emp_department' => isset($user['emp_department']) ? sanitize_text_field($user['emp_department']) : '',
                'emp_ehs_card' => isset($user['emp_ehs_card']) ? sanitize_text_field($user['emp_ehs_card']) : '',
                'emp_phone' => isset($user['emp_phone']) ? sanitize_text_field($user['emp_phone']) : '',
                'emp_blood_group' => isset($user['emp_blood_group']) ? sanitize_text_field($user['emp_blood_group']) : '',
                'emp_emergency_contact' => isset($user['emp_emergency_contact']) ? sanitize_text_field($user['emp_emergency_contact']) : '',
                'emp_status' => isset($user['emp_status']) && !empty($user['emp_status']) ? sanitize_text_field($user['emp_status']) : 'active',
                'emp_barcode' => isset($user['emp_barcode']) ? sanitize_text_field($user['emp_barcode']) : '',
                'emp_depot_location' => isset($user['emp_depot_location']) ? sanitize_text_field($user['emp_depot_location']) : '',
                'emp_last_working' => isset($user['emp_last_working']) ? sanitize_text_field($user['emp_last_working']) : '',
                'emp_residential_address' => isset($user['emp_residential_address']) ? sanitize_text_field($user['emp_residential_address']) : '',
                'emp_full_name' => sanitize_text_field($user['emp_full_name'])
            );
            
            // Also store the original data as a serialized array for backup/reference
            $user_meta['_wp_smart_badge_original_data'] = maybe_serialize($original_data);
            
            // Store the original data for logging
            $original_data = array(
                'emp_id' => isset($user['emp_id']) ? sanitize_text_field($user['emp_id']) : '',
                'emp_full_name' => sanitize_text_field($user['emp_full_name']),
                'emp_email' => $user['emp_email']
            );
            
            // Log the data being processed
            wp_smart_badge_log('Processing record', array(
                'row' => $index + 1,
                'emp_id' => $original_data['emp_id'],
                'emp_full_name' => $original_data['emp_full_name'],
                'user_login' => $wp_user_data['user_login'],
                'user_email' => $wp_user_data['user_email']
            ));

            // Check if user already exists by email
            $existing_user_id = email_exists($wp_user_data['user_email']);
            
            // Log the existence check for debugging
            wp_smart_badge_log('User existence check', array(
                'email' => $wp_user_data['user_email'],
                'existing_user_id' => $existing_user_id
            ));

            if ($existing_user_id) {
                // Update existing WordPress user
                $wp_user_data['ID'] = $existing_user_id; // Set user ID for update
                $result = wp_update_user($wp_user_data);
                
                // Log the update operation
                wp_smart_badge_log('Update WordPress user', array(
                    'user_id' => $existing_user_id,
                    'user_login' => $wp_user_data['user_login'],
                    'user_email' => $wp_user_data['user_email']
                ));
                
                if (!is_wp_error($result)) {
                    // Update user meta data
                    foreach ($user_meta as $meta_key => $meta_value) {
                        update_user_meta($existing_user_id, $meta_key, $meta_value);
                    }
                    
                    // Set default photo if not provided
                    if (empty(get_user_meta($existing_user_id, 'emp_photo', true))) {
                        update_user_meta($existing_user_id, 'emp_photo', plugins_url('/assets/images/default-avatar.jpg', __FILE__));
                    }
                    
                    $update_count++;
                    $success_count++;
                    $processed_records[] = array(
                        'row' => $index + 1,
                        'emp_id' => $original_data['emp_id'],
                        'user_id' => $existing_user_id,
                        'action' => 'updated'
                    );
                } else {
                    $error_message = "Error updating user: " . $original_data['emp_full_name'] . " - " . $result->get_error_message();
                    $errors[] = $error_message;
                    $error_details[] = array(
                        'row' => $index + 1,
                        'emp_id' => $original_data['emp_id'],
                        'error' => $result->get_error_message()
                    );
                    wp_smart_badge_log('Update error', array(
                        'emp_id' => $original_data['emp_id'],
                        'error' => $result->get_error_message()
                    ));
                }
            } else {
                // Insert new WordPress user
                $result = wp_insert_user($wp_user_data);
                
                // Log the insert operation
                wp_smart_badge_log('Insert WordPress user', array(
                    'user_login' => $wp_user_data['user_login'],
                    'user_email' => $wp_user_data['user_email']
                ));
                
                if (!is_wp_error($result)) {
                    // Add user meta data for the new user
                    foreach ($user_meta as $meta_key => $meta_value) {
                        update_user_meta($result, $meta_key, $meta_value);
                    }
                    
                    // Set default photo if not provided
                    if (empty(get_user_meta($result, 'emp_photo', true))) {
                        update_user_meta($result, 'emp_photo', plugins_url('/assets/images/default-avatar.jpg', __FILE__));
                    }
                    
                    $insert_count++;
                    $success_count++;
                    $processed_records[] = array(
                        'row' => $index + 1,
                        'emp_id' => $original_data['emp_id'],
                        'user_id' => $result,
                        'action' => 'inserted'
                    );
                } else {
                    $error_message = "Error inserting user: " . $original_data['emp_full_name'] . " - " . $result->get_error_message();
                    $errors[] = $error_message;
                    $error_details[] = array(
                        'row' => $index + 1,
                        'emp_id' => $original_data['emp_id'],
                        'error' => $result->get_error_message()
                    );
                    wp_smart_badge_log('Insert error', array(
                        'emp_id' => $original_data['emp_id'],
                        'error' => $result->get_error_message()
                    ));
                }
            }
        }

        // Commit transaction if no critical errors
        if ($success_count > 0) {
            $wpdb->query('COMMIT');
            wp_smart_badge_log('Import transaction committed', array(
                'success_count' => $success_count,
                'update_count' => $update_count,
                'insert_count' => $insert_count
            ));
        } else {
            $wpdb->query('ROLLBACK');
            wp_smart_badge_log('Import transaction rolled back', array(
                'error_count' => count($errors),
                'skipped_count' => count($skipped_records),
                'error_details' => $error_details,
                'skipped_records' => $skipped_records
            ));
            
            // Prepare a more detailed error message
            $detailed_error = 'No records were successfully processed. Import failed.\n';
            
            if (!empty($skipped_records)) {
                $detailed_error .= 'Skipped records: ' . count($skipped_records) . ' (missing required fields)\n';
            }
            
            if (!empty($error_details)) {
                $detailed_error .= 'Database errors: ' . count($error_details) . '\n';
                // Include first few error details
                $error_samples = array_slice($error_details, 0, 3);
                foreach ($error_samples as $error) {
                    $detailed_error .= "Row {$error['row']} (ID: {$error['emp_id']}): {$error['error']}\n";
                }
                if (count($error_details) > 3) {
                    $detailed_error .= '... and ' . (count($error_details) - 3) . ' more errors';
                }
            }
            
            wp_send_json_error($detailed_error, 500);
            return;
        }
    } catch (Exception $e) {
        // Rollback on exception
        $wpdb->query('ROLLBACK');
        wp_smart_badge_log('CSV import exception', array(
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ));
        wp_send_json_error('Error during import: ' . $e->getMessage() . ' (Line: ' . $e->getLine() . ')', 500);
        return;
    }

    // Get updated data
    $updated_data = $wpdb->get_results("SELECT * FROM $table_name ORDER BY ID DESC");

    // Create detailed success message
    $message = "Successfully processed $success_count records";
    if ($update_count > 0 && $insert_count > 0) {
        $message .= " ($insert_count new, $update_count updated)";
    } else if ($update_count > 0) {
        $message .= " (all records updated)";
    } else if ($insert_count > 0) {
        $message .= " (all records newly added)";
    }
    
    if (count($errors) > 0) {
        $message .= ". Errors: " . implode("; ", $errors);
    }

    // Log success
    wp_smart_badge_log('CSV import completed', array(
        'success_count' => $success_count,
        'update_count' => $update_count,
        'insert_count' => $insert_count,
        'error_count' => count($errors)
    ));

    wp_send_json_success(array(
        'message' => $message,
        'data' => $updated_data,
        'stats' => array(
            'success_count' => $success_count,
            'update_count' => $update_count,
            'insert_count' => $insert_count,
            'error_count' => count($errors),
            'skipped_count' => count($skipped_records)
        ),
        'processed_records' => $processed_records,
        'skipped_records' => $skipped_records,
        'errors' => $errors
    ));
}

// Handle CSV import/export
add_action('admin_init', 'handle_csv_actions');
function handle_csv_actions() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Handle CSV Export
    if (isset($_POST['export_users']) && check_admin_referer('export_users_csv', 'export_users_nonce')) {
        // Get all users
        $users = get_users(array(
            'orderby' => 'ID',
            'order' => 'DESC'
        ));
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="users_' . date('Y-m-d') . '.csv"');
        
        // Create output stream
        $output = fopen('php://output', 'w');
        
        // CSV Headers mapping to user meta fields
        $headers = array(
            'User ID' => 'ID',
            'Username' => 'user_login',
            'Email' => 'user_email',
            'Employee ID' => 'emp_id',
            'First Name' => 'first_name',
            'Last Name' => 'last_name',
            'Full Name' => 'emp_full_name',
            'CFMS ID' => 'emp_cfms_id',
            'HRMS ID' => 'emp_hrms_id',
            'Designation' => 'emp_designation',
            'Department' => 'emp_department',
            'EHS Card' => 'emp_ehs_card',
            'Phone' => 'emp_phone',
            'Blood Group' => 'emp_blood_group',
            'Emergency Contact' => 'emp_emergency_contact',
            'Status' => 'emp_status',
            'Photo URL' => 'emp_photo'
        );
        
        // Write display headers
        fputcsv($output, array_keys($headers));
        
        // Add data rows with mapped fields
        foreach ($users as $user) {
            $user_id = $user->ID;
            $row = array();
            
            foreach ($headers as $display => $field) {
                if (in_array($field, array('ID', 'user_login', 'user_email'))) {
                    // Core user data
                    $row[] = $user->$field;
                } else {
                    // User meta data
                    $meta_value = get_user_meta($user_id, $field, true);
                    $row[] = $meta_value;
                }
            }
            
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit();
    }

    // Handle CSV Import
    if (isset($_POST['do_import_users']) && check_admin_referer('import_users_csv', 'import_users_nonce')) {
        if (!isset($_FILES['import_users_file'])) {
            wp_die('No file uploaded');
        }

        $file = $_FILES['import_users_file'];
        
        // Basic validation
        if ($file['error'] !== UPLOAD_ERR_OK) {
            wp_die('Error uploading file');
        }

        // Accept both text/csv and application/vnd.ms-excel (some systems save CSV with this mime type)
        $allowed_types = array('text/csv', 'application/vnd.ms-excel');
        if (!in_array($file['type'], $allowed_types)) {
            wp_die('Please upload a CSV file');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'users';
        $success_count = 0;
        $error_count = 0;
        
        // Header mapping (CSV header => DB field)
        $header_mapping = array(
            'Employee ID' => 'emp_id',
            'Full Name' => 'emp_full_name',
            'CFMS ID' => 'emp_cfms_id',
            'HRMS ID' => 'emp_hrms_id',
            'Designation' => 'emp_designation',
            'Department' => 'emp_department',
            'EHS Card' => 'emp_ehs_card',
            'Phone' => 'emp_phone',
            'Blood Group' => 'emp_blood_group',
            'Emergency Contact' => 'emp_emergency_contact',
            'Status' => 'emp_status'
        );
        
        // Open and read the CSV file
        if (($handle = fopen($file['tmp_name'], 'r')) !== false) {
            // Get CSV headers and remove BOM if present
            $csv_headers = fgetcsv($handle);
            $csv_headers[0] = str_replace("\xEF\xBB\xBF", '', $csv_headers[0]); // Remove BOM if present
            
            // Process each row
            while (($data = fgetcsv($handle)) !== false) {
                $row = array();
                
                // Map CSV data to database fields
                foreach ($csv_headers as $index => $header) {
                    if (isset($header_mapping[$header]) && isset($data[$index])) {
                        $field_name = $header_mapping[$header];
                        $row[$field_name] = sanitize_text_field($data[$index]);
                    }
                }
                
                // Ensure required fields are present
                if (empty($row['emp_id'])) {
                    $error_count++;
                    continue;
                }
                
                // Check if employee exists
                $existing = $wpdb->get_var($wpdb->prepare(
                    "SELECT ID FROM $table_name WHERE emp_id = %s",
                    $row['emp_id']
                ));

                if ($existing) {
                    // Update existing record
                    $result = $wpdb->update(
                        $table_name,
                        $row,
                        array('emp_id' => $row['emp_id'])
                    );
                } else {
                    // Insert new record
                    $result = $wpdb->insert($table_name, $row);
                }
                
                if ($result !== false) {
                    $success_count++;
                } else {
                    $error_count++;
                }
            }
            fclose($handle);
            
            // Set admin notice
            add_action('admin_notices', function() use ($success_count, $error_count) {
                $class = ($error_count === 0) ? 'notice-success' : 'notice-warning';
                $message = sprintf(
                    'Import complete. Successfully processed %d records. %s',
                    $success_count,
                    $error_count > 0 ? sprintf('Failed to process %d records.', $error_count) : ''
                );
                printf('<div class="notice %s is-dismissible"><p>%s</p></div>', esc_attr($class), esc_html($message));
            });
        }
        
        // Redirect back to the page
        wp_redirect(add_query_arg('import', 'complete', wp_get_referer()));
        exit();
    }
}

// Admin pages
function wp_smart_badge_admin_page() {
    require_once WP_SMART_BADGE_PATH . 'templates/admin/generate.php';
}

function wp_smart_badge_templates_page() {
    require_once WP_SMART_BADGE_PATH . 'templates/admin/templates.php';
}

function wp_smart_badge_settings_page() {
    require_once WP_SMART_BADGE_PATH . 'templates/admin/settings.php';
}

function wp_smart_badge_preview_page() {
    require_once WP_SMART_BADGE_PATH . 'templates/admin/preview-badge.php';
}

// Admin Columns Integration
add_action('ac/column_types', 'register_badge_generation_column');
function register_badge_generation_column($columns) {
    if (!class_exists('AC_Column')) {
        return;
    }

    class AC_Column_Badge_Generation extends AC_Column {
        public function __construct() {
            $this->set_type('column-badge_generation');
            $this->set_label('Badge Generation');
        }

        public function get_value($user_id) {
            // Add nonce for security
            $nonce = wp_create_nonce('generate_badge_nonce');
            
            // Get user status
            $user_status = get_user_meta($user_id, 'employment_status', true);
            $is_retired = $user_status === 'retired';

            ob_start();
            ?>
            <div class="badge-actions" style="display: flex; gap: 8px; white-space: nowrap;">
                <select class="badge-template-select" data-user-id="<?php echo esc_attr($user_id); ?>" data-nonce="<?php echo esc_attr($nonce); ?>">
                    <option value="ActiveEmployee"<?php echo !$is_retired ? ' selected' : ''; ?>>Active Employee</option>
                    <option value="RetiredOfficer"<?php echo $is_retired ? ' selected' : ''; ?>>Retired Officer</option>
                    <option value="RetiredMedical">Retired Medical</option>
                    <option value="RetiredTravel">Retired Travel</option>
                </select>
                <button class="button button-small generate-badge" data-user-id="<?php echo esc_attr($user_id); ?>">Generate</button>
                <button class="button button-small download-badge" data-user-id="<?php echo esc_attr($user_id); ?>">Download Badge</button>
            </div>
            <?php
            return ob_get_clean();
        }

        public function get_raw_value($user_id) {
            return 'Badge Generation Controls';
        }
    }

    $columns['column-badge_generation'] = new AC_Column_Badge_Generation();
}

// Add necessary JavaScript and CSS
add_action('ac/table_scripts', 'add_badge_generation_assets');
function add_badge_generation_assets() {
    if (!is_admin() || get_current_screen()->base !== 'users') {
        return;
    }

    // Add styles
    ?>
    <style>
    .badge-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 250px;
    }
    .badge-template-select {
        min-width: 120px;
    }
    .generate-badge, .download-badge {
        margin: 0 4px !important;
    }
    </style>

    <script>
    jQuery(document).ready(function($) {
        // Handle Generate Badge click
        $(document).on('click', '.generate-badge', function() {
            const userId = $(this).data('user-id');
            const template = $(this).closest('.badge-actions').find('.badge-template-select').val();
            const nonce = $(this).closest('.badge-actions').find('.badge-template-select').data('nonce');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'generate_badge',
                    user_id: userId,
                    template_type: template,
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('Badge generated successfully!');
                    } else {
                        alert('Error generating badge: ' + response.data);
                    }
                },
                error: function() {
                    alert('Error generating badge. Please try again.');
                }
            });
        });

        // Handle Download Badge click
        $(document).on('click', '.download-badge', function() {
            const userId = $(this).data('user-id');
            const template = $(this).closest('.badge-actions').find('.badge-template-select').val();
            const nonce = $(this).closest('.badge-actions').find('.badge-template-select').data('nonce');
            
            window.location.href = ajaxurl + '?action=download_badge&user_id=' + userId + 
                                 '&template_type=' + template + '&nonce=' + nonce;
        });
    });
    </script>
    <?php
}

// Enqueue scripts and styles for template customizer
add_action('admin_enqueue_scripts', function($hook) {
    if ('smart-badge_page_smart-badge-templates' !== $hook) {
        return;
    }
    
    // Enqueue jQuery UI
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-draggable');
    wp_enqueue_script('jquery-ui-droppable');
    
    // Enqueue WordPress color picker
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    
    // Enqueue template customizer
    wp_enqueue_script(
        'smart-badge-template-customizer',
        plugins_url('/assets/js/template-customizer.js', __FILE__),
        array('jquery', 'jquery-ui-core', 'jquery-ui-draggable', 'jquery-ui-droppable', 'wp-color-picker'),
        filemtime(plugin_dir_path(__FILE__) . 'assets/js/template-customizer.js'),
        true
    );
    
    // Add localization
    wp_localize_script('smart-badge-template-customizer', 'smartBadgeCustomizer', array(
        'nonce' => wp_create_nonce('smart_badge_customizer'),
        'strings' => array(
            'saveSuccess' => __('Template saved successfully', 'smart-badge'),
            'saveError' => __('Failed to save template', 'smart-badge'),
            'confirmReset' => __('Are you sure you want to reset this template?', 'smart-badge')
        )
    ));
    
    // Add jQuery UI styles
    wp_enqueue_style(
        'jquery-ui-style',
        plugins_url('/assets/css/jquery-ui.min.css', __FILE__),
        array(),
        '1.13.2'
    );
});

// AJAX handler for saving template
add_action('wp_ajax_save_template', function() {
    check_ajax_referer('smart_badge_customizer', 'nonce');
    
    $template = sanitize_text_field($_POST['template']);
    $layout = $_POST['layout'];
    $styles = $_POST['styles'];
    
    wp_smart_badge_log('Saving template: ' . $template, array(
        'layout' => $layout,
        'styles' => $styles
    ));
    
    $template_data = array(
        'layout' => $layout,
        'styles' => $styles
    );
    
    $result = update_option('smart_badge_template_' . $template, $template_data);
    wp_smart_badge_log('Template save result: ' . ($result ? 'success' : 'failed'));
    
    if ($result) {
        wp_send_json_success();
    } else {
        wp_send_json_error('Failed to save template');
    }
});

// AJAX handler for getting template data
add_action('wp_ajax_get_template_data', function() {
    check_ajax_referer('smart_badge_customizer', 'nonce');
    
    $template = sanitize_text_field($_POST['template']);
    $data = get_option('smart_badge_template_' . $template);
    
    wp_send_json_success($data);
});

// Include required files
require_once WP_SMART_BADGE_PATH . 'includes/class-badge-generator.php';
require_once WP_SMART_BADGE_PATH . 'includes/class-data-importer.php';
require_once WP_SMART_BADGE_PATH . 'includes/class-template-customizer.php';

// Initialize components
function wp_smart_badge_init() {
    global $template_customizer;
    
    // Load template files
    // require_once WP_SMART_BADGE_PATH . 'includes/templates/class-badge-template.php';
    // require_once WP_SMART_BADGE_PATH . 'includes/templates/class-class-3-4-template.php';
    // require_once WP_SMART_BADGE_PATH . 'includes/templates/class-vertical-card-template.php';
    
    // Initialize main components
    $badge_generator = new WpSmartBadge\Badge_Generator();
    $data_importer = new WpSmartBadge\Data_Importer();
    $template_customizer = new WpSmartBadge\Template_Customizer(WP_SMART_BADGE_VERSION);
    
    // Register AJAX handlers
    add_action('wp_ajax_generate_badge', [$badge_generator, 'ajax_generate_badge']);
    add_action('wp_ajax_get_employees_data', [$badge_generator, 'get_employees_data']);
}
add_action('plugins_loaded', 'wp_smart_badge_init');

// AJAX endpoint for adding a new user
add_action('wp_ajax_add_new_user', 'wp_smart_badge_add_new_user');
function wp_smart_badge_add_new_user() {
    check_ajax_referer('wp_smart_badge_nonce', 'nonce');
    
    if (!current_user_can('create_users')) {
        wp_send_json_error('Permission denied');
        return;
    }
    
    $user_data = array(
        'user_login'    => sanitize_text_field($_POST['emp_id']),
        'user_pass'     => wp_generate_password(),
        'user_email'    => sanitize_email($_POST['user_email']),
        'display_name'  => sanitize_text_field($_POST['emp_full_name']),
        'role'         => 'subscriber'
    );
    
    $user_id = wp_insert_user($user_data);
    
    if (is_wp_error($user_id)) {
        wp_send_json_error($user_id->get_error_message());
        return;
    }
    
    // Add user meta
    $meta_fields = array(
        'emp_id', 'emp_full_name', 'emp_designation', 'emp_department',
        'emp_phone', 'emp_blood_group', 'emp_cfms_id', 'emp_hrms_id',
        'emp_status', 'emp_emergency_contact', 'emp_ehs_card',
        'emp_barcode', 'emp_depot_location', 'emp_last_working',
        'emp_residential_address', 'employee_info'
    );
    
    foreach ($meta_fields as $field) {
        if (isset($_POST[$field])) {
            update_user_meta($user_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
    
    // Handle base64 image data
    if (isset($_POST['emp_photo_data']) && !empty($_POST['emp_photo_data'])) {
        $upload_dir = wp_upload_dir();
        $upload_path = $upload_dir['path'];
        $upload_url = $upload_dir['url'];
        
        // Decode base64 data
        $image_parts = explode(";base64,", $_POST['emp_photo_data']);
        $image_base64 = base64_decode($image_parts[1]);
        
        // Generate unique filename
        $filename = 'user_' . $user_id . '_' . time() . '.jpg';
        $file_path = $upload_path . '/' . $filename;
        $file_url = $upload_url . '/' . $filename;
        
        // Save the file
        file_put_contents($file_path, $image_base64);
        
        // Create attachment
        $attachment_data = array(
            'post_mime_type' => 'image/jpeg',
            'post_title'     => sanitize_file_name($filename),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );
        
        $attach_id = wp_insert_attachment($attachment_data, $file_path);
        
        if (!is_wp_error($attach_id)) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            
            // Generate metadata and thumbnails
            $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
            wp_update_attachment_metadata($attach_id, $attach_data);
            
            // Update user meta and avatar
            update_user_meta($user_id, 'emp_photo', $file_url);
            update_user_meta($user_id, '_emp_photo_attachment_id', $attach_id);
            
            // Set as user's avatar in WordPress
            update_user_meta($user_id, 'wp_smart_badge_avatar', $file_url);

            // Force refresh of avatar cache
            clean_user_cache($user_id);
            
            // Update user's local avatar if the plugin is active
            if (function_exists('update_local_avatar')) {
                update_local_avatar($user_id, $attach_id);
            }
        }
    }
    
    wp_send_json_success(array(
        'message' => 'User added successfully',
        'user_id' => $user_id
    ));
}

// Hook into user profile update and creation
add_action('user_register', 'wp_smart_badge_update_full_name');
add_action('profile_update', 'wp_smart_badge_update_full_name');

function wp_smart_badge_update_full_name($user_id) {
    // Get first and last name
    $first_name = get_user_meta($user_id, 'first_name', true);
    $last_name = get_user_meta($user_id, 'last_name', true);
    
    // Combine into full name
    $full_name = trim($first_name . ' ' . $last_name);
    
    // Update emp_full_name
    if (!empty($full_name)) {
        update_user_meta($user_id, 'emp_full_name', $full_name);
    }
}

// Add filter for user meta fields
add_filter('user_contactmethods', 'wp_smart_badge_add_user_fields');

function wp_smart_badge_add_user_fields($user_contact) {
    // Add custom fields
    $user_contact['emp_id'] = 'Employee ID';
    $user_contact['emp_designation'] = 'Designation';
    $user_contact['emp_department'] = 'Department';
    $user_contact['emp_phone'] = 'Phone';
    $user_contact['emp_blood_group'] = 'Blood Group';
    $user_contact['emp_emergency_contact'] = 'Emergency Contact';
    $user_contact['emp_status'] = 'Status';
    
    return $user_contact;
}

// AJAX endpoint for updating user photos
add_action('wp_ajax_update_user_photo', 'wp_smart_badge_update_user_photo');
function wp_smart_badge_update_user_photo() {
    check_ajax_referer('wp_smart_badge_nonce', 'nonce');
    
    if (!current_user_can('edit_users')) {
        wp_send_json_error('Permission denied');
        return;
    }
    
    $user_id = intval($_POST['user_id']);
    
    if (!$user_id) {
        wp_send_json_error('Invalid user ID');
        return;
    }
    
    // Handle base64 image data
    if (isset($_POST['photo_data']) && !empty($_POST['photo_data'])) {
        $photo_data = $_POST['photo_data'];
        if (strpos($photo_data, 'data:image') === 0) {
            update_user_meta($user_id, 'emp_photo', $photo_data);
        }
    }

    // Get updated user data
    $updated_user = get_user_data($user_id);
    if (!$updated_user) {
        wp_send_json_error('Failed to get updated user data');
        return;
    }

    wp_send_json_success($updated_user);
}

// AJAX endpoint for updating user
add_action('wp_ajax_update_user', function() {
    check_ajax_referer('wp_smart_badge_nonce', 'nonce');
    
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    if (!$user_id) {
        wp_send_json_error('Invalid user ID');
        return;
    }

    // Update user meta fields
    $meta_fields = array(
        'emp_id',
        'first_name',
        'last_name',
        'emp_full_name',
        'emp_designation',
        'emp_department',
        'emp_phone',
        'emp_blood_group',
        'emp_cfms_id',
        'emp_hrms_id',
        'emp_emergency_contact',
        'emp_ehs_card',
        'emp_barcode',
        'emp_depot_location',
        'emp_last_working',
        'emp_residential_address',
        'emp_status'
    );

    foreach ($meta_fields as $field) {
        if (isset($_POST[$field])) {
            update_user_meta($user_id, $field, sanitize_text_field($_POST[$field]));
        }
    }

    // Handle photo upload
    if (isset($_POST['emp_photo_data']) && !empty($_POST['emp_photo_data'])) {
        $photo_data = $_POST['emp_photo_data'];
        if (strpos($photo_data, 'data:image') === 0) {
            // Get the base64 data
            list(, $base64_data) = explode(';base64,', $photo_data);
            $decoded_data = base64_decode($base64_data);
            
            // Create file in uploads directory
            $upload_dir = wp_upload_dir();
            $filename = 'user_' . $user_id . '_' . time() . '.jpg';
            $file_path = $upload_dir['path'] . '/' . $filename;
            
            file_put_contents($file_path, $decoded_data);
            
            // Create attachment
            $attachment = array(
                'post_mime_type' => 'image/jpeg',
                'post_title' => sanitize_file_name($filename),
                'post_content' => '',
                'post_status' => 'inherit'
            );
            
            $attach_id = wp_insert_attachment($attachment, $file_path);
            
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
            wp_update_attachment_metadata($attach_id, $attach_data);
            
            // Update user meta
            update_user_meta($user_id, '_emp_photo_attachment_id', $attach_id);
            update_user_meta($user_id, 'emp_photo', wp_get_attachment_url($attach_id));
        }
    }

    // Get updated user data
    $updated_user = get_user_data($user_id);
    if (!$updated_user) {
        wp_send_json_error('Failed to get updated user data');
        return;
    }

    wp_send_json_success($updated_user);
});

/**
 * Get template class name based on template type
 */
function wp_smart_badge_get_template_class($template_type) {
    $template_map = array(
        'ActiveEmployee' => 'ActiveEmployeeTemplate',
        'ActiveEmployeeHorizontal' => 'ActiveEmployeeHorizontalTemplate',
        'RetiredOfficer' => 'RetiredOfficerTemplate',
        'RetiredMedical' => 'RetiredMedicalTemplate',
        'RetiredTravel' => 'RetiredTravelTemplate',
        'Class3And4' => 'Class3And4Template',
        'VerticalCard' => 'VerticalCardTemplate'
    );

    $template_class = isset($template_map[$template_type]) ? $template_map[$template_type] : 'ActiveEmployeeTemplate';
    return "WpSmartBadge\\Templates\\{$template_class}";
}

/**
 * Preview badge
 */
function wp_smart_badge_preview_badge() {
    // Verify nonce and permissions
    if (!current_user_can('edit_users')) {
        wp_die('Permission denied');
    }

    $user_ids = isset($_GET['user_ids']) ? sanitize_text_field($_GET['user_ids']) : '';
    $template_type = isset($_GET['template_type']) ? sanitize_text_field($_GET['template_type']) : 'ActiveEmployee';
    $debug = isset($_GET['debug']) ? (bool)$_GET['debug'] : false;

    if (empty($user_ids)) {
        wp_die('No users selected');
    }

    // Get template class
    $template_class = wp_smart_badge_get_template_class($template_type);
    if (!class_exists($template_class)) {
        wp_die("Template class not found - {$template_class}");
    }

    try {
        // Generate badge
        $template = new $template_class();
        $user_ids = explode(',', $user_ids);
        $pdf = $template->generate($user_ids, $debug);

        // Output PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="badge-preview.pdf"');
        echo $pdf;
        exit;
    } catch (Exception $e) {
        wp_die('Error generating badge: ' . $e->getMessage());
    }
}

/**
 * Download badge
 */
function wp_smart_badge_download_badge() {
    check_ajax_referer('wp_smart_badge_nonce', 'nonce');

    if (!current_user_can('edit_users')) {
        wp_send_json_error('Permission denied');
        return;
    }

    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $template_type = isset($_POST['template_type']) ? sanitize_text_field($_POST['template_type']) : 'ActiveEmployee';

    if (!$user_id) {
        wp_send_json_error('Invalid user ID');
        return;
    }

    // Get template class
    $template_class = wp_smart_badge_get_template_class($template_type);
    if (!class_exists($template_class)) {
        wp_send_json_error("Template class not found - {$template_class}");
        return;
    }

    try {
        // Generate badge
        $template = new $template_class();
        $pdf = $template->generate(array($user_id));

        // Output PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="badge-' . $user_id . '.pdf"');
        echo $pdf;
        exit;
    } catch (Exception $e) {
        wp_send_json_error('Error generating badge: ' . $e->getMessage());
    }
}

/**
 * Hook into WordPress avatar system
 */
add_filter('get_avatar_url', 'wp_smart_badge_get_avatar_url', 10, 3);
function wp_smart_badge_get_avatar_url($url, $id_or_email, $args) {
    // Get user ID from email if necessary
    $user_id = 0;
    if (is_numeric($id_or_email)) {
        $user_id = $id_or_email;
    } elseif (is_string($id_or_email)) {
        $user = get_user_by('email', $id_or_email);
        if ($user) {
            $user_id = $user->ID;
        }
    } elseif (is_object($id_or_email)) {
        if (!empty($id_or_email->user_id)) {
            $user_id = $id_or_email->user_id;
        }
    }
    
    if ($user_id) {
        $custom_avatar = get_user_meta($user_id, 'emp_photo', true);
        if ($custom_avatar) {
            return $custom_avatar;
        }
    }
    
    return $url;
}

// Function to get MetaBox fields
function wp_smart_badge_get_meta_box_fields() {
    if (!function_exists('rwmb_get_registry')) {
        return array();
    }

    $registry = rwmb_get_registry('field');
    $meta_boxes = $registry->all();
    $fields = array();

    foreach ($meta_boxes as $meta_box) {
        if (isset($meta_box->meta_box['fields'])) {
            foreach ($meta_box->meta_box['fields'] as $field) {
                $fields[] = array(
                    'id' => $field['id'],
                    'type' => $field['type'],
                    'name' => $field['name'],
                    'required' => !empty($field['required']),
                    'options' => isset($field['options']) ? $field['options'] : array()
                );
            }
        }
    }

    return $fields;
}

// AJAX endpoint to get MetaBox fields
add_action('wp_ajax_get_meta_box_fields', 'wp_smart_badge_ajax_get_meta_box_fields');
function wp_smart_badge_ajax_get_meta_box_fields() {
    check_ajax_referer('wp_smart_badge_nonce', 'nonce');
    wp_send_json_success(wp_smart_badge_get_meta_box_fields());
}

function get_user_data($user_id) {
    $user = get_user_by('id', $user_id);
    if (!$user) {
        return false;
    }

    return array(
        'ID' => $user->ID,
        'user_email' => $user->user_email,
        'first_name' => get_user_meta($user_id, 'first_name', true),
        'last_name' => get_user_meta($user_id, 'last_name', true),
        'emp_id' => get_user_meta($user_id, 'emp_id', true),
        'emp_full_name' => get_user_meta($user_id, 'emp_full_name', true),
        'emp_designation' => get_user_meta($user_id, 'emp_designation', true),
        'emp_department' => get_user_meta($user_id, 'emp_department', true),
        'emp_phone' => get_user_meta($user_id, 'emp_phone', true),
        'emp_blood_group' => get_user_meta($user_id, 'emp_blood_group', true),
        'emp_cfms_id' => get_user_meta($user_id, 'emp_cfms_id', true),
        'emp_hrms_id' => get_user_meta($user_id, 'emp_hrms_id', true),
        'emp_emergency_contact' => get_user_meta($user_id, 'emp_emergency_contact', true),
        'emp_ehs_card' => get_user_meta($user_id, 'emp_ehs_card', true),
        'emp_barcode' => get_user_meta($user_id, 'emp_barcode', true),
        'emp_depot_location' => get_user_meta($user_id, 'emp_depot_location', true),
        'emp_last_working' => get_user_meta($user_id, 'emp_last_working', true),
        'emp_residential_address' => get_user_meta($user_id, 'emp_residential_address', true),
        'emp_status' => get_user_meta($user_id, 'emp_status', true),
        'emp_photo' => get_user_meta($user_id, 'emp_photo', true)
    );
}

// Handle CSV Export
// function wp_smart_badge_export_users() {
//     if (!current_user_can('manage_options')) {
//         return;
//     }

//     if (!isset($_POST['export_users_nonce']) || !wp_verify_nonce($_POST['export_users_nonce'], 'export_users_csv')) {
//         return;
//     }

//     $selected_users = isset($_POST['selected_users']) ? json_decode(stripslashes($_POST['selected_users']), true) : array();
    
//     $meta_fields = array(
//         'emp_designation', 'emp_department', 'emp_phone', 'emp_blood_group', 
//         'emp_cfms_id', 'emp_hrms_id', 'emp_status', 'emp_emergency_contact', 
//         'emp_ehs_card', 'emp_barcode', 'emp_depot_location', 'emp_last_working',
//         'emp_residential_address', 'employee_info'
//     );

//     // Get users based on selection
//     if (!empty($selected_users)) {
//         $users = get_users(array('include' => $selected_users));
//     } else {
//         $users = get_users();
//     }

//     $filename = 'users_' . date('Y-m-d') . '.csv';
    
//     header('Content-Type: text/csv');
//     header('Content-Disposition: attachment; filename="' . $filename . '"');
//     header('Pragma: no-cache');
//     header('Expires: 0');
    
//     $output = fopen('php://output', 'w');
    
//     // Add headers with friendly names
//     $headers = array(
//         'Employee ID',
//         'Full Name',
//         'Email',
//         'Designation',
//         'Department',
//         'Phone',
//         'Blood Group',
//         'CFMS ID',
//         'HRMS ID',
//         'Status',
//         'Emergency Contact',
//         'EHS Card',
//         'QR/Barcode',
//         'Depot Location',
//         'Last Working Place',
//         'Residential Address',
//         'Additional Info'
//     );
//     fputcsv($output, $headers);
    
//     foreach ($users as $user) {
//         $row = array(
//             get_user_meta($user->ID, 'emp_id', true),
//             get_user_meta($user->ID, 'emp_full_name', true),
//             $user->user_email
//         );
        
//         foreach ($meta_fields as $field) {
//             $row[] = get_user_meta($user->ID, $field, true);
//         }
//         fputcsv($output, $row);
//     }
    
//     fclose($output);
//     exit();
// }
// add_action('admin_init', 'wp_smart_badge_export_users');

// // Handle CSV Import
// function wp_smart_badge_import_users() {
//     if (!current_user_can('manage_options')) {
//         return;
//     }

//     if (!isset($_POST['import_users_nonce']) || !wp_verify_nonce($_POST['import_users_nonce'], 'import_users_csv')) {
//         return;
//     }

//     if (!isset($_FILES['import_users_file'])) {
//         wp_die('No file uploaded');
//     }

//     $file = $_FILES['import_users_file'];
    
//     // Basic validation
//     if ($file['error'] !== UPLOAD_ERR_OK) {
//         wp_die('Error uploading file');
//     }

//     // Accept both text/csv and application/vnd.ms-excel (some systems save CSV with this mime type)
//     $allowed_types = array('text/csv', 'application/vnd.ms-excel');
//     if (!in_array($file['type'], $allowed_types)) {
//         wp_die('Please upload a CSV file');
//     }

//     global $wpdb;
//     $table_name = $wpdb->prefix . 'users';
//     $success_count = 0;
//     $error_count = 0;
    
//     // Header mapping (CSV header => DB field)
//     $header_mapping = array(
//         'Employee ID' => 'emp_id',
//         'Full Name' => 'emp_full_name',
//         'Email' => 'user_email',
//         'Designation' => 'emp_designation',
//         'Department' => 'emp_department',
//         'Phone' => 'emp_phone',
//         'Blood Group' => 'emp_blood_group',
//         'CFMS ID' => 'emp_cfms_id',
//         'HRMS ID' => 'emp_hrms_id',
//         'Status' => 'emp_status',
//         'Emergency Contact' => 'emp_emergency_contact',
//         'EHS Card' => 'emp_ehs_card',
//         'QR/Barcode' => 'emp_barcode',
//         'Depot Location' => 'emp_depot_location',
//         'Last Working Place' => 'emp_last_working',
//         'Residential Address' => 'emp_residential_address',
//         'Additional Info' => 'employee_info'
//     );

//     $success_count = 0;
//     $error_count = 0;
//     $row_number = 1;

//     while (($data = fgetcsv($handle)) !== false) {
//         $row_number++;
//         if (count($data) !== count($headers)) {
//             $error_count++;
//             continue;
//         }

//         $user_data = array();
        
//         // Map CSV data to user fields
//         foreach ($headers as $index => $header) {
//             if (isset($header_mapping[$header]) && isset($data[$index])) {
//                 $user_data[$header_mapping[$header]] = sanitize_text_field($data[$index]);
//             }
//         }

//         // Validate required fields
//         if (empty($user_data['emp_id'])) {
//             $error_count++;
//             continue;
//         }

//         // Check if user exists
//         $existing_user = get_users(array(
//             'meta_key' => 'emp_id',
//             'meta_value' => $user_data['emp_id'],
//             'number' => 1
//         ));

//         try {
//             if (!empty($existing_user)) {
//                 $user_id = $existing_user[0]->ID;
                
//                 // Update existing user
//                 if (isset($user_data['user_email'])) {
//                     wp_update_user(array(
//                         'ID' => $user_id,
//                         'user_email' => $user_data['user_email']
//                     ));
//                 }
//             } else {
//                 // Create new user
//                 $username = sanitize_user($user_data['emp_id']);
//                 $email = !empty($user_data['user_email']) ? $user_data['user_email'] : $username . '@example.com';
                
//                 $user_id = wp_create_user($username, wp_generate_password(), $email);
//                 if (is_wp_error($user_id)) {
//                     $error_count++;
//                     continue;
//                 }
                
//                 // Set role
//                 $user = new WP_User($user_id);
//                 $user->set_role('subscriber');
//             }

//             // Update user meta
//             foreach ($user_data as $meta_key => $meta_value) {
//                 if ($meta_key !== 'user_email') {
//                     update_user_meta($user_id, $meta_key, $meta_value);
//                 }
//             }

//             $success_count++;
//         } catch (Exception $e) {
//             $error_count++;
//             $errors[] = "Error processing user " . $user_data['emp_id'] . ": " . $e->getMessage();
//         }
//     }

//     fclose($handle);

//     // Store errors in transient for display
//     if (!empty($errors)) {
//         set_transient('wp_smart_badge_import_errors', $errors, 60);
//     }

//     // Redirect with status
//     $status = sprintf('imported=%d&errors=%d', $success_count, $error_count);
//     wp_safe_redirect(add_query_arg('import_status', $status, wp_get_referer()));
//     exit();
// }
// add_action('admin_init', 'wp_smart_badge_import_users');

// // Add admin notices for import status
// function wp_smart_badge_admin_notices() {
//     if (!isset($_GET['import_status'])) {
//         return;
//     }

//     $status = $_GET['import_status'];
//     $notice_class = 'notice-info';
//     $message = '';

//     switch ($status) {
//         case 'no_file':
//             $notice_class = 'notice-error';
//             $message = 'Please select a file to import.';
//             break;
//         case 'upload_error':
//             $notice_class = 'notice-error';
//             $message = 'Error uploading file. Please try again.';
//             break;
//         case 'file_error':
//             $notice_class = 'notice-error';
//             $message = 'Error reading file. Please check the file format.';
//             break;
//         case 'invalid_format':
//             $notice_class = 'notice-error';
//             $message = 'Invalid file format. Please check the CSV structure.';
//             break;
//         case 'invalid_type':
//             $notice_class = 'notice-error';
//             $message = 'Invalid file type. Please upload a CSV file.';
//             break;
//         default:
//             if (strpos($status, 'imported=') !== false) {
//                 preg_match('/imported=(\d+)&errors=(\d+)/', $status, $matches);
//                 $success = intval($matches[1]);
//                 $errors = intval($matches[2]);
                
//                 if ($success > 0) {
//                     $notice_class = 'notice-success';
//                     $message = "Successfully imported {$success} users.";
//                 }
                
//                 if ($errors > 0) {
//                     // Show error details if available
//                     $error_details = get_transient('wp_smart_badge_import_errors');
//                     if ($error_details) {
//                         $message .= "<br>Failed to import {$errors} users:";
//                         $message .= "<ul style='list-style-type: disc; margin-left: 20px;'>";
//                         foreach ($error_details as $error) {
//                             $message .= "<li>" . esc_html($error) . "</li>";
//                         }
//                         $message .= "</ul>";
//                         delete_transient('wp_smart_badge_import_errors');
//                     } else {
//                         $message .= "<br>Failed to import {$errors} users.";
//                     }
//                     $notice_class = 'notice-warning';
//                 }
//             }
//     }

//     if ($message) {
//         printf(
//             '<div class="notice %s is-dismissible"><p>%s</p></div>',
//             esc_attr($notice_class),
//             wp_kses_post($message)
//         );
//     }
// }
// add_action('admin_notices', 'wp_smart_badge_admin_notices');
