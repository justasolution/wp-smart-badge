<?php
namespace WpSmartBadge\Templates;

class ActiveEmployeeTemplate extends BadgeTemplate {
    public function __construct($data) {
        parent::__construct($data);
    }
    
    protected function set_orientation() {
        return self::ORIENTATION_PORTRAIT;
    }
    
    protected function set_template_type() {
        return self::TYPE_CLASS_1;
    }
    
    public function get_type() {
        return self::TYPE_CLASS_1;
    }
    
    public function get_employee_data($user_id) {
        $data = array(
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
        return $data;
    }
    
    public function generate_front() {
        // Log user data for debugging
        wp_smart_badge_log('Active Employee Template - User data for front', $this->user_data);

        // Get user photo
        $photo = $this->get_user_meta('emp_photo');
        if (empty($photo)) {
            $photo = plugins_url('assets/images/default-avatar.jpg', WP_SMART_BADGE_FILE);
        }

        // Generate front side HTML
        $html = '<div class="badge-content" style="width: 54mm; height: 85.6mm; padding: 0; background: linear-gradient(to bottom, #ffb499 0%, #fff 20%, #fff 80%, #ffb499 100%); font-family: system-ui, -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; position: relative; display: flex; flex-direction: column;">';
        
        // Header with logo and title
        $html .= '<div style="padding: 2mm; display: flex; align-items: center; gap: 2mm; justify-content: center;">';
        $html .= '<img src="http://smartidportal.justasolutionaway.com/wp-content/uploads/2025/02/png-transparent-guntur-vijayawada-bus-nellore-andhra-pradesh-state-road-transport-corporation-bus-removebg-preview-e1740051277257.png" alt="Logo" style="height: 8mm; width: auto;">';
        $html .= '<div style="flex: 1;">';
        $html .= '<div style="font-size: 16pt; font-weight: bold; color: #f60; text-align: center;">APSRTC</div>';
        $html .= '<div style="font-size: 8pt; color: #333; text-align: center;">IDENTITY CARD</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Photo at top
        $html .= '<div style="width: 100%; display: flex; justify-content: center; padding: 0mm;">';
        $html .= '<div style="width: 15mm;height: 15mm;border: 1px solid #ccc;overflow: hidden;border-radius: 2mm;">';
        $html .= '<img src="' . $photo . '" alt="Employee Photo" style="width: 100%; height: 100%; object-fit: cover;">';
        $html .= '</div>';
        $html .= '</div>';
        
        // Main content area with info
        $html .= '<div style="padding: 1mm; flex: 1;">';
        
        // Staff info
        $html .= '<div style="display: flex; flex-direction: column; gap: 1.5mm;">';

        // Helper function to add info row
        $add_info_row = function($label, $value, $show_if_empty = false) {
            if ($show_if_empty || !empty($value)) {
                return '<div class="info-row" style="display: flex; gap: 1mm;">' .
                       '<span class="label" style="color: #333; font-weight: 500; font-size: 7pt; min-width: 60px;">' . esc_html($label) . ':</span>' .
                       '<span class="value" style="color: #333; font-size: 7pt;">' . esc_html($value) . '</span>' .
                       '</div>';
            }
            return '';
        };

        // Add all fields
        $html .= $add_info_row('Name', $this->user_data['emp_full_name'], true);
        $html .= $add_info_row('Staff No', $this->user_data['emp_id'], true);
        $html .= $add_info_row('CFMS ID', $this->user_data['emp_cfms_id']);
        $html .= $add_info_row('HRMS ID', $this->user_data['emp_hrms_id']);
        $html .= $add_info_row('Designation', $this->user_data['emp_designation']);
        $html .= $add_info_row('Contact No', $this->user_data['emp_phone']);
        $html .= $add_info_row('Blood Group', $this->user_data['emp_blood_group']);
        $html .= $add_info_row('EHS Card', $this->user_data['emp_ehs_card']);
        $html .= $add_info_row('Emergency', $this->user_data['emp_emergency_contact']);
        $html .= $add_info_row('Barcode', $this->user_data['emp_barcode']);
        $html .= $add_info_row('Depot Location', $this->user_data['emp_depot_location']);
        $html .= $add_info_row('Last Working Place', $this->user_data['emp_last_working']);
        $html .= $add_info_row('Residential Address', $this->user_data['emp_residential_address']);

        $html .= '</div>'; // End staff info

        // Valid from/to dates
        $valid_from = date('d-m-Y');
        $valid_to = date('d-m-Y', strtotime('+2 years'));
        $html .= '<div style="margin-top: auto; text-align: center; font-size: 6pt; color: #666; padding: 2mm;">';
        $html .= 'Valid from: ' . $valid_from . ' to ' . $valid_to;
        $html .= '</div>';

        $html .= '</div>'; // End main content area
        $html .= '</div>'; // End badge content

        return $html;
    }
    
    public function generate_back() {
        $html = '<div class="badge-content" style="width: 54mm; height: 85.6mm; padding: 0; background: linear-gradient(to bottom, #ffb499 0%, #fff 20%, #fff 80%, #ffb499 100%); font-family: system-ui, -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; position: relative; display: flex; flex-direction: column;">';
        
        // Log user data for debugging
        // $user_meta = $this->user_data;
        // $html .= '<pre>' . esc_html(print_r($user_meta, true)) . '</pre>';
        
        // Title
        $html .= '<div style="width: 100%; text-align: center; font-size: 10pt; font-weight: bold; color: #333; margin-bottom: 2mm; margin-top: 2mm">BENEFITS/CONCESSIONS</div>';
        
        // Instructions
        $html .= '<div style="flex: 1; padding: 0 4mm; font-size: 7pt; color: #333; line-height: 1.4;">';
        $html .= '<p style="margin: 1mm 0;">1. Eligible for Medical treatment in APSRTC Dispensaries and APSRTC Central Hospital.</p>';
        $html .= '<p style="margin: 1mm 0;">2. VTPM, Vijayawada on par with in-service employees.</p>';
        $html .= '<p style="margin: 1mm 0;">3. Other facilities under "The APSRTC Retired Employees Medical Facilities Scheme-2003".</p>';
        $html .= '<p style="margin: 1mm 0;">4. Eligible to stay in all APSRTC Guest Houses in AP not exceeding 3 Days at any place.</p>';
        $html .= '</div>';
        
        // QR Code and Signature
        $html .= '<div style="width: 100%; padding: 2mm; text-align: center;">';
        if (!empty($this->user_data['emp_barcode'])) {
            $html .= '<img src="http://smartidportal.justasolutionaway.com/wp-content/uploads/2025/02/qr.png" alt="QR Code" style="height: 9mm; width: auto; margin: 0 auto; display: block;">';
        }
        $html .= '<img src="http://smartidportal.justasolutionaway.com/wp-content/uploads/2025/02/sign.png" alt="Signature" style="height: 10mm; width: auto; margin: 0 auto; display: block;">';
        $html .= '<div style="font-size: 7pt; color: #333;">For MANAGING DIRECTOR<br>APSRTC, VIJAYAWADA</div>';
        $html .= '</div>';
        
        $html .= '</div>';
        return $html;
    }
}
