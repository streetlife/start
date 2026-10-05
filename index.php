<?php
include('functions.php');

ini_set('display_errors', 0);

define('LINKS_FILE', 'data/links.json');
define('TODO_FILE', 'data/todo.json');
define('PRIVATE_MODE', isset($_GET['private']) && $_GET['private'] === 'true');

// ---------- Todo / search history actions ----------
$todos = json_decode(@file_get_contents(TODO_FILE), true) ?: [];


clear_completed_todos($todos);

if (isset($_POST['action']) && $_POST['action'] === 'add_todo' && !empty($_POST['todo'])) {
    $id = empty($todos) ? 1 : max(array_column($todos, 'id')) + 1;
    $todos[] = ['id' => $id, 'text' => trim($_POST['todo']), 'done' => false];
    file_put_contents(TODO_FILE, json_encode($todos, JSON_PRETTY_PRINT));
    header('Location: index.php'); exit;
}

if (isset($_GET['action'])) {
    $a = $_GET['action'];
    if ($a === 'move_todo' && isset($_GET['id'], $_GET['to'])) {
        foreach ($todos as &$t) {
            if ($t['id'] == $_GET['id']) {
                $t['status'] = $_GET['to'];
                $t['done'] = ($_GET['to'] === 'done');
            }
        }
        unset($t);
        file_put_contents(TODO_FILE, json_encode($todos, JSON_PRETTY_PRINT));
        echo 'ok'; exit;
    }
    if ($a === 'delete_todo' && isset($_GET['id'])) {
        $todos = array_values(array_filter($todos, fn($t) => $t['id'] != $_GET['id']));
        file_put_contents(TODO_FILE, json_encode($todos, JSON_PRETTY_PRINT));
        echo 'ok'; exit;
    }
    if ($a === 'clear_completed') {
        $todos = array_values(array_filter($todos, fn($t) => ($t['status'] ?? null) !== 'done' && empty($t['done'])));
        file_put_contents(TODO_FILE, json_encode($todos, JSON_PRETTY_PRINT));
        echo 'ok'; exit;
    }
}

if (isset($_GET['q']) && strlen(trim($_GET['q'])) >= 2) {
    $q = strtolower(trim($_GET['q']));
    $hist = json_decode(@file_get_contents('data/search_history.json'), true) ?: [];
    if (!in_array($q, $hist)) {
        array_unshift($hist, $q);
        $hist = array_slice($hist, 0, 20);
        file_put_contents('data/search_history.json', json_encode($hist, JSON_PRETTY_PRINT));
    }
}

$search_history = json_decode(@file_get_contents('data/search_history.json'), true) ?: [];

// ---------- Load links ----------
$raw = json_decode(@file_get_contents(LINKS_FILE), true) ?: ['folders' => [], 'links' => []];
$folders = $raw['folders'] ?? [];
$links = $raw['links'] ?? [];

$project_links = [];
foreach (array_filter(glob('../' . '*'), 'is_dir') as $dir) {
    $val = strtolower(basename($dir));
    if ($val === 'start') continue;
    $project_links[] = ['id' => 'p_' . $val, 'label' => $val, 'url' => "https://$val.test", 'folder_id' => 'dev_root'];
}
$folders[] = ['id' => 'dev_root', 'name' => 'dev projects', 'sort' => -2, 'icon' => 'mdi:folder-star'];
$links = array_merge($links, $project_links);

usort($folders, fn($a, $b) => ($a['sort'] ?? 0) <=> ($b['sort'] ?? 0));

function folder_fill($c) {
    $c = ltrim($c ?? '', '#');
    if (strlen($c) !== 6 || strtolower($c) === '000000') return '#f6c445';
    $r = hexdec(substr($c, 0, 2));
    $g = hexdec(substr($c, 2, 2));
    $b = hexdec(substr($c, 4, 2));
    $lum = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
    return $lum < 0.18 ? '#f6c445' : $c;
}

$iconDir = 'img/icons/';
$desktopFolders = [];
$searchLinks = [];
foreach ($folders as $f) {
    $fl = array_filter($links, function ($l) use ($f) {
        return ($l['folder_id'] ?? '') === $f['id'];
    });

    $safeId = preg_replace('/[^a-z0-9]/i', '_', $f['id']);

    $folderLinks = [];
    foreach ($fl as $l) {
        $safe = preg_replace('/[^a-z0-9]/i', '_', $l['label']);
        $icon = $iconDir . $safe . '.png';
        if (!file_exists($icon) || filesize($icon) == 0) {
            fetch_favicon($l['label'], $l['url']);
        }
        $entry = [
            'id'     => $l['id'],
            'label'  => $l['label'],
            'url'    => $l['url'],
            'icon'   => $icon,
            'target' => $l['target'] ?? '_self',
            'hidden' => !empty($l['hidden']),
        ];
        $folderLinks[] = $entry;
        $searchLinks[] = ['label' => $l['label'], 'url' => $l['url'], 'icon' => $icon, 'folder' => $f['name']];
    }
    usort($folderLinks, fn($a, $b) => strnatcasecmp($a['label'], $b['label']));

    $fill = folder_fill($f['color'] ?? null);
    $glyph = ($fill === '#f6c445') ? '#3a2b00' : '#ffffff';

    // Multi-column flyout when there are many links
    $n = count($folderLinks);
    $cols = $n > 16 ? ($n > 32 ? 3 : 2) : 1;

    $desktopFolders[] = [
        'id'    => $safeId,
        'name'  => $f['name'],
        'icon'  => !empty($f['icon']) ? $f['icon'] : 'mdi:folder',
        'color' => $fill,
        'glyph' => $glyph,
        'cols'  => $cols,
        'links' => $folderLinks,
    ];
}

// Favorited links -> desktop icons
$favoriteLinks = [];
foreach ($links as $l) {
    if (empty($l['favorite'])) continue;
    $safe = preg_replace('/[^a-z0-9]/i', '_', $l['label']);
    $icon = $iconDir . $safe . '.png';
    if (!file_exists($icon) || filesize($icon) == 0) {
        fetch_favicon($l['label'], $l['url']);
    }
    $favoriteLinks[] = [
        'id'     => $l['id'],
        'label'  => $l['label'],
        'url'    => $l['url'],
        'icon'   => $icon,
        'target' => '_self',
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>esquire ~ desktop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
    <style>
        :root {
            --taskbar-h: 42px;
            --font: "Tahoma", "Segoe UI", Verdana, sans-serif;
            --face: #c0c0c0;
            --text: #1c1f24;
            --muted: #4a4f57;
            --navy: #000080;
            --accent: #1084d0;
            --radius: 0px;
            --blur: 0px;

            --desktop-bg:
                radial-gradient(1000px 600px at 18% 8%, rgba(255,255,255,0.55), transparent 60%),
                radial-gradient(900px 620px at 88% 100%, rgba(120,170,220,0.30), transparent 60%),
                linear-gradient(160deg, #e8eef5 0%, #c8d3de 55%, #b9c7d4 100%);

            --panel-bg: var(--face);
            --panel-fg: var(--text);
            --input-bg: #ffffff;
            --card-bg: #ffffff;
            --desktop-label: #14203a;

            --titlebar-active: linear-gradient(90deg, #000080, #1084d0);
            --titlebar-fg: #ffffff;
            --titlebar-inactive: linear-gradient(90deg, #7a7a7a, #b5b5b5);
            --titlebar-inactive-fg: #1c1f24;

            --taskbar-bg: var(--face);
            --taskbar-tab-bg: var(--face);
            --start-btn-bg: var(--face);
            --start-btn-fg: #000000;
            --rail-bg: linear-gradient(180deg, #000080, #1084d0);
            --sel-bg: var(--navy);
            --sel-fg: #ffffff;

            --bevel: inset 2px 2px 0 #dfdfdf, inset -2px -2px 0 #404040,
                     inset 1px 1px 0 #ffffff, inset -1px -1px 0 #808080;
            --bevel-btn: inset 1px 1px 0 #ffffff, inset -1px -1px 0 #404040;
            --bevel-btn-active: inset -1px -1px 0 #ffffff, inset 1px 1px 0 #404040;
            --bevel-sunken: inset -1px -1px 0 #ffffff, inset 1px 1px 0 #808080;
        }

        body.theme-xp {
            --font: "Tahoma", "Segoe UI", sans-serif;
            --radius: 8px;
            --face: #ece9d8;
            --text: #1c1c1c;
            --muted: #5a5a5a;
            --navy: #2f71cd;
            --accent: #3a93ff;
            --desktop-bg: linear-gradient(180deg, #4a86c8 0%, #2a5c96 30%, #8dbce0 60%, #bcd6ea 100%);
            --panel-bg: #ffffff;
            --panel-fg: #1c1c1c;
            --input-bg: #ffffff;
            --card-bg: #ffffff;
            --desktop-label: #ffffff;
            --titlebar-active: linear-gradient(180deg, #3a93ff, #0058e6);
            --titlebar-fg: #ffffff;
            --titlebar-inactive: linear-gradient(180deg, #a6caf0, #7ba4d8);
            --titlebar-inactive-fg: #1c1c1c;
            --taskbar-bg: linear-gradient(180deg, #4a8ff0 0%, #1c4fb0 4%, #245edb 8%, #1f52c0 100%);
            --taskbar-tab-bg: #3c81f3;
            --start-btn-bg: linear-gradient(180deg, #7cde7c, #3a9c3a);
            --start-btn-fg: #ffffff;
            --rail-bg: #3a93ff;
            --sel-bg: #2f71cd;
            --sel-fg: #ffffff;
            --bevel: 0 0 0 1px #0831d9, 2px 3px 8px rgba(0,0,0,0.35);
            --bevel-btn: inset 0 0 0 1px rgba(0,0,0,0.15);
            --bevel-btn-active: inset 0 0 0 1px #0831d9;
        }

        body.theme-modern {
            --font: "Segoe UI", "SF Pro Text", sans-serif;
            --radius: 8px;
            --blur: 16px;
            --face: #202020;
            --text: #e8e8e8;
            --muted: #9a9a9a;
            --navy: #005fb8;
            --accent: #0078d4;
            --desktop-bg:
                radial-gradient(1200px 800px at 20% 10%, rgba(0,120,212,0.35), transparent 60%),
                linear-gradient(160deg, #1b1f2a 0%, #0e1118 100%);
            --panel-bg: rgba(32,32,32,0.92);
            --panel-fg: #e8e8e8;
            --input-bg: #1f1f1f;
            --card-bg: #2a2a2a;
            --desktop-label: #ffffff;
            --titlebar-active: #1f1f1f;
            --titlebar-fg: #ffffff;
            --titlebar-inactive: #2a2a2a;
            --titlebar-inactive-fg: #cfcfcf;
            --taskbar-bg: rgba(32,32,32,0.85);
            --taskbar-tab-bg: rgba(255,255,255,0.06);
            --start-btn-bg: transparent;
            --start-btn-fg: #ffffff;
            --rail-bg: #1a1a1a;
            --sel-bg: #005fb8;
            --sel-fg: #ffffff;
            --bevel: 0 4px 18px rgba(0,0,0,0.55);
            --bevel-btn: none;
            --bevel-btn-active: none;
        }

        body.theme-mac {
            --taskbar-h: 30px;
            --font: -apple-system, "SF Pro Text", "Helvetica Neue", sans-serif;
            --radius: 8px;
            --blur: 18px;
            --face: #e9e9e9;
            --text: #1c1c1c;
            --muted: #6b6b6b;
            --navy: #0a60c2;
            --accent: #0a84ff;
            --desktop-bg: linear-gradient(160deg, #b7d3ea 0%, #8fb4d8 45%, #6f95bd 100%);
            --panel-bg: rgba(246,246,246,0.9);
            --panel-fg: #1c1c1c;
            --input-bg: #ffffff;
            --card-bg: #ffffff;
            --desktop-label: #1c1c1c;
            --titlebar-active: #f0f0f0;
            --titlebar-fg: #1c1c1c;
            --titlebar-inactive: #f0f0f0;
            --titlebar-inactive-fg: #666666;
            --taskbar-bg: rgba(248,248,248,0.85);
            --taskbar-tab-bg: transparent;
            --start-btn-bg: transparent;
            --start-btn-fg: #1c1c1c;
            --rail-bg: #e9e9e9;
            --sel-bg: #0a60c2;
            --sel-fg: #ffffff;
            --bevel: 0 12px 44px rgba(0,0,0,0.4);
            --bevel-btn: none;
            --bevel-btn-active: none;
        }

        body.theme-aero {
            --font: "Segoe UI", "Tahoma", sans-serif;
            --radius: 6px;
            --blur: 20px;
            --face: rgba(240,245,255,0.8);
            --text: #1a1a1a;
            --muted: #4a4a4a;
            --navy: #3c7fb1;
            --accent: #5dbbff;
            --desktop-bg:
                radial-gradient(1400px 900px at 50% 0%, rgba(255,255,255,0.6), transparent 70%),
                linear-gradient(180deg, #87c6f5 0%, #7fb9f2 40%, #6fa3e0 100%);
            --panel-bg: rgba(235,245,255,0.92);
            --panel-fg: #1a1a1a;
            --input-bg: rgba(255,255,255,0.9);
            --card-bg: rgba(255,255,255,0.95);
            --desktop-label: #ffffff;
            --titlebar-active: linear-gradient(90deg, #4b7cb5, #7fb3e6);
            --titlebar-fg: #ffffff;
            --titlebar-inactive: linear-gradient(90deg, #c9d9ea, #e6eef7);
            --titlebar-inactive-fg: #1a1a1a;
            --taskbar-bg: rgba(235,245,255,0.85);
            --taskbar-tab-bg: rgba(255,255,255,0.4);
            --start-btn-bg: rgba(255,255,255,0.6);
            --start-btn-fg: #1a1a1a;
            --rail-bg: linear-gradient(180deg, #5dbbff, #3c7fb1);
            --sel-bg: #3c7fb1;
            --sel-fg: #ffffff;
            --bevel: 0 2px 12px rgba(60,127,177,0.4), inset 0 1px 0 rgba(255,255,255,0.8);
            --bevel-btn: inset 0 1px 0 rgba(255,255,255,0.9);
            --bevel-btn-active: inset 0 1px 0 rgba(60,127,177,0.3);
        }

        body.theme-neon {
            --font: "JetBrains Mono", "Consolas", monospace;
            --radius: 4px;
            --blur: 0px;
            --face: #0f0f1a;
            --text: #00ff9d;
            --muted: #4d9b7a;
            --navy: #00ff9d;
            --accent: #00e68a;
            --desktop-bg: radial-gradient(1200px 800px at 50% 50%, #0a0a12 0%, #000000 100%);
            --panel-bg: #0f0f1a;
            --panel-fg: #00ff9d;
            --input-bg: #141420;
            --card-bg: #141420;
            --desktop-label: #00ff9d;
            --titlebar-active: linear-gradient(90deg, #00ff9d, #00cc7d);
            --titlebar-fg: #000000;
            --titlebar-inactive: #1a1a2e;
            --titlebar-inactive-fg: #4d9b7a;
            --taskbar-bg: #0f0f1a;
            --taskbar-tab-bg: #141420;
            --start-btn-bg: #00ff9d;
            --start-btn-fg: #000000;
            --rail-bg: #00ff9d;
            --sel-bg: #00ff9d;
            --sel-fg: #000000;
            --bevel: inset 0 0 0 1px #00ff9d;
            --bevel-btn: inset 0 0 0 1px #00ff9d;
            --bevel-btn-active: inset 0 0 0 1px #00cc7d;
        }

        body.theme-glass {
            --font: "Inter", "Segoe UI", sans-serif;
            --radius: 12px;
            --blur: 40px;
            --face: rgba(255,255,255,0.25);
            --text: #ffffff;
            --muted: rgba(255,255,255,0.7);
            --navy: rgba(255,255,255,0.4);
            --accent: rgba(255,255,255,0.6);
            --desktop-bg:
                radial-gradient(1400px 900px at 20% 10%, rgba(135,206,250,0.4), transparent 70%),
                radial-gradient(1200px 800px at 80% 90%, rgba(255,192,203,0.4), transparent 70%),
                linear-gradient(160deg, #667eea 0%, #764ba2 100%);
            --panel-bg: rgba(255,255,255,0.2);
            --panel-fg: #ffffff;
            --input-bg: rgba(255,255,255,0.25);
            --card-bg: rgba(255,255,255,0.15);
            --desktop-label: #ffffff;
            --titlebar-active: rgba(255,255,255,0.3);
            --titlebar-fg: #ffffff;
            --titlebar-inactive: rgba(255,255,255,0.15);
            --titlebar-inactive-fg: rgba(255,255,255,0.8);
            --taskbar-bg: rgba(255,255,255,0.2);
            --taskbar-tab-bg: rgba(255,255,255,0.15);
            --start-btn-bg: rgba(255,255,255,0.3);
            --start-btn-fg: #ffffff;
            --rail-bg: rgba(255,255,255,0.4);
            --sel-bg: rgba(255,255,255,0.4);
            --sel-fg: #1a1a1a;
            --bevel: 0 8px 32px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.5);
            --bevel-btn: inset 0 1px 0 rgba(255,255,255,0.6);
            --bevel-btn-active: inset 0 1px 0 rgba(255,255,255,0.3);
        }

        body.theme-terminal {
            --font: "JetBrains Mono", "Courier New", monospace;
            --radius: 0px;
            --blur: 0px;
            --face: #000000;
            --text: #00ff00;
            --muted: #008800;
            --navy: #00ff00;
            --accent: #00ff00;
            --desktop-bg: #000000;
            --panel-bg: #000000;
            --panel-fg: #00ff00;
            --input-bg: #001100;
            --card-bg: #001100;
            --desktop-label: #00ff00;
            --titlebar-active: #00ff00;
            --titlebar-fg: #000000;
            --titlebar-inactive: #003300;
            --titlebar-inactive-fg: #00ff00;
            --taskbar-bg: #000000;
            --taskbar-tab-bg: #001100;
            --start-btn-bg: #00ff00;
            --start-btn-fg: #000000;
            --rail-bg: #00ff00;
            --sel-bg: #00ff00;
            --sel-fg: #000000;
            --bevel: inset 0 0 0 1px #00ff00;
            --bevel-btn: inset 0 0 0 1px #00ff00;
            --bevel-btn-active: inset 0 0 0 1px #00cc00;
        }

        body.theme-sunset {
            --font: "Inter", "Segoe UI", sans-serif;
            --radius: 8px;
            --blur: 10px;
            --face: #f4d03f;
            --text: #2c1810;
            --muted: #7d4f24;
            --navy: #ff6b35;
            --accent: #f7931e;
            --desktop-bg:
                radial-gradient(1400px 900px at 50% 0%, rgba(255,140,0,0.4), transparent 70%),
                linear-gradient(180deg, #ff9c40 0%, #ff6b35 60%, #8e3326 100%);
            --panel-bg: rgba(244,208,63,0.95);
            --panel-fg: #2c1810;
            --input-bg: #fff5d6;
            --card-bg: #fff5d6;
            --desktop-label: #2c1810;
            --titlebar-active: linear-gradient(90deg, #ff6b35, #f7931e);
            --titlebar-fg: #ffffff;
            --titlebar-inactive: linear-gradient(90deg, #f4d03f, #ffe08a);
            --titlebar-inactive-fg: #2c1810;
            --taskbar-bg: rgba(244,208,63,0.95);
            --taskbar-tab-bg: rgba(255,255,255,0.3);
            --start-btn-bg: #ff6b35;
            --start-btn-fg: #ffffff;
            --rail-bg: #ff6b35;
            --sel-bg: #ff6b35;
            --sel-fg: #ffffff;
            --bevel: 0 4px 12px rgba(255,107,53,0.4), inset 0 1px 0 rgba(255,255,255,0.6);
            --bevel-btn: inset 0 1px 0 rgba(255,255,255,0.8);
            --bevel-btn-active: inset 0 1px 0 rgba(255,107,53,0.3);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; overflow: hidden; }
        body {
            font-family: var(--font);
            font-size: 12px;
            color: var(--text);
            background: var(--face);
            user-select: none;
            -webkit-user-select: none;
        }
        a { color: inherit; text-decoration: none; }

        .is-hidden { display: none !important; }
        body.show-hidden .is-hidden { display: flex !important; }

        #desktop {
            position: fixed;
            inset: 0 0 var(--taskbar-h) 0;
            background: var(--desktop-bg);
        }
        #desktop::after {
            content: "";
            position: absolute; inset: 0;
            background-image: linear-gradient(rgba(255,255,255,0.15) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(255,255,255,0.15) 1px, transparent 1px);
            background-size: 48px 48px;
            pointer-events: none;
        }
        body.theme-modern #desktop::after, body.theme-mac #desktop::after, body.theme-aero #desktop::after, body.theme-neon #desktop::after, body.theme-glass #desktop::after, body.theme-terminal #desktop::after, body.theme-sunset #desktop::after { display: none; }

        /* Desktop icons */
        #desktop-icons {
            position: absolute; top: 12px; left: 12px; bottom: 12px; width: 210px;
            display: flex; flex-direction: column; flex-wrap: wrap;
            align-content: flex-start; gap: 6px; z-index: 1;
        }
        .desk-icon {
            width: 92px; padding: 8px 4px 6px;
            display: flex; flex-direction: column; align-items: center; gap: 7px;
            border-radius: 3px; cursor: pointer; text-align: center; border: 1px solid transparent;
        }
        .desk-icon:hover { background: rgba(0, 0, 120, 0.06); border: 1px dotted #000080; }
        .desk-icon.selected { background: rgba(0, 0, 120, 0.12); border: 1px dotted #000080; }
        body.theme-modern .desk-icon:hover, body.theme-modern .desk-icon.selected { background: rgba(255,255,255,0.12); border-color: rgba(255,255,255,0.35); }
        body.theme-mac .desk-icon:hover, body.theme-mac .desk-icon.selected { background: rgba(0,0,0,0.06); border-color: rgba(0,0,0,0.25); }
        .desk-icon .folder {
            position: relative; width: 56px; height: 44px;
            filter: drop-shadow(0 2px 2px rgba(0,0,0,0.35));
        }
        .desk-icon .folder::before {
            content: ""; position: absolute; left: 3px; top: 0; width: 24px; height: 10px;
            border-radius: 3px 3px 0 0; background: var(--fill);
            box-shadow: inset 1px 1px 0 rgba(255,255,255,0.55);
        }
        .desk-icon .folder::after {
            content: ""; position: absolute; left: 0; top: 8px; right: 0; bottom: 0;
            border-radius: 3px; background: var(--fill);
            box-shadow: inset 1px 1px 0 rgba(255,255,255,0.45), inset -1px -1px 0 rgba(0,0,0,0.28);
        }
        .desk-icon .folder .glyph {
            position: absolute; inset: 11px 0 1px 0; z-index: 1;
            display: flex; align-items: center; justify-content: center;
        }
        .desk-icon .folder iconify-icon { font-size: 24px; color: var(--glyph); }
        .desk-icon .appicon {
            width: 44px; height: 44px;
            display: flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,0.4);
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.25);
        }
        .desk-icon .appicon img { width: 30px; height: 30px; object-fit: contain; }
        .desk-icon .lbl {
            font-size: 11px; line-height: 1.2; max-width: 100%;
            overflow: hidden; text-overflow: ellipsis; display: -webkit-box;
            -webkit-line-clamp: 2; -webkit-box-orient: vertical;
            color: var(--desktop-label); text-shadow: 0 1px 2px rgba(0,0,0,0.4); word-break: break-word;
        }

        /* Center search */
        #search-area { position: absolute; top: 20px; left: 50%; transform: translateX(-50%); width: min(460px, 88%); z-index: 5; }
        #search-form { display: flex; box-shadow: var(--bevel-btn); }
        #search-box {
            flex: 1; padding: 7px 10px; border: none; background: var(--input-bg); color: var(--text);
            font-family: inherit; font-size: 13px; outline: none; border-radius: var(--radius) 0 0 var(--radius);
        }
        #search-go {
            padding: 0 12px; border: none; background: var(--start-btn-bg); color: var(--start-btn-fg);
            cursor: pointer; font-family: inherit; font-size: 12px; box-shadow: var(--bevel-btn);
            border-radius: 0 var(--radius) var(--radius) 0;
        }
        #search-go:active { box-shadow: var(--bevel-btn-active); }
        #search-results {
            position: absolute; top: 100%; left: 0; right: 0; margin-top: 2px;
            display: none; max-height: 280px; overflow-y: auto; z-index: 6;
            background: var(--panel-bg); color: var(--panel-fg); border-radius: var(--radius);
            box-shadow: var(--bevel); backdrop-filter: blur(var(--blur));
        }
        .search-hit { display: flex; align-items: center; gap: 8px; padding: 5px 9px; }
        .search-hit:hover { background: var(--sel-bg); color: var(--sel-fg); }
        .search-hit img { width: 16px; height: 16px; object-fit: contain; flex: 0 0 16px; }
        .search-hit .lbl { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .search-hit .cat { font-size: 9px; opacity: 0.6; }

        /* Kanban */
        #kanban-wrap {
            position: absolute; top: 76px; left: 50%; transform: translateX(-50%);
            width: min(780px, 92%); z-index: 4; padding: 8px;
            background: var(--panel-bg); color: var(--panel-fg); border-radius: var(--radius);
            box-shadow: var(--bevel); backdrop-filter: blur(var(--blur));
            display: flex; flex-direction: column;
        }
        #kanban-wrap h5 { font-size: 12px; font-weight: bold; letter-spacing: 0.04em; text-transform: uppercase; }
        .kb-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; }
        #kanban-wrap .kb-top h5 { margin: 0; padding: 4px 6px; }
        .kb-actions { display: flex; gap: 6px; align-items: center; }
        .kb-actions button {
            padding: 3px 9px; border: none; cursor: pointer;
            font-family: inherit; font-size: 11px; box-shadow: var(--bevel-btn);
        }
        .kb-actions button:active { box-shadow: var(--bevel-btn-active); }
        .kb-actions #clearCompletedBtn { background: #a80000; color: #fff; }
        .kb-actions #kanbanMin { background: var(--start-btn-bg); color: var(--start-btn-fg); padding: 3px 10px; }
        #todoForm { display: flex; gap: 6px; margin-bottom: 8px; }
        #todoForm input[type=text] {
            flex: 1; padding: 5px 8px; border: none; background: var(--input-bg); color: var(--text);
            font-family: inherit; font-size: 12px; outline: none; box-shadow: var(--bevel-sunken);
        }
        #todoForm button {
            padding: 5px 10px; border: none; background: var(--start-btn-bg); color: var(--start-btn-fg);
            cursor: pointer; font-family: inherit; font-size: 12px; box-shadow: var(--bevel-btn);
        }
        #todoForm button:active { box-shadow: var(--bevel-btn-active); }
        .kb-cols { display: flex; gap: 8px; align-items: flex-start; }
        .kb-col { flex: 1; min-width: 0; padding: 6px; background: var(--face); color: var(--text); box-shadow: var(--bevel-sunken); }
        .kb-head {
            display: flex; justify-content: space-between; align-items: center;
            padding: 2px 4px 6px; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.03em;
        }
        .kb-cards { min-height: 54px; display: flex; flex-direction: column; gap: 4px; }
        .kb-card {
            position: relative; display: flex; align-items: center; gap: 6px;
            background: var(--card-bg); color: var(--panel-fg); padding: 5px 7px; font-size: 11px; cursor: grab;
            box-shadow: var(--bevel-btn);
        }
        .kb-card.dragging { opacity: 0.45; }
        .kb-col.drop { background: var(--accent); }
        .kb-text { flex: 1; overflow: hidden; text-overflow: ellipsis; }
        .kb-text.done { text-decoration: line-through; opacity: 0.55; }
        .kb-menu-btn { border: none; background: transparent; cursor: pointer; font-size: 13px; line-height: 1; color: inherit; opacity: 0.6; padding: 0 2px; }
        .kb-menu-btn:hover { opacity: 1; }
        .kb-menu {
            position: absolute; right: 0; top: 100%; z-index: 50; min-width: 138px;
            background: var(--panel-bg); color: var(--panel-fg); display: none; padding: 2px 0;
            box-shadow: var(--bevel); border-radius: var(--radius);
        }
        .kb-menu a { display: block; padding: 4px 8px; color: inherit; font-size: 11px; }
        .kb-menu a:hover { background: var(--sel-bg); color: var(--sel-fg); }
        .kb-menu a.danger:hover { background: #a80000; color: #fff; }
        .kb-menu a.done-act:hover { background: #0a6b0a; color: #fff; }

        /* Widgets */
        .widget {
            position: absolute; width: 158px; z-index: 3;
            background: var(--panel-bg); color: var(--panel-fg); border-radius: var(--radius);
            box-shadow: var(--bevel); backdrop-filter: blur(var(--blur));
        }
        .widget .w-title {
            height: 20px; display: flex; align-items: center; padding: 0 6px; cursor: grab;
            background: var(--titlebar-active); color: var(--titlebar-fg);
            font-size: 11px; font-weight: bold; letter-spacing: 0.03em;
            border-radius: var(--radius) var(--radius) 0 0;
        }
        body.theme-mac .widget .w-title { background: transparent; color: var(--muted); }
        .widget .w-body { padding: 8px; text-align: center; }
        .widget .w-time { font-size: 21px; font-weight: bold; font-variant-numeric: tabular-nums; }
        .widget .w-sub { font-size: 10px; opacity: 0.6; margin-top: 2px; }
        .widget .w-row { display: flex; align-items: center; gap: 6px; padding: 4px; font-size: 11px; }
        .widget .w-row input { margin: 0; }
        .widget select {
            width: 100%; padding: 4px 6px; border: none; background: var(--input-bg); color: var(--text);
            font-family: inherit; font-size: 11px; outline: none; box-shadow: var(--bevel-sunken);
        }

        /* Windows */
        .window {
            position: absolute; width: 640px; min-height: 240px; max-height: 76%;
            display: none; flex-direction: column;
            background: var(--panel-bg); color: var(--panel-fg);
            border-radius: var(--radius);
            box-shadow: var(--bevel); backdrop-filter: blur(var(--blur));
            overflow: hidden;
        }
        .window .titlebar {
            height: 22px; flex: 0 0 auto; display: flex; align-items: center;
            gap: 6px; padding: 0 3px 0 6px; cursor: grab;
            background: var(--titlebar-inactive); color: var(--titlebar-inactive-fg);
            border-radius: var(--radius) var(--radius) 0 0;
        }
        .window.active .titlebar { background: var(--titlebar-active); color: var(--titlebar-fg); }
        .window .titlebar iconify-icon { font-size: 13px; }
        .window .titlebar .t { flex: 1; font-size: 11px; font-weight: bold; letter-spacing: 0.03em; text-transform: uppercase; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .window .tb-btn {
            width: 18px; height: 16px; border: none; background: var(--start-btn-bg); color: var(--start-btn-fg);
            font-size: 11px; cursor: pointer; line-height: 1; font-family: inherit; box-shadow: var(--bevel-btn);
            border-radius: 2px;
        }
        .window .tb-btn:active { box-shadow: var(--bevel-btn-active); }
        body.theme-mac .window .tb-btn { width: 12px; height: 12px; border-radius: 50%; color: transparent; box-shadow: none; }
        body.theme-mac .window .tb-btn[data-act="min"] { background: #febc2e; }
        body.theme-mac .window .tb-btn.close { background: #ff5f57; }
        body.theme-modern .window .tb-btn { background: transparent; color: var(--panel-fg); box-shadow: none; }
        .window .win-body {
            flex: 1; overflow-y: auto; padding: 10px;
            display: grid; grid-template-columns: repeat(auto-fill, minmax(84px, 1fr));
            gap: 6px; align-content: start;
        }
        .app {
            display: flex; flex-direction: column; align-items: center; gap: 6px;
            padding: 8px 4px; border-radius: 4px; cursor: pointer; text-align: center; border: 1px solid transparent;
        }
        .app:hover { background: var(--sel-bg); color: var(--sel-fg); }
        .app img { width: 32px; height: 32px; object-fit: contain; }
        .app .lbl {
            font-size: 10px; line-height: 1.15; max-width: 100%;
            overflow: hidden; text-overflow: ellipsis; display: -webkit-box;
            -webkit-line-clamp: 2; -webkit-box-orient: vertical; word-break: break-word;
        }
        .empty { grid-column: 1 / -1; color: var(--muted); font-size: 11px; padding: 16px; text-align: center; }

        /* Taskbar */
        #taskbar {
            position: fixed; left: 0; right: 0; bottom: 0; height: var(--taskbar-h);
            display: flex; align-items: center; gap: 6px; padding: 0 4px 0 2px;
            background: var(--taskbar-bg); z-index: 1000;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.35);
            backdrop-filter: blur(var(--blur));
        }
        #startBtn {
            display: flex; align-items: center; gap: 6px;
            height: calc(var(--taskbar-h) - 12px); min-height: 22px; padding: 0 10px; border: none;
            background: var(--start-btn-bg); color: var(--start-btn-fg); font-family: inherit;
            font-size: 12px; font-weight: bold; cursor: pointer; border-radius: var(--radius);
            box-shadow: var(--bevel-btn);
        }
        #startBtn:active { box-shadow: var(--bevel-btn-active); }
        #startBtn iconify-icon { font-size: 16px; }
        #taskbar-tabs { display: flex; align-items: center; gap: 4px; flex: 1; overflow-x: auto; height: 100%; padding: 5px 0; }
        #taskbar-tabs::-webkit-scrollbar { height: 0; }
        body.theme-modern #taskbar-tabs { justify-content: center; }
        .task-tab {
            display: none; align-items: center; gap: 6px; height: 26px; padding: 0 10px;
            background: var(--taskbar-tab-bg); color: var(--start-btn-fg); font-size: 11px; cursor: pointer; white-space: nowrap;
            box-shadow: var(--bevel-btn); border-radius: var(--radius);
        }
        .task-tab.visible { display: flex; }
        .task-tab.active {
            box-shadow: var(--bevel-btn-active);
            background: var(--sel-bg); color: var(--sel-fg); font-weight: bold;
        }
        body.theme-modern .task-tab.active { background: rgba(255,255,255,0.12); color: #fff; }
        body.theme-mac .task-tab { box-shadow: none; color: var(--text); }
        body.theme-mac .task-tab.active { background: rgba(0,0,0,0.08); }
        .task-tab iconify-icon { font-size: 13px; }
        .tray {
            display: flex; flex-direction: column; justify-content: center; align-items: flex-end;
            padding: 3px 6px; height: 24px; min-width: 74px; box-shadow: var(--bevel-sunken);
        }
        body.theme-mac .tray, body.theme-modern .tray { box-shadow: none; }
        #clock { font-size: 11px; line-height: 1.2; }
        #date { font-size: 10px; opacity: 0.7; }

        /* Start menu */
        #start-menu {
            position: fixed; left: 2px; bottom: calc(var(--taskbar-h) + 2px);
            display: none; z-index: 1100;
            background: var(--panel-bg); color: var(--panel-fg); border-radius: var(--radius);
            box-shadow: var(--bevel); backdrop-filter: blur(var(--blur));
        }
        #start-menu.open { display: flex; }
        .sm-rail {
            width: 26px; background: var(--rail-bg);
            display: flex; align-items: center; justify-content: center; flex: 0 0 auto;
            border-radius: var(--radius) 0 0 var(--radius);
        }
        body.theme-mac .sm-rail { display: none; }
        .sm-rail span {
            writing-mode: vertical-rl; transform: rotate(180deg);
            color: #fff; font-weight: bold; letter-spacing: 0.18em; font-size: 13px; white-space: nowrap;
        }
        .sm-panel { display: flex; flex-direction: column; width: 210px; }
        .sm-list { list-style: none; max-height: 62vh; overflow-y: auto; padding: 2px 0; }
        .sm-item { display: flex; align-items: center; gap: 8px; padding: 5px 8px; cursor: pointer; white-space: nowrap; }
        .sm-item iconify-icon { font-size: 15px; width: 16px; flex: 0 0 16px; text-align: center; }
        .sm-item .arrow { margin-left: auto; font-size: 10px; opacity: 0.6; }
        .sm-item:hover, .sm-item.open { background: var(--sel-bg); color: var(--sel-fg); }
        .sm-sep { height: 0; border-top: 1px solid #808080; border-bottom: 1px solid #fff; margin: 3px 2px; }

        /* Flyout submenu */
        .flyout {
            position: fixed; display: none; min-width: 214px; max-width: 76vw; max-height: 70vh;
            overflow-y: auto; padding: 2px 0; z-index: 1200;
            background: var(--panel-bg); color: var(--panel-fg); border-radius: var(--radius);
            box-shadow: var(--bevel); backdrop-filter: blur(var(--blur));
            column-gap: 1px;
        }
        .flyout .flyout-title {
            padding: 4px 8px; font-size: 10px; font-weight: bold; letter-spacing: 0.04em;
            text-transform: uppercase; color: #fff; background: var(--sel-bg);
            column-span: all;
        }
        .flyout .sm-link {
            display: flex; align-items: center; gap: 8px; padding: 5px 8px; white-space: nowrap;
            break-inside: avoid;
        }
        .flyout .sm-link:hover { background: var(--sel-bg); color: var(--sel-fg); }
        .flyout .sm-link img { width: 16px; height: 16px; object-fit: contain; flex: 0 0 16px; }
        .flyout .sm-link .lbl { overflow: hidden; text-overflow: ellipsis; }
        .flyout .theme-opt.active .lbl::before { content: "\2713  "; }

        body.theme-mac #taskbar { top: 0; bottom: auto; box-shadow: inset 0 -1px 0 rgba(0,0,0,0.12); }
        body.theme-mac #desktop { inset: var(--taskbar-h) 0 0 0; }
        body.theme-mac #start-menu { bottom: auto; top: calc(var(--taskbar-h) + 4px); }

        @media (max-width: 640px) {
            .window { width: 92vw; }
            #desktop-icons { display: none; }
        }
    </style>
</head>
<body class="theme-win95">
    <div id="desktop">
        <div id="desktop-icons">
            <?php foreach ($desktopFolders as $f): ?>
                <div class="desk-icon" data-id="<?php echo htmlspecialchars($f['id']); ?>" title="<?php echo htmlspecialchars($f['name']); ?>">
                    <span class="folder" style="--fill:<?php echo htmlspecialchars($f['color']); ?>;--glyph:<?php echo htmlspecialchars($f['glyph']); ?>">
                        <span class="glyph"><iconify-icon icon="<?php echo htmlspecialchars($f['icon']); ?>"></iconify-icon></span>
                    </span>
                    <span class="lbl"><?php echo htmlspecialchars($f['name']); ?></span>
                </div>
            <?php endforeach; ?>

            <div class="desk-icon" id="desk-kanban" title="Tasks">
                <span class="folder" style="--fill:#1f6f8c;--glyph:#ffffff;">
                    <span class="glyph"><iconify-icon icon="mdi:trello"></iconify-icon></span>
                </span>
                <span class="lbl">Tasks</span>
            </div>

            <?php foreach ($favoriteLinks as $l): ?>
                <a class="desk-icon" href="<?php echo htmlspecialchars($l['url']); ?>" title="<?php echo htmlspecialchars($l['label']); ?>">
                    <span class="appicon"><img src="<?php echo htmlspecialchars($l['icon']); ?>" alt="" onerror="this.style.visibility='hidden'"></span>
                    <span class="lbl"><?php echo htmlspecialchars($l['label']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Center search -->
        <div id="search-area">
            <form id="search-form" method="get" onsubmit="return handleSearch();">
                <input type="text" id="search-box" name="q" placeholder="Filter or Search..."
                       list="search-history-list" autocomplete="off">
                <datalist id="search-history-list">
                    <?php foreach ($search_history as $item): ?>
                        <option value="<?php echo htmlspecialchars($item); ?>">
                    <?php endforeach; ?>
                </datalist>
                <button id="search-go" type="submit">Go</button>
            </form>
            <div id="search-results"></div>
        </div>

        <!-- Kanban -->
        <div id="kanban-wrap">
            <div class="kb-top titlebar" style="cursor: move;">
                <h5>Tasks</h5>
                <div class="kb-actions">
                    <button id="kanbanMin" type="button" title="Minimize">&#8211;</button>
                    <button id="clearCompletedBtn" type="button">Clear completed</button>
                </div>
            </div>
            <form id="todoForm" method="post" action="index.php">
                <input type="text" name="todo" placeholder="New task + Enter" required>
                <input type="hidden" name="action" value="add_todo">
                <button type="submit">Add</button>
            </form>
            <div class="kb-cols" id="kanbanBoard">
                <?php
                $titles = ['todo' => 'To Do', 'doing' => 'In Progress', 'done' => 'Done'];
                foreach (['todo', 'doing', 'done'] as $status):
                    $items = array_filter($todos, function ($t) use ($status) {
                        $c = $t['status'] ?? ($t['done'] ? 'done' : 'todo');
                        return $c === $status;
                    });
                ?>
                    <div class="kb-col" data-status="<?php echo $status; ?>">
                        <div class="kb-head"><?php echo $titles[$status]; ?> <span class="kb-count"><?php echo count($items); ?></span></div>
                        <div class="kb-cards">
                            <?php foreach ($items as $t): ?>
                                <div class="kb-card kb-status-<?php echo $status; ?>" draggable="true" data-id="<?php echo $t['id']; ?>">
                                    <span class="kb-text<?php echo $status === 'done' ? ' done' : ''; ?>"><?php echo htmlspecialchars($t['text']); ?></span>
                                    <button class="kb-menu-btn" type="button">&#8943;</button>
                                    <div class="kb-menu">
                                        <?php foreach (['todo' => 'Move to To Do', 'doing' => 'Move to In Progress', 'done' => 'Complete'] as $to => $label): ?>
                                            <a href="#" data-to="<?php echo $to; ?>"<?php echo $to === $status ? ' style="display:none"' : ''; ?><?php echo $to === 'done' ? ' class="done-act"' : ''; ?>><?php echo $label; ?></a>
                                        <?php endforeach; ?>
                                        <a href="#" class="danger" data-del="1">Delete</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Clock widgets -->
        <div class="widget" id="widget-ng" style="top:12px; right:12px;">
            <div class="w-title">Nigeria</div>
            <div class="w-body">
                <div class="w-time" id="wClockNg">--:--:--</div>
                <div class="w-sub">Africa / Lagos</div>
            </div>
        </div>
        <div class="widget" id="widget-ca" style="top:96px; right:12px;">
            <div class="w-title">Canada</div>
            <div class="w-body">
                <div class="w-time" id="wClockCa">--:--:--</div>
                <div class="w-sub">America / Toronto</div>
            </div>
        </div>
        <div class="widget" id="widget-hidden" style="top:180px; right:12px;">
            <div class="w-title">Links</div>
            <div class="w-body" style="text-align:left;">
                <label class="w-row">
                    <input type="checkbox" id="showHidden">
                    <span>Show hidden links</span>
                </label>
            </div>
        </div>
        <div class="widget" id="widget-theme" style="top:244px; right:12px;">
            <div class="w-title">Theme</div>
            <div class="w-body" style="text-align:left;">
                <select id="themeSelect">
                    <option value="win95">Windows 95</option>
                    <option value="xp">Windows XP</option>
                    <option value="modern">Windows 11</option>
                    <option value="mac">macOS</option>
                    <option value="aero">Aero</option>
                    <option value="neon">Neon</option>
                    <option value="glass">Glass</option>
                    <option value="terminal">Terminal</option>
                    <option value="sunset">Sunset</option>
                </select>
            </div>
        </div>

        <!-- Folder windows -->
        <?php foreach ($desktopFolders as $f): ?>
            <div class="window" id="win-<?php echo htmlspecialchars($f['id']); ?>" data-id="<?php echo htmlspecialchars($f['id']); ?>">
                <div class="titlebar">
                    <iconify-icon icon="<?php echo htmlspecialchars($f['icon']); ?>"></iconify-icon>
                    <span class="t"><?php echo htmlspecialchars($f['name']); ?></span>
                    <button class="tb-btn" data-act="min" title="Minimize">&#8211;</button>
                    <button class="tb-btn close" data-act="close" title="Close">&#215;</button>
                </div>
                <div class="win-body">
                    <?php if (empty($f['links'])): ?>
                        <div class="empty">empty folder</div>
                    <?php else: foreach ($f['links'] as $l): ?>
                        <a class="app<?php echo $l['hidden'] ? ' is-hidden' : ''; ?>" href="<?php echo htmlspecialchars($l['url']); ?>" title="<?php echo htmlspecialchars($l['label']); ?>">
                            <img src="<?php echo htmlspecialchars($l['icon']); ?>" alt="" loading="lazy" onerror="this.style.visibility='hidden'">
                            <span class="lbl"><?php echo htmlspecialchars($l['label']); ?></span>
                        </a>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Start menu -->
    <div id="start-menu">
        <div class="sm-rail"><span>esquire OS</span></div>
        <div class="sm-panel">
            <ul class="sm-list">
                <?php foreach ($desktopFolders as $f): ?>
                    <li class="sm-item" data-folder="<?php echo htmlspecialchars($f['id']); ?>">
                        <iconify-icon icon="<?php echo htmlspecialchars($f['icon']); ?>" style="color:<?php echo htmlspecialchars($f['color']); ?>"></iconify-icon>
                        <span class="nm"><?php echo htmlspecialchars($f['name']); ?></span>
                        <span class="arrow">&#9656;</span>
                    </li>
                <?php endforeach; ?>
                <li class="sm-sep"></li>
                <li class="sm-item" data-folder="themes">
                    <iconify-icon icon="mdi:palette" style="color:#b5830a"></iconify-icon>
                    <span class="nm">Theme</span>
                    <span class="arrow">&#9656;</span>
                </li>
                <li class="sm-item"><a href="classic.php" style="display:flex;gap:8px;align-items:center;width:100%"><iconify-icon icon="mdi:web"></iconify-icon> Classic start page</a></li>
                <li class="sm-item"><a href="manage.php" style="display:flex;gap:8px;align-items:center;width:100%"><iconify-icon icon="mdi:folder-cog"></iconify-icon> Manage Links</a></li>
            </ul>
        </div>
    </div>

    <!-- Flyout submenus -->
    <div id="flyouts">
        <?php foreach ($desktopFolders as $f): ?>
            <div class="flyout" data-folder="<?php echo htmlspecialchars($f['id']); ?>" style="columns:<?php echo $f['cols']; ?>; width:<?php echo 214 * $f['cols']; ?>px;">
                <div class="flyout-title"><?php echo htmlspecialchars($f['name']); ?></div>
                <?php foreach ($f['links'] as $l): ?>
                    <a class="sm-link<?php echo $l['hidden'] ? ' is-hidden' : ''; ?>" href="<?php echo htmlspecialchars($l['url']); ?>"
                       data-label="<?php echo htmlspecialchars(strtolower($l['label'])); ?>"
                       data-url="<?php echo htmlspecialchars(strtolower($l['url'])); ?>">
                        <img src="<?php echo htmlspecialchars($l['icon']); ?>" alt="" loading="lazy" onerror="this.style.visibility='hidden'">
                        <span class="lbl"><?php echo htmlspecialchars($l['label']); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

            <div class="flyout" data-folder="themes" style="columns:1; width:214px;">
                <div class="flyout-title">Desktop Themes</div>
                <a class="sm-link theme-opt" href="#" data-theme="win95"><span class="lbl">Windows 95</span></a>
                <a class="sm-link theme-opt" href="#" data-theme="xp"><span class="lbl">Windows XP</span></a>
                <a class="sm-link theme-opt" href="#" data-theme="modern"><span class="lbl">Windows 11</span></a>
                <a class="sm-link theme-opt" href="#" data-theme="mac"><span class="lbl">macOS</span></a>
                <a class="sm-link theme-opt" href="#" data-theme="aero"><span class="lbl">Aero</span></a>
                <a class="sm-link theme-opt" href="#" data-theme="neon"><span class="lbl">Neon</span></a>
                <a class="sm-link theme-opt" href="#" data-theme="glass"><span class="lbl">Glass</span></a>
                <a class="sm-link theme-opt" href="#" data-theme="terminal"><span class="lbl">Terminal</span></a>
                <a class="sm-link theme-opt" href="#" data-theme="sunset"><span class="lbl">Sunset</span></a>
                <div class="flyout-title" style="margin-top:8px;">Classic Themes</div>
                <a class="sm-link" href="classic.php?style=css/bootstrap-brite.min.css"><span class="lbl">Brite</span></a>
                <a class="sm-link" href="classic.php?style=css/bootstrap-cosmo.min.css"><span class="lbl">Cosmo</span></a>
                <a class="sm-link" href="classic.php?style=css/bootstrap-cyborg.min.css"><span class="lbl">Cyborg</span></a>
                <a class="sm-link" href="classic.php?style=css/bootstrap-darkly.min.css"><span class="lbl">Darkly</span></a>
                <a class="sm-link" href="classic.php?style=css/bootstrap-flatly.min.css"><span class="lbl">Flatly</span></a>
                <a class="sm-link" href="classic.php?style=css/bootstrap-simplex.min.css"><span class="lbl">Simplex</span></a>
                <a class="sm-link" href="classic.php?style=css/bootstrap-sketchy.min.css"><span class="lbl">Sketchy</span></a>
                <a class="sm-link" href="classic.php?style=css/bootstrap-slate.min.css"><span class="lbl">Slate</span></a>
                <a class="sm-link" href="classic.php?style=css/bootstrap-superhero.min.css"><span class="lbl">Superhero</span></a>
            </div>
    </div>

    <!-- Taskbar -->
    <div id="taskbar">
        <button id="startBtn" type="button"><iconify-icon icon="mdi:windows"></iconify-icon><span class="start-label">Start</span></button>
        <div id="taskbar-tabs">
            <?php foreach ($desktopFolders as $f): ?>
                <button class="task-tab" data-id="<?php echo htmlspecialchars($f['id']); ?>" type="button">
                    <iconify-icon icon="<?php echo htmlspecialchars($f['icon']); ?>"></iconify-icon>
                    <?php echo htmlspecialchars($f['name']); ?>
                </button>
            <?php endforeach; ?>
        </div>
        <div class="tray">
            <div id="clock">--:--</div>
            <div id="date"></div>
        </div>
    </div>

    <script>
    window.SEARCH_LINKS = <?php echo json_encode($searchLinks, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    </script>
    <script>
    (function () {
        var zCounter = 10;

        function $(id) { return document.getElementById(id); }
        function win(id) { return $('win-' + id); }
        function tab(id) { return document.querySelector('.task-tab[data-id="' + id + '"]'); }

        function saveWindows() {
            var state = {};
            document.querySelectorAll('.task-tab.visible').forEach(function (t) {
                var id = t.dataset.id;
                var el = win(id);
                if (!el) return;
                var x = parseInt(el.style.left, 10);
                var y = parseInt(el.style.top, 10);
                state[id] = { x: isNaN(x) ? 0 : x, y: isNaN(y) ? 0 : y };
            });
            localStorage.setItem('openWindows', JSON.stringify(state));
            var kanban = $('kanban-wrap');
            if (kanban) {
                localStorage.setItem('kanbanMinimized', kanban.style.display === 'none' ? 'true' : 'false');
            }
        }

        function focusWindow(el) {
            el.style.zIndex = ++zCounter;
            document.querySelectorAll('.window').forEach(function (w) { w.classList.remove('active'); });
            el.classList.add('active');
            document.querySelectorAll('.task-tab').forEach(function (t) { t.classList.remove('active'); });
            var t = tab(el.dataset.id);
            if (t) { t.classList.add('active'); t.classList.add('visible'); }
        }
        function openWindow(id) {
            var el = win(id);
            if (!el) return;
            el.style.display = 'flex';
            focusWindow(el);
            saveWindows();
        }
        function minimizeWindow(el) {
            el.style.display = 'none';
            el.classList.remove('active');
            var t = tab(el.dataset.id);
            if (t) t.classList.remove('active');
            saveWindows();
        }

        document.querySelectorAll('.desk-icon[data-id]').forEach(function (ic) {
            ic.addEventListener('click', function () {
                openWindow(ic.dataset.id);
                document.querySelectorAll('.desk-icon').forEach(function (x) { x.classList.remove('selected'); });
                ic.classList.add('selected');
            });
        });

        // Kanban board: minimize into the desktop "Tasks" icon
        var kanbanWrap = $('kanban-wrap');
        var kanbanMin = $('kanbanMin');
        var deskKanban = $('desk-kanban');
        function showKanban() { if (kanbanWrap) kanbanWrap.style.display = 'flex'; }
        function hideKanban() { if (kanbanWrap) kanbanWrap.style.display = 'none'; }
        if (kanbanMin) kanbanMin.addEventListener('click', function() { hideKanban(); saveWindows(); });
        if (deskKanban) deskKanban.addEventListener('click', function() { showKanban(); focusKanban(); saveWindows(); });
        var kanbanState = localStorage.getItem('kanbanMinimized');
        if (kanbanState === 'true') { hideKanban(); } else { showKanban(); }
        function focusKanban() {
            if (!kanbanWrap) return;
            kanbanWrap.style.zIndex = ++zCounter;
        }
        if (kanbanWrap) {
            kanbanWrap.addEventListener('pointerdown', focusKanban);
            var kbBar = kanbanWrap.querySelector('.titlebar');
            if (kbBar) {
                kbBar.addEventListener('pointerdown', function (e) {
                    if (e.target.closest('button')) return;
                    e.preventDefault();
                    var startX = e.clientX, startY = e.clientY;
                    var rect = kanbanWrap.getBoundingClientRect();
                    var offX = startX - rect.left, offY = startY - rect.top;
                    focusKanban();
                    function move(ev) {
                        kanbanWrap.style.left = Math.max(0, Math.min(ev.clientX - offX, window.innerWidth - 60)) + 'px';
                        kanbanWrap.style.top = Math.max(0, Math.min(ev.clientY - offY, window.innerHeight - 80)) + 'px';
                        kanbanWrap.style.transform = 'none';
                    }
                    function up() { window.removeEventListener('pointermove', move); window.removeEventListener('pointerup', up); }
                    window.addEventListener('pointermove', move);
                    window.addEventListener('pointerup', up);
                });
            }
        }

        document.querySelectorAll('.task-tab').forEach(function (t) {
            t.addEventListener('click', function () {
                var id = t.dataset.id;
                var el = win(id);
                if (!el) return;
                if (el.style.display === 'flex' && el.classList.contains('active')) minimizeWindow(el);
                else openWindow(id);
            });
        });

        document.querySelectorAll('.window').forEach(function (el) {
            el.addEventListener('pointerdown', function () { focusWindow(el); });
            var bar = el.querySelector('.titlebar');
            if (bar) {
                bar.addEventListener('pointerdown', function (e) {
                    if (e.target.closest('.tb-btn')) return;
                    e.preventDefault();
                    var startX = e.clientX, startY = e.clientY;
                    var rect = el.getBoundingClientRect();
                    var offX = startX - rect.left, offY = startY - rect.top;
                    function move(ev) {
                        el.style.left = Math.max(0, Math.min(ev.clientX - offX, window.innerWidth - 60)) + 'px';
                        el.style.top = Math.max(0, Math.min(ev.clientY - offY, window.innerHeight - 80)) + 'px';
                    }
                    function up() { window.removeEventListener('pointermove', move); window.removeEventListener('pointerup', up); saveWindows(); }
                    window.addEventListener('pointermove', move);
                    window.addEventListener('pointerup', up);
                });
            }
            el.querySelectorAll('.tb-btn').forEach(function (b) {
                b.addEventListener('click', function (e) {
                    e.stopPropagation();
                    if (b.dataset.act === 'close') {
                        el.style.display = 'none';
                        el.classList.remove('active');
                        var t = tab(el.dataset.id);
                        if (t) { t.classList.remove('active'); t.classList.remove('visible'); }
                        saveWindows();
                    } else minimizeWindow(el);
                });
            });
        });

        document.querySelectorAll('.widget').forEach(function (el) {
            var bar = el.querySelector('.w-title');
            bar.addEventListener('pointerdown', function (e) {
                e.preventDefault();
                var sx = e.clientX, sy = e.clientY;
                var r = el.getBoundingClientRect();
                var ox = sx - r.left, oy = sy - r.top;
                function mv(ev) {
                    el.style.right = 'auto';
                    el.style.left = Math.max(0, Math.min(ev.clientX - ox, window.innerWidth - 160)) + 'px';
                    el.style.top = Math.max(0, Math.min(ev.clientY - oy, window.innerHeight - 130)) + 'px';
                }
                function up() { window.removeEventListener('pointermove', mv); window.removeEventListener('pointerup', up); }
                window.addEventListener('pointermove', mv);
                window.addEventListener('pointerup', up);
            });
        });

        // ---------- Start menu + flyouts ----------
        var startMenu = $('start-menu');
        var startBtn = $('startBtn');
        var flyouts = {};
        document.querySelectorAll('.flyout').forEach(function (f) { flyouts[f.dataset.folder] = f; });

        var openFlyout = null, closeTimer = null;
        function clearFlyoutTimer() { if (closeTimer) { clearTimeout(closeTimer); closeTimer = null; } }
        function hideFlyout() {
            if (openFlyout) { openFlyout.style.display = 'none'; openFlyout = null; }
            document.querySelectorAll('.sm-item').forEach(function (i) { i.classList.remove('open'); });
        }
        function showFlyout(item) {
            var f = flyouts[item.dataset.folder];
            if (!f) return;
            clearFlyoutTimer();
            hideFlyout();
            var r = item.getBoundingClientRect();
            var menuR = startMenu.getBoundingClientRect();
            f.style.display = 'block';
            f.style.top = r.top + 'px';
            f.style.left = menuR.right + 'px';
            var fr = f.getBoundingClientRect();
            if (fr.right > window.innerWidth - 4) f.style.left = Math.max(4, menuR.left - fr.width - 2) + 'px';
            if (fr.bottom > window.innerHeight - 4) f.style.top = Math.max(4, window.innerHeight - fr.height - 4) + 'px';
            openFlyout = f;
            item.classList.add('open');
        }
        document.querySelectorAll('.sm-item[data-folder]').forEach(function (item) {
            item.addEventListener('mouseenter', function () { showFlyout(item); });
            item.addEventListener('mouseleave', function () { closeTimer = setTimeout(hideFlyout, 160); });
            item.addEventListener('click', function () {
                clearFlyoutTimer();
                if (openFlyout && openFlyout === flyouts[item.dataset.folder] && openFlyout.style.display === 'block') hideFlyout();
                else showFlyout(item);
            });
        });
        document.querySelectorAll('.flyout').forEach(function (f) {
            f.addEventListener('mouseenter', clearFlyoutTimer);
            f.addEventListener('mouseleave', function () { closeTimer = setTimeout(hideFlyout, 160); });
        });
        startBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (!startMenu.classList.toggle('open')) hideFlyout();
        });
        document.addEventListener('click', function (e) {
            if (!startMenu.contains(e.target) && e.target !== startBtn) { startMenu.classList.remove('open'); hideFlyout(); }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { startMenu.classList.remove('open'); hideFlyout(); }
        });

        // ---------- Theme switcher ----------
        var THEME_META = {
            win95:  { icon: 'mdi:windows', label: 'Start' },
            xp:     { icon: 'mdi:windows', label: 'start' },
            modern: { icon: 'mdi:windows', label: '' },
            mac:    { icon: 'mdi:apple',   label: '' },
            aero:   { icon: 'mdi:windows', label: 'Start' },
            neon:   { icon: 'mdi:flask-outline', label: '' },
            glass:  { icon: 'mdi:blur', label: 'Start' },
            terminal: { icon: 'mdi:console', label: '>' },
            sunset: { icon: 'mdi:weather-sunset', label: 'Start' }
        };
        function applyTheme(name) {
            var meta = THEME_META[name] || THEME_META.win95;
            document.body.classList.remove('theme-win95', 'theme-xp', 'theme-modern', 'theme-mac', 'theme-aero', 'theme-neon', 'theme-glass', 'theme-terminal', 'theme-sunset');
            document.body.classList.add('theme-' + name);
            var sb = $('startBtn');
            sb.querySelector('iconify-icon').setAttribute('icon', meta.icon);
            sb.querySelector('.start-label').textContent = meta.label;
            var sel = $('themeSelect');
            if (sel) sel.value = name;
            document.querySelectorAll('.theme-opt').forEach(function (o) { o.classList.toggle('active', o.dataset.theme === name); });
            localStorage.setItem('desktopTheme', name);
        }
        applyTheme(localStorage.getItem('desktopTheme') || 'win95');
        $('themeSelect').addEventListener('change', function () { applyTheme(this.value); });
        document.getElementById('flyouts').addEventListener('click', function (e) {
            var t = e.target.closest('.theme-opt');
            if (t) {
                e.preventDefault();
                applyTheme(t.dataset.theme);
                hideFlyout();
                startMenu.classList.remove('open');
            }
        });

        // ---------- Search ----------
        var searchLinks = window.SEARCH_LINKS || [];
        var searchBox = $('search-box');
        var searchResults = $('search-results');
        function isValidURL(s) {
            try { new URL(s.startsWith('http') ? s : 'http://' + s); return s.includes('.'); }
            catch (_) { return false; }
        }
        function matches(q) {
            return searchLinks.filter(function (l) {
                return l.label.toLowerCase().indexOf(q) !== -1 || l.url.toLowerCase().indexOf(q) !== -1;
            });
        }
        function escapeHtml(s) {
            return String(s).replace(/[&<>"']/g, function (c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }
        searchBox.addEventListener('input', function () {
            var q = this.value.trim().toLowerCase();
            if (!q) { searchResults.style.display = 'none'; return; }
            var hits = matches(q).slice(0, 14);
            searchResults.innerHTML = '';
            if (!hits.length) { searchResults.style.display = 'none'; return; }
            hits.forEach(function (l) {
                var a = document.createElement('a');
                a.className = 'search-hit';
                a.href = l.url; a.target = '_self';
                a.innerHTML = '<img src="' + l.icon + '" alt=""><span class="lbl">' + escapeHtml(l.label) + '</span><span class="cat">' + escapeHtml(l.folder) + '</span>';
                searchResults.appendChild(a);
            });
            searchResults.style.display = 'block';
        });
        function handleSearch() {
            var q = searchBox.value.trim();
            if (!q) return false;
            fetch('index.php?q=' + encodeURIComponent(q));
            var hits = matches(q.toLowerCase());
            if (hits.length === 1) { window.open(hits[0].url, '_blank'); return false; }
            if (isValidURL(q)) { window.location.href = q.startsWith('http') ? q : 'http://' + q; }
            else { window.location.href = 'https://www.google.com/search?q=' + encodeURIComponent(q); }
            return false;
        }
        document.addEventListener('click', function (e) {
            if (!e.target.closest('#search-area')) searchResults.style.display = 'none';
        });

        // ---------- Kanban ----------
        var board = $('kanbanBoard');
        function closeMenus() { board.querySelectorAll('.kb-menu').forEach(function (m) { m.style.display = 'none'; }); }
        function updateCounts() {
            board.querySelectorAll('.kb-col').forEach(function (col) {
                var c = col.querySelector('.kb-count');
                if (c) c.textContent = col.querySelectorAll('.kb-card').length;
            });
        }
        function moveCard(card, to) {
            var from = card.closest('.kb-col').dataset.status;
            if (from === to) { closeMenus(); return; }
            fetch('index.php?action=move_todo&id=' + encodeURIComponent(card.dataset.id) + '&to=' + encodeURIComponent(to))
                .then(function (r) { if (!r.ok) throw new Error('move'); })
                .then(function () {
                    board.querySelector('.kb-col[data-status="' + to + '"] .kb-cards').appendChild(card);
                    card.classList.remove('kb-status-todo', 'kb-status-doing', 'kb-status-done');
                    card.classList.add('kb-status-' + to);
                    card.querySelector('.kb-text').classList.toggle('done', to === 'done');
                    card.querySelectorAll('.kb-menu a[data-to]').forEach(function (a) {
                        a.style.display = a.dataset.to === to ? 'none' : '';
                    });
                    updateCounts();
                    closeMenus();
                })
                .catch(function () { window.location.reload(); });
        }
        function deleteCard(card) {
            if (!confirm('Delete this task?')) return;
            fetch('index.php?action=delete_todo&id=' + encodeURIComponent(card.dataset.id))
                .then(function (r) { if (!r.ok) throw new Error('del'); })
                .then(function () { card.remove(); updateCounts(); closeMenus(); })
                .catch(function () { window.location.reload(); });
        }
        board.addEventListener('click', function (e) {
            var btn = e.target.closest('.kb-menu-btn');
            if (btn) {
                var menu = btn.parentElement.querySelector('.kb-menu');
                var open = menu.style.display === 'block';
                closeMenus();
                menu.style.display = open ? 'none' : 'block';
                return;
            }
            var act = e.target.closest('.kb-menu a');
            if (act) {
                e.preventDefault();
                var card = act.closest('.kb-card');
                if (act.dataset.to) moveCard(card, act.dataset.to);
                else if (act.dataset.del) deleteCard(card);
            }
        });
        document.addEventListener('click', function (e) {
            if (!e.target.closest('.kb-card')) closeMenus();
        });

        var clearBtn = $('clearCompletedBtn');
        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                if (!confirm('Clear all completed tasks?')) return;
                fetch('index.php?action=clear_completed')
                    .then(function (r) { if (!r.ok) throw new Error('clear'); })
                    .then(function () { window.location.reload(); })
                    .catch(function () { window.location.reload(); });
            });
        }

        var dragCard = null;
        board.addEventListener('dragstart', function (e) {
            var c = e.target.closest('.kb-card');
            if (!c) return;
            dragCard = c;
            c.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', c.dataset.id);
        });
        board.addEventListener('dragend', function () {
            if (dragCard) dragCard.classList.remove('dragging');
            dragCard = null;
            clearDrops();
        });
        board.addEventListener('dragover', function (e) {
            if (!dragCard) return;
            var col = e.target.closest('.kb-col');
            if (!col) return;
            e.preventDefault();
            col.classList.add('drop');
        });
        board.addEventListener('dragleave', function (e) {
            var col = e.target.closest('.kb-col');
            if (col && !col.contains(e.relatedTarget)) col.classList.remove('drop');
        });
        board.addEventListener('drop', function (e) {
            if (!dragCard) return;
            var col = e.target.closest('.kb-col');
            if (!col) return;
            e.preventDefault();
            clearDrops();
            moveCard(dragCard, col.dataset.status);
        });
        function clearDrops() { board.querySelectorAll('.kb-col').forEach(function (c) { c.classList.remove('drop'); }); }

        // ---------- Clocks ----------
        function updateClocks() {
            ['wClockNg', 'wClockCa'].forEach(function (id, i) {
                var zone = i === 0 ? 'Africa/Lagos' : 'America/Toronto';
                var parts = new Intl.DateTimeFormat('en-US', {
                    timeZone: zone, hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true
                }).formatToParts(new Date());
                var h = parts.find(function (p) { return p.type === 'hour'; }).value;
                var m = parts.find(function (p) { return p.type === 'minute'; }).value;
                var s = parts.find(function (p) { return p.type === 'second'; }).value;
                var ampm = parts.find(function (p) { return p.type === 'dayPeriod'; });
                var el = $(id);
                if (el) el.textContent = h + ':' + m + ':' + s + (ampm ? ' ' + ampm.value : '');
            });
        }
        updateClocks();
        setInterval(updateClocks, 1000);

        function tick() {
            var d = new Date();
            $('clock').textContent = d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            $('date').textContent = d.toLocaleDateString([], { month: 'short', day: 'numeric', year: 'numeric' });
        }
        tick();
        setInterval(tick, 1000);

        // ---------- Hidden links switch ----------
        var hiddenToggle = $('showHidden');
        hiddenToggle.checked = localStorage.getItem('showHidden') === 'true';
        document.body.classList.toggle('show-hidden', hiddenToggle.checked);
        hiddenToggle.addEventListener('change', function () {
            document.body.classList.toggle('show-hidden', this.checked);
            localStorage.setItem('showHidden', this.checked);
        });

        // Cascade windows on load
        document.querySelectorAll('.window').forEach(function (el, i) {
            el.style.left = (240 + (i % 4) * 30) + 'px';
            el.style.top = (120 + (i % 4) * 26) + 'px';
        });

        // Restore previously open windows (and their positions)
        (function () {
            var saved = {};
            try { saved = JSON.parse(localStorage.getItem('openWindows') || '{}'); } catch (e) {}
            Object.keys(saved).forEach(function (id) {
                var el = win(id);
                if (!el) return;
                var s = saved[id];
                if (s && typeof s.x === 'number') {
                    el.style.left = s.x + 'px';
                    el.style.top = s.y + 'px';
                }
                openWindow(id);
            });
        })();
    })();
    </script>
</body>
</html>
