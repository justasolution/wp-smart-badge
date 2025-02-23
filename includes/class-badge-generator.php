<?php
/**
 * Badge Generator Class
 * Handles the generation of ID cards using mPDF
 */

namespace WpSmartBadge;

use Mpdf\Mpdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Color\Color;
use Exception;

class Badge_Generator {
    private $user_id;
    private $user_data;
    private $template;
    private $mpdf;
    
    public function __construct($user_id = null) {
        if ($user_id) {
            $this->user_id = $user_id;
            $this->load_user_data();
            $this->init_template();
        }
        
        add_action('wp_ajax_generate_badge', [$this, 'ajax_generate_badge']);
        add_action('wp_ajax_get_employees_data', [$this, 'get_employees_data']);
    }
    
    public function ajax_generate_badge() {
        check_ajax_referer('wp_smart_badge_generate', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'wp-smart-badge')]);
            return;
        }
        
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        if (!$user_id) {
            wp_send_json_error(['message' => __('Invalid user ID', 'wp-smart-badge')]);
            return;
        }
        
        $generator = new self($user_id);
        $result = $generator->generate();
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        } else {
            wp_send_json_success($result);
        }
    }
    
    private function load_user_data() {
        $user = get_userdata($this->user_id);
        if (!$user) {
            return false;
        }
        
        $get_meta_value = function($key, $default = '') {
            return get_user_meta($this->user_id, $key, true) ?: $default;
        };
        
        $this->user_data = array(
            'ID' => $user->ID,
            'user_email' => $user->user_email,
            'display_name' => $user->display_name,
            'user_registered' => $user->user_registered,
            'employee_info' => $get_meta_value('employee_info'),
            'emp_id' => $get_meta_value('emp_id') ?: $user->user_login,
            'emp_full_name' => $get_meta_value('emp_full_name') ?: $user->display_name,
            'emp_designation' => $get_meta_value('emp_designation') ?: $user->roles[0],
            'emp_department' => $get_meta_value('emp_department'),
            'emp_phone' => $get_meta_value('emp_phone'),
            'emp_photo' => $get_meta_value('emp_photo'),
            'emp_blood_group' => $get_meta_value('emp_blood_group'),
            'emp_cfms_id' => $get_meta_value('emp_cfms_id'),
            'emp_hrms_id' => $get_meta_value('emp_hrms_id'),
            'emp_ehs_card' => $get_meta_value('emp_ehs_card'),
            'emp_emergency_contact' => $get_meta_value('emp_emergency_contact'),
            'emp_status' => $get_meta_value('emp_status'),
            'emp_barcode' => $get_meta_value('emp_barcode'),
            'emp_organisation' => $get_meta_value('emp_organisation'),
            'emp_location' => $get_meta_value('emp_location'),
            'emp_job_title' => $get_meta_value('emp_job_title'),
            'emp_manager' => $get_meta_value('emp_manager'),
            'emp_vehicle' => $get_meta_value('emp_vehicle'),
            'emp_vehicle_registration' => $get_meta_value('emp_vehicle_registration'),
            'qr_code' => '',  // Will be generated later if needed
            'emp_valid_until' => date('Y-m-d', strtotime('+2 years'))
        );
        
        return true;
    }
    
    private function init_template() {
        if (empty($this->user_data)) {
            return false;
        }
        
        $template_class = $this->get_template_class();
        if (!$template_class) {
            return false;
        }
        
        $this->template = new $template_class($this->user_data);
        return true;
    }
    
    private function get_template_class() {
        $status = $this->user_data['emp_status'] ?? 'active';
        $template_map = [
            'active' => 'ActiveEmployeeTemplate',
            'retired_medical' => 'RetiredMedicalTemplate',
            'retired_travel' => 'RetiredTravelTemplate'
        ];
        
        $template_name = $template_map[$status] ?? 'ActiveEmployeeTemplate';
        $class_name = "\\WpSmartBadge\\Templates\\{$template_name}";
        
        return class_exists($class_name) ? $class_name : false;
    }
    
    public function generate() {
        if (!$this->template) {
            return new \WP_Error('no_template', __('Template not initialized', 'wp-smart-badge'));
        }
        
        try {
            $front = $this->template->generate_front();
            $back = $this->template->generate_back();
            
            $this->init_mpdf();
            
            $this->mpdf->WriteHTML('<div class="badge-front">' . $front . '</div>', 2);
            
            $this->mpdf->AddPage();
            
            $this->mpdf->WriteHTML('<div class="badge-back">' . $back . '</div>', 2);
            
            $upload_dir = wp_upload_dir();
            $badge_dir = $upload_dir['basedir'] . '/badges';
            if (!file_exists($badge_dir)) {
                wp_mkdir_p($badge_dir);
            }
            
            $temp_dir = $upload_dir['basedir'] . '/mpdf';
            if (!file_exists($temp_dir)) {
                wp_mkdir_p($temp_dir);
            }
            
            $file_name = 'badge_' . $this->user_id . '_' . time() . '.pdf';
            $file_path = $badge_dir . '/' . $file_name;
            
            $this->mpdf->Output($file_path, 'F');
            
            return array(
                'success' => true,
                'file_path' => $file_path,
                'file_url' => str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $file_path)
            );
        } catch (\Exception $e) {
            return new \WP_Error('generation_failed', $e->getMessage());
        }
    }
    
    private function init_mpdf() {
        $this->mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => [54, 85.6],
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'tempDir' => wp_upload_dir()['basedir'] . '/mpdf'
        ]);
        
        $this->mpdf->showImageErrors = true;
        $this->mpdf->keepColumns = true;
        $this->mpdf->useSubstitutions = false;
        $this->mpdf->simpleTables = true;
        $this->mpdf->packTableData = true;
    }
    
    public function get_employees_data() {
        check_ajax_referer('wp_smart_badge_generate', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'wp-smart-badge')]);
            return;
        }
        
        $user_location = get_user_meta(get_current_user_id(), 'work_location', true);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'smart_badge_employees';
        
        $query = "SELECT * FROM $table_name";
        if (!empty($user_location)) {
            $query .= $wpdb->prepare(" WHERE work_location = %s", $user_location);
        }
        $query .= " ORDER BY emp_full_name ASC";
        
        $results = $wpdb->get_results($query, ARRAY_A);
        
        wp_send_json_success($results);
    }
}
