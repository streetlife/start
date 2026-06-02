<?php
// prevent caching
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 1 Jan 2000 00:00:00 GMT");

include('functions.php');

ini_set('display_errors', 1);

define('SHOW_ICON', false);
define('REFRESH_RATE', 600);
define('SETTINGS_FILE', 'data/settings.json');
define('TODO_FILE', 'data/todo.json');
define('LINKS_FILE', 'data/links_v2.json');

// Default Settings
$defaults = [
    'cols' => 7,
    'show_icon' => false,
    'selected_style' => 'css/bootstrap-default.css',
    'search_history' => [],
    'view_mode' => 'list'
];

// Load Settings
$settings = json_decode(@file_get_contents(SETTINGS_FILE), true) ?: $defaults;
$settings = array_merge($defaults, $settings);

if (isset($_GET['private']) && $_GET['private'] === 'true') {
    define('PRIVATE_MODE', true);
} else {
    define('PRIVATE_MODE', false);
}

// Update Settings on Request
if (isset($_GET['style'])) {
    $settings['selected_style'] = $_GET['style'];
    save_settings($settings);
}

// Process tracking if search is submitted
if (isset($_GET['q'])) {
    track_search($_GET['q']);
}

$search_history = json_decode(@file_get_contents('data/search_history.json'), true) ?: [];
$todos = json_decode(@file_get_contents(TODO_FILE), true) ?: [];
$rawMenu = json_decode(@file_get_contents(LINKS_FILE), true) ?: ['folders' => [], 'links' => []];

$project_links = [];
foreach (array_filter(glob('../' . '*'), 'is_dir') as $dir) {
    $val = strtolower(basename($dir));
    if ($val === 'start') continue;
    $project_links[] = ['id' => 'p_'.$val, 'label' => $val, 'url' => "https://$val.test", 'folder_id' => 'dev_root'];
}
$project_folder = [['id' => 'dev_root', 'name' => 'dev projects', 'sort' => -1]];

// Merge project links into main links
$rawMenu['links'] = array_merge($project_links, $rawMenu['links']);
$rawMenu['folders'] = array_merge($project_folder, $rawMenu['folders']);

$css_form = load_css_files();
$selected_style = get_selected_style();

check_delete_todo($todos);

$stats = get_stats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title> ~ esquire </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link rel="stylesheet" type="text/css" href="<?php echo $settings['selected_style']; ?>" >
    <link rel="stylesheet" type="text/css" href="css/style.min.css">
    <meta http-equiv="refresh" content="<?php echo REFRESH_RATE; ?>" />
    <style>
        li { list-style-type: none; }
        .hidden { display: none; }
        #search { margin-bottom: 20px; padding: 10px; width: 300px; font-size: 16px; }
        .link { padding: 0; margin: 0; }
    </style>
    <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
</head>
<body class="p-0">

<div class="container-fluid">
    <div class="row g-0">
        <div class="col-md-10">
            <div class="card">
                <div class="card-body p-2">
                    <form id="search-form" method="get" onsubmit="return handleSearch();" class="form">
                        <input type="text" id="search-box" name="q" 
                            placeholder="Filter or Search..." 
                            class="form-control form-control-sm m-0" 
                            list="search-history-list" 
                            autocomplete="off" 
                            required>
                        <datalist id="search-history-list">
                            <?php foreach ($search_history as $item): ?>
                                <option value="<?php echo htmlspecialchars($item); ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </form>
                </div>
            </div>
            <div class="card bg-transparent m-0">
                <div class="card-body">
                    <nav class="nav">
                        <?php echo createMenu($rawMenu['folders'], $rawMenu['links']); ?>
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

            <div class="card-body border-top">
                <div class="card-header border-0 bg-transparent p-0"><h6>Stats</h6></div>
                <pre style="font-size: 10px; max-height: 200px; overflow-y: auto;"><?php echo htmlspecialchars($stats); ?></pre>
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

    document.getElementById("search-box").addEventListener("input", function() {
        const filter = this.value.toLowerCase();
        const navUl = document.querySelector(".nav > ul");
        const allLinks = document.querySelectorAll(".sub-menu li.link");
        
        // Toggle multi-column layout
        if (filter.length > 0) {
            if(navUl) navUl.classList.remove("multi-column-list");
        } else {
            if(navUl) navUl.classList.add("multi-column-list");
        }

        allLinks.forEach(item => {
            const labelText = item.textContent.toLowerCase();
            const urlText = item.getAttribute("data-url"); // Get the hidden URL data
            
            // Search matches if filter is in label OR in URL
            const isMatch = labelText.includes(filter) || urlText.includes(filter);
            item.style.display = isMatch ? "" : "none";
        });
        
        // Update Folder Visibility
        document.querySelectorAll(".nav .folder-container").forEach(folder => {
            const hasVisibleChild = folder.querySelector("li.link:not([style*='display: none'])");
            folder.style.display = (hasVisibleChild || filter === "") ? "" : "none";
        });
    });

    function handleSearch() {
        const query = document.getElementById('search-box').value.trim();
        if (!query) return false;

        // Track the search via background ping before navigating
        fetch('index.php?q=' + encodeURIComponent(query));

        const visibleLinks = document.querySelectorAll(".nav li.link:not([style*='display: none']) a");

        // Launch if exactly one link is visible
        if (visibleLinks.length === 1 && query !== "") {
            const link = visibleLinks[0];
            if (link.target === "_blank") {
                window.open(link.href, '_blank');
            } else {
                window.location.href = link.href;
            }
            return false;
        }

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