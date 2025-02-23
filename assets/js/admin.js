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
        // { field: 'ID', headerName: 'ID', width: 80, filter: 'agNumberColumnFilter' },
        { field: 'emp_id', headerName: 'Employee ID', width: 120 },
        { field: 'emp_full_name', headerName: 'Full Name', width: 150 },
        { field: 'emp_cfms_id', headerName: 'CFMS ID', width: 120 },
        { field: 'emp_hrms_id', headerName: 'HRMS ID', width: 120 },
        { field: 'emp_designation', headerName: 'Designation', width: 150 },
        { field: 'emp_department', headerName: 'Department', width: 150 },
        { field: 'emp_ehs_card', headerName: 'EHS Card', width: 120 },
        { field: 'emp_phone', headerName: 'Phone', width: 120 },
        { field: 'emp_blood_group', headerName: 'Blood Group', width: 100 },
        { field: 'emp_emergency_contact', headerName: 'Emergency Contact', width: 150 },
        { field: 'emp_status', headerName: 'Status', width: 100 },
        {
            headerName: 'Actions',
            field: 'actions',
            sortable: false,
            filter: false,
            width: 400,
            pinned: 'right',
            lockPosition: true,
            cellRenderer: function(params) {
                const container = document.createElement('div');
                container.style.display = 'flex';
                container.style.gap = '8px';
                
                // Template selection dropdown
                const select = document.createElement('select');
                select.className = 'template-select';
                select.dataset.userId = params.data.ID;
                select.innerHTML = `
                    <option value="ActiveEmployee">Active Employee</option>
                    <option value="RetiredOfficer">Retired Officer</option>
                    <option value="RetiredMedical">Retired Medical</option>
                    <option value="RetiredTravel">Retired Travel</option>
                `;
                select.value = params.data.emp_status?.toLowerCase() === 'retired' ? 'RetiredOfficer' : 'ActiveEmployee';
                
                // Generate button
                const button = document.createElement('button');
                button.innerHTML = 'Generate';
                button.className = 'button button-small';
                button.onclick = () => generateBadge(params.data.ID, select.value);
                
                // Download button
                const downloadButton = document.createElement('button');
                downloadButton.innerHTML = 'Download Badge';
                downloadButton.className = 'button button-small';
                downloadButton.onclick = () => downloadBadge(params.data.ID, select.value);
                
                container.appendChild(select);
                container.appendChild(button);
                container.appendChild(downloadButton);
                return container;
            }
        }
    ];

    const gridOptions = {
        columnDefs: columnDefs,
        defaultColDef: {
            sortable: true,
            filter: true,
            resizable: true,
            floatingFilter: true
        },
        rowData: [],
        rowSelection: 'multiple',
        suppressRowClickSelection: true,
        pagination: true,
        paginationPageSize: 10,
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

    // Handle file upload
    $('#emp_photo').on('change', function() {
        var file = this.files[0];
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#emp_photo_preview').attr('src', e.target.result);
        }
        reader.readAsDataURL(file);
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
});
