<?php
/**
 * Retired Travel Template Class
 */

namespace WpSmartBadge\Templates;

class RetiredTravelTemplate extends BadgeTemplate {
    public function __construct($user_data) {
        parent::__construct($user_data);
    }
    
    public function generate_front() {
        $html = '<div class="badge-content" style="width: 54mm; height: 85.6mm; padding: 0; background: linear-gradient(to bottom, #ffb499 0%, #fff 20%, #fff 80%, #ffb499 100%); font-family: system-ui, -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; position: relative; display: flex; flex-direction: column;">';
        
        // Header with logo and title
        $html .= '<div style="padding: 2mm; display: flex; align-items: center; gap: 2mm; justify-content: center;">';
        $html .= '<img src="http://smartidportal.justasolutionaway.com/wp-content/uploads/2025/02/png-transparent-guntur-vijayawada-bus-nellore-andhra-pradesh-state-road-transport-corporation-bus-removebg-preview-e1740051277257.png" alt="Logo" style="height: 8mm; width: auto;">';
        $html .= '<div style="flex: 1;">';
        $html .= '<div style="font-size: 16pt; font-weight: bold; color: #f60; text-align: center;">APSRTC</div>';
        $html .= '<div style="font-size: 8pt; color: #333; text-align: center;">RETIRED TRAVEL CARD</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Photo at top
        $html .= '<div style="width: 100%; display: flex; justify-content: center; padding: 0mm;">';
        $html .= '<div style="width: 15mm;height: 15mm;border: 1px solid #ccc;overflow: hidden;border-radius: 2mm;">';
        $html .= '<img src="http://smartidportal.justasolutionaway.com/wp-content/uploads/2025/02/4a83e065-8883-4c11-bc11-f1ff1d35dbb8-scaled.jpg" alt="Employee Photo" style="width: 100%; height: 100%; object-fit: cover;">';
        $html .= '</div>';
        $html .= '</div>';
        
        // Main content area with info
        $html .= '<div style="padding: 1mm; flex: 1;">';
        $html .= '<div style="display: flex; flex-direction: column; gap: 1.5mm;margin-bottom: 4mm">';

        // Name
        $html .= '<div class="info-row" style="display: flex; gap: 1mm;">';
        $html .= '<span class="label" style="color: #333; font-weight: 500; font-size: 7pt; min-width: 60px;">Name:</span>';
        $html .= '<span class="value" style="color: #333; font-size: 7pt;">' . esc_html($this->user_data['emp_full_name']) . '</span>';
        $html .= '</div>';
        
        // Staff No
        $html .= '<div class="info-row" style="display: flex; gap: 1mm;">';
        $html .= '<span class="label" style="color: #333; font-weight: 500; font-size: 7pt; min-width: 60px;">Staff No:</span>';
        $html .= '<span class="value" style="color: #333; font-size: 7pt;">' . esc_html($this->user_data['emp_id']) . '</span>';
        $html .= '</div>';
        
        // Designation
        if (!empty($this->user_data['emp_designation'])) {
            $html .= '<div class="info-row" style="display: flex; gap: 1mm;">';
            $html .= '<span class="label" style="color: #333; font-weight: 500; font-size: 7pt; min-width: 60px;">Designation:</span>';
            $html .= '<span class="value" style="color: #333; font-size: 7pt;">' . esc_html($this->user_data['emp_designation']) . '</span>';
            $html .= '</div>';
        }

        // Contact No
        if (!empty($this->user_data['emp_phone'])) {
            $html .= '<div class="info-row" style="display: flex; gap: 1mm;">';
            $html .= '<span class="label" style="color: #333; font-weight: 500; font-size: 7pt; min-width: 60px;">Contact No:</span>';
            $html .= '<span class="value" style="color: #333; font-size: 7pt;">' . esc_html($this->user_data['emp_phone']) . '</span>';
            $html .= '</div>';
        }
        
        $html .= '</div>'; // End staff info
        $html .= '</div>'; // End main content area
        
        // Footer with validity dates
        $html .= '<div style="padding: 2mm; font-size: 7pt; color: #333; text-align: center; background: rgba(255,255,255,0.8);">';
        $html .= 'Valid from: ' . date('d-m-Y') . ' to ' . date('d-m-Y', strtotime('+2 years'));
        $html .= '</div>';
        
        $html .= '</div>'; // End badge-content
        
        return $html;
    }
    
    public function generate_back() {
        $html = '<div class="badge-content" style="width: 54mm; height: 85.6mm; padding: 0; background: linear-gradient(to bottom, #ffb499 0%, #fff 20%, #fff 80%, #ffb499 100%); font-family: system-ui, -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; position: relative; display: flex; flex-direction: column;">';
        
        // Title
        $html .= '<div style="width: 100%; text-align: center; font-size: 10pt; font-weight: bold; color: #333; margin-bottom: 2mm; margin-top: 2mm">TRAVEL BENEFITS</div>';
        
        // Instructions
        $html .= '<div style="flex: 1; padding: 0 4mm; font-size: 7pt; color: #333; line-height: 1.4;">';
        $html .= '<p style="margin: 1mm 0;">1. Free travel in APSRTC buses within AP.</p>';
        $html .= '<p style="margin: 1mm 0;">2. Valid for travel in all types of services except AC services.</p>';
        $html .= '<p style="margin: 1mm 0;">3. Card holder should produce this card while traveling.</p>';
        $html .= '<p style="margin: 1mm 0;">4. This card is non-transferable.</p>';
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
