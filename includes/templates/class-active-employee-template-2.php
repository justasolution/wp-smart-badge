<?php
namespace WpSmartBadge\Templates;

use WpSmartBadge\Badge_Template;

class ActiveEmployeeTemplate2 extends Badge_Template {
    protected $user_data;
    
    public function __construct($data) {
        $this->user_data = $data;
    }
    
    public function generate_front() {
        $html = '<div class="badge-content" style="width: 85.6mm; height: 54mm; padding: 0; background: linear-gradient(to bottom, #ffb499 0%, #fff 20%, #fff 80%, #ffb499 100%); font-family: system-ui, -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; position: relative; display: flex;">';
        
        // Right side photo
        $html .= '<div style="flex: 0 0 30%; padding: 4mm; display: flex; flex-direction: column; align-items: center; justify-content: center;">';
        if (!empty($this->user_data['emp_photo'])) {
            $html .= '<img src="' . esc_url($this->user_data['emp_photo']) . '" alt="Employee Photo" style="width: 25mm; height: 30mm; object-fit: cover; border: 1px solid #ddd;">';
        }
        $html .= '<img src="http://smartidportal.justasolutionaway.com/wp-content/uploads/2025/02/qr.png" alt="QR Code" style="width: 15mm; height: 15mm; margin-top: 2mm;">';
        $html .= '</div>';
        
        // Left side information
        $html .= '<div style="flex: 1; padding: 4mm; display: flex; flex-direction: column; justify-content: center;">';
        
        // Logo and title
        $html .= '<div style="text-align: center; margin-bottom: 2mm;">';
        $html .= '<img src="http://smartidportal.justasolutionaway.com/wp-content/uploads/2025/02/png-transparent-guntur-vijayawada-bus-nellore-andhra-pradesh-state-road-transport-corporation-bus-removebg-preview-e1740051277257.png" alt="Logo" style="height: 8mm; width: auto;">';
        $html .= '</div>';
        
        // Employee details
        $html .= '<div style="font-size: 7pt; color: #333;">';
        $html .= '<p style="margin: 1mm 0;"><strong>ID:</strong> ' . esc_html($this->user_data['emp_id']) . '</p>';
        $html .= '<p style="margin: 1mm 0;"><strong>Name:</strong> ' . esc_html($this->user_data['emp_full_name']) . '</p>';
        $html .= '<p style="margin: 1mm 0;"><strong>Designation:</strong> ' . esc_html($this->user_data['emp_designation']) . '</p>';
        $html .= '<p style="margin: 1mm 0;"><strong>Department:</strong> ' . esc_html($this->user_data['emp_department']) . '</p>';
        if (!empty($this->user_data['emp_blood_group'])) {
            $html .= '<p style="margin: 1mm 0;"><strong>Blood Group:</strong> ' . esc_html($this->user_data['emp_blood_group']) . '</p>';
        }
        $html .= '</div>';
        
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }
    
    public function generate_back() {
        $html = '<div class="badge-content" style="width: 85.6mm; height: 54mm; padding: 0; background: linear-gradient(to bottom, #ffb499 0%, #fff 20%, #fff 80%, #ffb499 100%); font-family: system-ui, -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; position: relative; display: flex; flex-direction: column;">';
        
        // Title
        $html .= '<div style="width: 100%; text-align: center; font-size: 10pt; font-weight: bold; color: #333; margin-bottom: 2mm; margin-top: 2mm">IMPORTANT NOTICE</div>';
        
        // Instructions
        $html .= '<div style="flex: 1; padding: 0 4mm; font-size: 7pt; color: #333; line-height: 1.4;">';
        $html .= '<p style="margin: 1mm 0;">1. This card must be carried during work hours.</p>';
        $html .= '<p style="margin: 1mm 0;">2. Report loss/damage to HR immediately.</p>';
        $html .= '<p style="margin: 1mm 0;">3. This card is non-transferable.</p>';
        $html .= '<p style="margin: 1mm 0;">4. Return card upon leaving the organization.</p>';
        $html .= '</div>';
        
        // QR Code and Signature
        $html .= '<div style="width: 100%; padding: 2mm; text-align: center;">';
        $html .= '<img src="http://smartidportal.justasolutionaway.com/wp-content/uploads/2025/02/qr.png" alt="QR Code" style="height: 9mm; width: auto; margin: 0 auto; display: block;">';
        $html .= '<img src="http://smartidportal.justasolutionaway.com/wp-content/uploads/2025/02/sign.png" alt="Signature" style="height: 10mm; width: auto; margin: 0 auto; display: block;">';
        $html .= '<div style="font-size: 7pt; color: #333;">For MANAGING DIRECTOR<br>APSRTC, VIJAYAWADA</div>';
        $html .= '</div>';
        
        $html .= '</div>';
        return $html;
    }
}
