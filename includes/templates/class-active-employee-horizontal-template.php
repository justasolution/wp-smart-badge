<?php
namespace WpSmartBadge\Templates;

class ActiveEmployeeHorizontalTemplate extends BadgeTemplate {
    public function __construct($data) {
        parent::__construct($data);
    }

    protected function set_orientation() {
        return self::ORIENTATION_LANDSCAPE;
    }
    
    protected function set_template_type() {
        return 'active_employee_horizontal';
    }
    
    public function get_type() {
        return 'active_employee_horizontal';
    }
    
    public function get_employee_data($user_id) {
        return array(
            'emp_id'                => get_user_meta($user_id, 'emp_id', true),
            'emp_full_name'         => get_user_meta($user_id, 'emp_full_name', true),
            'emp_designation'       => get_user_meta($user_id, 'emp_designation', true),
            'emp_department'        => get_user_meta($user_id, 'emp_department', true),
            'emp_phone'            => get_user_meta($user_id, 'emp_phone', true),
            'emp_blood_group'      => get_user_meta($user_id, 'emp_blood_group', true),
            'emp_cfms_id'          => get_user_meta($user_id, 'emp_cfms_id', true),
            'emp_hrms_id'          => get_user_meta($user_id, 'emp_hrms_id', true),
            'emp_emergency_contact' => get_user_meta($user_id, 'emp_emergency_contact', true),
            'emp_ehs_card'         => get_user_meta($user_id, 'emp_ehs_card', true),
            'emp_barcode'          => get_user_meta($user_id, 'emp_barcode', true),
            'emp_depot_location'   => get_user_meta($user_id, 'emp_depot_location', true),
            'emp_last_working'     => get_user_meta($user_id, 'emp_last_working', true),
            'emp_residential_address' => get_user_meta($user_id, 'emp_residential_address', true),
            'emp_status'           => get_user_meta($user_id, 'emp_status', true),
            'emp_photo'            => get_user_meta($user_id, 'emp_photo', true)
        );
    }
    
    public function generate_front() {
        // Log user data for debugging
        wp_smart_badge_log('Active Employee Horizontal Template - User Data', $this->user_data);

        // Get user photo
        $photo = $this->get_user_meta('emp_photo');
        if (empty($photo)) {
            $photo = plugins_url('assets/images/default-avatar.jpg', WP_SMART_BADGE_FILE);
        }

        // Generate front side HTML with horizontal layout
        $html = '<div class="badge-content" style="width: 85.6mm; height: 54mm; padding: 0; background: linear-gradient(to right, #ffb499 0%, #fff 20%, #fff 80%, #ffb499 100%); font-family: system-ui, -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; position: relative; display: flex;">';

        // Left section with photo and logo
        $html .= '<div style="width: 30%; padding: 2mm; display: flex; flex-direction: column; align-items: center; gap: 2mm;">';
        
        // Logo
        $html .= '<img src="http://smartidportal.justasolutionaway.com/wp-content/uploads/2025/02/png-transparent-guntur-vijayawada-bus-nellore-andhra-pradesh-state-road-transport-corporation-bus-removebg-preview-e1740051277257.png" alt="Logo" style="height: 8mm; width: auto;">';
        
        // Title
        $html .= '<div style="text-align: center;">';
        $html .= '<div style="font-size: 12pt; font-weight: bold; color: #f60;">APSRTC</div>';
        $html .= '<div style="font-size: 6pt; color: #333;">IDENTITY CARD</div>';
        $html .= '</div>';
        
        // Photo
        $html .= '<div style="width: 20mm; height: 20mm; border: 1px solid #ccc; overflow: hidden; border-radius: 2mm;">';
        $html .= '<img src="' . $photo . '" alt="Employee Photo" style="width: 100%; height: 100%; object-fit: cover;">';
        $html .= '</div>';
        $html .= '</div>';

        // Right section with information
        $html .= '<div style="width: 70%; padding: 2mm; display: flex; flex-direction: column;">';
        
        // Helper function to add info row
        $add_info_row = function($label, $value, $show_if_empty = false) {
            if ($show_if_empty || !empty($value)) {
                return '<div class="info-row" style="display: flex; gap: 1mm; margin-bottom: 0.5mm;">' .
                       '<span class="label" style="color: #333; font-weight: 500; font-size: 6pt; min-width: 50px;">' . esc_html($label) . ':</span>' .
                       '<span class="value" style="color: #333; font-size: 6pt;">' . esc_html($value) . '</span>' .
                       '</div>';
            }
            return '';
        };

        // Add all fields
        $fields = [
            'Name' => 'emp_full_name',
            'Staff No' => 'emp_id',
            'CFMS ID' => 'emp_cfms_id',
            'HRMS ID' => 'emp_hrms_id',
            'Designation' => 'emp_designation',
            'Contact No' => 'emp_phone',
            'Blood Group' => 'emp_blood_group',
            'EHS Card' => 'emp_ehs_card',
            'Emergency' => 'emp_emergency_contact',
            'Barcode' => 'emp_barcode',
            'Depot Location' => 'emp_depot_location',
            'Last Working Place' => 'emp_last_working'
        ];

        foreach ($fields as $label => $field) {
            $value = $this->user_data[$field] ?? 'N/A';
            if ($label === 'Name' || $label === 'Staff No') {
                $html .= $add_info_row($label, $value, true);
            } else {
                $html .= $add_info_row($label, $value);
            }
        }

        // Valid from/to dates at the bottom
        $valid_from = date('d-m-Y');
        $valid_to = date('d-m-Y', strtotime('+2 years'));
        $html .= '<div style="margin-top: auto; text-align: center; font-size: 6pt; color: #666;">';
        $html .= 'Valid from: ' . $valid_from . ' to ' . $valid_to;
        $html .= '</div>';

        $html .= '</div>'; // End right section
        $html .= '</div>'; // End badge content

        return $html;
    }
    
    public function generate_back() {
        $html = '<div class="badge-content" style="width: 85.6mm; height: 54mm; padding: 0; background: linear-gradient(to right, #ffb499 0%, #fff 20%, #fff 80%, #ffb499 100%); font-family: system-ui, -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; position: relative; display: flex;">';
        
        // Left section with benefits
        $html .= '<div style="width: 70%; padding: 2mm;">';
        $html .= '<div style="font-size: 8pt; font-weight: bold; color: #333; margin-bottom: 2mm;">BENEFITS/CONCESSIONS</div>';
        
        // Instructions with smaller font
        $html .= '<div style="font-size: 6pt; color: #333; line-height: 1.2;">';
        $html .= '<p style="margin: 1mm 0;">1. Eligible for Medical treatment in APSRTC Dispensaries and APSRTC Central Hospital.</p>';
        $html .= '<p style="margin: 1mm 0;">2. VTPM, Vijayawada on par with in-service employees.</p>';
        $html .= '<p style="margin: 1mm 0;">3. Other facilities under "The APSRTC Retired Employees Medical Facilities Scheme-2003".</p>';
        $html .= '<p style="margin: 1mm 0;">4. Eligible to stay in all APSRTC Guest Houses in AP not exceeding 3 Days at any place.</p>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Right section with QR code and signature
        $html .= '<div style="width: 30%; padding: 2mm; display: flex; flex-direction: column; align-items: center; justify-content: center;">';
        if (!empty($this->user_data['emp_barcode'])) {
            $html .= '<img src="http://smartidportal.justasolutionaway.com/wp-content/uploads/2025/02/qr.png" alt="QR Code" style="height: 15mm; width: auto; margin-bottom: 2mm;">';
        }
        $html .= '<img src="http://smartidportal.justasolutionaway.com/wp-content/uploads/2025/02/sign.png" alt="Signature" style="height: 10mm; width: auto; margin-bottom: 1mm;">';
        $html .= '<div style="font-size: 6pt; color: #333; text-align: center;">For MANAGING DIRECTOR<br>APSRTC, VIJAYAWADA</div>';
        $html .= '</div>';
        
        $html .= '</div>';
        return $html;
    }
}