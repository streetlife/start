<?php 

function save_settings($data) {
    if (!is_dir('data')) mkdir('data', 0777, true);
    file_put_contents(SETTINGS_FILE, json_encode($data, JSON_PRETTY_PRINT));
}

function track_search($query) {
    global $settings;
    if (empty($query) || strlen($query) < 2) return;
    
    $query = strtolower(trim($query));
    if (!in_array($query, $settings['search_history'])) {
        array_unshift($settings['search_history'], $query);
        $settings['search_history'] = array_slice($settings['search_history'], 0, 20);
        save_settings($settings);
    }
}

function get_selected_style() {
    if (isset($_GET['style'])) {
        file_put_contents('selected_style.txt', $_GET['style']);
    }
    return file_exists('selected_style.txt') ? file_get_contents('selected_style.txt') : 'css/bootstrap-default.css';
}

function load_css_files() {
    global $settings;
    $css_files = glob('css/bootstrap-*.css');
    $select_list = '<select name="style" onchange="this.form.submit()" class="form-control form-control-sm">';
    foreach ($css_files as $css_file) {
        $css_file_name = str_replace(['css/bootstrap-', '.min.css', '.css'], '', $css_file);
        $selected = ($settings['selected_style'] == $css_file) ? 'selected' : '';
        $select_list .= "<option value='$css_file' $selected>" . strtoupper($css_file_name) . "</option>";
    }
    $select_list .= '</select>';
    return '<form method="get" class="form">' . $select_list . '</form>';
}

function load_todo($completed_flag = false) {
    global $todos;
    if (empty($todos)) return '';
    $result = '<ul class="list-group list-group-flush border-0 mb-2">';
    foreach ($todos as $todo) {
        if ($todo['done'] == $completed_flag) {
            $result .= '<a href="index.php?action=delete_todo&id=' . $todo['id'] . '" class="list-group-item list-group-item-action" onclick="return confirm(\'Are you sure you want to delete - ' . addslashes($todo['text']) . '?\')">' . htmlspecialchars($todo['text']) . '</a>';
        }
    }
    $result .= '</ul>';
    return $result;
}

function load_todo_column($status) {
    global $todos;
    $titles = ['todo' => 'To Do', 'doing' => 'In Progress', 'done' => 'Completed'];
    $bg = ['todo' => 'bg-secondary', 'doing' => 'bg-info', 'done' => 'bg-success'];
    
    // Count items for this column
    $count = 0;
    if (!empty($todos)) {
        foreach ($todos as $t) {
            $curr = $t['status'] ?? ($t['done'] ? 'done' : 'todo');
            if ($curr === $status) $count++;
        }
    }

    $result = '<div class="kanban-col mb-3">';
    
    // Header with Toggle for "Done"
    $result .= '<div class="kanban-header p-1 mb-2 border-bottom d-flex justify-content-between align-items-center" ' . ($status === 'done' ? 'data-bs-toggle="collapse" data-bs-target="#collapseDone" style="cursor:pointer"' : '') . '>';
    $result .= '<h6 class="m-0 small fw-bold text-uppercase">' . $titles[$status] . ' (' . $count . ')</h6>';
    $result .= '<span class="badge rounded-pill ' . $bg[$status] . ' opacity-75" style="font-size:0.6rem">' . ($status === 'done' ? '↕' : '') . '</span>';
    $result .= '</div>';
    
    // Wrap Done items in a collapse div
    if ($status === 'done') {
        $result .= '<div class="collapse" id="collapseDone">';
    }

    if ($count > 0) {
        foreach ($todos as $todo) {
            $currentStatus = $todo['status'] ?? ($todo['done'] ? 'done' : 'todo');
            if ($currentStatus === $status) {
                $result .= '<div class="card mb-1 shadow-sm kanban-card border-0">';
                $result .= '<div class="card-body p-2 small d-flex justify-content-between align-items-center">';
                $result .= '<span class="' . ($status === 'done' ? 'text-decoration-line-through text-muted' : '') . '">' . htmlspecialchars($todo['text']) . '</span>';
                $result .= '<div class="dropdown">';
                // Find this line inside your load_todo_column function:
                $result .= '<button class="btn btn-sm p-0 opacity-50" data-bs-toggle="dropdown" data-bs-boundary="viewport">⋮</button>';
                $result .= '<ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 small">';
                
                if ($status != 'todo') $result .= '<li><a class="dropdown-item" href="index.php?action=move_todo&id='.$todo['id'].'&to=todo">Move to To-Do</a></li>';
                if ($status != 'doing') $result .= '<li><a class="dropdown-item" href="index.php?action=move_todo&id='.$todo['id'].'&to=doing">Move to Doing</a></li>';
                if ($status != 'done') $result .= '<li><a class="dropdown-item text-success" href="index.php?action=move_todo&id='.$todo['id'].'&to=done">Complete</a></li>';
                
                $result .= '<li><hr class="dropdown-divider"></li>';
                $result .= '<li><a class="dropdown-item text-danger" href="index.php?action=delete_todo&id='.$todo['id'].'" onclick="return confirm(\'Delete?\')">Delete Permanently</a></li>';
                $result .= '</ul></div></div></div>';
            }
        }
    }
    
    if ($status === 'done') {
        $result .= '</div>'; // Close collapse div
    }

    $result .= '</div>';
    return $result;
}

// Generate a random color
function rand_color() {
	return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
	// return '#FFFFFF';
}

function randomDarkColor() {
	// Define the maximum brightness for a dark color (adjustable)
	$maxBrightness = 40;
	
	$red = rand(floor($maxBrightness / 2), $maxBrightness);
    $green = rand(floor($maxBrightness / 2), $maxBrightness);
    $blue = rand(floor($maxBrightness / 2), $maxBrightness);
  
	// Convert values to hex string and return as #rrggbb format
	return '#' . dechex($red) . dechex($green) . dechex($blue);
}

function randomLightColor() {
	// Define the maximum brightness for a dark color (adjustable)
	$maxBrightness = 255;
	
	$red = rand($maxBrightness / 2, $maxBrightness);
    $green = rand($maxBrightness / 2, $maxBrightness);
    $blue = rand($maxBrightness / 2, $maxBrightness);
  
	// Convert values to hex string and return as #rrggbb format
	return '#' . dechex($red) . dechex($green) . dechex($blue);

}

function createMenu($folders, $allLinks) {
    global $settings;
    $output = '';
    $colCount = $settings['cols'];
    $showIcon = $settings['show_icon'];
    $multiColumn = ($colCount > 1);

    $multiColumnClass = $multiColumn ? 'multi-column-list' : '';
    // We inject the column count into a CSS variable --col-count
    $output .= '<ul class="'.$multiColumnClass.' p-0 m-0" style="width:100%; --col-count: '.$colCount.';">';

    // $output .= '<ul class="'.$multiColumnClass.' p-0 m-0" style="width:100%">';
    foreach ($folders as $folder) {
        // 1. Filter links belonging to this folder
        $folderLinks = array_filter($allLinks, function($l) use ($folder) {
            return $l['folder_id'] === $folder['id'];
        });

        // 2. NEW: Remove links explicitly marked as 'hidden' in JSON
        $folderLinks = array_filter($folderLinks, function($l) {
            return !(isset($l['hidden']) && $l['hidden'] === true);
        });

        // If no visible links exist, skip the folder entirely
        if (empty($folderLinks)) continue;

        if (isset($folder['private']) && $folder['private'] == 'true') {
            if (PRIVATE_MODE) {
                continue; // Show private folders only in private mode
            }
        }

        if (isset($folder['color']) && !empty($folder['color'])) {
            $bgColor = $folder['color'];
        } else {
            $bgColor = '#000000';
        }

        // Add a class 'folder-container' to make JS targeting easier
        $output .= '
        <li class="link folder-container bg-transparent">
            <div class="card m-0 bg-transparent border-0 pb-2">
                <div class="card-body p-0">
                    <div class="card-header bg-transparent folder-header">
                        <h6 class="m-0 fw-bold text-uppercase">
                        <!-- <iconify-icon icon="' . (isset($folder['icon']) ? $folder['icon'] : 'mdi:home') . '"></iconify-icon> -->
                        ' . $folder['name'] . '</h6>
                    </div>
                    <ul class="sub-menu p-0 px-3 m-0">';
        
        usort($folderLinks, fn($a, $b) => strnatcasecmp($a['label'], $b['label']));

        foreach ($folderLinks as $link) {
            $target = '_self';
            // Added data-url attribute here
            $output .= '<li class="link" style="" data-url="' . htmlspecialchars(strtolower($link['url'])) . '">
                <a href="' . htmlspecialchars($link['url']) . '" target="' . $target . '" class="nav-link p-0 m-0">';
            
            if ($showIcon) {
                $safeLabel = preg_replace('/[^a-z0-9]/i', '_', $link['label']);
                $local_name = 'img/icons/' . $safeLabel . '.png';
                if (!file_exists($local_name) || filesize($local_name) == 0) {
                    fetch_favicon($safeLabel, $link['url']);
                    //@copy($local_name_offline, $local_name);
                }
                $output .= '<img src="' . $local_name . '" class="icon" style="clear:both" /> ';
            }

            $displayLabel = strtolower(str_replace(['www.'], [''], $link['label']));
            $displayLabel = textrim($displayLabel, 14);
            $output .= htmlspecialchars($displayLabel) . '</a></li>';
        }
        $output .= '</ul>
                </div>
            </div>
        </li>';
    }
    $output .= '</ul>';
    return $output;
}

function createMenu2($folders, $allLinks) {
    global $settings;
    $output = '';
    $local_name_offline = 'img/icon-local.png';
    $colCount = $settings['cols'];
    $showIcon = $settings['show_icon'];
    
    // Determine view mode: 'list' (Explorer Tiles) or 'grid' (Large Icons)
    $viewMode = $settings['view_mode'] ?? 'list'; 

    $output .= '<div class="folder-grid mode-' . $viewMode . '" style="--col-count: ' . $colCount . ';">';

    foreach ($folders as $folder) {
        $folderLinks = array_filter($allLinks, fn($l) => $l['folder_id'] === $folder['id'] && !(isset($l['hidden']) && $l['hidden']));
        if (empty($folderLinks)) continue;

        $output .= '<div class="folder-container mb-4">';
        $output .= '<div class="folder-header d-flex align-items-center mb-2 px-2">';
        $output .= '<iconify-icon icon="' . ($folder['icon'] ?? 'mdi:folder') . '" style="font-size: 1.2rem; margin-right: 8px;"></iconify-icon>';
        $output .= '<h6 class="m-0 fw-bold text-uppercase small">' . $folder['name'] . '</h6>';
        $output .= '</div>';
        
        $output .= '<div class="link-collection">';
        usort($folderLinks, fn($a, $b) => strnatcasecmp($a['label'], $b['label']));

        foreach ($folderLinks as $link) {
            $safeLabel = preg_replace('/[^a-z0-9]/i', '_', $link['label']);
            $iconPath = 'img/icons/' . $safeLabel . '.png';
            $displayLabel = strtolower(str_replace(['www.', '_', ' '], ['', '.', '.'], $link['label']));
            
            // Generate icon with cache-busting timestamp
            $imgSrc = (file_exists($iconPath)) ? $iconPath . '?v=' . filemtime($iconPath) : $local_name_offline;

            $output .= '<a href="' . htmlspecialchars($link['url']) . '" class="item-link" data-url="' . htmlspecialchars(strtolower($link['url'])) . '">';
            $output .= '<div class="item-content">';
            if ($showIcon) {
                $output .= '<img src="' . $imgSrc . '" class="item-icon" />';
            }
            $output .= '<span class="item-label">' . htmlspecialchars($displayLabel) . '</span>';
            $output .= '</div></a>';
        }
        $output .= '</div></div>';
    }
    $output .= '</div>';
    return $output;
}

function check_new_todo(&$todos) {
    if (isset($_POST['action']) && $_POST['action'] == 'add_todo' && !empty($_POST['todo'])) {
        $id = empty($todos) ? 1 : max(array_column($todos, 'id')) + 1;
        $todos[] = ['id' => $id, 'text' => $_POST['todo'], 'done' => false];
        file_put_contents(TODO_FILE, json_encode($todos));
        header('Location: index.php'); exit;
    }
}

function check_delete_todo(&$todos) {
    // Move Todo Action
    if (isset($_GET['action']) && $_GET['action'] == 'move_todo') {
        foreach ($todos as &$todo) {
            if ($todo['id'] == $_GET['id']) {
                $todo['status'] = $_GET['to'];
                // Sync with legacy boolean for safety
                $todo['done'] = ($_GET['to'] === 'done');
            }
        }
        file_put_contents(TODO_FILE, json_encode($todos, JSON_PRETTY_PRINT));
        header('Location: index.php'); exit;
    }
}

function ensure_folder_colors(&$data) {
    $updated = false;
    
    foreach ($data['folders'] as &$folder) {
        // Check if color is missing, empty, or default white
        if (!isset($folder['color']) || empty($folder['color']) || $folder['color'] === '#ffffff') {
            
            // Generate a random dark color
            $red = rand(20, 100);
            $green = rand(20, 100);
            $blue = rand(20, 100);
            $folder['color'] = sprintf('#%02x%02x%02x', $red, $green, $blue);
            
            $updated = true;
        }
    }

    // Only write to disk if we actually changed something to save I/O
    if ($updated) {
        if (!is_dir('data')) mkdir('data', 0777, true);
        file_put_contents(LINKS_FILE, json_encode($data, JSON_PRETTY_PRINT));
    }
}
function fetch_favicon($label, $url) {
    $iconDir = 'img/icons/';
    if (!is_dir($iconDir)) {
        mkdir($iconDir, 0777, true);
    }

    $safeLabel = preg_replace('/[^a-z0-9]/i', '_', $label);
    $local_path = $iconDir . $safeLabel . '.png';
    $local_name_offline = 'img/icon-local.png';

    $host = parse_url($url, PHP_URL_HOST);
    if (!$host) {
        return false;
    }

    // Mimic browser to avoid 403 Forbidden errors
    $options = [
        "http" => [
            "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36\r\n",
            "timeout" => 5,
            "follow_location" => 1,  // Follow redirects
            "max_redirects" => 3
        ],
        "ssl" => [
            "verify_peer" => false,  // Handle self-signed certs
            "verify_peer_name" => false
        ]
    ];
    $context = stream_context_create($options);

    // FIXED: Removed space after 'domain=' in the URL
    $favicon_url = 'https://www.google.com/s2/favicons?domain=' . $host . '&sz=64';
    $iconData = @file_get_contents($favicon_url, false, $context);

    // Save only if we got valid data (larger than 500 bytes to avoid generic globe icons)
    if ($iconData !== false && strlen($iconData) > 500) {
        file_put_contents($local_path, $iconData);
        return true;
    }

    // Fallback to offline icon if fetch fails
    if (file_exists($local_name_offline)) {
        @copy($local_name_offline, $local_path);
        return 'fallback';
    }

    return false;
}

function save($data) {
    usort($data['folders'], fn($a, $b) => $a['sort'] <=> $b['sort']);
    if (!is_dir('data')) mkdir('data', 0777, true);
    file_put_contents(DATA_FILE, json_encode($data, JSON_PRETTY_PRINT));
}

function get_stats() {
    return print_r($_SERVER, true);
}

function textrim($text, $maxLength = 16) {
    if (strlen($text) <= $maxLength) return $text;
    return substr($text, 0, $maxLength - 3) . '...';
}