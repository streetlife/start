<?php 
function get_selected_style() {
    if (isset($_GET['style'])) {
        file_put_contents('selected_style.txt', $_GET['style']);
    }

    $selected_style = file_get_contents('selected_style.txt');
    return $selected_style;
}
function load_css_files() {
    $css_files = glob('css/bootstrap-*.css');
    // die(print_r($css_files));
    $select_list = '<select name="style" onchange="this.form.submit()" class="form-control form-control-sm">';
    foreach ($css_files as $css_file) {
        $css_file_name = str_replace('css/bootstrap-','',$css_file);
        $css_file_name = str_replace('.min.css','',$css_file_name);
        $strSelectedTheme = 'Change theme to ' . strtoupper($css_file_name);
        $selected_style = get_selected_style();
        if ($selected_style == $css_file) {
            $strSelectedTheme = strtoupper($css_file_name);
        }
        $select_list .= '<option value="' . $css_file . '" ' . ($selected_style == $css_file ? 'selected' : '') . '>'. $strSelectedTheme  . '</option>';
    }
    $select_list .= '</select>';
    $css_form = '<form method="get" class="form">' . $select_list . '</form>';

    return $css_form;
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

// function to randomly select a picture from a folder
function display_random_wallpaper() {
	// Path to the folder containing images
	$imageFolder = 'img/wallpapers/';

	// Get all image files from the folder
	$imageFiles = glob($imageFolder . '*.{jpg,jpeg,png}', GLOB_BRACE);

	// Select a random image from the array
	$randomImage = $imageFiles[array_rand($imageFiles)];

	// Display the random image on the webpage
	// return '<img src="' . $randomImage . '"  width="100%" class="img">';
	return $randomImage;
}

// function to load a list of todos from a json file
function load_todo() {
	global $todos;

	// Render the list
	$result = '<ul class="list-group list-group-flush border-0 mb-2">';
	foreach ($todos as $todo) {
		if ($todo['done']!=true) {
			// $result .= '<li class="list-group-item text-white"><a href="index.php?action=delete_todo&id=' . $todo['id'] . '" class="text-white">' . $todo['text'] . '</a></li>';

			$result .= '<a href="index.php?action=delete_todo&id=' . $todo['id'] . '" class="list-group-item list-group-item-action" onclick="return confirm(\'Are you sure you want to delete - ' . $todo['text'] . '?\')">' . $todo['text'] . '</a>';
		}
	}
	$result .= '</ul>';
	return $result;
}

// Function to create the menu from a multi-level array
function createMenu($menu, $subFolder = false) {
	$local_name_offline = 'img/icon-local.png';

    if ($subFolder) {
        echo '<ul class="sub-menu">';
    }
    // echo '<ul>';
    foreach ($menu as $label => $linkOrSubmenu) {
        echo '<li class="link">';
        if (is_array($linkOrSubmenu)) {
            // If it's an array, create a sub-menu
            echo '<a class="nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">' . $label . '</a>'; // Label for the sub-menu
            ksort($linkOrSubmenu);
            createMenu($linkOrSubmenu, true); // Recursively create sub-menu
        } else {
            
			$local_name = 'img/icons/'.$label.'.png';
			if (!file_exists($local_name)) {
				file_put_contents($local_name, file_get_contents('https://www.google.com/s2/favicons?domain='.$linkOrSubmenu.'&sz=256'));
			}

			if (filesize($local_name) == 0) {
				copy($local_name_offline, $local_name);
			}
            // If it's a link, create an anchor tag
            echo '<a href="' . $linkOrSubmenu . '">
				<img src="'.$local_name.'" class="icon" style="clear:both" /> '
                . $label . '</a>';
        }
        echo '</li>';
    }
    if ($subFolder) {
        echo '</ul>';
    }
}

function check_new_todo($todos) {
    // Add a new todo
    if (isset($_POST['action']) && $_POST['action'] == 'add_todo') {
        
        if (empty($todos)) {
            $todos = array();
        }
        $last_todo = end($todos);
        $todo['id'] = $last_todo['id'] + 1;
        $todo['text'] = $_POST['todo'];
        $todo['done'] = false;

        $todos[] = $todo;
        // array_push($todos, $_POST['todo']);
        file_put_contents(TODO_FILE, json_encode($todos, true));
        header('Location: index.php');
    }
}

function check_delete_todo($todos) {
    // Delete a todo
    if (isset($_GET['action'])) {
        if ($_GET['action'] == 'delete_todo') {
            // $todos = json_decode(file_get_contents($todo_file), true);
            foreach ($todos as $key=>$todo) {
                if ($todo['id']==$_GET['id']) {
                    $todos[$key]['done'] = true;
                }
            }
            file_put_contents(TODO_FILE, json_encode($todos, true));
            header('Location: index.php');
        }
    }
}