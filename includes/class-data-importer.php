<?php
namespace WpSmartBadge;

class Data_Importer {
    private $required_fields = [
        'emp_id',
        'emp_full_name',
        'emp_designation',
        'emp_department',
        'work_location'
    ];
    
    public function __construct() {
        add_action('wp_ajax_import_employee_data', [$this, 'handle_import']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }
    
    public function enqueue_scripts($hook) {
        if ('smart-badge_page_smart-badge-import' !== $hook) {
            return;
        }
        
        wp_enqueue_script('papaparse', plugins_url('assets/js/papaparse.min.js', SMART_BADGE_PLUGIN_FILE), [], '5.3.0', true);
        wp_enqueue_script('smart-badge-import', plugins_url('assets/js/import.js', SMART_BADGE_PLUGIN_FILE), ['jquery', 'papaparse', 'ag-grid'], '1.0.0', true);
        
        wp_localize_script('smart-badge-import', 'smartBadgeImport', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_smart_badge_import'),
            'strings' => [
                'success' => __('Data imported successfully!', 'wp-smart-badge'),
                'error' => __('Error importing data:', 'wp-smart-badge'),
                'confirm' => __('Are you sure you want to import this data?', 'wp-smart-badge')
            ]
        ]);
    }
    
    public function handle_import() {
        check_ajax_referer('wp_smart_badge_import', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'wp-smart-badge')]);
            return;
        }
        
        if (!isset($_FILES['csvFile'])) {
            wp_send_json_error(['message' => __('No file uploaded', 'wp-smart-badge')]);
            return;
        }
        
        $file = $_FILES['csvFile'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(['message' => __('File upload error', 'wp-smart-badge')]);
            return;
        }
        
        // Parse CSV using PapaParse
        $csv_content = file_get_contents($file['tmp_name']);
        if (!$csv_content) {
            wp_send_json_error(['message' => __('Could not read file', 'wp-smart-badge')]);
            return;
        }
        
        // Get current user's work location
        $user_location = get_user_meta(get_current_user_id(), 'work_location', true);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'smart_badge_employees';
        $imported = 0;
        $errors = [];
        
        // Start transaction
        $wpdb->query('START TRANSACTION');
        
        try {
            $rows = array_map('str_getcsv', explode("\n", $csv_content));
            $headers = array_shift($rows);
            
            // Validate required fields
            $missing_fields = array_diff($this->required_fields, $headers);
            if (!empty($missing_fields)) {
                throw new \Exception(sprintf(
                    __('Missing required fields: %s', 'wp-smart-badge'),
                    implode(', ', $missing_fields)
                ));
            }
            
            foreach ($rows as $row) {
                if (count($row) !== count($headers)) {
                    continue; // Skip malformed rows
                }
                
                $data = array_combine($headers, $row);
                
                // Skip records not matching user's work location (if set)
                if (!empty($user_location) && strtoupper($data['work_location']) !== strtoupper($user_location)) {
                    continue;
                }
                
                // Validate work location
                if (!$this->validate_work_location($data['work_location'])) {
                    throw new \Exception(sprintf(
                        __('Invalid work location: %s', 'wp-smart-badge'),
                        $data['work_location']
                    ));
                }
                
                // Prepare data for insert/update
                $employee_data = apply_filters('smart_badge_import_employee_data', [
                    'emp_id' => sanitize_text_field($data['emp_id']),
                    'emp_full_name' => sanitize_text_field($data['emp_full_name']),
                    'emp_designation' => sanitize_text_field($data['emp_designation']),
                    'emp_department' => sanitize_text_field($data['emp_department']),
                    'work_location' => sanitize_text_field($data['work_location']),
                    'emp_phone' => isset($data['emp_phone']) ? sanitize_text_field($data['emp_phone']) : '',
                    'emp_blood_group' => isset($data['emp_blood_group']) ? sanitize_text_field($data['emp_blood_group']) : '',
                    'emp_cfms_id' => isset($data['emp_cfms_id']) ? sanitize_text_field($data['emp_cfms_id']) : '',
                    'emp_hrms_id' => isset($data['emp_hrms_id']) ? sanitize_text_field($data['emp_hrms_id']) : '',
                    'emp_status' => isset($data['emp_status']) ? sanitize_text_field($data['emp_status']) : 'active',
                    'updated_at' => current_time('mysql')
                ], $data);
                
                // Check if employee exists
                $existing = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT id FROM $table_name WHERE emp_id = %s",
                        $data['emp_id']
                    )
                );
                
                if ($existing) {
                    // Update existing employee
                    $result = $wpdb->update(
                        $table_name,
                        $employee_data,
                        ['emp_id' => $data['emp_id']]
                    );
                } else {
                    // Insert new employee
                    $employee_data['created_at'] = current_time('mysql');
                    $result = $wpdb->insert($table_name, $employee_data);
                }
                
                if ($result === false) {
                    throw new \Exception(sprintf(
                        __('Error processing employee: %s', 'wp-smart-badge'),
                        $data['emp_id']
                    ));
                }
                
                $imported++;
            }
            
            // Commit transaction
            $wpdb->query('COMMIT');
            
            // Get updated data for AG Grid
            $updated_data = $this->get_employees_data($user_location);
            
            wp_send_json_success([
                'message' => sprintf(
                    __('Successfully imported %d employees', 'wp-smart-badge'),
                    $imported
                ),
                'imported' => $imported,
                'gridData' => $updated_data
            ]);
            
        } catch (\Exception $e) {
            // Rollback transaction
            $wpdb->query('ROLLBACK');
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    private function get_employees_data($location = '') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'smart_badge_employees';
        
        $query = "SELECT * FROM $table_name";
        if (!empty($location)) {
            $query .= $wpdb->prepare(" WHERE work_location = %s", $location);
        }
        $query .= " ORDER BY emp_full_name ASC";
        
        return $wpdb->get_results($query, ARRAY_A);
    }
    
    private function validate_work_location($location) {
        $valid_locations = apply_filters('smart_badge_valid_locations', [
            'VIJAYAWADA',
            'GUNTUR',
            'ELURU',
            'KAKINADA',
            'RAJAHMUNDRY'
        ]);
        
        return in_array(strtoupper($location), array_map('strtoupper', $valid_locations));
    }
}
