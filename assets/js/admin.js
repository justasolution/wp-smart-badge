jQuery(document).ready(function($) {
    // Initialize AG Grid
    const columnDefs = [
        {
            headerName: 'Select',
            field: 'checkbox',
            checkboxSelection: true,
            headerCheckboxSelection: true,
            width: 40,
            pinned: 'left',
            lockPosition: true
        },
        {
            field: 'emp_photo',
            headerName: 'Photo',
            width: 80,
            cellRenderer: function(params) {
                const defaultAvatar = wpSmartBadge.pluginUrl + '/assets/images/default-avatar.jpg';
                const imageUrl = params.value || defaultAvatar;
                return `<img src="${imageUrl}" alt="Profile" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">`;
            }
        },
        { field: 'emp_id', headerName: 'Employee ID', filter: 'agTextColumnFilter' },
        { field: 'emp_full_name', headerName: 'Full Name', filter: 'agTextColumnFilter' },
        { field: 'emp_designation', headerName: 'Designation', filter: 'agTextColumnFilter' },
        { field: 'emp_department', headerName: 'Department', filter: 'agTextColumnFilter' },
        { field: 'emp_phone', headerName: 'Phone', filter: 'agTextColumnFilter' },
        { field: 'emp_blood_group', headerName: 'Blood Group', filter: 'agTextColumnFilter' },
        { field: 'emp_cfms_id', headerName: 'CFMS ID', filter: 'agTextColumnFilter' },
        { field: 'emp_hrms_id', headerName: 'HRMS ID', filter: 'agTextColumnFilter' },
        { field: 'emp_emergency_contact', headerName: 'Emergency Contact', filter: 'agTextColumnFilter' },
        { field: 'emp_ehs_card', headerName: 'EHS Card', filter: 'agTextColumnFilter' },
        { field: 'emp_barcode', headerName: 'QR/Barcode', filter: 'agTextColumnFilter' },
        { field: 'emp_depot_location', headerName: 'Depot Location', filter: 'agTextColumnFilter' },
        { field: 'emp_last_working', headerName: 'Last Working Place', filter: 'agTextColumnFilter' },
        { field: 'emp_residential_address', headerName: 'Residential Address', filter: 'agTextColumnFilter' },
        { field: 'emp_status', headerName: 'Status', filter: 'agTextColumnFilter' },
        {
            headerName: 'Actions',
            field: 'actions',
            sortable: false,
            filter: false,
            pinned: 'right',
            lockPosition: true,
            width: 200,
            cellRenderer: ActionsCellRenderer
        }
    ];

    // Profile Picture Cell Renderer
    function ProfilePictureCellRenderer() {}
    
    ProfilePictureCellRenderer.prototype.init = function(params) {
        this.eGui = document.createElement('div');
        this.eGui.className = 'profile-pic-cell';
        
        const photoUrl = params.value || wpSmartBadge.defaultAvatar;
        const userId = params.data.ID;
        
        this.eGui.innerHTML = `
            <div class="profile-pic-preview">
                <img src="${photoUrl}" alt="Profile" />
                <div class="profile-pic-overlay">
                    <button class="edit-photo-btn" data-user-id="${userId}">
                        <span class="dashicons dashicons-camera"></span>
                    </button>
                </div>
            </div>
        `;
        
        // Add click handler
        const editBtn = this.eGui.querySelector('.edit-photo-btn');
        editBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            showPhotoEditModal(userId);
        });
    };
    
    ProfilePictureCellRenderer.prototype.getGui = function() {
        return this.eGui;
    };

    // Action Cell Renderer
    function ActionsCellRenderer() {}

    ActionsCellRenderer.prototype.init = function(params) {
        this.eGui = document.createElement('div');
        this.eGui.className = 'action-buttons';
        
        const userId = params.data.ID;
        const container = document.createElement('div');
        container.style.display = 'flex';
        container.style.gap = '8px';
        
        // Template selection dropdown
        const select = document.createElement('select');
        select.className = 'template-select';
        select.dataset.userId = userId;
        select.innerHTML = `
            <option value="ActiveEmployee">Active Employee</option>
            <option value="RetiredOfficer">Retired Officer</option>
            <option value="RetiredMedical">Retired Medical</option>
            <option value="RetiredTravel">Retired Travel</option>
        `;
        select.value = params.data.emp_status?.toLowerCase() === 'retired' ? 'RetiredOfficer' : 'ActiveEmployee';
        
        // Edit button
        const editButton = document.createElement('button');
        editButton.className = 'button button-small edit-user-btn';
        editButton.innerHTML = '<span class="dashicons dashicons-edit"></span> Edit';
        editButton.onclick = (e) => {
            e.stopPropagation();
            showEditUserModal(params.data);
        };
        
        // Generate button
        const generateButton = document.createElement('button');
        generateButton.innerHTML = 'Generate';
        generateButton.className = 'button button-small';
        generateButton.onclick = () => generateBadge(userId, select.value);
        
        // Download button
        const downloadButton = document.createElement('button');
        downloadButton.innerHTML = 'Download Badge';
        downloadButton.className = 'button button-small';
        downloadButton.onclick = () => downloadBadge(userId, select.value);
        
        container.appendChild(editButton);
        container.appendChild(select);
        container.appendChild(generateButton);
        container.appendChild(downloadButton);
        
        this.eGui.appendChild(container);
    };

    ActionsCellRenderer.prototype.getGui = function() {
        return this.eGui;
    };

    const gridOptions = {
        columnDefs: columnDefs,
        defaultColDef: {
            flex: 1,
            minWidth: 150,
            filter: true,
            sortable: true,
            resizable: true
        },
        suppressMovableColumns: true,
        suppressColumnVirtualisation: true,
        rowData: [],
        rowSelection: 'multiple',
        suppressRowClickSelection: true,
        pagination: true,
        paginationPageSize: 10,
        domLayout: 'normal',
        onSelectionChanged: function() {
            const selectedRows = gridOptions.api.getSelectedRows();
            const bulkButton = document.getElementById('bulk_generate');
            if (bulkButton) {
                bulkButton.disabled = selectedRows.length === 0;
            }
        },
        onGridReady: function(params) {
            params.api.sizeColumnsToFit();
            // Add CSV export button
            const exportButton = document.createElement('button');
            exportButton.innerHTML = 'Export to CSV';
            exportButton.className = 'button button-primary';
            exportButton.style.marginRight = '10px';
            exportButton.addEventListener('click', function() {
                params.api.exportDataAsCsv({
                    fileName: 'users_data_' + new Date().toISOString().split('T')[0] + '.csv',
                    skipHeader: false,
                    skipFooters: true,
                    skipGroups: true,
                    skipPinnedTop: true,
                    skipPinnedBottom: true,
                    allColumns: true,
                    onlySelected: false,
                    columnKeys: ['emp_id', 'emp_full_name', 'emp_cfms_id', 'emp_hrms_id', 'emp_designation', 
                                'emp_department', 'emp_ehs_card', 'emp_phone', 'emp_blood_group', 
                                'emp_emergency_contact', 'emp_status']
                });
            });

            // Add CSV import button and input
            const importContainer = document.createElement('div');
            importContainer.style.display = 'inline-block';
            importContainer.style.marginRight = '10px';

            const importInput = document.createElement('input');
            importInput.type = 'file';
            importInput.accept = '.csv';
            importInput.style.display = 'none';
            importInput.id = 'csvImport';

            const importButton = document.createElement('button');
            importButton.innerHTML = 'Import CSV';
            importButton.className = 'button button-primary';
            importButton.onclick = () => importInput.click();

            importInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        const csvData = event.target.result;
                        const lines = csvData.split('\n');
                        const headers = lines[0].split(',').map(h => h.trim());
                        
                        const newData = [];
                        for(let i = 1; i < lines.length; i++) {
                            if(!lines[i]) continue;
                            const values = lines[i].split(',').map(v => v.trim());
                            const row = {};
                            headers.forEach((header, index) => {
                                row[header] = values[index] || '';
                            });
                            newData.push(row);
                        }

                        // Show confirmation modal
                        const confirmModal = document.createElement('div');
                        confirmModal.className = 'modal';
                        confirmModal.innerHTML = `
                            <div class="modal-content">
                                <h3>Import Confirmation</h3>
                                <p>Are you sure you want to import ${newData.length} records?</p>
                                <div class="modal-actions">
                                    <button class="button button-primary confirm-import">Confirm</button>
                                    <button class="button cancel-import">Cancel</button>
                                </div>
                            </div>
                        `;
                        document.body.appendChild(confirmModal);

                        confirmModal.querySelector('.confirm-import').onclick = function() {
                            // Send data to server
                            $.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'import_users_csv',
                                    users_data: JSON.stringify(newData),
                                    nonce: $('#wp_smart_badge_nonce').val()
                                },
                                success: function(response) {
                                    if(response.success) {
                                        alert('Data imported successfully!');
                                        // Refresh grid data
                                        params.api.setRowData(response.data);
                                    } else {
                                        alert('Error importing data: ' + response.data);
                                    }
                                },
                                error: function() {
                                    alert('Error importing data. Please try again.');
                                }
                            });
                            confirmModal.remove();
                        };

                        confirmModal.querySelector('.cancel-import').onclick = function() {
                            confirmModal.remove();
                        };
                    };
                    reader.readAsText(file);
                }
                this.value = ''; // Reset file input
            });

            importContainer.appendChild(importButton);
            importContainer.appendChild(importInput);

            // Add buttons to grid toolbar
            const toolbar = document.querySelector('#userGrid').parentElement;
            toolbar.insertBefore(exportButton, toolbar.firstChild);
            toolbar.insertBefore(importContainer, toolbar.firstChild);
        }
    };

    // Create grid instance
    const gridDiv = document.querySelector('#userGrid');
    if (gridDiv) {
        new agGrid.Grid(gridDiv, gridOptions);
        
        // Load user data
        $.post(ajaxurl, {
            action: 'get_users_data',
            nonce: wpSmartBadge.nonce,
            page: 1,
            per_page: 100
        }, function(response) {
            if (response.success && Array.isArray(response.data)) {
                gridOptions.api.setRowData(response.data);
            } else {
                gridDiv.innerHTML = '<p class="error">Error loading user data. Please refresh the page.</p>';
            }
        }).fail(function(error) {
            console.error('Error loading user data:', error);
            gridDiv.innerHTML = '<p class="error">Error loading user data. Please refresh the page.</p>';
        });
    }

    // Quick filter functionality
    const quickFilterInput = document.querySelector('#quickFilter');
    if (quickFilterInput) {
        quickFilterInput.addEventListener('input', function() {
            if (gridOptions && gridOptions.api) {
                gridOptions.api.setQuickFilter(this.value);
            }
        });
    }

    // Bulk generate button click handler
    const bulkGenerateBtn = document.querySelector('#bulk_generate');
    if (bulkGenerateBtn) {
        bulkGenerateBtn.addEventListener('click', function() {
            const selectedNodes = gridOptions.api.getSelectedRows();
            if (selectedNodes.length > 0) {
                generateBulkBadges(selectedNodes);
            }
        });
    }

    // Generate badge function
    window.generateBadge = function(userId, templateType) {
        const url = `${wpSmartBadge.ajaxurl}?action=preview_badge&preview_key=${wpSmartBadge.previewKey}&user_ids=${userId}&template_type=${templateType}&debug=1`;
        window.open(url, '_blank');
    };

    // Download badge function
    window.downloadBadge = function(userId, templateType) {
        const button = document.querySelector('.button-primary');
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<span class="spinner is-active" style="float: none; margin: 0 4px 0 0;"></span> Generating...';

        jQuery.ajax({
            url: wpSmartBadge.ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_smart_badge_download',
                user_id: userId,
                template_type: templateType,
                nonce: wpSmartBadge.nonce
            },
            xhrFields: {
                responseType: 'blob'
            },
            success: function(response) {
                const blob = new Blob([response], { type: 'application/pdf' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                a.download = `badge-${userId}.pdf`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
            },
            error: function(xhr, status, error) {
                console.error('Error downloading badge:', error);
                alert('Failed to download badge. Please try again.');
            },
            complete: function() {
                button.disabled = false;
                button.innerHTML = originalText;
            }
        });
    };

    // Handle photo preview
    $('#emp_photo').on('change', function(e) {
        if (this.files && this.files[0]) {
            const file = this.files[0];
            const reader = new FileReader();
            
            reader.onload = function(e) {
                $('#photoPreview').attr('src', e.target.result);
            }
            
            reader.readAsDataURL(file);
        }
    });

    // Camera and Photo Upload Handling
    let stream = null;
    let photoTaken = false;
    
    // Initialize file input
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = 'image/*';
    fileInput.style.display = 'none';
    document.body.appendChild(fileInput);
    
    // Handle file selection
    fileInput.addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            processSelectedImage(this.files[0]);
        }
    });
    
    // Process selected image (from file or camera)
    function processSelectedImage(file, previewId = 'photoPreview', dataId = 'emp_photo_data') {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                // Create canvas for image processing
                const canvas = document.createElement('canvas');
                let width = img.width;
                let height = img.height;
                
                // Resize if larger than 800px
                if (width > 800 || height > 800) {
                    if (width > height) {
                        height *= 800 / width;
                        width = 800;
                    } else {
                        width *= 800 / height;
                        height = 800;
                    }
                }
                
                canvas.width = width;
                canvas.height = height;
                
                // Draw and compress image
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);
                
                // Convert to JPEG data URL
                const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
                
                // Update preview and hidden input
                document.getElementById(previewId).src = dataUrl;
                document.getElementById(dataId).value = dataUrl;
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
    
    // Handle camera initialization
    async function initCamera() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                    facingMode: 'user'
                },
                audio: false
            });
            
            const video = document.getElementById('camera');
            video.srcObject = stream;
            video.style.display = 'block';
            document.getElementById('photoCanvas').style.display = 'none';
            document.getElementById('captureBtn').style.display = 'block';
            document.getElementById('retakeBtn').style.display = 'none';
            document.getElementById('savePhotoBtn').style.display = 'none';
            
        } catch (err) {
            alert('Error accessing camera: ' + err.message);
        }
    }
    
    // Handle camera controls
    $('#takePhotoBtn').on('click', function() {
        $('#cameraModal').show();
        initCamera();
    });
    
    $('#uploadPhotoBtn').on('click', function() {
        fileInput.click();
    });
    
    $('#captureBtn').on('click', function() {
        const video = document.getElementById('camera');
        const canvas = document.getElementById('photoCanvas');
        const context = canvas.getContext('2d');
        
        // Set canvas size to video size
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        // Draw video frame to canvas
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        // Show canvas and update controls
        video.style.display = 'none';
        canvas.style.display = 'block';
        document.getElementById('captureBtn').style.display = 'none';
        document.getElementById('retakeBtn').style.display = 'block';
        document.getElementById('savePhotoBtn').style.display = 'block';
        
        photoTaken = true;
    });
    
    $('#retakeBtn').on('click', function() {
        const video = document.getElementById('camera');
        const canvas = document.getElementById('photoCanvas');
        
        // Show video and update controls
        video.style.display = 'block';
        canvas.style.display = 'none';
        document.getElementById('captureBtn').style.display = 'block';
        document.getElementById('retakeBtn').style.display = 'none';
        document.getElementById('savePhotoBtn').style.display = 'none';
        
        photoTaken = false;
    });
    
    $('#savePhotoBtn').on('click', function() {
        const canvas = document.getElementById('photoCanvas');
        const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
        
        // Update preview and form data
        $('#photoPreview').attr('src', dataUrl);
        $('#emp_photo_data').val(dataUrl);
        
        // Close camera
        closeCamera();
    });
    
    // Close camera when modal is closed
    function closeCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        $('#cameraModal').hide();
        photoTaken = false;
    }
    
    $('.close').on('click', closeCamera);
    
    // Handle new user form submission
    $('#addUserForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'add_new_user');
        formData.append('nonce', wpSmartBadge.nonce);
        
        // Show loading state
        const submitButton = $(this).find('button[type="submit"]');
        const originalText = submitButton.text();
        submitButton.prop('disabled', true).text('Adding User...');
        
        $.ajax({
            url: wpSmartBadge.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Close modal and reset form
                    $('#addUserModal').hide();
                    $('#addUserForm')[0].reset();
                    $('#photoPreview').attr('src', wpSmartBadge.defaultAvatar);
                    
                    // Refresh grid
                    if (typeof gridOptions !== 'undefined' && gridOptions.api) {
                        gridOptions.api.refreshServerSideStore({ purge: true });
                    }
                    
                    // Show success message
                    alert('User added successfully!');
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('Error adding user. Please try again.');
            },
            complete: function() {
                submitButton.prop('disabled', false).text(originalText);
            }
        });
    });

    // Add spinning animation for the loading icon
    const style = document.createElement('style');
    style.textContent = `
        .spin {
            animation: spin 2s linear infinite;
        }
        @keyframes spin {
            100% { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);

    // Generate bulk badges function
    function generateBulkBadges(selectedUsers) {
        // Get template types from the inline dropdowns
        const userBadges = selectedUsers.map(user => ({
            userId: user.ID,
            templateType: document.querySelector(`.template-select[data-user-id="${user.ID}"]`).value
        }));
        
        const userIds = userBadges.map(badge => badge.userId).join(',');
        const url = `${wpSmartBadge.ajaxurl}?action=preview_badge&preview_key=${wpSmartBadge.previewKey}&user_ids=${userIds}&bulk=1&debug=1`;
        window.open(url, '_blank');
    }

    // Close modal handlers
    document.querySelectorAll('.close-button').forEach(button => {
        button.addEventListener('click', function() {
            this.closest('.modal').style.display = 'none';
        });
    });

    // Photo Edit Modal
    function showPhotoEditModal(userId) {
        const modalHtml = `
            <div id="photoEditModal" class="modal">
                <div class="modal-content camera-modal-content">
                    <div class="modal-header">
                        <h2>Update Profile Picture</h2>
                        <span class="close">&times;</span>
                    </div>
                    <div class="photo-edit-container">
                        <div class="preview-container">
                            <img id="editPhotoPreview" src="${wpSmartBadge.defaultAvatar}" alt="Preview">
                            <div class="preview-overlay">
                                <i class="dashicons dashicons-camera"></i>
                            </div>
                        </div>
                        <input type="hidden" id="editPhotoData">
                        <div class="upload-buttons">
                            <button type="button" class="button" id="editTakePhotoBtn">
                                <i class="dashicons dashicons-camera"></i> Take Photo
                            </button>
                            <button type="button" class="button" id="editUploadPhotoBtn">
                                <i class="dashicons dashicons-upload"></i> Upload Photo
                            </button>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="button button-primary" id="saveEditPhotoBtn">Save Photo</button>
                            <button type="button" class="button" onclick="document.getElementById('photoEditModal').remove()">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        const modal = document.getElementById('photoEditModal');
        const closeBtn = modal.querySelector('.close');
        const takePhotoBtn = document.getElementById('editTakePhotoBtn');
        const uploadPhotoBtn = document.getElementById('editUploadPhotoBtn');
        const savePhotoBtn = document.getElementById('saveEditPhotoBtn');
        
        // Initialize file input
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.accept = 'image/*';
        fileInput.style.display = 'none';
        document.body.appendChild(fileInput);
        
        // Handle file selection
        fileInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                processSelectedImage(this.files[0], 'editPhotoPreview', 'editPhotoData');
            }
        });
        
        // Handle buttons
        uploadPhotoBtn.addEventListener('click', () => fileInput.click());
        takePhotoBtn.addEventListener('click', () => initEditCamera());
        savePhotoBtn.addEventListener('click', () => saveUpdatedPhoto(userId));
        closeBtn.addEventListener('click', () => modal.remove());
        
        modal.style.display = 'block';
    }
    
    // Save updated photo
    function saveUpdatedPhoto(userId) {
        const photoData = document.getElementById('editPhotoData').value;
        if (!photoData) {
            alert('Please select or take a photo first');
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'update_user_photo');
        formData.append('nonce', wpSmartBadge.nonce);
        formData.append('user_id', userId);
        formData.append('photo_data', photoData);
        
        $.ajax({
            url: wpSmartBadge.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Close modal
                    document.getElementById('photoEditModal').remove();
                    
                    // Refresh grid
                    gridOptions.api.refreshServerSideStore({ purge: true });
                    
                    // Show success message
                    alert('Profile picture updated successfully!');
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('Error updating profile picture. Please try again.');
            }
        });
    }
    
    // Initialize camera for edit modal
    function initEditCamera() {
        const modal = document.getElementById('photoEditModal');
        const container = modal.querySelector('.photo-edit-container');
        
        // Create camera elements
        const cameraHtml = `
            <div class="camera-container">
                <video id="editCamera" autoplay playsinline></video>
                <canvas id="editPhotoCanvas" style="display: none;"></canvas>
                <div class="camera-controls">
                    <button type="button" class="button button-primary" id="editCaptureBtn">
                        <i class="dashicons dashicons-camera"></i> Capture
                    </button>
                    <button type="button" class="button" id="editRetakeBtn" style="display: none;">
                        <i class="dashicons dashicons-controls-repeat"></i> Retake
                    </button>
                    <button type="button" class="button button-primary" id="editSaveCaptureBtn" style="display: none;">
                        <i class="dashicons dashicons-yes"></i> Use Photo
                    </button>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', cameraHtml);
        
        const video = document.getElementById('editCamera');
        const canvas = document.getElementById('editPhotoCanvas');
        const captureBtn = document.getElementById('editCaptureBtn');
        const retakeBtn = document.getElementById('editRetakeBtn');
        const saveCaptureBtn = document.getElementById('editSaveCaptureBtn');
        
        // Get camera stream
        navigator.mediaDevices.getUserMedia({
            video: {
                width: { ideal: 1280 },
                height: { ideal: 720 },
                facingMode: 'user'
            },
            audio: false
        }).then(stream => {
            window.editStream = stream;
            video.srcObject = stream;
        }).catch(err => {
            alert('Error accessing camera: ' + err.message);
        });
        
        // Handle capture
        captureBtn.addEventListener('click', () => {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            
            video.style.display = 'none';
            canvas.style.display = 'block';
            captureBtn.style.display = 'none';
            retakeBtn.style.display = 'inline-block';
            saveCaptureBtn.style.display = 'inline-block';
        });
        
        // Handle retake
        retakeBtn.addEventListener('click', () => {
            video.style.display = 'block';
            canvas.style.display = 'none';
            captureBtn.style.display = 'inline-block';
            retakeBtn.style.display = 'none';
            saveCaptureBtn.style.display = 'none';
        });
        
        // Handle save capture
        saveCaptureBtn.addEventListener('click', () => {
            const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
            document.getElementById('editPhotoPreview').src = dataUrl;
            document.getElementById('editPhotoData').value = dataUrl;
            
            // Clean up camera
            if (window.editStream) {
                window.editStream.getTracks().forEach(track => track.stop());
            }
            
            // Remove camera container
            document.querySelector('.camera-container').remove();
        });
    }

    // Show edit user modal
    function showEditUserModal(userData) {
        const modalHtml = `
            <div id="editUserModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Edit User</h2>
                        <span class="close">&times;</span>
                    </div>
                    <form id="editUserForm">
                        <input type="hidden" name="user_id" value="${userData.ID}">
                        <div class="form-row">
                            <label for="edit_emp_photo">Profile Picture</label>
                            <div class="photo-upload-container">
                                <div class="preview-container">
                                    <img id="editUserPhotoPreview" src="${userData.emp_photo || wpSmartBadge.defaultAvatar}" alt="Profile Preview">
                                    <div class="preview-overlay">
                                        <i class="dashicons dashicons-camera"></i>
                                    </div>
                                </div>
                                <input type="hidden" id="edit_emp_photo_data" name="emp_photo_data">
                                <div class="upload-buttons">
                                    <button type="button" class="button" id="editUserTakePhotoBtn">
                                        <i class="dashicons dashicons-camera"></i> Take Photo
                                    </button>
                                    <button type="button" class="button" id="editUserUploadPhotoBtn">
                                        <i class="dashicons dashicons-upload"></i> Upload Photo
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <label for="edit_emp_id">Employee ID*</label>
                            <input type="text" id="edit_emp_id" name="emp_id" value="${userData.emp_id || ''}" required>
                        </div>
                        <div class="form-row">
                            <label for="edit_emp_full_name">Full Name*</label>
                            <input type="text" id="edit_emp_full_name" name="emp_full_name" value="${userData.emp_full_name || ''}" required>
                        </div>
                        <div class="form-row">
                            <label for="edit_user_email">Email*</label>
                            <input type="email" id="edit_user_email" name="user_email" value="${userData.user_email || ''}" required>
                        </div>
                        <div class="form-row">
                            <label for="edit_emp_designation">Designation</label>
                            <input type="text" id="edit_emp_designation" name="emp_designation" value="${userData.emp_designation || ''}">
                        </div>
                        <div class="form-row">
                            <label for="edit_emp_department">Department</label>
                            <input type="text" id="edit_emp_department" name="emp_department" value="${userData.emp_department || ''}">
                        </div>
                        <div class="form-row">
                            <label for="edit_emp_phone">Phone</label>
                            <input type="tel" id="edit_emp_phone" name="emp_phone" value="${userData.emp_phone || ''}">
                        </div>
                        <div class="form-row">
                            <label for="edit_emp_blood_group">Blood Group</label>
                            <select id="edit_emp_blood_group" name="emp_blood_group">
                                <option value="">Select Blood Group</option>
                                <option value="A+" ${userData.emp_blood_group === 'A+' ? 'selected' : ''}>A+</option>
                                <option value="A-" ${userData.emp_blood_group === 'A-' ? 'selected' : ''}>A-</option>
                                <option value="B+" ${userData.emp_blood_group === 'B+' ? 'selected' : ''}>B+</option>
                                <option value="B-" ${userData.emp_blood_group === 'B-' ? 'selected' : ''}>B-</option>
                                <option value="O+" ${userData.emp_blood_group === 'O+' ? 'selected' : ''}>O+</option>
                                <option value="O-" ${userData.emp_blood_group === 'O-' ? 'selected' : ''}>O-</option>
                                <option value="AB+" ${userData.emp_blood_group === 'AB+' ? 'selected' : ''}>AB+</option>
                                <option value="AB-" ${userData.emp_blood_group === 'AB-' ? 'selected' : ''}>AB-</option>
                            </select>
                        </div>
                        <div class="form-row">
                            <label for="edit_emp_cfms_id">CFMS ID</label>
                            <input type="text" id="edit_emp_cfms_id" name="emp_cfms_id" value="${userData.emp_cfms_id || ''}">
                        </div>
                        <div class="form-row">
                            <label for="edit_emp_hrms_id">HRMS ID</label>
                            <input type="text" id="edit_emp_hrms_id" name="emp_hrms_id" value="${userData.emp_hrms_id || ''}">
                        </div>
                        <div class="form-row">
                            <label for="edit_emp_emergency_contact">Emergency Contact</label>
                            <input type="tel" id="edit_emp_emergency_contact" name="emp_emergency_contact" value="${userData.emp_emergency_contact || ''}">
                        </div>
                        <div class="form-row">
                            <label for="edit_emp_ehs_card">EHS Card</label>
                            <input type="text" id="edit_emp_ehs_card" name="emp_ehs_card" value="${userData.emp_ehs_card || ''}">
                        </div>
                        <div class="form-row">
                            <label for="edit_emp_designation">Designation</label>
                            <input type="text" id="edit_emp_designation" name="emp_designation" value="${userData.emp_designation || ''}">
                        </div>
                        <div class="form-row">
                            <label for="edit_emp_department">Department</label>
                            <input type="text" id="edit_emp_department" name="emp_department" value="${userData.emp_department || ''}">
                        </div>
                        <div class="form-row">
                            <label for="edit_emp_qr_code">QR Code</label>
                            <input type="text" id="edit_emp_qr_code" name="emp_qr_code" value="${userData.emp_qr_code || ''}">
                        </div>
                        <div class="form-row">
                            <label for="edit_emp_depot_location">Depot Location</label>
                            <input type="text" id="edit_emp_depot_location" name="emp_depot_location" value="${userData.emp_depot_location || ''}">
                        </div>
                        <div class="form-row">
                            <label for="edit_emp_last_working_place">Last Working Place</label>
                            <input type="text" id="edit_emp_last_working_place" name="emp_last_working_place" value="${userData.emp_last_working || ''}">
                        </div>
                        <div class="form-row">
                            <label for="edit_emp_residential_address">Residential Address</label>
                            <input type="text" id="edit_emp_residential_address" name="emp_residential_address" value="${userData.emp_residential_address || ''}">
                        </div>
                        <div class="form-row">
                            <label for="edit_emp_status">Status</label>
                            <select id="edit_emp_status" name="emp_status">
                                <option value="">Select Status</option>
                                <option value="Active" ${userData.emp_status === 'Active' ? 'selected' : ''}>Active</option>
                                <option value="Inactive" ${userData.emp_status === 'Inactive' ? 'selected' : ''}>Inactive</option>
                            </select>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="button button-primary">Save Changes</button>
                            <button type="button" class="button" onclick="document.getElementById('editUserModal').remove()">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        `;
        
        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        const modal = document.getElementById('editUserModal');
        const closeBtn = modal.querySelector('.close');
        const takePhotoBtn = document.getElementById('editUserTakePhotoBtn');
        const uploadPhotoBtn = document.getElementById('editUserUploadPhotoBtn');
        
        // Initialize file input
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.accept = 'image/*';
        fileInput.style.display = 'none';
        document.body.appendChild(fileInput);
        
        // Handle file selection
        fileInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                processSelectedImage(this.files[0], 'editUserPhotoPreview', 'edit_emp_photo_data');
            }
        });
        
        // Handle buttons
        uploadPhotoBtn.addEventListener('click', () => fileInput.click());
        takePhotoBtn.addEventListener('click', () => initEditUserCamera());
        closeBtn.addEventListener('click', () => {
            if (window.editUserStream) {
                window.editUserStream.getTracks().forEach(track => track.stop());
            }
            modal.remove();
            fileInput.remove();
        });
        
        // Handle form submission
        document.getElementById('editUserForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'update_user');
            formData.append('nonce', wpSmartBadge.nonce);
            
            // Show loading state
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.textContent = 'Saving...';
            
            $.ajax({
                url: wpSmartBadge.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        // Clean up camera if active
                        if (window.editUserStream) {
                            window.editUserStream.getTracks().forEach(track => track.stop());
                        }
                        
                        // Close modal
                        modal.remove();
                        fileInput.remove();
                        
                        // Refresh grid
                        gridOptions.api.refreshServerSideStore({ purge: true });
                        
                        // Show success message
                        alert('User updated successfully!');
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function() {
                    alert('Error updating user. Please try again.');
                },
                complete: function() {
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                }
            });
        });
        
        modal.style.display = 'block';
    }
    
    // Initialize camera for edit user modal
    function initEditUserCamera() {
        const modal = document.getElementById('editUserModal');
        const container = modal.querySelector('.photo-upload-container');
        
        // Create camera elements
        const cameraHtml = `
            <div class="camera-container">
                <video id="editUserCamera" autoplay playsinline></video>
                <canvas id="editUserPhotoCanvas" style="display: none;"></canvas>
                <div class="camera-controls">
                    <button type="button" class="button button-primary" id="editUserCaptureBtn">
                        <i class="dashicons dashicons-camera"></i> Capture
                    </button>
                    <button type="button" class="button" id="editUserRetakeBtn" style="display: none;">
                        <i class="dashicons dashicons-controls-repeat"></i> Retake
                    </button>
                    <button type="button" class="button button-primary" id="editUserSaveCaptureBtn" style="display: none;">
                        <i class="dashicons dashicons-yes"></i> Use Photo
                    </button>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', cameraHtml);
        
        const video = document.getElementById('editUserCamera');
        const canvas = document.getElementById('editUserPhotoCanvas');
        const captureBtn = document.getElementById('editUserCaptureBtn');
        const retakeBtn = document.getElementById('editUserRetakeBtn');
        const saveCaptureBtn = document.getElementById('editUserSaveCaptureBtn');
        
        // Get camera stream
        navigator.mediaDevices.getUserMedia({
            video: {
                width: { ideal: 1280 },
                height: { ideal: 720 },
                facingMode: 'user'
            },
            audio: false
        }).then(stream => {
            window.editUserStream = stream;
            video.srcObject = stream;
        }).catch(err => {
            alert('Error accessing camera: ' + err.message);
        });
        
        // Handle capture
        captureBtn.addEventListener('click', () => {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            
            video.style.display = 'none';
            canvas.style.display = 'block';
            captureBtn.style.display = 'none';
            retakeBtn.style.display = 'inline-block';
            saveCaptureBtn.style.display = 'inline-block';
        });
        
        // Handle retake
        retakeBtn.addEventListener('click', () => {
            video.style.display = 'block';
            canvas.style.display = 'none';
            captureBtn.style.display = 'inline-block';
            retakeBtn.style.display = 'none';
            saveCaptureBtn.style.display = 'none';
        });
        
        // Handle save capture
        saveCaptureBtn.addEventListener('click', () => {
            const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
            document.getElementById('editUserPhotoPreview').src = dataUrl;
            document.getElementById('edit_emp_photo_data').value = dataUrl;
            
            // Clean up camera
            if (window.editUserStream) {
                window.editUserStream.getTracks().forEach(track => track.stop());
            }
            
            // Remove camera container
            document.querySelector('.camera-container').remove();
        });
    }
});
