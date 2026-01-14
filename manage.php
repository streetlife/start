<?php
define('DATA_FILE', 'data/links_v2.json');

// --- 1. DATA INITIALIZATION ---
$data = json_decode(@file_get_contents(DATA_FILE), true) ?: [
    'folders' => [['id' => 'root', 'name' => 'General', 'sort' => 0]],
    'links' => []
];

function save($data) {
    // Sort folders by their sort index before saving
    usort($data['folders'], fn($a, $b) => $a['sort'] <=> $b['sort']);
    file_put_contents(DATA_FILE, json_encode($data, JSON_PRETTY_PRINT));
}

// --- 2. ACTION HANDLERS ---

$action = $_REQUEST['action'] ?? '';

// FOLDER ACTIONS
if ($action === 'upsert_folder') {
    $id = $_POST['id'] ?: uniqid();
    $found = false;
    foreach ($data['folders'] as &$f) {
        if ($f['id'] === $id) {
            $f['name'] = $_POST['name'];
            $f['sort'] = (int)$_POST['sort'];
            $found = true;
        }
    }
    if (!$found) $data['folders'][] = ['id' => $id, 'name' => $_POST['name'], 'sort' => (int)$_POST['sort']];
    save($data);
    header('Location: manage.php'); exit;
}

if ($action === 'delete_folder') {
    $target = $_GET['id'];
    $data['folders'] = array_filter($data['folders'], fn($f) => $f['id'] !== $target);
    // Move orphaned links to root
    foreach ($data['links'] as &$l) { if ($l['folder_id'] === $target) $l['folder_id'] = 'root'; }
    save($data);
    header('Location: manage.php'); exit;
}

// LINK ACTIONS
if ($action === 'upsert_link') {
    $id = $_POST['link_id'] ?: uniqid();
    $newLink = [
        'id' => $id,
        'label' => $_POST['label'],
        'url' => $_POST['url'],
        'folder_id' => $_POST['folder_id'] ?: 'root'
    ];
    
    $found = false;
    foreach ($data['links'] as &$l) {
        if ($l['id'] === $id) { $l = $newLink; $found = true; }
    }
    if (!$found) $data['links'][] = $newLink;
    
    save($data);
    header('Location: manage.php'); exit;
}

if ($action === 'delete_link') {
    $data['links'] = array_filter($data['links'], fn($l) => $l['id'] !== $_GET['id']);
    save($data);
    header('Location: manage.php'); exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Advanced Link & Folder Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; font-family: sans-serif; }
        .card { border: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .sticky-form { position: sticky; top: 20px; }
        .folder-badge { font-size: 0.75rem; background: #e9ecef; color: #495057; padding: 4px 8px; border-radius: 4px; }
    </style>
</head>
<body class="p-4">

<div class="container-fluid">
    <div class="row g-4">
        
        <div class="col-lg-4">
            <div class="sticky-form">
                <div class="card mb-4">
                    <div class="card-header bg-dark text-white fw-bold">Manage Folders</div>
                    <div class="card-body">
                        <form action="manage.php" method="post">
                            <input type="hidden" name="action" value="upsert_folder">
                            <input type="hidden" name="id" id="f_id">
                            <div class="row g-2">
                                <div class="col-7"><input type="text" name="name" id="f_name" class="form-control form-control-sm" placeholder="Folder Name" required></div>
                                <div class="col-3"><input type="number" name="sort" id="f_sort" class="form-control form-control-sm" placeholder="Sort" value="0"></div>
                                <div class="col-2"><button type="submit" class="btn btn-sm btn-primary w-100">OK</button></div>
                            </div>
                        </form>
                        <hr>
                        <div class="list-group list-group-flush" style="max-height: 200px; overflow-y: auto;">
                            <?php foreach ($data['folders'] as $f): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center p-1 small">
                                <span><strong><?= $f['sort'] ?>.</strong> <?= htmlspecialchars($f['name']) ?></span>
                                <div>
                                    <button class="btn btn-sm text-info p-0" onclick="editFolder('<?= $f['id'] ?>', '<?= addslashes($f['name']) ?>', <?= $f['sort'] ?>)">✎</button>
                                    <?php if($f['id'] !== 'root'): ?>
                                    <a href="manage.php?action=delete_folder&id=<?= $f['id'] ?>" class="text-danger ms-2" onclick="return confirm('Delete folder?')">&times;</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-primary text-white fw-bold" id="l_title">Add Link</div>
                    <div class="card-body">
                        <form action="manage.php" method="post">
                            <input type="hidden" name="action" value="upsert_link">
                            <input type="hidden" name="link_id" id="l_id">
                            <div class="mb-3"><label class="small fw-bold">Label</label><input type="text" name="label" id="l_label" class="form-control" required></div>
                            <div class="mb-3"><label class="small fw-bold">URL</label><input type="text" name="url" id="l_url" class="form-control" required></div>
                            <div class="mb-3">
                                <label class="small fw-bold">Folder</label>
                                <select name="folder_id" id="l_folder" class="form-select">
                                    <?php foreach ($data['folders'] as $f): ?>
                                    <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Save Link</button>
                            <button type="button" class="btn btn-link btn-sm w-100 mt-2" onclick="location.reload()">Reset</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-bold">Link Library</h5>
                    <a href="index.php" class="btn btn-sm btn-outline-dark">Dashboard</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Folder (Sort Index)</th>
                                <th>Label</th>
                                <th>URL</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Sort links by folder's sort index, then by label
                            $folderSorts = array_column($data['folders'], 'sort', 'id');
                            usort($data['links'], function($a, $b) use ($folderSorts) {
                                $sA = $folderSorts[$a['folder_id']] ?? 999;
                                $sB = $folderSorts[$b['folder_id']] ?? 999;
                                if ($sA === $sB) return $a['label'] <=> $b['label'];
                                return $sA <=> $sB;
                            });

                            foreach ($data['links'] as $l): 
                                $fName = "Unknown";
                                foreach($data['folders'] as $f) if($f['id'] === $l['folder_id']) $fName = $f['name'];
                            ?>
                            <tr>
                                <td><span class="folder-badge"><?= htmlspecialchars($fName) ?></span></td>
                                <td class="fw-bold"><?= htmlspecialchars($l['label']) ?></td>
                                <td class="text-muted small"><?= htmlspecialchars($l['url']) ?></td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-light border" onclick="editLink('<?= $l['id'] ?>', '<?= addslashes($l['label']) ?>', '<?= addslashes($l['url']) ?>', '<?= $l['folder_id'] ?>')">Edit</button>
                                    <a href="manage.php?action=delete_link&id=<?= $l['id'] ?>" class="btn btn-sm btn-outline-danger ms-1" onclick="return confirm('Delete?')">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function editFolder(id, name, sort) {
        document.getElementById('f_id').value = id;
        document.getElementById('f_name').value = name;
        document.getElementById('f_sort').value = sort;
    }

    function editLink(id, label, url, folderId) {
        document.getElementById('l_title').innerText = "Edit Link";
        document.getElementById('l_id').value = id;
        document.getElementById('l_label').value = label;
        document.getElementById('l_url').value = url;
        document.getElementById('l_folder').value = folderId;
        window.scrollTo({top: 0, behavior: 'smooth'});
    }
</script>
</body>
</html>