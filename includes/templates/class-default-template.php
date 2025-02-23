<?php
namespace WpSmartBadge\Templates;

use WpSmartBadge\Badge_Template;

class Default_Template extends Badge_Template {
    private $data;
    
    public function __construct($data) {
        $this->data = $data;
    }
    
    public function needs_qr_code() {
        return true;
    }
    
    public function generate_front() {
        ob_start();
        ?>
        <style>
            .badge-front, .badge-back {
                width: 54mm;
                height: 85.6mm;
                padding: 5mm;
                box-sizing: border-box;
                font-family: Arial, sans-serif;
            }
            .badge-header {
                text-align: center;
                margin-bottom: 10px;
            }
            .logo {
                max-width: 60px;
                height: auto;
                margin-bottom: 5px;
            }
            .badge-header h1 {
                font-size: 16px;
                margin: 5px 0;
                color: #333;
            }
            .badge-header h2 {
                font-size: 12px;
                margin: 5px 0;
                color: #666;
            }
            .photo {
                width: 25mm;
                height: 30mm;
                border: 1px solid #ddd;
                margin: 5px auto;
                overflow: hidden;
            }
            .photo img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            .photo-placeholder {
                width: 100%;
                height: 100%;
                background: #f5f5f5;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #999;
            }
            .details {
                margin: 10px 0;
                font-size: 10px;
            }
            .details p {
                margin: 3px 0;
            }
            .qr-code {
                text-align: center;
                margin-top: 5px;
            }
            .qr-code img {
                width: 20mm;
                height: 20mm;
            }
        </style>
        <div class="badge-front">
            <div class="badge-header">
                <img src="<?php echo plugins_url('assets/images/logo.png', WP_SMART_BADGE_FILE); ?>" alt="Logo" class="logo">
                <h1>Employee ID Card</h1>
                <h2><?php echo esc_html($this->data['emp_department']); ?></h2>
            </div>
            
            <div class="badge-content">
                <div class="photo">
                    <?php if (!empty($this->data['emp_photo'])): ?>
                        <img src="http://smartidportal.justasolutionaway.com/wp-content/uploads/2025/02/4a83e065-8883-4c11-bc11-f1ff1d35dbb8-scaled.jpg" alt="Employee Photo">
                    <?php else: ?>
                        <div class="photo-placeholder">Photo</div>
                    <?php endif; ?>
                </div>
                
                <div class="details">
                    <p><strong>Name:</strong> <?php echo esc_html($this->data['emp_full_name']); ?></p>
                    <p><strong>Employee ID:</strong> <?php echo esc_html($this->data['emp_id']); ?></p>
                    <p><strong>Designation:</strong> <?php echo esc_html($this->data['emp_designation']); ?></p>
                    <?php if (!empty($this->data['emp_department'])): ?>
                        <p><strong>Department:</strong> <?php echo esc_html($this->data['emp_department']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($this->data['emp_blood_group'])): ?>
                        <p><strong>Blood Group:</strong> <?php echo esc_html($this->data['emp_blood_group']); ?></p>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($this->data['qr_code'])): ?>
                    <div class="qr-code">
                        <img src="http://smartidportal.justasolutionaway.com/wp-content/uploads/2025/02/qr.png" alt="QR Code">
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function generate_back() {
        $html = '<div class="badge-content" style="width: 54mm; height: 85.6mm; padding: 0; background: linear-gradient(to bottom, #ffb499 0%, #fff 20%, #fff 80%, #ffb499 100%); font-family: system-ui, -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; position: relative; display: flex; flex-direction: column;">';
        
        // Title
        $html .= '<div style="width: 100%; text-align: center; font-size: 10pt; font-weight: bold; color: #333; margin-bottom: 2mm; margin-top: 2mm">CONTACT INFORMATION</div>';
        
        // Contact Info
        $html .= '<div style="flex: 1; padding: 0 4mm; font-size: 7pt; color: #333; line-height: 1.4;">';
        $html .= '<p style="margin: 2mm 0;"><strong>Email:</strong> ' . esc_html($this->data['user_email']) . '</p>';
        if (!empty($this->data['emp_phone'])) {
            $html .= '<p style="margin: 2mm 0;"><strong>Phone:</strong> ' . esc_html($this->data['emp_phone']) . '</p>';
        }
        if (!empty($this->data['emp_cfms_id'])) {
            $html .= '<p style="margin: 2mm 0;"><strong>CFMS ID:</strong> ' . esc_html($this->data['emp_cfms_id']) . '</p>';
        }
        if (!empty($this->data['emp_hrms_id'])) {
            $html .= '<p style="margin: 2mm 0;"><strong>HRMS ID:</strong> ' . esc_html($this->data['emp_hrms_id']) . '</p>';
        }
        $html .= '</div>';
        
        // Validity and Note
        $html .= '<div style="width: 100%; padding: 2mm; text-align: center;">';
        if (!empty($this->data['emp_valid_until'])) {
            $html .= '<p style="margin: 1mm 0;"><strong>Valid Until:</strong> ' . esc_html($this->data['emp_valid_until']) . '</p>';
        }
        $html .= '<p style="margin: 1mm 0; font-size: 6pt; color: #666; font-style: italic;">This card is non-transferable and must be shown on demand</p>';
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
