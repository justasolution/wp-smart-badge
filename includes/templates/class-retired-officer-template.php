<?php
namespace WpSmartBadge\Templates;

class RetiredOfficerTemplate extends BadgeTemplate {
    public function __construct($data) {
        parent::__construct($data);
    }
    
    protected function set_orientation() {
        return self::ORIENTATION_PORTRAIT;
    }
    
    protected function set_template_type() {
        return self::TYPE_RETIRED_OFFICER;
    }
    
    public function generate_front() {
        ob_start();
        ?>
        <div class="badge-front retired-officer">
            <div class="badge-header">
                <div class="company-logo">
                    <img src="<?php echo WP_SMART_BADGE_URL . 'assets/images/logo.png'; ?>" alt="Logo" class="logo">
                </div>
                <div class="badge-title">MEDICAL IDENTITY CARD</div>
                <div class="badge-subtitle">FOR RETIRED OFFICER</div>
            </div>
            
            <div class="photo-container">
                <div class="photo-frame">
                    <img src="<?php echo $this->get_photo_path(); ?>" alt="Officer Photo" class="officer-photo">
                </div>
            </div>
            
            <div class="details-grid">
                <div class="detail-row">
                    <div class="detail-label">Name:</div>
                    <div class="detail-value"><?php echo $this->get_user_meta('emp_full_name'); ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Staff No:</div>
                    <div class="detail-value"><?php echo $this->get_user_meta('emp_id'); ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Post Held:</div>
                    <div class="detail-value"><?php echo $this->get_user_meta('emp_designation'); ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Dt. of Retirement:</div>
                    <div class="detail-value"><?php echo $this->get_user_meta('retirement_date'); ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Place last worked:</div>
                    <div class="detail-value"><?php echo $this->get_user_meta('emp_department'); ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Contact No:</div>
                    <div class="detail-value"><?php echo $this->get_user_meta('emp_phone'); ?></div>
                </div>
            </div>
            
            <?php if ($this->needs_qr_code()): ?>
            <div class="qr-code">
                <?php echo $this->generate_qr_code($this->get_user_meta('emp_id')); ?>
            </div>
            <?php endif; ?>
        </div>
        <style>
        .badge-front.retired-officer {
            width: 85.6mm;
            height: 54mm;
            background: linear-gradient(135deg, #8B0000 0%, #A52A2A 100%);
            color: white;
            padding: 10px;
            border-radius: 10px;
            position: relative;
            font-family: 'Arial', sans-serif;
        }
        
        .badge-header {
            text-align: center;
            margin-bottom: 10px;
        }
        
        .company-logo {
            margin-bottom: 5px;
        }
        
        .logo {
            height: 30px;
            width: auto;
        }
        
        .badge-title {
            font-size: 14px;
            font-weight: bold;
            color: #FFD700;
        }
        
        .badge-subtitle {
            font-size: 12px;
            color: #FFD700;
        }
        
        .photo-container {
            text-align: center;
            margin: 10px 0;
        }
        
        .photo-frame {
            width: 70px;
            height: 70px;
            border-radius: 5px;
            overflow: hidden;
            margin: 0 auto;
            border: 2px solid #FFD700;
        }
        
        .officer-photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .details-grid {
            padding: 0 10px;
            font-size: 10px;
        }
        
        .detail-row {
            display: flex;
            margin: 2px 0;
        }
        
        .detail-label {
            flex: 1;
            font-weight: bold;
            color: #FFD700;
        }
        
        .detail-value {
            flex: 2;
        }
        
        .qr-code {
            text-align: center;
            margin: 5px 0;
        }
        
        .qr-code img {
            width: 40px;
            height: 40px;
        }
        </style>
        <?php
        return ob_get_clean();
    }
    
    public function generate_back() {
        ob_start();
        ?>
        <div class="badge-back retired-officer">
            <div class="benefits-section">
                <h3>BENEFITS/CONCESSIONS</h3>
                <ol>
                    <li>Eligible for Medical treatment in APSRTC Dispensaries and APSRTC Central Hospital VTPPM, Vijayawada on par with in-service employees.</li>
                    <li>Other facilities under "The APSRTC Retired Employees Medical Facilities Scheme-2003"</li>
                    <li>Eligible to stay in all APSRTC Guest Houses in AP, not exceeding 3 Days at any place, on par with in-service Officers as per the Existing Tariff</li>
                </ol>
            </div>
            
            <div class="validity-info">
                <div class="date-row">
                    <span class="date-label">Date of Issue:</span>
                    <span class="date-value"><?php echo date('d-m-Y'); ?></span>
                </div>
                <div class="date-row">
                    <span class="date-label">Valid upto:</span>
                    <span class="date-value"><?php echo date('d-m-Y', strtotime('+2 years')); ?></span>
                </div>
            </div>
            
            <div class="signature-section">
                <div class="signature-line"></div>
                <div class="signature-title">For MANAGING DIRECTOR</div>
                <div class="signature-location">APSRTC, VIJAYAWADA</div>
            </div>
        </div>
        <style>
        .badge-back.retired-officer {
            width: 85.6mm;
            height: 54mm;
            background: white;
            padding: 10px;
            border-radius: 10px;
            position: relative;
            font-family: 'Arial', sans-serif;
        }
        
        .benefits-section {
            padding: 5px;
        }
        
        .benefits-section h3 {
            font-size: 11px;
            margin: 0 0 5px 0;
            color: #8B0000;
            text-align: center;
        }
        
        .benefits-section ol {
            margin: 0;
            padding-left: 15px;
            font-size: 7px;
            color: #333;
        }
        
        .benefits-section li {
            margin: 2px 0;
            line-height: 1.2;
        }
        
        .validity-info {
            margin: 8px 0;
            padding: 0 5px;
            font-size: 8px;
        }
        
        .date-row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
        }
        
        .date-label {
            color: #8B0000;
            font-weight: bold;
        }
        
        .signature-section {
            text-align: center;
            margin-top: 10px;
            font-size: 8px;
        }
        
        .signature-line {
            width: 60%;
            margin: 10px auto;
            border-bottom: 1px solid #8B0000;
        }
        
        .signature-title {
            color: #8B0000;
            font-weight: bold;
        }
        
        .signature-location {
            color: #8B0000;
        }
        </style>
        <?php
        return ob_get_clean();
    }
}
