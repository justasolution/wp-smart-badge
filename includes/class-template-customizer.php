<?php
namespace WpSmartBadge;

class Template_Customizer {
    private $plugin_name;
    private $version;
    private $templates_option = 'wp_smart_badge_templates';
    private $template_options;

    public function __construct($plugin_name = 'wp-smart-badge', $version = '1.0.0') {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->init_template_options();

        // Add menu pages
        add_action('admin_menu', array($this, 'add_templates_page'));
        add_action('admin_menu', array($this, 'register_preview_page'));
        
        // Add scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Add AJAX handlers
        add_action('wp_ajax_wp_smart_badge_get_template_data', array($this, 'ajax_get_template_data'));
        add_action('wp_ajax_wp_smart_badge_save_template', array($this, 'ajax_save_template'));

        // Debug log
        error_log('Template Customizer initialized with version: ' . $version);
    }

    private function init_template_options() {
        $this->template_options = array(
            'default' => array(
                'name' => __('Default Template', 'wp-smart-badge'),
                'layout' => array(
                    'front' => array(
                        array(
                            'type' => 'photo',
                            'x' => 20,
                            'y' => 20,
                            'width' => 100,
                            'height' => 120,
                            'text' => __('Photo', 'wp-smart-badge')
                        ),
                        array(
                            'type' => 'name',
                            'x' => 140,
                            'y' => 20,
                            'width' => 200,
                            'height' => 30,
                            'text' => __('Name', 'wp-smart-badge')
                        ),
                        array(
                            'type' => 'designation',
                            'x' => 140,
                            'y' => 60,
                            'width' => 200,
                            'height' => 30,
                            'text' => __('Designation', 'wp-smart-badge')
                        ),
                        array(
                            'type' => 'department',
                            'x' => 140,
                            'y' => 100,
                            'width' => 200,
                            'height' => 30,
                            'text' => __('Department', 'wp-smart-badge')
                        )
                    ),
                    'back' => array(
                        array(
                            'type' => 'qr_code',
                            'x' => 20,
                            'y' => 20,
                            'width' => 100,
                            'height' => 100,
                            'text' => __('QR Code', 'wp-smart-badge')
                        ),
                        array(
                            'type' => 'id',
                            'x' => 140,
                            'y' => 20,
                            'width' => 200,
                            'height' => 30,
                            'text' => __('Employee ID', 'wp-smart-badge')
                        ),
                        array(
                            'type' => 'blood_group',
                            'x' => 140,
                            'y' => 60,
                            'width' => 200,
                            'height' => 30,
                            'text' => __('Blood Group', 'wp-smart-badge')
                        )
                    )
                ),
                'styles' => array(
                    'background' => array(
                        'type' => 'solid',
                        'color' => '#ffffff'
                    ),
                    'text' => array(
                        'color' => '#000000',
                        'size' => 'medium'
                    )
                )
            )
        );
    }

    public function add_templates_page() {
        $hook = add_submenu_page(
            'edit.php?post_type=smart_badge',
            __('Badge Templates', 'wp-smart-badge'),
            __('Templates', 'wp-smart-badge'),
            'manage_options',
            'wp-smart-badge-templates',
            array($this, 'render_templates_page')
        );

        // Debug log the hook name
        error_log('Template Customizer - Templates page hook: ' . $hook);
    }

    public function register_preview_page() {
        add_submenu_page(
            null, // Hidden from menu
            __('Badge Preview', 'wp-smart-badge'),
            __('Badge Preview', 'wp-smart-badge'),
            'manage_options',
            'wp-smart-badge-preview',
            array($this, 'render_preview_page')
        );
    }

    public function enqueue_scripts($hook) {
        // Debug the hook name
        error_log('Template Customizer - Current hook: ' . $hook);

        // The hook for submenu pages is: {parent_slug}_page_{page_slug}
        if ($hook !== 'wp-smart-badge_page_wp-smart-badge-templates' && $hook !== 'admin_page_wp-smart-badge-preview') {
            error_log('Template Customizer - Hook not matched: ' . $hook);
            return;
        }

        // Log to confirm the method is called
        error_log('Enqueuing template customizer scripts');

        // Enqueue jQuery UI and its dependencies
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('jquery-ui-droppable');
        wp_enqueue_script('jquery-ui-resizable');
        wp_enqueue_script('jquery-touch-punch'); // For touch device support

        // Enqueue WordPress color picker
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        
        // Enqueue our custom styles and scripts
        wp_enqueue_style(
            'wp-smart-badge-customizer',
            WP_SMART_BADGE_URL . 'assets/css/template-customizer.css',
            array(),
            $this->version
        );

        // Debug log the script URL
        error_log('Template Customizer JS URL: ' . WP_SMART_BADGE_URL . 'assets/js/template-customizer.js');

        wp_enqueue_script(
            'wp-smart-badge-customizer',
            WP_SMART_BADGE_URL . 'assets/js/template-customizer.js',
            array('jquery', 'jquery-ui-draggable', 'jquery-ui-droppable', 'wp-color-picker'),
            $this->version,
            true
        );

        // Add required data to JavaScript
        wp_localize_script('wp-smart-badge-customizer', 'wpSmartBadge', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'adminUrl' => admin_url(),
            'nonce' => wp_create_nonce('wp_smart_badge_template_customizer'),
            'i18n' => array(
                'confirmReset' => __('Are you sure you want to reset the template? All unsaved changes will be lost.', 'wp-smart-badge'),
                'savingTemplate' => __('Saving template...', 'wp-smart-badge'),
                'templateSaved' => __('Template saved successfully!', 'wp-smart-badge'),
                'errorSaving' => __('Error saving template. Please try again.', 'wp-smart-badge')
            )
        ));

        // Add jQuery UI styles
        wp_enqueue_style(
            'jquery-ui-styles',
            'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css',
            array(),
            '1.12.1'
        );

        // Debug log completion
        error_log('Template Customizer scripts enqueued successfully');
    }

    public function render_templates_page() {
        if (isset($_GET['action']) && $_GET['action'] === 'preview') {
            require_once plugin_dir_path(dirname(__FILE__)) . 'templates/admin/template-preview.php';
            return;
        }
        require_once plugin_dir_path(dirname(__FILE__)) . 'templates/admin/templates.php';
    }

    public function render_preview_page() {
        include_once WP_SMART_BADGE_PATH . 'templates/admin/template-preview.php';
    }

    public function ajax_get_template_data() {
        check_ajax_referer('wp_smart_badge_template_customizer', 'nonce');
        
        $template_id = sanitize_text_field($_POST['template']);
        
        if ($template_id === 'default') {
            $default_template = array(
                'layout' => array(
                    'front' => array(
                        array('type' => 'photo', 'x' => 20, 'y' => 20, 'width' => 100, 'height' => 120),
                        array('type' => 'name', 'x' => 140, 'y' => 20, 'width' => 200, 'height' => 30),
                        array('type' => 'id', 'x' => 140, 'y' => 60, 'width' => 120, 'height' => 25),
                        array('type' => 'designation', 'x' => 140, 'y' => 95, 'width' => 200, 'height' => 25)
                    ),
                    'back' => array(
                        array('type' => 'qr_code', 'x' => 20, 'y' => 20, 'width' => 120, 'height' => 120),
                        array('type' => 'department', 'x' => 160, 'y' => 20, 'width' => 200, 'height' => 25),
                        array('type' => 'blood_group', 'x' => 160, 'y' => 55, 'width' => 100, 'height' => 25)
                    )
                ),
                'styles' => array(
                    'background' => array(
                        'type' => 'solid',
                        'color' => '#ffffff',
                        'gradient' => array(
                            'start' => '#ffffff',
                            'end' => '#f0f0f0',
                            'direction' => 'to bottom'
                        )
                    ),
                    'text' => array(
                        'color' => '#333333',
                        'size' => 'medium'
                    )
                )
            );
            wp_send_json_success($default_template);
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }

        $template_data = $this->get_template($template_id);
        if ($template_data) {
            wp_send_json_success($template_data);
        } else {
            wp_send_json_error('Template not found');
        }
    }

    public function ajax_save_template() {
        check_ajax_referer('wp_smart_badge_template_customizer', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }

        $template_id = sanitize_text_field($_POST['template']);
        $template_data = json_decode(stripslashes($_POST['data']), true);

        if (empty($template_id) || empty($template_data)) {
            wp_send_json_error('Invalid template data');
            return;
        }

        $result = $this->save_template($template_id, $template_data);
        if ($result) {
            wp_send_json_success('Template saved successfully');
        } else {
            wp_send_json_error('Error saving template');
        }
    }

    private function get_template($template_id) {
        $templates = get_option($this->templates_option, array());
        return isset($templates[$template_id]) ? $templates[$template_id] : null;
    }

    private function save_template($template_id, $template_data) {
        $templates = get_option($this->templates_option, array());
        
        // Validate template data
        if (!isset($template_data['layout']) || !isset($template_data['styles'])) {
            error_log('Invalid template data structure');
            return false;
        }

        // Sanitize template data
        $template_data = $this->sanitize_template_data($template_data);
        
        // Save template
        $templates[$template_id] = $template_data;
        return update_option($this->templates_option, $templates);
    }

    private function sanitize_template_data($data) {
        if (isset($data['layout'])) {
            foreach (['front', 'back'] as $side) {
                if (isset($data['layout'][$side]) && is_array($data['layout'][$side])) {
                    foreach ($data['layout'][$side] as &$field) {
                        $field = array(
                            'type' => sanitize_text_field($field['type']),
                            'x' => absint($field['x']),
                            'y' => absint($field['y']),
                            'width' => absint($field['width']),
                            'height' => absint($field['height']),
                            'text' => sanitize_text_field($field['text'])
                        );
                    }
                }
            }
        }

        if (isset($data['styles'])) {
            $data['styles'] = array(
                'background' => array(
                    'type' => sanitize_text_field($data['styles']['background']['type']),
                    'color' => sanitize_hex_color($data['styles']['background']['color']),
                    'gradient' => array(
                        'start' => sanitize_hex_color($data['styles']['background']['gradient']['start']),
                        'end' => sanitize_hex_color($data['styles']['background']['gradient']['end']),
                        'direction' => sanitize_text_field($data['styles']['background']['gradient']['direction'])
                    )
                ),
                'text' => array(
                    'color' => sanitize_hex_color($data['styles']['text']['color']),
                    'size' => sanitize_text_field($data['styles']['text']['size'])
                )
            );
        }

        return $data;
    }
}
