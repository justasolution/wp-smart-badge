<?php
if (!defined('ABSPATH')) exit;

// Localize script data
wp_localize_script('wp-smart-badge-admin', 'wpSmartBadge', array(
    'ajaxurl' => admin_url('admin-ajax.php'),
    'adminUrl' => admin_url(),
    'previewKey' => wp_create_nonce('badge_preview'),
    'nonce' => wp_create_nonce('wp_smart_badge_nonce'),
    'defaultAvatar' => plugins_url('assets/images/default-avatar.jpg', dirname(dirname(__FILE__)))
));
?>

<div class="wrap">
    <h1>Generate ID Badges</h1>
    
    <div class="badge-actions">
        <div class="left-actions">
            <button id="bulk_generate" class="button button-primary" disabled>
                Generate Selected Badges
            </button>
            
            <button type="button" class="button" onclick="document.getElementById('addUserModal').style.display='block';">
                Add New User
            </button>
            
            <form method="post" enctype="multipart/form-data" style="display: inline-block; margin-left: 10px;">
                <?php wp_nonce_field('import_users_csv', 'import_users_nonce'); ?>
                <input type="file" name="import_users_file" id="import_users_file" accept=".csv" style="display: none;" />
                <button type="button" class="button" onclick="document.getElementById('import_users_file').click();">
                    Import Users
                </button>
                <input type="submit" name="do_import_users" id="do_import_users" style="display: none;" />
            </form>
            
            <form method="post" style="display: inline-block; margin-left: 10px;">
                <?php wp_nonce_field('export_users_csv', 'export_users_nonce'); ?>
                <input type="hidden" name="selected_users" id="selectedUsersForExport" value="">
                <div class="button-group">
                    <input type="submit" name="export_users" class="button" value="Export All Users" />
                    <button type="submit" name="export_selected_users" id="exportSelectedUsers" class="button" disabled>Export Selected Users</button>
                </div>
            </form>

            <button id="refreshGrid" class="button button-secondary" style="margin-left: 10px;">
                Refresh Grid
            </button>
        </div>
        
        <div class="search-filter">
            <input type="text" id="quickFilter" placeholder="Quick search..." />
        </div>
    </div>
    
    <div id="userGrid" class="ag-theme-alpine"></div>
</div>

<style>
.badge-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 20px 0;
    gap: 20px;
}

.left-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.search-filter input {
    width: 300px;
    height: 30px;
    padding: 5px 10px;
}

#userGrid {
    width: 100%;
    height: 600px;
}

.template-select {
    height: 28px;
    margin-right: 5px;
    min-width: 120px;
}

.button.button-small {
    padding: 0 10px;
    height: 28px;
    line-height: 26px;
}

/* AG Grid Theme Customizations */
.ag-theme-alpine {
    --ag-font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    --ag-font-size: 13px;
    --ag-selected-row-background-color: rgba(0, 115, 170, 0.1);
    --ag-row-hover-color: rgba(0, 115, 170, 0.05);
    --ag-header-background-color: #f8f9fa;
    --ag-odd-row-background-color: #ffffff;
}

.ag-header-cell-label {
    font-weight: 600;
}

.ag-row-hover {
    background-color: var(--ag-row-hover-color) !important;
}

.ag-row-selected {
    background-color: var(--ag-selected-row-background-color) !important;
}

/* Add loading indicator styles */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.loading-spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.4);
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 600px;
    border-radius: 4px;
    max-height: 80vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.modal-header h2 {
    margin: 0;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

.form-row {
    margin-bottom: 15px;
}

.form-row label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}

.form-row input,
.form-row select {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.form-actions {
    margin-top: 20px;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.preview-container {
    position: relative;
    width: 150px;
    height: 150px;
    margin: 0 auto 15px;
    cursor: pointer;
}

.preview-container img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #ddd;
}

.preview-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s;
}

.preview-container:hover .preview-overlay {
    opacity: 1;
}

.preview-overlay .dashicons {
    color: white;
    font-size: 24px;
    width: 24px;
    height: 24px;
}

.upload-buttons {
    display: flex;
    gap: 10px;
    justify-content: center;
}

.upload-buttons .button {
    display: flex;
    align-items: center;
    gap: 5px;
}

.upload-buttons .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
}

/* Profile Picture Cell Styles */
.profile-pic-cell {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
    padding: 5px;
}

.profile-pic-preview {
    position: relative;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
}

.profile-pic-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-pic-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s;
}

.profile-pic-preview:hover .profile-pic-overlay {
    opacity: 1;
}

.edit-photo-btn {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    padding: 0;
}

.edit-photo-btn .dashicons {
    width: 20px;
    height: 20px;
    font-size: 20px;
}

/* Photo Edit Modal Styles */
.photo-edit-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
    padding: 20px;
}

/* Camera Modal Styles */
.camera-modal-content {
    max-width: 800px;
    padding: 20px;
}

.camera-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
}

#camera {
    width: 100%;
    max-width: 640px;
    height: auto;
    background: #000;
}

.camera-controls {
    display: flex;
    gap: 10px;
    justify-content: center;
    margin-top: 15px;
}

.camera-controls .button {
    display: flex;
    align-items: center;
    gap: 5px;
}

#photoCanvas {
    max-width: 640px;
    width: 100%;
    height: auto;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 8px;
    align-items: center;
}

.edit-user-btn, .preview-badge-btn {
    display: flex;
    align-items: center;
    gap: 4px;
}

.edit-user-btn .dashicons, .preview-badge-btn .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}

/* Edit User Modal */
#editUserModal .modal-content {
    max-width: 800px;
    width: 90%;
}

#editUserModal .form-row {
    margin-bottom: 15px;
}

#editUserModal label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}

#editUserModal input[type="text"],
#editUserModal input[type="email"],
#editUserModal input[type="tel"],
#editUserModal select {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

#editUserModal .form-actions {
    margin-top: 20px;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}
</style>

<!-- Loading overlay -->
<div id="loadingOverlay" class="loading-overlay">
    <div class="loading-spinner"></div>
</div>

<!-- Add User Modal -->
<div id="addUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New User</h2>
            <span class="close" onclick="document.getElementById('addUserModal').style.display='none';">&times;</span>
        </div>
        <form id="addUserForm">
            <?php wp_nonce_field('add_user_nonce', 'add_user_nonce'); ?>
            
            <div class="form-row">
                <label for="emp_id">Employee ID*</label>
                <input type="text" id="emp_id" name="emp_id" required>
            </div>
            
            <div class="form-row">
                <label for="user_email">Email*</label>
                <input type="email" id="user_email" name="user_email" required>
            </div>
            
            <div class="form-row">
                <label for="first_name">First Name*</label>
                <input type="text" id="first_name" name="first_name" required>
            </div>
            
            <div class="form-row">
                <label for="last_name">Last Name*</label>
                <input type="text" id="last_name" name="last_name" required>
            </div>
            <div class="form-row">
                <label for="emp_phone">Phone</label>
                <input type="tel" id="emp_phone" name="emp_phone">
            </div>
            <div class="form-row">
                <label for="emp_blood_group">Blood Group</label>
                <select id="emp_blood_group" name="emp_blood_group">
                    <option value="">Select Blood Group</option>
                    <option value="A+">A+</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B-">B-</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>
                    <option value="AB+">AB+</option>
                    <option value="AB-">AB-</option>
                </select>
            </div>
            <div class="form-row">
                <label for="emp_cfms_id">CFMS ID</label>
                <input type="text" id="emp_cfms_id" name="emp_cfms_id">
            </div>
            <div class="form-row">
                <label for="emp_hrms_id">HRMS ID</label>
                <input type="text" id="emp_hrms_id" name="emp_hrms_id">
            </div>
            <div class="form-row">
                <label for="emp_emergency_contact">Emergency Contact</label>
                <input type="tel" id="emp_emergency_contact" name="emp_emergency_contact">
            </div>
            <div class="form-row">
                <label for="emp_ehs_card">EHS Card</label>
                <input type="text" id="emp_ehs_card" name="emp_ehs_card">
            </div>
            <div class="form-row">
                <label for="emp_designation">Designation</label>
                <input type="text" id="emp_designation" name="emp_designation">
            </div>

            <div class="form-row">
                <label for="emp_department">Department</label>
                <input type="text" id="emp_department" name="emp_department">
            </div>

            <div class="form-row">
                <label for="emp_barcode">QR/Barcode</label>
                <input type="text" id="emp_barcode" name="emp_barcode">
            </div>

            <div class="form-row">
                <label for="emp_depot_location">Depot Location</label>
                <input type="text" id="emp_depot_location" name="emp_depot_location">
            </div>

            <div class="form-row">
                <label for="emp_last_working">Last Working Place</label>
                <input type="text" id="emp_last_working" name="emp_last_working">
            </div>

            <div class="form-row">
                <label for="emp_residential_address">Residential Address</label>
                <textarea id="emp_residential_address" name="emp_residential_address" rows="3"></textarea>
            </div>

            <div class="form-row">
                <label for="emp_status">Status</label>
                <select id="emp_status" name="emp_status">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="retired">Retired</option>
                </select>
            </div>

            <div class="form-row">
                <label for="emp_photo">Profile Picture</label>
                <div class="photo-upload-container">
                    <div class="preview-container">
                        <img id="photoPreview" src="<?php echo plugins_url('assets/images/default-avatar.jpg', dirname(dirname(__FILE__))); ?>" alt="Profile Preview">
                        <div class="preview-overlay">
                            <i class="dashicons dashicons-camera"></i>
                        </div>
                    </div>
                    <input type="hidden" id="emp_photo_data" name="emp_photo_data">
                    <div class="upload-buttons">
                        <button type="button" class="button" id="takePhotoBtn">
                            <i class="dashicons dashicons-camera"></i> Take Photo
                        </button>
                        <button type="button" class="button" id="uploadPhotoBtn">
                            <i class="dashicons dashicons-upload"></i> Upload Photo
                        </button>
                    </div>
                </div>
            </div>

            <!-- MetaBox fields container -->
            <div id="metabox-fields"></div>

            <div class="form-actions">
                <button type="submit" class="button button-primary">Add User</button>
                <button type="button" class="button" onclick="document.getElementById('addUserModal').style.display='none';">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.4);
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 600px;
    max-height: 80vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.close {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.form-row {
    margin-bottom: 15px;
}

.form-row label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}

.form-row input,
.form-row select {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.photo-upload-container {
    text-align: center;
}

.preview-container {
    position: relative;
    display: inline-block;
    margin-bottom: 10px;
}

.preview-container img {
    max-width: 150px;
    height: 100%;
    border-radius: 50%;
    object-fit: contain;
}

.preview-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s;
}

.preview-container:hover .preview-overlay {
    opacity: 1;
}

.preview-overlay i {
    color: white;
    font-size: 24px;
}

.upload-buttons {
    margin-top: 10px;
}

.upload-buttons button {
    margin: 0 5px;
}

.form-actions {
    margin-top: 20px;
    text-align: right;
}

.form-actions button {
    margin-left: 10px;
}
</style>

<!-- Camera Modal -->
<div id="cameraModal" class="modal">
    <div class="modal-content camera-modal-content">
        <div class="modal-header">
            <h2>Take Photo</h2>
            <span class="close">&times;</span>
        </div>
        <div class="camera-container">
            <video id="camera" autoplay playsinline></video>
            <canvas id="photoCanvas" style="display: none;"></canvas>
            <div class="camera-controls">
                <button type="button" class="button button-primary" id="captureBtn">
                    <i class="dashicons dashicons-camera"></i> Capture
                </button>
                <button type="button" class="button" id="retakeBtn" style="display: none;">
                    <i class="dashicons dashicons-controls-repeat"></i> Retake
                </button>
                <button type="button" class="button button-primary" id="savePhotoBtn" style="display: none;">
                    <i class="dashicons dashicons-yes"></i> Use Photo
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('import_users_file').addEventListener('change', function() {
    if (this.files.length > 0) {
        document.getElementById('do_import_users').click();
    }
});
</script>
