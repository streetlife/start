<?php 
ini_set('display_errors', 0);
$settings = [
	'show_icon'=>true,
	'refresh_rate'=>600,
];

$column_css = 'col-sm-6 col-md-4 col-lg-4 col-xl-2 listColumn border-0';

if (isset($_GET['style'])) {
    file_put_contents('selected_style.txt', $_GET['style']);
}

$selected_style = file_get_contents('selected_style.txt');

$projects_folder = '..';
$dirs = array_filter(glob($projects_folder . '/*'), 'is_dir');
foreach ($dirs as $value) {
	$value = strtolower(str_replace('../','',$value));
	$project_links['https://'.$value.'.test'] = $value;
}

$list_cards = [];

// $css_files = glob('css/bootstrap-*.css');
// // die(print_r($css_files));
// $select_list = '<select name="style" onchange="this.form.submit()" class="form-control form-control-sm">';
// foreach ($css_files as $css_file) {
// 	$css_file_name = str_replace('css/bootstrap-','',$css_file);
// 	$css_file_name = str_replace('.min.css','',$css_file_name);
//     $select_list .= '<option value="' . $css_file . '" ' . ($selected_style == $css_file ? 'selected' : '') . '>' . $css_file_name . '</option>';
// }
// $select_list .= '</select>';
// $css_form = '<form method="get" class="form">' . $select_list . '</form>';

$todo_file = 'data/todo.json';
$links_file = 'data/links.json';
$log_file = "hits.log";

$links = json_decode(file_get_contents($links_file), true);
$todos = json_decode(file_get_contents($todo_file), true);
// $logs = json_decode(file_get_contents($log_file), true);

// Add a new todo
if (isset($_POST['action']) && $_POST['action'] == 'add_todo') {
	// $todos = json_decode(file_get_contents($todo_file), true);
	
	if (empty($todos)) {
		$todos = array();
	}
	$last_todo = end($todos);
	$todo['id'] = $last_todo['id'] + 1;
	$todo['text'] = $_POST['todo'];
	$todo['done'] = false;

	$todos[] = $todo;
	// array_push($todos, $_POST['todo']);
	file_put_contents($todo_file, json_encode($todos, true));
	header('Location: index.php');
}

// Delete a todo
if (isset($_GET['action'])) {
	if ($_GET['action'] == 'delete_todo') {
		// $todos = json_decode(file_get_contents($todo_file), true);
		foreach ($todos as $key=>$todo) {
			if ($todo['id']==$_GET['id']) {
				$todos[$key]['done'] = true;
			}
		}
		file_put_contents($todo_file, json_encode($todos, true));
		header('Location: index.php');
	}
}


// Generate a random color
function rand_color() {
	return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
	// return '#FFFFFF';
}
function randomDarkColor() {
	// Define the maximum brightness for a dark color (adjustable)
	$maxBrightness = 0;
	
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
/**
 * Renders a list of links in a card format.
 *
 * @param array $links An array of links to be displayed.
 * @param string $title The title of the card.
 * @throws None
 * @return void
 */
function show_links($links, $title) {
	// global $logs;
	// global $settings;

	// die(print_r($logs));

	global $list_cards;
	$link_card_name = "card".uniqid();
	$link_card_background_color = randomLightColor();

	$list_cards[$link_card_name] = $link_card_background_color;

	$border_color = $link_card_background_color;

	natcasesort($links);
	echo '
	<div class="card list-card" style="border-color:'.$border_color.'" >
		<div class="card-body p-0 m-0 bg-transparent border-0">
			<div class="card-header bg-transparent p-0"><span class="title">'.$title.'</span></div>
			<ul class="list-group list-group-flush border-0 pb-1">';
	foreach ($links as $key=>$value) {
		
		
		$short_url = parse_url($key);
		$name_display = $value; 
		// $name_display = $short_url['host'];
		// if (isset($short_url['port'])) { $name_display .= ':' . $short_url['port']; }
		// $name_display = str_replace('www.','', $name_display);
		
		$value = strtolower(str_replace(' ','',$value));
		$local_name_offline = 'img/icon-local.png';
		
		echo '<li class="list-group-item list-group-item-action bg-transparent border-0 p-1">';
		echo '<a href="' . $key . '" style="display:block">';
		$local_name = 'img/icons/'.$value.'.png';

		if (!file_exists($local_name)) {
			file_put_contents($local_name, file_get_contents('https://www.google.com/s2/favicons?domain='.$key.'&sz=256'));
		}

		if (filesize($local_name) == 0) {
			copy($local_name_offline, $local_name);
		}
		echo '<img src="'.$local_name.'" class="icon" style="clear:both" /> ';
		
		echo '<span class="icon-name">' . $name_display. '</span>';
		echo '</a>';
		echo '</li>';
	}
	echo '
			</ul>
		</div>
	</div>';
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

			$result .= '<a href="index.php?action=delete_todo&id=' . $todo['id'] . '" class="list-group-item list-group-item-action bg-transparent">' . $todo['text'] . '</a>';
		}
	}
	$result .= '</ul>';
	return $result;
}


?>

<!DOCTYPE html>
<html>
	<head>
		<title> ~ esquire </title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
		<link rel="stylesheet" type="text/css" href="<?php echo $selected_style; ?>" >
		<link rel="stylesheet" type="text/css" href="css/style.min.css">
		<meta http-equiv="refresh" content="<?php echo $settings['refresh_rate']; ?>" />
	</head>
	<body>
	
		<div class="container-fluid">
			<div class="row">
				<div class="col-lg-10 col-md-9 col-sm-6 p-0">					
					<div class="row row-no-gutters">
						<div class="<?php echo $column_css; ?>">
							<?php 
								show_links($project_links, 'projects'); 
								show_links($links['system'], 'system'); 
								show_links($links['softalliance'], 'soft alliance');
							?>
						</div>
						<div class="<?php echo $column_css; ?>">
							<?php 
								show_links($links['work'], 'work'); 
								show_links($links['hosting'], 'hosting');
								show_links($links['utilities'], 'utilities'); 
							?>	
						</div>
						<div class="<?php echo $column_css; ?>">
							<?php  
								show_links($links['reading'], 'reading');
								show_links($links['media'], 'media');
								show_links($links['learning'], 'learning');
							?>
						</div>
						<div class="<?php echo $column_css; ?>">
							<?php 
								show_links($links['projectmgt'], 'project mgt');
								show_links($links['games'], 'games');
								show_links($links['sports'], 'sports');
							?>
						</div>
						<div class="<?php echo $column_css; ?>">
							<?php 
							show_links($links['graphics'], 'graphics');
							show_links($links['coding'], 'coding');
							?>
						</div>
						<div class="<?php echo $column_css; ?>">
							<?php 
								show_links($links['ai'], 'ai');
								show_links($links['warez'], 'warez'); 
							?>
						</div>
					</div>
				</div>
				<div class="col-lg-2 col-md-3 col-sm-6 p-0"> 
					<div class="card bg-transparent">
						<?php echo $css_form; ?>
					</div>
					<!-- <div class="card bg-transparent">
						<div class="card-body">
						<img src="<?php echo display_random_wallpaper(); ?>" class="img-fluid" /></div>
					</div> -->
					<div class="card bg-transparent">
						<div class="card-body">
							<form id="search-form" method="get" onsubmit="return handleSearch();">
								<label for="search-box">Search Google or Enter URL:</label>
								<input type="text" id="search-box" name="q" placeholder="" class="form-control form-control-sm m-0" required>
							</form>
						</div>
					</div>
					<div class="card bg-transparent">
						<div class="card-body">
							<?php echo load_todo(); ?>
							<form action="index.php" method="post" class="form">
								<div class="form row align-items-center p-1">
								<input type="text" name="todo" class="form-control form-control-sm m-0">
									<input type="hidden" name="action" value="add_todo">
								</div>
															
							</form>
						</div>
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
		</script>
		<style>
			<?php 
			foreach ($list_cards as $key => $value) {
				echo '#'.$key.':hover { background-color:'.$value.'; }';
			}
			?>
		</style>
	</body>
</html>
