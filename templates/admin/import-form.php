<div class="import-container">
    <h2>Import Employee Data</h2>
    <form id="importForm" class="import-form">
        <div class="file-upload-wrapper">
            <input type="file" id="csvFile" name="csvFile" accept=".csv" required>
            <label for="csvFile">Choose CSV File</label>
        </div>
        <div class="preview-container" style="display: none;">
            <h3>File Preview</h3>
            <div id="previewGrid" class="ag-theme-alpine" style="height: 300px;"></div>
            <div class="preview-actions">
                <button type="button" id="confirmImport" class="button button-primary">Import Data</button>
                <button type="button" id="cancelImport" class="button">Cancel</button>
            </div>
        </div>
    </form>
</div>

<style>
.import-container {
    padding: 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.import-form {
    max-width: 800px;
    margin: 0 auto;
}

.file-upload-wrapper {
    position: relative;
    text-align: center;
    padding: 20px;
    border: 2px dashed #ddd;
    border-radius: 8px;
    margin-bottom: 20px;
}

.file-upload-wrapper input[type="file"] {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    opacity: 0;
    cursor: pointer;
}

.file-upload-wrapper label {
    display: inline-block;
    padding: 10px 20px;
    background: #2271b1;
    color: white;
    border-radius: 4px;
    cursor: pointer;
}

.preview-container {
    margin-top: 20px;
}

.preview-actions {
    margin-top: 20px;
    text-align: right;
}

.preview-actions button {
    margin-left: 10px;
}
</style>

<script>
jQuery(document).ready(function($) {
    let previewGridApi = null;
    
    // Initialize preview grid
    function initPreviewGrid(data) {
        const container = document.querySelector('#previewGrid');
        const columnDefs = Object.keys(data[0]).map(field => ({
            field: field,
            sortable: true,
            filter: true
        }));
        
        const gridOptions = {
            columnDefs: columnDefs,
            rowData: data,
            pagination: true,
            paginationPageSize: 10,
            defaultColDef: {
                flex: 1,
                minWidth: 100,
                resizable: true
            }
        };
        
        previewGridApi = new agGrid.Grid(container, gridOptions);
        $('.preview-container').show();
    }
    
    // Handle file selection
    $('#csvFile').on('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const csv = e.target.result;
            const data = $.csv.toObjects(csv);
            initPreviewGrid(data);
        };
        reader.readAsText(file);
    });
    
    // Handle import confirmation
    $('#confirmImport').on('click', function() {
        const formData = new FormData($('#importForm')[0]);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert('Data imported successfully!');
                    // Refresh the main grid
                    if (typeof mainGridApi !== 'undefined') {
                        mainGridApi.refreshCells();
                    }
                    // Reset the form
                    $('#importForm')[0].reset();
                    $('.preview-container').hide();
                } else {
                    alert('Error importing data: ' + response.data);
                }
            },
            error: function() {
                alert('Error importing data. Please try again.');
            }
        });
    });
    
    // Handle cancel
    $('#cancelImport').on('click', function() {
        $('#importForm')[0].reset();
        $('.preview-container').hide();
    });
});</script>
