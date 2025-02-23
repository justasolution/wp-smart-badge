<?php
/**
 * Badge Template Base Class
 * 
 * This class serves as the base for all ID card templates
 */

namespace WpSmartBadge\Templates;

/**
 * Abstract base class for badge templates
 */
abstract class BadgeTemplate {
    // CR80 card dimensions in millimeters
    const CARD_WIDTH = 54.00;  // Width for portrait orientation
    const CARD_HEIGHT = 85.60; // Height for portrait orientation
    
    // Template types
    const TYPE_CLASS_3_4 = 'class_3_4';
    const TYPE_CLASS_2 = 'class_2';
    const TYPE_CLASS_1 = 'class_1';
    const TYPE_RETIRED_TRAVEL = 'retired_travel';
    const TYPE_RETIRED_MEDICAL = 'retired_medical';
    const TYPE_RETIRED_SPOUSE = 'retired_spouse';
    const TYPE_RETIRED_OFFICER = 'retired_officer';
    
    // Orientation
    const ORIENTATION_PORTRAIT = 'P';
    const ORIENTATION_LANDSCAPE = 'L';
    
    protected $user_data;
    protected $orientation;
    protected $template_type;
    
    public function __construct($user_data) {
        $this->user_data = $user_data;
        $this->set_orientation();
        $this->set_template_type();
    }
    
    abstract protected function set_orientation();
    abstract protected function set_template_type();
    
    /**
     * Generate the front side of the badge
     *
     * @return string HTML content for the front side
     */
    abstract public function generate_front();
    
    /**
     * Generate the back side of the badge
     *
     * @return string HTML content for the back side
     */
    abstract public function generate_back();
    
    /**
     * Get user meta data
     *
     * @param string $key Meta key
     * @return string Meta value
     */
    protected function get_user_meta($key) {
        return isset($this->user_data[$key]) ? $this->user_data[$key] : '';
    }
    
    /**
     * Get photo path for the badge
     *
     * @return string Photo URL
     */
    protected function get_photo_path() {
        $photo_path = get_user_meta($this->user_data['ID'], 'emp_photo', true);
        return !empty($photo_path) ? $photo_path : WP_SMART_BADGE_URL . 'assets/images/default-photo.png';
    }
    
    /**
     * Check if QR code is needed for this badge
     *
     * @return boolean True if QR code should be displayed
     */
    protected function needs_qr_code() {
        return true; // By default, all badges have QR codes
    }
    
    /**
     * Generate QR code for the badge
     *
     * @param string $data Data to encode in QR code
     * @return string HTML for QR code image
     */
    protected function generate_qr_code($data) {
        if (empty($data)) {
            return '';
        }
        
        // Include QR code library if not already included
        if (!class_exists('QRcode')) {
            require_once WP_SMART_BADGE_PATH . 'includes/libraries/phpqrcode/qrlib.php';
        }
        
        // Generate unique filename for this QR code
        $filename = 'qr-' . md5($data) . '.png';
        $qr_path = WP_SMART_BADGE_PATH . 'assets/qrcodes/';
        $qr_url = WP_SMART_BADGE_URL . 'assets/qrcodes/';
        
        // Create QR codes directory if it doesn't exist
        if (!file_exists($qr_path)) {
            wp_mkdir_p($qr_path);
        }
        
        // Generate QR code if it doesn't exist
        if (!file_exists($qr_path . $filename)) {
            \QRcode::png($data, $qr_path . $filename, QR_ECLEVEL_L, 3, 2);
        }
        
        return '<img src="http://smartidportal.justasolutionaway.com/wp-content/uploads/2025/02/qr.png" alt="QR Code" class="qr-code-img">';
    }
    
    protected function generate_barcode($data) {
        // Implementation for barcode generation
        // Will be implemented later
        return '';
    }
    
    /**
     * Generate a preview of the badge
     *
     * @return string HTML content for preview
     */
    public function generate_preview() {
        ob_start();
        ?>
        <div class="badge-preview-content" style="max-width: 600px; margin: 0 auto;">
            <div class="badge-front">
                <?php echo $this->generate_front(); ?>
            </div>
            <?php if (method_exists($this, 'generate_back')): ?>
            <div class="badge-back" style="margin-top: 20px;">
                <?php echo $this->generate_back(); ?>
            </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

}
