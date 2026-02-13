<?php 
define('LINKS_FILE', 'data/links_v2.json');
// Load Data
$rawMenu = json_decode(@file_get_contents(LINKS_FILE), true) ?: ['folders' => [], 'links' => []];

// Run the color check
ensure_folder_colors($rawMenu);

function ensure_folder_colors(&$data) {
    $updated = false;
    
    foreach ($data['folders'] as &$folder) {
        // Generate a random dark color
        $red = rand(20, 50);
        $green = rand(20, 50);
        $blue = rand(20, 50);
        $folder['color'] = sprintf('#%02x%02x%02x', $red, $green, $blue);
        
        $updated = true;
    }

    // Only write to disk if we actually changed something to save I/O
    if ($updated) {
        if (!is_dir('data')) mkdir('data', 0777, true);
        file_put_contents(LINKS_FILE, json_encode($data, JSON_PRETTY_PRINT));
    }
}