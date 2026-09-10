<?php
include('functions.php');

define('LINKS_FILE', 'data/links_v2.json');
define('REFRESH_RATE', 600);

$rawMenu = json_decode(@file_get_contents(LINKS_FILE), true) ?: ['folders' => [], 'links' => []];

// Scan parent directory for project folders
$project_links = [];
foreach (array_filter(glob('../' . '*'), 'is_dir') as $dir) {
    $name = strtolower(basename($dir));
    if ($name === 'start') continue;
    $project_links[] = [
        'id' => 'p_' . $name,
        'label' => $name,
        'url' => "https://$name.test",
        'folder_id' => 'dev_root'
    ];
}
usort($project_links, fn($a, $b) => strnatcasecmp($a['label'], $b['label']));

$project_folder = [['id' => 'dev_root', 'name' => 'dev projects', 'sort' => -2, 'icon' => 'iconoir:laptop-dev-mode']];

// Merge projects into main data
$allFolders = array_merge($project_folder, $rawMenu['folders']);
$allLinks = array_merge($project_links, array_values($rawMenu['links']));

// Group links by folder
$grouped = [];
foreach ($allFolders as $folder) {
    $folderLinks = array_filter($allLinks, fn($l) => $l['folder_id'] === $folder['id']);
    usort($folderLinks, fn($a, $b) => strnatcasecmp($a['label'], $b['label']));
    if (!empty($folderLinks)) {
        $grouped[] = ['folder' => $folder, 'links' => $folderLinks];
    }
}

$css_form = load_css_files();
$selected_style = get_selected_style();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>start</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link rel="stylesheet" type="text/css" href="<?php echo $selected_style; ?>">
    <link rel="stylesheet" type="text/css" href="css/style.css?v=<?php echo filemtime('css/style.css'); ?>">
    <meta http-equiv="refresh" content="<?php echo REFRESH_RATE; ?>" />
    <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
    <style>
        * { box-sizing: border-box; }
        body { background: #0a0a0a; color: #e0e0e0; margin: 0; padding: 0; overflow-x: hidden; }
        .hidden { display: none; }
        .is-hidden { display: none; }
        body.show-hidden .is-hidden { display: revert; opacity: 0.3; }

        /* Top bar */
        .topbar {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 20px; background: #111; border-bottom: 1px solid #222;
            position: sticky; top: 0; z-index: 100;
        }
        .topbar input[type="text"] {
            flex: 1; max-width: 400px;
            background: #1a1a1a; border: 1px solid #333; color: #fff;
            padding: 8px 14px; border-radius: 8px; font-size: 14px; outline: none;
            font-family: 'SF Mono', 'Fira Code', monospace;
        }
        .topbar input:focus { border-color: #555; }
        .topbar .actions { display: flex; gap: 8px; margin-left: auto; align-items: center; }
        .topbar .actions a, .topbar .actions button {
            background: #1a1a1a; border: 1px solid #333; color: #aaa;
            padding: 6px 12px; border-radius: 6px; font-size: 12px; cursor: pointer;
            text-decoration: none; font-family: inherit; transition: all 0.15s;
        }
        .topbar .actions a:hover, .topbar .actions button:hover { background: #2a2a2a; color: #fff; }
        .clock { font-size: 13px; color: #666; font-family: 'SF Mono', monospace; white-space: nowrap; }
        .clock strong { color: #aaa; }

        /* Main grid */
        .main-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1px; padding: 0; background: #111;
        }

        /* Folder card */
        .folder-card {
            background: #0d0d0d; padding: 16px; min-height: 120px;
            transition: background 0.15s;
        }
        .folder-card:hover { background: #141414; }
        .folder-head {
            display: flex; align-items: center; gap: 8px;
            margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid #1e1e1e;
        }
        .folder-head iconify-icon { font-size: 16px; color: #555; }
        .folder-name {
            font-size: 11px; text-transform: uppercase; letter-spacing: 0.1em;
            color: #666; font-weight: 700; margin: 0;
        }

        /* Links */
        .link-list { list-style: none; padding: 0; margin: 0; }
        .link-list li { margin: 0; }
        .link-list a {
            display: flex; align-items: center; gap: 8px;
            padding: 4px 6px; border-radius: 4px; text-decoration: none;
            color: #bbb; font-size: 13px; transition: all 0.12s;
            font-family: 'SF Mono', 'Fira Code', Consolas, monospace;
        }
        .link-list a:hover { background: #1e1e1e; color: #fff; }
        .link-list .icon {
            width: 16px; height: 16px; border-radius: 3px;
            opacity: 0.7; flex-shrink: 0;
        }
        .link-list .label { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        /* Dev projects special style */
        .folder-card.dev-projects {
            background: #0c100c;
        }
        .folder-card.dev-projects .folder-head {
            border-bottom-color: #1a2e1a;
        }
        .folder-card.dev-projects .folder-name { color: #4a8; }
    </style>
</head>
<body>

<div class="topbar">
    <input type="text" id="search" placeholder="Search links..." autocomplete="off">
    <div class="actions">
        <div class="clock"><strong>NG</strong> <span id="clockNG">--:--</span></div>
        <div class="clock"><strong>CA</strong> <span id="clockCA">--:--</span></div>
        <label style="display:flex;align-items:center;gap:4px;color:#666;font-size:11px;cursor:pointer;">
            <input type="checkbox" id="showHidden" style="accent-color:#555"> hidden
        </label>
        <?php echo $css_form; ?>
        <a href="manage.php">manage</a>
    </div>
</div>

<div class="main-grid">
    <?php foreach ($grouped as $group):
        $folder = $group['folder'];
        $links = $group['links'];
        $folderHidden = !empty($folder['hidden']);
        $icon = $folder['icon'] ?? 'mdi:folder';
        $name = strtolower($folder['name']);
        $isDev = ($folder['id'] === 'dev_root');
        $cardClass = $isDev ? 'folder-card dev-projects' : 'folder-card';
        $hiddenClass = $folderHidden ? ' is-hidden' : '';
    ?>
    <div class="<?php echo $cardClass . $hiddenClass; ?>">
        <div class="folder-head">
            <iconify-icon icon="<?php echo $icon; ?>"></iconify-icon>
            <h6 class="folder-name"><?php echo htmlspecialchars($name); ?></h6>
        </div>
        <ul class="link-list">
            <?php foreach ($links as $link):
                $linkHidden = isset($link['hidden']) && $link['hidden'] === true;
                $linkHiddenClass = $linkHidden ? ' is-hidden' : '';
                $safeLabel = preg_replace('/[^a-z0-9]/i', '_', $link['label']);
                $iconPath = 'img/icons/' . $safeLabel . '.png';
                $target = $link['target'] ?? '_self';
                if (!file_exists($iconPath) || filesize($iconPath) == 0) {
                    fetch_favicon($safeLabel, $link['url']);
                }
            ?>
            <li class="link<?php echo $linkHiddenClass; ?>">
                <a href="<?php echo htmlspecialchars($link['url']); ?>"
                   target="<?php echo $target; ?>"
                   data-url="<?php echo htmlspecialchars(strtolower($link['url'])); ?>">
                    <img src="<?php echo $iconPath; ?>" class="icon" alt="">
                    <span class="label"><?php echo htmlspecialchars(strtolower($link['label'])); ?></span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endforeach; ?>
</div>

<script>
document.getElementById('search').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.link-list li.link').forEach(li => {
        const text = li.textContent.toLowerCase();
        const url = li.querySelector('a')?.dataset.url || '';
        li.style.display = (!q || text.includes(q) || url.includes(q)) ? '' : 'none';
    });
    document.querySelectorAll('.folder-card').forEach(card => {
        const visible = card.querySelectorAll('.link-list li.link:not([style*="display: none"])');
        card.style.display = visible.length || !q ? '' : 'none';
    });
    if (!q) document.querySelectorAll('.link-list li.link').forEach(li => li.style.display = '');
});

document.getElementById('search').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const visible = document.querySelectorAll('.link-list li.link:not([style*="display: none"]) a');
        if (visible.length === 1) {
            visible[0].click();
        } else if (this.value.trim()) {
            window.location.href = 'https://www.google.com/search?q=' + encodeURIComponent(this.value);
        }
    }
});

function updateClocks() {
    const zones = [
        { el: 'clockNG', tz: 'Africa/Lagos' },
        { el: 'clockCA', tz: 'America/Toronto' }
    ];
    zones.forEach(z => {
        const el = document.getElementById(z.el);
        if (el) el.textContent = new Intl.DateTimeFormat('en-US', {
            timeZone: z.tz, hour: '2-digit', minute: '2-digit', hour12: true
        }).format(new Date());
    });
}
setInterval(updateClocks, 1000);
updateClocks();

// Show hidden toggle
const hiddenCb = document.getElementById('showHidden');
hiddenCb.checked = localStorage.getItem('showHidden') === 'true';
document.body.classList.toggle('show-hidden', hiddenCb.checked);
hiddenCb.addEventListener('change', function() {
    document.body.classList.toggle('show-hidden', this.checked);
    localStorage.setItem('showHidden', this.checked);
});

window.onload = () => document.getElementById('search').focus();
</script>

</body>
</html>
