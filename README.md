# WP Smart Badge - ID Card Generator

A WordPress plugin for generating professional ID cards with QR codes and custom templates.

## Features

- Generate professional ID cards with customizable templates
- Support for multiple badge templates based on roles/departments
- QR code generation for easy identification
- Bulk generation of badges for multiple employees
- Client-side pagination and filtering with AG Grid
- Responsive design for all screen sizes
- Enhanced print functionality with color gradients and shadows
- Interactive card preview with hover effects
- PDF download support for digital storage
- Bulk import/export functionality for employee data
- Role-based access control for different cities
- Mandatory profile picture upload
- Manual employee data management by managers

## Access Control

1. **City-Based Access**
   - 5 different login accounts for different cities
   - Data filtering based on work location
   - Only Depot managers have edit rights

2. **User Management**
   - Manual employee addition by managers
   - Bulk data import/export capabilities
   - Profile picture mandatory upload
   - Work location based filtering

## Templates

The plugin includes several badge templates:

1. **Active Employee Template (Horizontal)**
   - Left-side photo layout
   - Right-side employee information
   - Clean, professional design
   - Includes QR code for digital verification
   - Supports both front and back sides
   - Modern gradient background with shadow effects

2. **Active Employee Template 2 (Right-side)**
   - Alternative layout with right-aligned elements
   - Customizable field positioning
   - Modern design elements
   - Gradient background support

3. **Retired Employee Template**
   - Specialized template for retired staff
   - Includes retirement-specific information
   - Special styling and formatting
   - Additional fields for retirement benefits

4. **Retired Medical Card Template**
   - Medical-specific information
   - Special medical styling theme
   - Benefits and concessions display
   - Extended validity period

5. **Retired Employee Spouse Card**
   - Spouse-specific information
   - Family member details
   - Dependent benefits display
   - Special styling for family cards

## Badge Template Customization

The plugin includes a powerful template customizer with advanced features:

- **Canva-like Interface**: Modern drag-and-drop interface for easy badge customization
  - Drag elements from the sidebar onto your badge
  - Resize and reposition elements with precision using the grid system
  - Double-click text to edit content directly
  - Switch between front and back sides of the badge
  - Preview your changes in real-time

- Interactive canvas-like interface for precise template customization
- Drag-and-drop functionality with grid snapping for perfect alignment
- Resize handles for adjusting field dimensions
- Double-click text editing for quick content updates
- Grid overlay (press Alt key) for precise positioning
- Real-time preview of changes
- Save and load template functionality
- Default templates for quick start (Active Employee, Retired Medical, etc.)

### Available Fields

- **User Details**
  - Photo
  - Name
  - Employee ID

- **Professional Info**
  - Designation
  - Department

- **Additional Info**
  - Blood Group
  - QR Code

### Style Options

- **Background**
  - Solid Color
  - Gradient (with customizable start/end colors and direction)
  - Grid overlay for alignment assistance

- **Text**
  - Color
  - Size (Small, Medium, Large)
  - Double-click editing support
  - Drag-and-drop positioning

### Usage

1. Navigate to WP Smart Badge > Templates
2. Select a template from the dropdown or start with a default template
3. Use the canvas interface to:
   - Drag and drop fields onto the badge preview
   - Resize fields using the handles
   - Double-click text to edit content
   - Hold Alt to show grid for alignment
4. Customize colors and styles using the sidebar options
5. Click "Save Template" to save your changes
6. Use "Reset" to revert to default template settings

## Data Management

### Import Features
- Bulk CSV data import
- File preview before import
- Data validation
- AG Grid integration
- Real-time grid refresh after import
- Work location filtering

### Export Features
- Export to CSV/Excel
- Filtered data export
- Custom field selection
- Batch processing support

## Template System

The plugin uses an object-oriented template system for generating ID badges:

1. **Base Template Class**
   - Located in `includes/templates/class-badge-template.php`
   - Abstract class that defines the structure for all badge templates
   - Provides common functionality like QR code generation and user data handling

2. **Template Types**
   - `ActiveEmployeeTemplate`: For current employees (vertical)
   - `ActiveEmployeeHorizontalTemplate`: Alternative layout (horizontal)
   - `ActiveEmployeeTemplate2`: Alternative layout (right-side)
   - `RetiredEmployeeTemplate`: For retired staff
   - `RetiredMedicalTemplate`: For medical cards
   - `RetiredSpouseTemplate`: For family members

### Print and Preview Features

1. **Enhanced Print Support**
   - True color printing with gradients and backgrounds
   - Print-specific CSS adjustments
   - Proper page orientation
   - Support for both single and double-sided printing

2. **Interactive Preview**
   - Real-time preview of both card sides
   - Hover effects for better user experience
   - Card-like appearance with shadows
   - Accurate representation of final output

3. **PDF Generation**
   - High-quality PDF export
   - Maintains all styling and formatting
   - Perfect for digital storage
   - Supports batch processing

## Installation

1. Upload the plugin files to `/wp-content/plugins/wp-smart-badge`
2. Activate the plugin through WordPress admin
3. Configure city-based access and user roles
4. Import initial employee data
5. Start generating badges

## Usage

### For Managers
1. Log in with city-specific credentials
2. Access employee data filtered by work location
3. Add/Edit employee information
4. Upload profile pictures
5. Generate and print badges

### For Administrators
1. Manage city-based access
2. Configure template settings
3. Handle bulk data operations
4. Monitor system usage

## Development

### Requirements
- WordPress 5.0+
- PHP 7.4+
- MySQL 5.6+

### Database Schema
The plugin uses custom tables for:
- Employee data
- Template configurations
- Access control
- Work location mapping
