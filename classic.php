<?php
// prevent caching
// header("Cache-Control: no-cache, must-revalidate");
// header("Expires: Sat, 1 Jan 2000 00:00:00 GMT");

include('functions.php');

ini_set('display_errors', 0);

define('SHOW_ICON', true);
define('REFRESH_RATE', 600);
define('SETTINGS_FILE', 'data/settings.json');
define('TODO_FILE', 'data/todo.json');
define('LINKS_FILE', 'data/links_v2.json');
define('LABEL_LENGTH', 0);
define('MAIN_LIST_TYPE', 'separate'); // Options: 'merged', 'separate'

// Default Settings
$defaults = [
    'cols' => 6,
    'show_icon' => true,
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
$project_folder = [['id' => 'dev_root', 'name' => 'dev projects', 'sort' => -2]];
$project_folder2 = [['id' => 'dev_root2', 'name' => 'dev projects 2', 'sort' => -1]];

// Merge project links into main links
$projectsMenu['links'] = $project_links;
$projectsMenu['folders'] = $project_folder;

// // Split project links into two columns for display
// $projectsMenu1['links'] = array_splice($project_links, 0, ceil(count($project_links) / 2));
// $projectsMenu1['folders'] = $project_folder;

// $projectsMenu2['links'] = $project_links; // Remaining links
// $projectsMenu2['folders'] = $project_folder2;


// $rawMenu['links'] = array_merge($projectsMenu1['links'], $projectsMenu2['links'], $rawMenu['links']);
// $rawMenu['folders'] = array_merge($projectsMenu1['folders'], $projectsMenu2['folders'], $rawMenu['folders']);



// $rawMenu['links'] = array_merge($project_links, $rawMenu['links']);
// $rawMenu['folders'] = array_merge($project_folder, $rawMenu['folders']);

$css_form = load_css_files();
$selected_style = get_selected_style();

check_delete_todo($todos);

if (isset($_GET['action']) && $_GET['action'] === 'clear_completed') {
    $todos = array_values(array_filter($todos, fn($t) => ($t['status'] ?? null) !== 'done' && empty($t['done'])));
    file_put_contents(TODO_FILE, json_encode($todos, JSON_PRETTY_PRINT));
    header('Location: classic.php'); exit;
}

// $stats = get_stats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title> ~ esquire </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link rel="stylesheet" type="text/css" href="<?php echo $settings['selected_style']; ?>" >
    <link rel="stylesheet" type="text/css" href="css/style.css?v=<?php echo filemtime('css/style.css'); ?>">
    <meta http-equiv="refresh" content="<?php echo REFRESH_RATE; ?>" />
    <style>
        li { list-style-type: none; }
        .hidden { display: none; }
        .is-hidden { display: none; }
        body.show-hidden .is-hidden { display: revert; opacity: 1; }
        #search { margin-bottom: 20px; padding: 10px; width: 300px; font-size: 16px; }
        .link { padding: 0; margin: 0; }
    </style>
    <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
</head>
<body class="p-0">

<div class="container-fluid">
    <div class="row g-0">
        <div class="col-md-2">
            <nav class="nav">
                <?php echo createMenu($projectsMenu['folders'], $projectsMenu['links'], 2, true, SHOW_ICON); ?>
            </nav>
        </div>
        <div class="col-md-8">
            <nav class="nav">
                <?php 
                if (MAIN_LIST_TYPE === 'separate') {
                    echo createMenu($rawMenu['folders'], $rawMenu['links'], 8, false, SHOW_ICON); 
                } else {
                    echo createMenuMerged($rawMenu['folders'], $rawMenu['links'], 8, false, SHOW_ICON); 
                }
                // echo createMenuMerged($rawMenu['folders'], $rawMenu['links'], 8, false, SHOW_ICON); 
                ?>
            </nav>
        </div>
        <div class="col-md-2">  
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
                <div class="card-body">
                    <a href="manage.php" class="btn btn-sm btn-outline-secondary w-100">Manage Links</a>
                </div>
                <div class="card-body">
                    <a href="index.php" class="btn btn-sm btn-outline-secondary w-100">Desktop</a>
                </div>
                <div class="card-body">
                    <a href="phpinfo.php" class="btn btn-sm btn-outline-secondary w-100">PHP Info</a>
                </div>
                <div class="card-body pt-0">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="showHidden">
                        <label class="form-check-label small" for="showHidden">Show hidden</label>
                    </div>
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
                <?php $open_tasks = count(array_filter($todos, fn($t) => ($t['status'] ?? ($t['done'] ? 'done' : 'todo')) !== 'done')); ?>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="m-0 fw-bold">Tasks</h6>
                    <span class="badge rounded-pill bg-secondary" id="openTaskCount" style="font-size:0.6rem"><?php echo $open_tasks; ?> open</span>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary w-100" data-bs-toggle="modal" data-bs-target="#kanbanModal">
                    Open Kanban Board
                </button>
                <?php
                $open_list = array_filter($todos, fn($t) => ($t['status'] ?? ($t['done'] ? 'done' : 'todo')) !== 'done');
                usort($open_list, fn($a, $b) => (($b['status'] ?? 'todo') === 'doing') <=> (($a['status'] ?? 'todo') === 'doing'));
                ?>
                <div id="taskSummary" class="mt-2 small">
                    <?php if (empty($open_list)): ?>
                        <div class="text-muted fst-italic">No open tasks</div>
                    <?php else: foreach ($open_list as $t):
                        $st = $t['status'] ?? 'todo';
                        $dot = ($st === 'doing') ? 'bg-info' : 'bg-secondary';
                        $edge = ($st === 'doing') ? 'border-info' : 'border-secondary';
                    ?>
                        <div class="d-flex align-items-center gap-1 py-1 ps-2 border-start border-2 <?php echo $edge; ?>" data-id="<?php echo $t['id']; ?>">
                            <span class="badge rounded-pill <?php echo $dot; ?> flex-shrink-0" style="width:6px;height:6px;padding:0"></span>
                            <span class="text-truncate" title="<?php echo htmlspecialchars($t['text']); ?>"><?php echo htmlspecialchars($t['text']); ?></span>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Kanban Board Overlay -->
<div class="modal fade" id="kanbanModal" tabindex="-1" aria-labelledby="kanbanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="kanbanModalLabel">Tasks</h5>
                <div class="d-flex align-items-center gap-2">
                    <a href="classic.php?action=clear_completed" class="btn btn-sm btn-outline-danger" onclick="return confirm('Clear all completed tasks?')">Clear completed</a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body">
                <form action="classic.php" method="post" class="mb-3">
                    <input type="text" name="todo" class="form-control form-control-sm" placeholder="New task + Enter" required>
                    <input type="hidden" name="action" value="add_todo">
                </form>
                <div class="kanban-board" id="kanbanBoard">
                    <?php echo load_todo_column('todo'); ?>
                    <?php echo load_todo_column('doing'); ?>
                    <?php echo load_todo_column('done'); ?>
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
        fetch('classic.php?q=' + encodeURIComponent(query));

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

    const showHiddenCheckbox = document.getElementById('showHidden');
    const saved = localStorage.getItem('showHidden') === 'true';
    showHiddenCheckbox.checked = saved;
    document.body.classList.toggle('show-hidden', saved);
    showHiddenCheckbox.addEventListener('change', function() {
        document.body.classList.toggle('show-hidden', this.checked);
        localStorage.setItem('showHidden', this.checked);
    });

    // Kanban drag and drop
    (function() {
        const board = document.getElementById('kanbanBoard');
        if (!board) return;
        let dragCard = null;

        board.addEventListener('dragstart', function(e) {
            const card = e.target.closest('.kanban-card');
            if (!card) return;
            dragCard = card;
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', card.dataset.id);
            card.classList.add('dragging');
        });

        board.addEventListener('dragend', function() {
            if (dragCard) dragCard.classList.remove('dragging');
            dragCard = null;
            clearHighlights();
        });

        board.addEventListener('dragover', function(e) {
            if (!dragCard) return;
            const col = e.target.closest('.kanban-col');
            if (!col) return;
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            board.querySelectorAll('.kanban-col.drop-target').forEach(function(c) {
                if (c !== col) c.classList.remove('drop-target');
            });
            col.classList.add('drop-target');
            // Auto-expand a collapsed "Done" column while dragging over it
            const collapsed = col.querySelector('.collapse:not(.show)');
            if (collapsed) collapsed.classList.add('show');
        });

        board.addEventListener('drop', function(e) {
            if (!dragCard) return;
            const col = e.target.closest('.kanban-col');
            if (!col) return;
            e.preventDefault();
            moveCard(dragCard, col);
            clearHighlights();
        });

        board.addEventListener('dragleave', function(e) {
            if (!board.contains(e.relatedTarget)) clearHighlights();
        });

        function clearHighlights() {
            board.querySelectorAll('.kanban-col.drop-target').forEach(function(c) {
                c.classList.remove('drop-target');
            });
        }

        function moveCard(card, targetCol) {
            const fromCol = card.closest('.kanban-col');
            const from = fromCol.dataset.status;
            const to = targetCol.dataset.status;
            if (from === to) return;

            fetch('classic.php?action=move_todo&id=' + encodeURIComponent(card.dataset.id) + '&to=' + encodeURIComponent(to))
                .then(function(r) { if (!r.ok) throw new Error('move failed'); })
                .then(function() {
                    targetCol.querySelector('.kanban-cards').appendChild(card);

                    // Update column counts
                    const dec = fromCol.querySelector('.kanban-count');
                    const inc = targetCol.querySelector('.kanban-count');
                    if (dec) dec.textContent = Math.max(0, parseInt(dec.textContent, 10) - 1);
                    if (inc) inc.textContent = parseInt(inc.textContent, 10) + 1;

                    // Strike-through styling + status color
                    card.classList.remove('kanban-status-todo', 'kanban-status-doing', 'kanban-status-done');
                    card.classList.add('kanban-status-' + to);
                    const text = card.querySelector('.kanban-text');
                    if (text) {
                        text.classList.toggle('text-decoration-line-through', to === 'done');
                        text.classList.toggle('text-muted', to === 'done');
                    }

                    // Refresh dropdown visibility (hide "move to" link for the new column)
                    card.querySelectorAll('.move-link').forEach(function(a) {
                        a.classList.toggle('d-none', a.dataset.to === to);
                    });

                    // Update sidebar open-task badge
                    const badge = document.getElementById('openTaskCount');
                    if (badge) {
                        let n = parseInt(badge.textContent, 10);
                        if (to === 'done') n--; else if (from === 'done') n++;
                        badge.textContent = Math.max(0, n);
                    }

                    // Sync sidebar summary list
                    const summary = document.getElementById('taskSummary');
                    if (summary) {
                        const textEl = card.querySelector('.kanban-text');
                        if (to === 'done') {
                            const row = summary.querySelector('[data-id="' + card.dataset.id + '"]');
                            if (row) row.remove();
                            if (!summary.children.length) {
                                const empty = document.createElement('div');
                                empty.className = 'text-muted fst-italic';
                                empty.textContent = 'No open tasks';
                                summary.appendChild(empty);
                            }
                        } else if (textEl) {
                            const empty = summary.querySelector('.fst-italic');
                            if (empty) empty.remove();
                            if (!summary.querySelector('[data-id="' + card.dataset.id + '"]')) {
                                const row = document.createElement('div');
                                row.className = 'd-flex align-items-center gap-1 py-1 ps-2 border-start border-2 ' + (to === 'doing' ? 'border-info' : 'border-secondary');
                                row.dataset.id = card.dataset.id;
                                const dot = document.createElement('span');
                                dot.className = 'badge rounded-pill ' + (to === 'doing' ? 'bg-info' : 'bg-secondary') + ' flex-shrink-0';
                                dot.style.cssText = 'width:6px;height:6px;padding:0';
                                const text = document.createElement('span');
                                text.className = 'text-truncate';
                                text.title = textEl.textContent;
                                text.textContent = textEl.textContent;
                                row.append(dot, text);
                                summary.prepend(row);
                            }
                        }
                    }
                })
                .catch(function() { window.location.reload(); });
        }
    })();
</script>

</body>
</html>