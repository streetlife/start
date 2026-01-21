<?php
/**
 * index.php - Updated to support Folder IDs and Sort Index
 */

function get_selected_style() {
    if (isset($_GET['style'])) {
        file_put_contents('selected_style.txt', $_GET['style']);
    }
    return file_exists('selected_style.txt') ? file_get_contents('selected_style.txt') : 'css/bootstrap-default.css';
}

function load_css_files() {
    $css_files = glob('css/bootstrap-*.css');
    $select_list = '<select name="style" onchange="this.form.submit()" class="form-control form-control-sm">';
    foreach ($css_files as $css_file) {
        $css_file_name = str_replace(['css/bootstrap-', '.min.css', '.css'], '', $css_file);
        $selected_style = get_selected_style();
        $strSelectedTheme = ($selected_style == $css_file) ? strtoupper($css_file_name) : 'Change theme to ' . strtoupper($css_file_name);
        $select_list .= '<option value="' . $css_file . '" ' . ($selected_style == $css_file ? 'selected' : '') . '>'. $strSelectedTheme  . '</option>';
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
/**
 * Updated createMenu to handle the new relational JSON structure
 */
function createMenu($folders, $allLinks, $showIcon = true, $multiColumn = false) {
    $local_name_offline = 'img/icon-local.png';

    $label_number = 1;

    $multiColumnClass = '';
    if ($multiColumn) {
        $multiColumnClass = 'multi-column-list';
    }

    $lastkey = array_key_last($folders);

    echo '<ul class="'.$multiColumnClass.'" style="width:100%">';
    foreach ($folders as $folder_key=>$folder) {
        // Filter links belonging to this folder
        $folderLinks = array_filter($allLinks, function($l) use ($folder) {
            return $l['folder_id'] === $folder['id'];
        });

        if (empty($folderLinks)) continue;

        echo '<div class="folder-links">';
        echo '<li class="title" style="display:block">' . $folder['name'].'<li>';

        // Sort links alphabetically within the folder
        usort($folderLinks, fn($a, $b) => strnatcasecmp($a['label'], $b['label']));

        foreach ($folderLinks as $link) {
            $target = isset($link['target']) ? $link['target'] : '_self';
            echo '<li class="link">';
            echo '<a href="' . htmlspecialchars($link['url']) . '" target="' . $target . '" class="nav-link p-0 m-0">';
            
            if ($showIcon) {
                $safeLabel = preg_replace('/[^a-z0-9]/i', '_', $link['label']);
                $local_name = 'img/icons/' . $safeLabel . '.png';
                
                if (!file_exists($local_name) && strpos($link['url'], ".test") === false) {
                    $iconData = @file_get_contents('https://www.google.com/s2/favicons?domain=' . parse_url($link['url'], PHP_URL_HOST) . '&sz=64');
                    if ($iconData) file_put_contents($local_name, $iconData);
                }

                if (!file_exists($local_name) || filesize($local_name) == 0) {
                    @copy($local_name_offline, $local_name);
                }
                echo '<img src="' . $local_name . '" class="icon" style="clear:both" /> ';
            }

            $displayLabel = strtolower(str_replace(['www.', '_'], ['', '.'], $link['label']));
            echo htmlspecialchars($displayLabel) . '</a></li>';
        }
        // echo '</ul></div></div></li>';
        if ($folder_key !== $lastkey)  echo '<li class="link">--------<li>';
        echo '</div>';
    }
    echo '</ul>';
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
    // if (isset($_GET['action']) && $_GET['action'] == 'delete_todo') {
    //     foreach ($todos as &$todo) {
    //         if ($todo['id'] == $_GET['id']) $todo['done'] = true;
    //     }
    //     file_put_contents(TODO_FILE, json_encode($todos));
    //     header('Location: index.php'); exit;
    // }
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

ini_set('display_errors', 1);
define('SHOW_ICON', true);
define('REFRESH_RATE', 600);
define('TODO_FILE', 'data/todo.json');
define('LINKS_FILE', 'data/links_v2.json');

// Load Data
$todos = json_decode(@file_get_contents(TODO_FILE), true) ?: [];
$rawMenu = json_decode(@file_get_contents(LINKS_FILE), true) ?: ['folders' => [], 'links' => []];

// Prepare Projects (Auto-discovery stays as a dynamic "Folder")
$project_links = [];
foreach (array_filter(glob('../' . '*'), 'is_dir') as $dir) {
    $val = strtolower(basename($dir));
    $project_links[] = ['id' => 'p_'.$val, 'label' => $val, 'url' => "https://$val.test", 'folder_id' => 'dev_root'];
}
$project_folder = [['id' => 'dev_root', 'name' => 'dev projects', 'sort' => -1]];

$css_form = load_css_files();
$selected_style = get_selected_style();

// check_new_todo($todos);
check_delete_todo($todos);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title> ~ esquire </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link rel="stylesheet" type="text/css" href="<?php echo $selected_style; ?>" >
    <link rel="stylesheet" type="text/css" href="css/style.min.css">
    <meta http-equiv="refresh" content="<?php echo REFRESH_RATE; ?>" />
    <style>
        li { list-style-type: none; }
        .hidden { display: none; }
        #search { margin-bottom: 20px; padding: 10px; width: 300px; font-size: 16px; }
        .link { padding: 0; margin: 0; }
    </style>
</head>
<body class="p-0">

<div class="container-fluid p-2">
    <div class="row g-0 p-0">
        <div class="col-md-1">
            <div class="card bg-transparent m-0">
                <div class="card-body p-0">
                    <nav class="nav">
                        <?php createMenu($project_folder, $project_links, true, false); ?>
                    </nav>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card-body">
                <form id="search-form" method="get" onsubmit="return handleSearch();" class="form">
                    <input type="text" id="search-box" name="q" placeholder="Filter or Search..." class="form-control form-control-sm m-0" required>
                </form>
            </div>
            <div class="card bg-transparent m-0">
                <div class="card-body p-1">
                    <nav class="nav">
                        <?php createMenu($rawMenu['folders'], $rawMenu['links'], true, true); ?>
                    </nav>
                </div>
            </div>
        </div>
        <div class="col-md-2">  
            <div class="card bg-transparent m-0">
                <div class="card-body">
                    <a href="manage.php" class="btn btn-sm btn-outline-secondary w-100">Manage Links</a>
                </div>
                <div class="card-body">
                    <?php echo $css_form; ?>
                </div>
                <div class="card-body border-top">
                    <div class="card-header border-0 bg-transparent p-0"><h6>Nigeria</h6></div>
                    <div id="currentTime" class="fw-bold">--:--:--</div>
                </div>
                <div class="card-body border-top">
                    <div class="card-header border-0 bg-transparent p-0"><h6>Canada</h6></div>
                    <div id="currentTime2" class="fw-bold">--:--:--</div>
                </div>
            </div>
            
            <div class="card-body border-top">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="m-0 fw-bold">Tasks</h6>
                    <button class="btn btn-sm btn-light py-0 px-1 border" type="button" data-bs-toggle="collapse" data-bs-target="#kanbanContainer">+</button>
                </div>
                
                <form action="index.php" method="post" class="mb-3">
                    <input type="text" name="todo" class="form-control form-control-sm" placeholder="New task + Enter" required>
                    <input type="hidden" name="action" value="add_todo">
                </form>

                <div id="kanbanContainer" class="collapse show">
                    <div class="kanban-wrapper" style="max-height: 500px; overflow-y: auto; overflow-x: hidden;">
                        <?php echo load_todo_column('todo'); ?>
                        <?php echo load_todo_column('doing'); ?>
                        <?php echo load_todo_column('done'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="js/bootstrap.bundle.min.js"></script>
<script>
    window.onload = function() { document.getElementById('search-box').focus(); };

    function isValidURL(string) {
        try { new URL(string.startsWith("http") ? string : "http://" + string); return string.includes('.'); } 
        catch (_) { return false; }
    }

    function handleSearch() {
        var query = document.getElementById('search-box').value.trim();
        if (isValidURL(query)) {
            window.location.href = query.startsWith("http") ? query : "http://" + query;
        } else {
            window.location.href = "https://www.google.com/search?q=" + encodeURIComponent(query);
        }
        return false;
    }

    function updateClocks() {
        const locales = [
            { id: 'currentTime', zone: 'Africa/Lagos' },
            { id: 'currentTime2', zone: 'America/Toronto' }
        ];
        locales.forEach(loc => {
            const timeStr = new Intl.DateTimeFormat('en-US', {
                timeZone: loc.zone, hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true
            }).format(new Date());
            const el = document.getElementById(loc.id);
            if(el) el.textContent = timeStr;
        });
    }
    setInterval(updateClocks, 1000);
    updateClocks();

    document.getElementById("search-box").addEventListener("input", function() {
        const filter = this.value.toLowerCase();
        document.querySelectorAll(".nav li.link").forEach(item => {
            item.style.display = item.textContent.toLowerCase().includes(filter) ? "" : "none";
        });
    });
</script>
</body>
</html>