<?php
namespace WpSmartBadge\Templates;

use WpSmartBadge\Badge_Template;

/**
 * Vertical card template implementation
 */
class VerticalCardTemplate extends Badge_Template {
    protected function set_orientation() {
        $this->orientation = self::ORIENTATION_PORTRAIT;
    }
    
    protected function set_template_type() {
        $this->template_type = 'vertical_card';
    }
    
    public function generate_front() {
        $html = '<div class="badge-front vertical-card">';
        
        // Header with Logo
        $html .= '<div class="header">';
        $html .= '<img src="' . plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/images/logo.png" alt="Logo" class="logo">';
        $html .= '<h1>APSRTC</h1>';
        $html .= '</div>';
        
        // Photo
        $html .= '<div class="photo">';
        $html .= '<img src="' . esc_url($this->get_user_meta('emp_photo')) . '" alt="Employee Photo">';
        $html .= '</div>';
        
        // Employee Details
        $html .= '<div class="details">';
        $html .= '<h2>' . esc_html($this->get_user_meta('emp_full_name')) . '</h2>';
        
        // Two-column layout for details
        $html .= '<div class="details-grid">';
        $html .= '<div class="detail-item"><span class="label">RTC Staff No:</span><span class="value">' . esc_html($this->get_user_meta('emp_id')) . '</span></div>';
        $html .= '<div class="detail-item"><span class="label">CFMS ID:</span><span class="value">' . esc_html($this->get_user_meta('emp_cfms_id')) . '</span></div>';
        $html .= '<div class="detail-item"><span class="label">HRMS ID:</span><span class="value">' . esc_html($this->get_user_meta('emp_hrms_id')) . '</span></div>';
        $html .= '<div class="detail-item"><span class="label">Designation:</span><span class="value">' . esc_html($this->get_user_meta('emp_designation')) . '</span></div>';
        $html .= '<div class="detail-item"><span class="label">Status:</span><span class="value">' . esc_html($this->get_user_meta('emp_status')) . '</span></div>';
        $html .= '<div class="detail-item"><span class="label">Blood Group:</span><span class="value">' . esc_html($this->get_user_meta('emp_blood_group')) . '</span></div>';
        $html .= '<div class="detail-item"><span class="label">Mobile:</span><span class="value">' . esc_html($this->get_user_meta('emp_phone')) . '</span></div>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        $html .= '</div>';
        
        return $html;
    }
    
    public function generate_back() {
        $html = '<div class="badge-back vertical-card">';
        
        // Emergency Contact
        $html .= '<div class="section emergency-contact">';
        $html .= '<h3>Emergency Contact</h3>';
        $html .= '<p>' . esc_html($this->get_user_meta('emp_emergency_contact')) . '</p>';
        $html .= '</div>';
        
        // EHS Card
        $html .= '<div class="section ehs-info">';
        $html .= '<h3>EHS Information</h3>';
        $html .= '<p>Card No: ' . esc_html($this->get_user_meta('emp_ehs_card')) . '</p>';
        $html .= '</div>';
        
        // QR Code
        $html .= '<div class="section qr-code">';
        $html .= '<h3>Scan QR Code</h3>';
        $html .= $this->generate_qr_code($this->get_user_meta('emp_id'));
        $html .= '</div>';
        
        // Validity
        $html .= '<div class="section validity">';
        $html .= '<p class="valid-until">Valid Until: ' . esc_html($this->get_user_meta('emp_valid_until')) . '</p>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        return $html;
    }
}
