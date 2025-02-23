jQuery(document).ready(function($) {
    let mainGridApi = null;
    let previewGridApi = null;
    
    // Initialize main grid if it exists
    if (document.querySelector('#employeesGrid')) {
        initMainGrid();
    }
    
    // Initialize preview grid when file is selected
    $('#csvFile').on('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        Papa.parse(file, {
            header: true,
            complete: function(results) {
                if (results.data && results.data.length > 0) {
                    initPreviewGrid(results.data);
                    $('.preview-container').show();
                }
            }
        });
    });
    
    // Initialize preview grid
    function initPreviewGrid(data) {
        const container = document.querySelector('#previewGrid');
        const columnDefs = Object.keys(data[0]).map(field => ({
            field: field,
            sortable: true,
            filter: true,
            resizable: true
        }));
        
        const gridOptions = {
            columnDefs: columnDefs,
            rowData: data,
            pagination: true,
            paginationPageSize: 10,
            defaultColDef: {
                flex: 1,
                minWidth: 100
            }
        };
        
        if (previewGridApi) {
            previewGridApi.destroy();
        }
        previewGridApi = new agGrid.Grid(container, gridOptions);
    }
    
    // Initialize main grid
    function initMainGrid() {
        const container = document.querySelector('#employeesGrid');
        const columnDefs = [
            { field: 'emp_id', headerName: 'ID', sortable: true, filter: true },
            { field: 'emp_full_name', headerName: 'Name', sortable: true, filter: true },
            { field: 'emp_designation', headerName: 'Designation', sortable: true, filter: true },
            { field: 'emp_department', headerName: 'Department', sortable: true, filter: true },
            { field: 'work_location', headerName: 'Location', sortable: true, filter: true },
            { field: 'emp_phone', headerName: 'Phone', sortable: true, filter: true },
            { field: 'emp_blood_group', headerName: 'Blood Group', sortable: true, filter: true },
            { field: 'emp_status', headerName: 'Status', sortable: true, filter: true }
        ];
        
        const gridOptions = {
            columnDefs: columnDefs,
            pagination: true,
            paginationPageSize: 50,
            defaultColDef: {
                flex: 1,
                minWidth: 100,
                resizable: true,
                floatingFilter: true
            }
        };
        
        mainGridApi = new agGrid.Grid(container, gridOptions);
        
        // Load initial data
        refreshMainGrid();
    }
    
    // Refresh main grid data
    function refreshMainGrid() {
        $.ajax({
            url: smartBadgeImport.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_employees_data',
                nonce: smartBadgeImport.nonce
            },
            success: function(response) {
                if (response.success && mainGridApi) {
                    mainGridApi.gridOptions.api.setRowData(response.data);
                }
            }
        });
    }
    
    // Handle import confirmation
    $('#confirmImport').on('click', function() {
        if (!confirm(smartBadgeImport.strings.confirm)) {
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'import_employee_data');
        formData.append('nonce', smartBadgeImport.nonce);
        formData.append('csvFile', $('#csvFile')[0].files[0]);
        
        $.ajax({
            url: smartBadgeImport.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert(smartBadgeImport.strings.success);
                    
                    // Update main grid with new data
                    if (mainGridApi && response.data.gridData) {
                        mainGridApi.gridOptions.api.setRowData(response.data.gridData);
                    }
                    
                    // Reset form
                    $('#importForm')[0].reset();
                    $('.preview-container').hide();
                    if (previewGridApi) {
                        previewGridApi.destroy();
                        previewGridApi = null;
                    }
                } else {
                    alert(smartBadgeImport.strings.error + ' ' + response.data.message);
                }
            },
            error: function() {
                alert(smartBadgeImport.strings.error);
            }
        });
    });
    
    // Handle cancel
    $('#cancelImport').on('click', function() {
        $('#importForm')[0].reset();
        $('.preview-container').hide();
        if (previewGridApi) {
            previewGridApi.destroy();
            previewGridApi = null;
        }
    });
});
