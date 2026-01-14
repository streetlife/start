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
function load_todo($completed_flag = false) {
	global $todos;

	// Render the list
	$result = '<ul class="list-group list-group-flush border-0 mb-2">';
	foreach ($todos as $todo) {
		if ($todo['done']==$completed_flag) {
			// $result .= '<li class="list-group-item text-white"><a href="index.php?action=delete_todo&id=' . $todo['id'] . '" class="text-white">' . $todo['text'] . '</a></li>';

			$result .= '<a href="index.php?action=delete_todo&id=' . $todo['id'] . '" class="list-group-item list-group-item-action" onclick="return confirm(\'Are you sure you want to delete - ' . $todo['text'] . '?\')">' . $todo['text'] . '</a>';
		}
	}
	$result .= '</ul>';
	return $result;
}

// Function to create the menu from a multi-level array
function createMenu($menu, $subFolder = false, $showIcon = false) {
	$local_name_offline = 'img/icon-local.png';

    if ($subFolder) {
        echo '<ul class="sub-menu p-1 m-1">';
    }
    // echo '<ul>';
    $i = 1;
    foreach ($menu as $label => $linkOrSubmenu) {
        echo '<li class="link">';
        if (is_array($linkOrSubmenu)) {
            // If it's an array, create a sub-menu
            echo '<div class="menu-stack p-1 m-2">';
            echo '<a class="nav-title p-0 m-0" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">' . $label . '</a>'; // Label for the sub-menu
            ksort($linkOrSubmenu);
            createMenu($linkOrSubmenu, true, $showIcon); // Recursively create sub-menu
            echo '</div>';
        } else {
            echo '<a href="' . $linkOrSubmenu . '" class="nav-link p-0 m-0">';
            // display $i with a leading zero to 2 characters
            // echo str_pad($i, 2, '0', STR_PAD_LEFT) . '. ';
            if ($showIcon) {
                // if $linkOrSubmenu contains ".test", skip
                $local_name = 'img/icons/'.$label.'.png';
                if (!file_exists($local_name)) {
                    if (strpos($linkOrSubmenu, ".test") == false) {
                        file_put_contents($local_name, file_get_contents('https://www.google.com/s2/favicons?domain='.$linkOrSubmenu.'&sz=256'));
                    }
                }

                if (filesize($local_name) == 0) {
                    copy($local_name_offline, $local_name);
                }

                echo '<img src="'.$local_name.'" class="icon" style="clear:both" /> ';
            }
            
            $label = str_replace('_','.',$label);
            // $label = str_replace(' ', '.', $label);
            $label = str_replace('www.', '', $label);
            $label = strtolower($label);
            echo $label . '</a>';
        }
        echo '</li>';
        $i++;
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


ini_set('display_errors', 1);
define('SHOW_ICON', true);
define('REFRESH_RATE', 600);
define('TODO_FILE', 'data/todo.json');
define('LINKS_FILE', 'data/links.json');

$column_css = 'col-sm-6 col-md-4 col-lg-4 col-xl-2 listColumn border-0';

$todos = json_decode(file_get_contents(TODO_FILE), true);
$menuData = json_decode(file_get_contents(LINKS_FILE), true);

$projects_folder = '..';
$dirs = array_filter(glob($projects_folder . '/*'), 'is_dir');
foreach ($dirs as $value) {
	$value = strtolower(str_replace('../','',$value));
	$project_links[$value] = 'https://'.$value.'.test';
}

$menuData_projects['dev projects'] = $project_links;

$css_form = load_css_files();
$selected_style = get_selected_style();

check_new_todo($todos);
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
        /* Basic styling for the menu */
        li {
            list-style-type: none;
        }
        .hidden {
            display: none;
        }
        #search {
            margin-bottom: 20px;
            padding: 10px;
            width: 300px;
            font-size: 16px;
        }
        .link {
            padding: 0;
            margin: 0;
        }
    </style>
</head>
<body class="p-0">

<div class="container-fluid p-1" >
    <div class="row g-0 p-0 ">
        <div class="col-lg-10 col-md-10 col-sm-6 col-xs-6 p-0">
            <div class="row">
                <div class="col-md-2">
                    <div class="card bg-transparent m-0">
                        <div class="card-body p-1">
                            <nav class="nav">
                                <?php createMenu($menuData_projects, true, true); ?>
                            </nav>
                        </div>
                    </div>
                </div>
                <div class="col-md-10">
                    <div class="card bg-transparent m-0">
                        <div class="card-body p-1">
                            <nav class="nav">
                                <?php createMenu($menuData, false, true); ?>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6 p-0">
            <div class="card bg-transparent m-0">
                <div class="card-body">
                    <form id="search-form" method="get" onsubmit="return handleSearch();" class="form">
                        <input type="text" id="search-box" name="q" placeholder="Filter menu items or Search Google or Enter URL" class="form-control form-control-sm m-0" required>
                    </form>
                </div>
                <div class="card-body">
                    <?php echo $css_form; ?>
                </div>
                <div class="card-body">
                    <div class="card-header"><h6>Nigeria</h6></div>
                    <div id="currentTime"><?php echo date('H:i:s A'); ?></div>
                </div>
                <div class="card-body">
                    <div class="card-header"><h6>Canada</h6></div>
                    <div id="currentTime2"><?php echo date('H:i:s A'); ?></div>
                </div>
                <div class="card-body">
                    <?php echo load_todo(); ?>
                    <form action="index.php" method="post" class="form">
                        <div class="form p-1">
                        <input type="text" name="todo" class="form-control form-control-sm m-0">
                            <input type="hidden" name="action" value="add_todo">
                        </div>           
                    </form>
                    <?php // echo load_todo(true); ?>
                </div>
            </div>
        </div>
    </div>
    

    <script src="js/bootstrap.bundle.min.js"></script>
		<script>
			window.onload = function() {
				document.getElementById('search-box').focus();
			};

			function isValidURL(string) {
				try {
					new URL(string);
					return true;
				} catch (_) {
					return false;
				}
			}

			function handleSearch() {
				var query = document.getElementById('search-box').value.trim();

				if (isValidURL(query)) {
					window.location.href = query.startsWith("http") ? query : "http://" + query;
				} else {
					// Perform a Google search
					var form = document.getElementById('search-form');
					form.action = "https://www.google.com/search";
					form.submit();
				}

				return false; // Prevent default form submission
			}
			function updateTime() {
            // Specify the timezone you want to display
            var timezone = 'Africa/Lagos'; // Change to your desired timezone

            // Get the current time in the specified timezone
            var options = { timeZone: timezone, hour: '2-digit', minute: '2-digit', second: '2-digit' };
            var currentTime = new Intl.DateTimeFormat('en-US', options).format(new Date());

            // Update the HTML element
            document.getElementById("currentTime").innerHTML = currentTime;

			var timezone = 'America/Thule';

			// Get the current time in the specified timezone
			var options = { timeZone: timezone, hour: '2-digit', minute: '2-digit', second: '2-digit' };
			var currentTime = new Intl.DateTimeFormat('en-US', options).format(new Date());

			// Update the HTML element
			document.getElementById("currentTime2").innerHTML = currentTime;

        }

        // Update the time every second
        setInterval(updateTime, 1000);

        // Function to search through the menu
        document.getElementById("search-box").addEventListener("input", function() {
            var filter = this.value.toLowerCase();
            var listItems = document.querySelectorAll(".nav li");

            listItems.forEach(function(item) {
                // Check if the item contains the search term
                var text = item.textContent.toLowerCase();
                if (text.includes(filter)) {
                    item.style.display = "";
                } else {
                    item.style.display = "none";
                }
            });
        });
    </script>

</body>
</html>
