jQuery(document).ready(function($) {
    'use strict';
    
    // Prevent double initialization
    if (window.templateCustomizerInitialized) {
        console.log('Template customizer already initialized');
        return;
    }
    window.templateCustomizerInitialized = true;
    
    // Constants
    const GRID_SIZE = 10;
    let showGrid = false;
    let activeTemplate = null;
    let isDragging = false;
    let isResizing = false;
    let activeField = null;
    
    console.log('Template Customizer initialized');

    // Default templates
    const DEFAULT_TEMPLATES = {
        'active-employee': {
            name: 'Active Employee',
            layout: {
                front: [
                    { type: 'photo', x: 20, y: 20, width: 100, height: 120, text: 'Photo' },
                    { type: 'name', x: 140, y: 20, width: 200, height: 30, text: 'John Doe' },
                    { type: 'id', x: 140, y: 60, width: 120, height: 25, text: 'EMP001' },
                    { type: 'designation', x: 140, y: 95, width: 200, height: 25, text: 'Software Engineer' }
                ],
                back: [
                    { type: 'qr_code', x: 20, y: 20, width: 120, height: 120, text: 'QR Code' },
                    { type: 'department', x: 160, y: 20, width: 200, height: 25, text: 'Engineering' },
                    { type: 'blood_group', x: 160, y: 55, width: 100, height: 25, text: 'A+' }
                ]
            },
            styles: {
                background: {
                    type: 'solid',
                    color: '#ffffff',
                    gradient: {
                        start: '#ffffff',
                        end: '#f0f0f0',
                        direction: 'to bottom'
                    }
                },
                text: {
                    color: '#333333',
                    size: 'medium'
                }
            }
        },
        'retired-medical': {
            name: 'Retired Medical',
            layout: {
                front: [
                    { type: 'photo', x: 20, y: 20, width: 100, height: 120, text: 'Photo' },
                    { type: 'name', x: 140, y: 20, width: 200, height: 30, text: 'Dr. Jane Smith' },
                    { type: 'id', x: 140, y: 60, width: 120, height: 25, text: 'MED001' },
                    { type: 'designation', x: 140, y: 95, width: 200, height: 25, text: 'Senior Physician (Retd.)' }
                ],
                back: [
                    { type: 'qr_code', x: 20, y: 20, width: 120, height: 120, text: 'QR Code' },
                    { type: 'department', x: 160, y: 20, width: 200, height: 25, text: 'Medical Department' },
                    { type: 'blood_group', x: 160, y: 55, width: 100, height: 25, text: 'O+' }
                ]
            },
            styles: {
                background: {
                    type: 'gradient',
                    color: '#ffffff',
                    gradient: {
                        start: '#f0f9ff',
                        end: '#e1f5fe',
                        direction: 'to right'
                    }
                },
                text: {
                    color: '#01579b',
                    size: 'medium'
                }
            }
        }
    };

    // Initialize color pickers
    $('.color-picker').wpColorPicker({
        change: function(event, ui) {
            updateBadgeStyle();
        }
    });

    // Initialize draggable fields
    function initDraggable() {
        console.log('Initializing draggable elements');
        
        // Make field items draggable
        $('.field-item').draggable({
            helper: 'clone',
            start: function(event, ui) {
                console.log('Drag started:', {
                    field: $(this).data('field'),
                    position: ui.position
                });
                $(this).addClass('dragging');
            },
            stop: function(event, ui) {
                console.log('Drag stopped');
                $(this).removeClass('dragging');
            }
        });

        // Make existing badge fields draggable
        $('.badge-field').draggable({
            containment: '.badge-content',
            start: function(event, ui) {
                console.log('Existing field drag started:', {
                    position: ui.position
                });
            },
            stop: function(event, ui) {
                console.log('Existing field drag stopped:', {
                    finalPosition: ui.position
                });
                updateFieldPosition($(this));
            }
        });
    }

    // Make badge content areas droppable
    $('.badge-content').droppable({
        accept: '.field-item, .badge-field',
        over: function(event, ui) {
            console.log('Draggable over drop target');
            $(this).addClass('drag-over');
        },
        out: function(event, ui) {
            console.log('Draggable left drop target');
            $(this).removeClass('drag-over');
        },
        drop: function(event, ui) {
            console.log('Drop event triggered', {
                target: this.id,
                offset: {
                    left: event.pageX - $(this).offset().left,
                    top: event.pageY - $(this).offset().top
                }
            });
            
            $(this).removeClass('drag-over');
            
            const isNewField = ui.draggable.hasClass('field-item');
            if (isNewField) {
                console.log('Adding new field');
                addNewField($(this), ui.draggable, event);
            } else {
                console.log('Updating existing field position');
                updateFieldPosition(ui.draggable);
            }
        }
    });

    function addNewField($container, $draggedItem, event) {
        const fieldType = $draggedItem.data('field');
        const containerOffset = $container.offset();
        const x = event.pageX - containerOffset.left;
        const y = event.pageY - containerOffset.top;

        console.log('Adding new field:', {
            type: fieldType,
            position: { x, y }
        });

        const $newField = $('<div>', {
            class: 'badge-field',
            'data-field': fieldType,
            css: {
                left: x + 'px',
                top: y + 'px'
            }
        });

        const $icon = $('<span>', {
            class: 'dashicons ' + getFieldIcon(fieldType)
        });

        const $text = $('<span>', {
            text: getFieldLabel(fieldType)
        });

        const $removeBtn = $('<span>', {
            class: 'remove-field',
            html: '×',
            click: function(e) {
                e.stopPropagation();
                console.log('Removing field:', fieldType);
                $(this).parent().remove();
            }
        });

        $newField.append($icon, $text, $removeBtn);
        $container.append($newField);

        // Make the new field draggable
        $newField.draggable({
            containment: $container,
            start: function(event, ui) {
                console.log('New field drag started');
            },
            stop: function(event, ui) {
                console.log('New field drag stopped:', {
                    position: ui.position
                });
                updateFieldPosition($(this));
            }
        });
    }

    function updateFieldPosition($field) {
        const position = $field.position();
        console.log('Field position updated:', {
            field: $field.data('field'),
            position: position
        });
    }

    function getFieldIcon(fieldType) {
        const icons = {
            photo: 'dashicons-format-image',
            name: 'dashicons-admin-users',
            id: 'dashicons-id',
            designation: 'dashicons-businessman',
            department: 'dashicons-groups',
            blood_group: 'dashicons-heart',
            qr_code: 'dashicons-qr-code'
        };
        return icons[fieldType] || 'dashicons-plus';
    }

    function getFieldLabel(fieldType) {
        const labels = {
            photo: 'Photo',
            name: 'Name',
            id: 'Employee ID',
            designation: 'Designation',
            department: 'Department',
            blood_group: 'Blood Group',
            qr_code: 'QR Code'
        };
        return labels[fieldType] || fieldType;
    }

    // Initialize draggable elements
    initDraggable();

    // Button click handlers
    $('#preview_template').on('click', function() {
        console.log('Preview button clicked');
        openPreview();
    });

    $('#reset_template').on('click', function() {
        console.log('Reset button clicked');
        if (confirm(wpSmartBadge.i18n.confirmReset)) {
            loadTemplate('default');
        }
    });

    $('#save_template').on('click', function() {
        console.log('Save button clicked');
        saveTemplate();
    });

    // Grid toggle
    $('.show-grid').on('click', function(e) {
        e.preventDefault();
        console.log('Grid toggle clicked');
        $('.badge-content').toggleClass('show-grid');
        $(this).toggleClass('active');
        showGrid = !showGrid;
    });

    // Initialize grid state
    if ($('.show-grid').hasClass('active')) {
        $('.badge-content').addClass('show-grid');
        showGrid = true;
    }

    // Side switching
    $('.side-switcher button').on('click', function() {
        const side = $(this).data('side');
        console.log('Switching to side:', side);
        $('.badge-side').removeClass('active');
        $('.badge-side.' + side).addClass('active');
        $('.side-switcher button').removeClass('active');
        $(this).addClass('active');
    });

    function openPreview() {
        const templateData = {
            layout: {
                front: getFieldsData('#badge_front'),
                back: getFieldsData('#badge_back')
            },
            styles: getStyleData()
        };

        const encodedData = encodeURIComponent(JSON.stringify(templateData));
        const previewUrl = `${wpSmartBadge.adminUrl}admin.php?page=wp-smart-badge-preview&data=${encodedData}`;
        window.open(previewUrl, '_blank', 'width=1200,height=800');
    }

    function saveTemplate() {
        const templateData = {
            layout: {
                front: getFieldsData('#badge_front'),
                back: getFieldsData('#badge_back')
            },
            styles: getStyleData()
        };

        $.ajax({
            url: wpSmartBadge.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wp_smart_badge_save_template',
                nonce: wpSmartBadge.nonce,
                template_id: $('#template_select').val(),
                template_data: JSON.stringify(templateData)
            },
            beforeSend: function() {
                console.log('Saving template...');
            },
            success: function(response) {
                console.log('Template saved:', response);
                if (response.success) {
                    alert(wpSmartBadge.i18n.templateSaved);
                } else {
                    alert(wpSmartBadge.i18n.errorSaving);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error saving template:', error);
                alert(wpSmartBadge.i18n.errorSaving);
            }
        });
    }

    function loadTemplate(templateId) {
        console.log('Loading template:', templateId);
        $.ajax({
            url: wpSmartBadge.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wp_smart_badge_get_template_data',
                nonce: wpSmartBadge.nonce,
                template_id: templateId
            },
            success: function(response) {
                console.log('Template loaded:', response);
                if (response.success && response.data) {
                    applyTemplate(response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading template:', error);
            }
        });
    }

    function applyTemplate(templateData) {
        console.log('Applying template:', templateData);
        
        // Clear existing fields
        $('#badge_front, #badge_back').empty();

        if (templateData.layout) {
            if (templateData.layout.front) {
                templateData.layout.front.forEach(field => {
                    addFieldToBadge(field, $('#badge_front'));
                });
            }
            if (templateData.layout.back) {
                templateData.layout.back.forEach(field => {
                    addFieldToBadge(field, $('#badge_back'));
                });
            }
        }

        if (templateData.styles) {
            applyStyles(templateData.styles);
        }
    }

    function addFieldToBadge(field, $container) {
        const $field = $('<div>', {
            class: 'badge-field',
            'data-field': field.type,
            css: {
                left: field.x + 'px',
                top: field.y + 'px',
                width: field.width + 'px',
                height: field.height + 'px'
            }
        });

        const $icon = $('<span>', {
            class: 'dashicons ' + getFieldIcon(field.type)
        });

        const $text = $('<span>', {
            text: field.text || getFieldLabel(field.type)
        });

        const $remove = $('<span>', {
            class: 'remove-field',
            html: '×',
            click: function(e) {
                e.stopPropagation();
                $(this).parent().remove();
            }
        });

        $field.append($icon, $text, $remove);
        $container.append($field);

        $field.draggable({
            containment: 'parent',
            start: function(event, ui) {
                console.log('Field drag started');
            },
            stop: function(event, ui) {
                console.log('Field drag stopped');
            }
        });
    }

    // Utility functions
    function getFieldsData($container) {
        const fields = [];
        $container.find('.badge-field').each(function() {
            const $field = $(this);
            const position = $field.position();
            fields.push({
                type: $field.data('field'),
                x: position.left,
                y: position.top,
                width: $field.width(),
                height: $field.height(),
                text: $field.find('.field-label').text()
            });
        });
        return fields;
    }

    function getStyleData() {
        return {
            background: {
                type: $('#background_type').val(),
                color: $('#background_color').val(),
                gradient: {
                    start: $('#gradient_start').val(),
                    end: $('#gradient_end').val(),
                    direction: $('#gradient_direction').val()
                }
            },
            text: {
                color: $('#text_color').val(),
                size: $('#text_size').val()
            }
        };
    }

    function applyStyles(styles) {
        if (!styles) return;
        
        if (styles.background) {
            $('#background_type').val(styles.background.type);
            if (styles.background.type === 'gradient') {
                $('#gradient_start').wpColorPicker('color', styles.background.gradient.start);
                $('#gradient_end').wpColorPicker('color', styles.background.gradient.end);
                $('#gradient_direction').val(styles.background.gradient.direction);
                $('.solid-bg').hide();
                $('.gradient-bg').show();
            } else {
                $('#background_color').wpColorPicker('color', styles.background.color);
                $('.solid-bg').show();
                $('.gradient-bg').hide();
            }
        }
        
        if (styles.text) {
            $('#text_color').wpColorPicker('color', styles.text.color);
            $('#text_size').val(styles.text.size);
        }
        
        updateBadgeStyle();
    }

    function updateBadgeStyle() {
        const styles = getStyleData();
        const $badge = $('.badge-content');
        
        if (styles.background.type === 'solid') {
            $badge.css({
                background: styles.background.color,
                backgroundImage: 'none'
            });
        } else {
            $badge.css({
                backgroundImage: `linear-gradient(${styles.background.gradient.direction}, ${styles.background.gradient.start}, ${styles.background.gradient.end})`
            });
        }
        
        $('.badge-field').css({
            color: styles.text.color,
            fontSize: getFontSize(styles.text.size)
        });
    }

    function getFontSize(size) {
        switch (size) {
            case 'small': return '10px';
            case 'large': return '14px';
            default: return '12px';
        }
    }

    function showNotice(type, message) {
        const $notice = $('<div>', {
            class: 'notice notice-' + type + ' is-dismissible',
            html: '<p>' + message + '</p>'
        });

        $('.wrap h1').after($notice);
        setTimeout(function() {
            $notice.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }

    // Background type change handler
    $('#background_type').on('change', function() {
        const type = $(this).val();
        if (type === 'solid') {
            $('.solid-bg').show();
            $('.gradient-bg').hide();
        } else {
            $('.solid-bg').hide();
            $('.gradient-bg').show();
        }
        updateBadgeStyle();
    });

    // Event handlers
    $('#template_select').on('change', function() {
        loadTemplate($(this).val());
    });

    // Initialize event listeners
    jQuery(document).ready(function($) {
        // Add preview button click handler
        $('#preview_template').on('click', function() {
            openPreview();
        });
    });

    // Initialize
    initDraggable();
    loadTemplate($('#template_select').val());
});
