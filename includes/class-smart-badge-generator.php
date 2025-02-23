<?php
require_once(ABSPATH . 'wp-admin/includes/file.php');

class WP_Smart_Badge_Generator {
    private $user_id;
    private $user_data;
    private $pdf;
    
    public function __construct($user_id) {
        $this->user_id = $user_id;
        $this->load_user_data();
        $this->init_pdf();
    }
    
    private function load_user_data() {
        $user = get_userdata($this->user_id);
        if (!$user) {
            throw new Exception('User not found');
        }
        
        $this->user_data = array(
            'ID' => $user->ID,
            'emp_id' => get_user_meta($user->ID, 'emp_id', true),
            'emp_full_name' => get_user_meta($user->ID, 'emp_full_name', true),
            'emp_designation' => get_user_meta($user->ID, 'emp_designation', true),
            'emp_department' => get_user_meta($user->ID, 'emp_department', true),
            'emp_phone' => get_user_meta($user->ID, 'emp_phone', true),
            'emp_blood_group' => get_user_meta($user->ID, 'emp_blood_group', true),
            'emp_cfms_id' => get_user_meta($user->ID, 'emp_cfms_id', true),
            'emp_hrms_id' => get_user_meta($user->ID, 'emp_hrms_id', true),
            'emp_emergency_contact' => get_user_meta($user->ID, 'emp_emergency_contact', true),
            'emp_status' => get_user_meta($user->ID, 'emp_status', true),
            'photo_url' => get_user_meta($user->ID, 'emp_photo', true)
        );
    }
    
    private function init_pdf() {
        if (!class_exists('TCPDF')) {
            require_once WP_SMART_BADGE_PATH . 'includes/tcpdf/tcpdf.php';
        }
        
        // Create new PDF document (CR80 card size: 85.6mm x 54mm)
        $this->pdf = new TCPDF('L', 'mm', array(54, 85.6), true, 'UTF-8', false);
        
        // Set document information
        $this->pdf->SetCreator('WP Smart Badge');
        $this->pdf->SetAuthor('WP Smart Badge');
        $this->pdf->SetTitle('ID Badge - ' . $this->user_data['emp_full_name']);
        
        // Remove default header/footer
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);
        
        // Set margins
        $this->pdf->SetMargins(3, 3, 3);
        
        // Set auto page breaks
        $this->pdf->SetAutoPageBreak(false, 0);
        
        // Set image scale factor
        $this->pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        
        // Set font
        $this->pdf->SetFont('helvetica', '', 8);
        
        // Add a page
        $this->pdf->AddPage();
    }
    
    public function generate() {
        try {
            // Add organization logo
            $logo_path = WP_SMART_BADGE_PATH . 'assets/images/logo.png';
            if (file_exists($logo_path)) {
                $this->pdf->Image($logo_path, 5, 5, 15, 0, 'PNG');
            }
            
            // Add smart card chip design
            $chip_path = WP_SMART_BADGE_PATH . 'assets/images/chip.png';
            if (file_exists($chip_path)) {
                $this->pdf->Image($chip_path, 65, 5, 15, 0, 'PNG');
            }
            
            // Add user photo
            if (!empty($this->user_data['photo_url'])) {
                $photo_path = get_attached_file($this->user_data['photo_url']);
                if ($photo_path && file_exists($photo_path)) {
                    $this->pdf->Image($photo_path, 5, 15, 20, 25);
                }
            }
            
            // Add user information
            $this->pdf->SetXY(30, 15);
            $this->pdf->SetFont('helvetica', 'B', 10);
            $this->pdf->Cell(0, 5, $this->user_data['emp_full_name'], 0, 1);
            
            $this->pdf->SetXY(30, 20);
            $this->pdf->SetFont('helvetica', '', 8);
            $this->pdf->Cell(0, 5, $this->user_data['emp_designation'], 0, 1);
            
            $this->pdf->SetXY(30, 25);
            $this->pdf->Cell(0, 5, $this->user_data['emp_department'], 0, 1);
            
            $this->pdf->SetXY(30, 30);
            $this->pdf->Cell(0, 5, 'ID: ' . $this->user_data['emp_id'], 0, 1);
            
            $this->pdf->SetXY(30, 35);
            $this->pdf->Cell(0, 5, 'Phone: ' . $this->user_data['emp_phone'], 0, 1);
            
            // Add QR code
            if (!class_exists('QRcode')) {
                require_once WP_SMART_BADGE_PATH . 'includes/phpqrcode/qrlib.php';
            }
            
            $qr_data = json_encode(array(
                'id' => $this->user_data['emp_id'],
                'name' => $this->user_data['emp_full_name'],
                'dept' => $this->user_data['emp_department']
            ));
            
            $qr_file = wp_upload_dir()['path'] . '/qr_' . $this->user_id . '.png';
            QRcode::png($qr_data, $qr_file, QR_ECLEVEL_L, 3);
            
            if (file_exists($qr_file)) {
                $this->pdf->Image($qr_file, 65, 25, 15, 15, 'PNG');
                unlink($qr_file); // Clean up QR code file
            }
            
            // Save PDF
            $upload_dir = wp_upload_dir();
            $badge_dir = $upload_dir['basedir'] . '/smart-badge';
            if (!file_exists($badge_dir)) {
                wp_mkdir_p($badge_dir);
            }
            
            $file_path = $badge_dir . '/badge_' . $this->user_id . '.pdf';
            $this->pdf->Output($file_path, 'F');
            
            return array(
                'file_path' => $file_path,
                'file_url' => str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $file_path)
            );
            
        } catch (Exception $e) {
            throw new Exception('Failed to generate badge: ' . $e->getMessage());
        }
    }
}
