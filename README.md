# Start Page

A customizable PHP-based start page / desktop interface for organizing links, folders, and tasks.

## Features

### Desktop Interface (index.php)
- Windows 95/XP/Modern/macOS desktop themes with additional themes (Aero, Neon, Glass, Terminal, Sunset)
- Draggable windows for folder contents
- Start menu with folder navigation and theme selector
- Taskbar with window management and clock
- Desktop icons for favorites and folders
- Kanban-style task board (draggable, remembers minimized state)
- Search functionality with history
- Toggle visibility of hidden links
- Multiple widgets (NG, Canada clocks)

### Classic View (classic.php)
- Bootstrap-based tile/grid view
- Multiple Bootstrap theme options
- Folder filtering and organization
- Icon support for links

### Management (manage.php)
- Add/edit/delete links and folders
- CSV import for bulk link addition
- Folder visibility toggling
- Icon management and favicon refresh
- Drag/reorder and sorting capabilities
- Global settings (columns, view mode, display options)

### Backend (functions.php)
- Data persistence (JSON files in data/)
- Favicon fetching and caching
- Search history tracking
- Link and folder utilities

## File Structure
- `index.php` - Main desktop interface
- `classic.php` - Classic tile view
- `manage.php` - Link/folder management interface with CSV import
- `functions.php` - Core utility functions
- `css/` - Stylesheets and Bootstrap themes
- `js/` - JavaScript files
- `data/` - JSON data storage (links, settings, todos, search history)
- `img/` - Images and cached icons
- `adminneo/` - Database Admin tools

## Getting Started
1. Ensure PHP is installed
2. Set up web server pointing to this directory
3. Access index.php for desktop view or manage.php to configure links
4. Data is stored in `data/*.json` files

## Data Format
Links and folders stored in `data/links_v2.json`. Settings in `data/settings.json`. Tasks in `data/todo.json`.
