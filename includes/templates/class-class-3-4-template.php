<?php
/**
 * Template for Class III & IV Employees
 */

namespace WpSmartBadge\Templates;

class Class_3_4_Template extends BadgeTemplate {
    protected function set_orientation() {
        $this->orientation = self::ORIENTATION_PORTRAIT;
    }
    
    protected function set_template_type() {
        $this->template_type = self::TYPE_CLASS_3_4;
    }
    
    public function generate_front() {
        $html = '<div class="badge-front">';
        
        // Photo
        $html .= '<div class="photo">';
        $html .= '<img src="http://smartidportal.justasolutionaway.com/wp-content/uploads/2025/02/4a83e065-8883-4c11-bc11-f1ff1d35dbb8-scaled.jpg" alt="Employee Photo">';
        $html .= '</div>';
        
        // Employee Details
        $html .= '<div class="details">';
        $html .= '<h2>' . esc_html($this->get_user_meta('emp_full_name')) . '</h2>';
        $html .= '<p>RTC Staff No: ' . esc_html($this->get_user_meta('emp_id')) . '</p>';
        $html .= '<p>CFMS ID: ' . esc_html($this->get_user_meta('emp_cfms_id')) . '</p>';
        $html .= '<p>HRMS ID: ' . esc_html($this->get_user_meta('emp_hrms_id')) . '</p>';
        $html .= '<p>Designation: ' . esc_html($this->get_user_meta('emp_designation')) . '</p>';
        $html .= '<p>Status: ' . esc_html($this->get_user_meta('emp_status')) . '</p>';
        $html .= '<p>Blood Group: ' . esc_html($this->get_user_meta('emp_blood_group')) . '</p>';
        $html .= '<p>Mobile: ' . esc_html($this->get_user_meta('emp_phone')) . '</p>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        return $html;
    }
    
    public function generate_back() {
        $html = '<div class="badge-back">';
        
        // Emergency Contact
        $html .= '<div class="emergency-contact">';
        $html .= '<h3>Emergency Contact</h3>';
        $html .= '<p>' . esc_html($this->get_user_meta('emp_emergency_contact')) . '</p>';
        $html .= '</div>';
        
        // EHS Card
        $html .= '<div class="ehs-info">';
        $html .= '<p>EHS Card No: ' . esc_html($this->get_user_meta('emp_ehs_card')) . '</p>';
        $html .= '</div>';
        
        // QR Code
        $html .= '<div class="qr-code">';
        $html .= $this->generate_qr_code($this->get_user_meta('emp_id'));
        $html .= '</div>';
        
        // Validity
        $html .= '<div class="validity">';
        $html .= '<p>Valid Until: ' . esc_html($this->get_user_meta('emp_valid_until')) . '</p>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        return $html;
    }
}
