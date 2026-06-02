<?php
include('functions.php');
set_time_limit(30000);


define('DATA_FILE', 'data/links_v2.json');
define('SETTINGS_FILE', 'data/settings.json');

$settings = json_decode(@file_get_contents(SETTINGS_FILE), true) ?: [
    'cols' => 7,
    'show_icon' => true,
    'selected_style' => 'css/bootstrap-default.css',
    'view_mode' => 'list',
    'search_history' => []
];

// --- DATA LOGIC ---
$data = json_decode(@file_get_contents(DATA_FILE), true) ?: [
    'folders' => [['id' => 'root', 'name' => 'General', 'sort' => 0]],
    'links' => []
];

// --- ACTION HANDLERS ---
$action = $_REQUEST['action'] ?? '';

if ($action === 'update_settings') {
    $settings['cols'] = (int)$_POST['cols'];
    $settings['view_mode'] = $_POST['view_mode'];
    $settings['show_icon'] = isset($_POST['show_icon']);
    // We keep selected_style and search_history as they are unless changed elsewhere
    file_put_contents(SETTINGS_FILE, json_encode($settings, JSON_PRETTY_PRINT));
    header('Location: manage.php?settings_updated=1'); exit;
}

if ($action === 'upsert_folder') {
    $id = $_POST['id'] ?: uniqid('f');
    $color = $_POST['color'] ?: '#050000';
    $icon = $_POST['icon'] ?: 'mdi:folder'; // Default icon if empty
    
    $found = false;
    foreach ($data['folders'] as &$f) {
        if ($f['id'] === $id) {
            $f['name'] = $_POST['name'];
            $f['sort'] = (int)$_POST['sort'];
            $f['color'] = $color;
            $f['icon'] = $icon; // Save icon
            $found = true;
        }
    }
    if (!$found) {
        $data['folders'][] = [
            'id' => $id, 
            'name' => $_POST['name'], 
            'sort' => (int)$_POST['sort'], 
            'color' => $color,
            'icon' => $icon // Save icon for new folder
        ];
    }
    save($data);
    header('Location: manage.php'); exit;
}

if ($action === 'delete_folder') {
    $target = $_GET['id'];
    $data['folders'] = array_filter($data['folders'], fn($f) => $f['id'] !== $target);
    foreach ($data['links'] as &$l) { if ($l['folder_id'] === $target) $l['folder_id'] = 'root'; }
    save($data);
    header('Location: manage.php'); exit;
}

if ($action === 'upsert_link') {
    $id = $_POST['link_id'] ?: uniqid('l');
    $label = $_POST['label'];
    $url = $_POST['url'];
    $hidden = $_POST['hidden'] ?? false;
    
    // Trigger favicon fetch
    fetch_favicon($label, $url);

    $newLink = [
        'id' => $id, 
        'label' => $_POST['label'], 
        'url' => $_POST['url'], 
        'folder_id' => $_POST['folder_id'] ?: 'root',
        'target' => $_POST['target'] ?? '_self', // Capture the target
        'hidden' => $hidden ? true : false // Capture the hidden status
    ];
    // $id = $_POST['link_id'] ?: uniqid('l');
    // $newLink = ['id' => $id, 'label' => $_POST['label'], 'url' => $_POST['url'], 'folder_id' => $_POST['folder_id'] ?: 'root'];
    $found = false;
    foreach ($data['links'] as &$l) { if ($l['id'] === $id) { $l = $newLink; $found = true; } }
    if (!$found) $data['links'][] = $newLink;
    save($data);
    header('Location: manage.php'); exit;
}

if ($action === 'delete_link') {
    $data['links'] = array_filter($data['links'], fn($l) => $l['id'] !== $_GET['id']);
    save($data);
    header('Location: manage.php'); exit;
}

if ($action === 'refresh_icons') {
    set_time_limit(120);
    $maxAgeDays = 7; // Only refresh icons older than this
    
    foreach ($data['links'] as $l) {
        $safeLabel = preg_replace('/[^a-z0-9]/i', '_', $l['label']);
        $local_path = 'img/icons/' . $safeLabel . '.png';
        
        // Check if file exists and is recent enough
        if (file_exists($local_path)) {
            $fileAgeDays = (time() - filemtime($local_path)) / 86400;
            if ($fileAgeDays < $maxAgeDays) {
                echo "Skipping (recent): " . htmlspecialchars($l['label']) . " (" . round($fileAgeDays, 1) . " days old)<br>";
                ob_flush(); flush();
                continue;
            }
        }
        
        echo "Refreshing icon for: " . htmlspecialchars($l['label']) . "<br>";
        ob_flush(); flush();
        
        fetch_favicon($l['label'], $l['url']);
        usleep(100000); // 100ms delay between requests
    }
    
    // header('Location: manage.php?refreshed=1');
    echo '<meta http-equiv="refresh" content="2;url=manage.php?refreshed=1">';
    exit;
}

if ($action === 'refresh_single_icon') {
    $id = $_GET['id'];
    foreach ($data['links'] as $l) {
        if ($l['id'] === $id) {
            fetch_favicon($l['label'], $l['url']);
            break;
        }
    }
    header('Location: manage.php?refreshed_single=1'); exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --sidebar-width: 360px; }
        body { background: #f4f7f6; font-family: 'Inter', system-ui, sans-serif; }
        .sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; left: 0; top: 0; background: #fff; border-right: 1px solid #e0e0e0; padding: 2rem 1.5rem; z-index: 1000; overflow-y: auto; }
        .main-content { margin-left: var(--sidebar-width); padding: 2.5rem; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .folder-badge { font-size: 0.75rem; background: #eef2f7; color: #495057; padding: 4px 10px; border-radius: 6px; border: 1px solid #d1d9e6; }
        
        /* RESTORED SEARCH STYLE */
        #search-admin {
            border-radius: 30px;
            padding-left: 2.5rem;
            background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23aaa' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z'/%3E%3C/svg%3E") no-repeat 15px center;
        }
        @media (max-width: 992px) { .sidebar { width: 100%; height: auto; position: relative; } .main-content { margin-left: 0; } }
        .icon-preview { font-size: 1.2rem; vertical-align: middle; margin-right: 5px; }
    </style>
    <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2"> 
                <h6 class="fw-bold mb-3">Global Settings</h6>
                <div class="card mb-4 bg-white border shadow-sm">
                    <div class="card-body p-3">
                        <form action="manage.php" method="post">
                            <input type="hidden" name="action" value="update_settings">
                            
                            <div class="mb-3">
                                <label class="small fw-bold mb-1 d-block">Default Columns</label>
                                <select name="cols" class="form-select form-select-sm">
                                    <?php foreach([3, 4, 5, 6,  7, 8, 9, 10, 11] as $num): ?>
                                        <option value="<?= $num ?>" <?= $settings['cols'] == $num ? 'selected' : '' ?>><?= $num ?> Columns</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="small fw-bold mb-1 d-block">Default View</label>
                                <select name="view_mode" class="form-select form-select-sm">
                                    <option value="list" <?= $settings['view_mode'] == 'list' ? 'selected' : '' ?>>Explorer Tiles (List)</option>
                                    <option value="grid" <?= $settings['view_mode'] == 'grid' ? 'selected' : '' ?>>Large Icons (Grid)</option>
                                </select>
                            </div>

                            <div class="mb-3 form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="show_icon" id="set_show_icon" <?= $settings['show_icon'] ? 'checked' : '' ?>>
                                <label class="form-check-label small fw-bold" for="set_show_icon">Display Icons</label>
                            </div>

                            <button class="btn btn-primary btn-sm w-100 fw-bold" type="submit">Apply Settings</button>
                        </form>
                    </div>
                </div>    
            
                <h6 class="fw-bold mb-3">Manage Folders</h6>

                <div class="card mb-4 bg-light border-0">
                    <div class="card-body p-3">
                        <label class="small fw-bold mb-2 d-block">Folder Editor</label>
                        <form action="manage.php" method="post" class="mb-3">
                            <input type="hidden" name="action" value="upsert_folder">
                            <input type="hidden" name="id" id="f_id">
                            
                            <div class="mb-2">
                                <input type="text" name="name" id="f_name" class="form-control form-control-sm mb-2" placeholder="Folder Name" required>
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text"><iconify-icon icon="mdi:emoticon-outline"></iconify-icon></span>
                                    <input type="text" name="icon" id="f_icon" class="form-control" placeholder="mdi:home">
                                    
                                    <input type="number" name="sort" id="f_sort" class="form-control" style="max-width:60px" placeholder="Idx">
                                    <input type="color" name="color" id="f_color" class="form-control form-control-color" style="max-width:45px" title="Folder Color">
                                </div>
                                <button class="btn btn-dark btn-sm w-100" type="submit">Save Folder</button>
                            </div>
                        </form>

                        <div class="small overflow-auto">
                            <?php foreach ($data['folders'] as $f): ?>
                            <div class="d-flex justify-content-between border-bottom py-2 align-items-center">
                                <span>
                                    <iconify-icon icon="<?= $f['icon'] ?? 'mdi:folder' ?>" class="icon-preview" style="color: <?= $f['color'] ?? '#000' ?>"></iconify-icon>
                                    <strong><?= htmlspecialchars($f['name']) ?> </strong> 
                                </span>
                                <span><?= htmlspecialchars($f['sort']) ?></span>
                                <a href="javascript:void(0)" onclick="editFolder('<?= $f['id'] ?>', '<?= addslashes($f['name']) ?>', <?= $f['sort'] ?>, '<?= $f['color'] ?? '#0f0202' ?>', '<?= $f['icon'] ?? 'mdi:folder' ?>')" class="text-primary text-decoration-none">Edit</a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <form action="manage.php" method="post" id="link-form">
                    <h6 class="fw-bold mb-3" id="l_title">Add New Link</h6>
                    <input type="hidden" name="action" value="upsert_link">
                    <input type="hidden" name="link_id" id="l_id">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Link Label</label>
                        <input type="text" name="label" id="l_label" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">URL</label>
                        <input type="text" name="url" id="l_url" class="form-control" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Assign to Folder</label>
                        <select name="folder_id" id="l_folder" class="form-select">
                            <?php foreach ($data['folders'] as $f): ?>
                            <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3 form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="target" id="l_target" value="_blank" <?php if (isset($l['target']) && $l['target'] === '_blank') echo 'checked'; ?>>
                        <label class="form-check-label small fw-bold" for="l_target">Open in new tab</label>
                    </div>
                    <div class="mb-3 form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="hidden" id="l_hidden" value="true" <?php if (isset($l['hidden']) && $l['hidden'] === true) echo 'checked'; ?>>
                        <label class="form-check-label small fw-bold" for="l_hidden">Hide from menu</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Save Link</button>
                    <button type="button" class="btn btn-link btn-sm w-100 mt-2 text-muted" onclick="location.reload()">Reset Form</button>
                </form>

                <div class="mt-5 pt-4 border-top">
                    <a href="index.php" class="btn btn-outline-dark w-100">← Back to Dashboard</a>
                </div>

                <div class="mt-3">
                    <a href="manage.php?action=refresh_icons" class="btn btn-sm btn-outline-info w-100" onclick="return confirm('This may take a moment. Proceed?')">
                        🔄 Refresh All Favicons
                    </a>
                </div>
            </div>
            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-3" id="l_title">Link Inventor</h6>
                    <div style="width: 350px;">
                        <input type="text" id="search-admin" class="form-control border-0 shadow-sm" placeholder="Filter by name, URL or folder...">
                    </div>
                </div>

                <div class="mb-4 d-flex gap-2 flex-wrap">
                    <button class="btn btn-sm btn-dark rounded-pill px-3" onclick="filterFolder('all')">All Folders</button>
                    <?php foreach ($data['folders'] as $f): ?>
                        <button class="btn btn-sm btn-white border rounded-pill px-3 bg-white" onclick="filterFolder('<?= $f['id'] ?>')">
                            <?= htmlspecialchars($f['name']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <div class="card shadow-sm border-0">
                    <table class="table table-hover mb-0" id="linksTable">
                        <thead class="table-light">
                            <tr class="small text-uppercase text-muted">
                                <th onclick="sortTable(0)" style="cursor:pointer">Folder ↕</th>
                                <th onclick="sortTable(1)" style="cursor:pointer">Label ↕</th>
                                <th>URL</th>
                                <th>Hidden</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="link-table-body">
                            <?php 
                            foreach ($data['links'] as $l): 
                                $fName = "Unknown";
                                foreach($data['folders'] as $f) { if($f['id'] === $l['folder_id']) $fName = $f['name']; }
                            ?>
                            <tr class="align-middle link-row" data-folder-id="<?= $l['folder_id'] ?>">
                                <td><span class="folder-badge"><?= htmlspecialchars($fName) ?></span></td>
                                <td class="fw-bold">
                                    <?php 
                                    $iconPath = 'img/icons/' . preg_replace('/[^a-z0-9]/i', '_', $l['label']) . '.png';
                                    if (file_exists($iconPath)) {
                                        echo '<img src="' . $iconPath . '" alt="Icon" style="width:16px; height:16px; margin-right:5px; vertical-align:middle;">';
                                    }
                                    ?>
                                    <?= htmlspecialchars($l['label']) ?></td>
                                <td class="text-muted small"><code><a href="<?= $l['url'] ?>" target="_blank"><?= htmlspecialchars($l['url']) ?></a></code></td>
                                <td>
                                    <?php if (isset($l['hidden']) && $l['hidden']): ?>
                                        <span class="text-danger">Yes</span>
                                    <?php else: ?>
                                        <span class="text-success">No</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="manage.php?action=refresh_single_icon&id=<?= $l['id'] ?>" 
                                        class="btn btn-sm btn-outline-info" 
                                        title="Refresh Favicon">
                                        <iconify-icon icon="mdi:refresh" style="vertical-align: middle;"></iconify-icon>
                                        </a>
                                        
                                        <button class="btn btn-sm btn-outline-secondary" onclick="editLink('<?= $l['id'] ?>', '<?= addslashes($l['label']) ?>', '<?= addslashes($l['url']) ?>', '<?= $l['folder_id'] ?>', '<?=  $l['target'] ?>', <?= isset($l['hidden'])?$l['hidden']:'0' ?>)">Edit</button>
                                        <a href="manage.php?action=delete_link&id=<?= $l['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <script>
        // RESTORED SEARCH LOGIC
        document.getElementById('search-admin').addEventListener('input', function() {
            let query = this.value.toLowerCase();
            document.querySelectorAll('.link-row').forEach(row => {
                let content = row.innerText.toLowerCase();
                row.style.display = content.includes(query) ? '' : 'none';
            });
        });

        // Folder Filtering
        function filterFolder(folderId) {
            document.querySelectorAll('.link-row').forEach(row => {
                row.style.display = (folderId === 'all' || row.getAttribute('data-folder-id') === folderId) ? '' : 'none';
            });
        }

        // Form Helpers
        function editFolder(id, name, sort, color, icon) {
            document.getElementById('f_id').value = id;
            document.getElementById('f_name').value = name;
            document.getElementById('f_sort').value = sort;
            document.getElementById('f_color').value = color || '#000000';
            document.getElementById('f_icon').value = icon || 'mdi:folder';
            document.getElementById('f_name').focus();
        }

        function editLink(id, label, url, folderId, target, hidden) {
            document.getElementById('l_title').innerText = "Update Link";
            document.getElementById('l_id').value = id;
            document.getElementById('l_label').value = label;
            document.getElementById('l_url').value = url;
            document.getElementById('l_folder').value = folderId;
            document.getElementById('l_target').checked = (target === '_blank');
            document.getElementById('l_hidden').checked = (hidden === 1);
            window.scrollTo({top: 0, behavior: 'smooth'});
        }

        // Column Sorting
        function sortTable(n) {
            let table = document.getElementById("linksTable");
            let rows = Array.from(table.rows).slice(1);
            let dir = table.getAttribute("data-sort-dir") === "asc" ? "desc" : "asc";
            rows.sort((a, b) => {
                let x = a.getElementsByTagName("TD")[n].textContent.toLowerCase();
                let y = b.getElementsByTagName("TD")[n].textContent.toLowerCase();
                return dir === "asc" ? x.localeCompare(y, undefined, {numeric: true}) : y.localeCompare(x, undefined, {numeric: true});
            });
            rows.forEach(row => table.tBodies[0].appendChild(row));
            table.setAttribute("data-sort-dir", dir);
        }
    </script>
</body>
</html>