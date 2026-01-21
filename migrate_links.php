<?php
define('DATA_FILE', 'data/links_v2.json');

function migrate_link_targets() {
    if (!file_exists(DATA_FILE)) {
        die("Error: Data file not found.");
    }

    // 1. Load the existing data
    $json_content = file_get_contents(DATA_FILE);
    $data = json_decode($json_content, true);

    if (!$data || !isset($data['links'])) {
        die("Error: Invalid JSON structure.");
    }

    $modified_count = 0;

    // 2. Process each link
    foreach ($data['links'] as &$link) {
        // Only process if target is missing or empty
        if (!isset($link['target']) || empty($link['target'])) {
            
            $url = $link['url'];
            $host = parse_url($url, PHP_URL_HOST);

            // Check if it's a local link
            // Detects: localhost, 127.0.0.1, and .test domains
            $is_local = (
                $host === 'localhost' || 
                $host === '127.0.0.1' || 
                (strpos($host, '.test') !== false) ||
                empty($host) // Handles relative paths or simple IP strings
            );

            if ($is_local) {
                $link['target'] = '_self';
            } else {
                $link['target'] = '_blank';
            }
            
            $modified_count++;
        }
    }

    // 3. Save the updated data back to the file
    if ($modified_count > 0) {
        $new_json_content = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents(DATA_FILE, $new_json_content);
        echo "Migration complete! Updated $modified_count links.";
    } else {
        echo "No updates needed. All links already have targets.";
    }
}

// Run the function
migrate_link_targets();
?>