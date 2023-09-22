<?php 
ini_set('display_errors', 0);
$settings = [
	'show_icon'=>true,
	'log_clicks'=>false,
	'refresh_rate'=>600,
	'show_count'=>false,
];


$projects_folder = '..';
$dirs = array_filter(glob($projects_folder . '/*'), 'is_dir');
foreach ($dirs as $value) {
	$value = strtolower(str_replace('../','',$value));
	$project_links['https://'.$value.'.test'] = $value;
}

$log_file = 'data/hits.json';
$todo_file = 'data/todo.json';
$links_file = 'data/links.json';

$links = json_decode(file_get_contents($links_file), true);
$logs = json_decode(file_get_contents($log_file), true);
$todos = json_decode(file_get_contents($todo_file), true);

// Add a new todo
if (isset($_POST['action']) && $_POST['action'] == 'add_todo') {
	$todos = json_decode(file_get_contents($todo_file), true);
	
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
		$todos = json_decode(file_get_contents($todo_file), true);
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
	global $logs;
	global $settings;

	$border_color = rand_color();

	natcasesort($links);
	echo '
	<div class="card mb-2 bg-transparent border-top-2" style="border-top-color: '.$border_color.'">
		<div class="card-body p-0 bg-transparent border-0">
			<div class="card-header bg-transparent"><span class="title">'.$title.'</span></div>
			<ul class="list-group list-group-flush border-0">';
	foreach ($links as $key=>$value) {
		
		$hit_count = intval($logs[$key]);

		$name_display = $value; // . ' - ' . $hit_count;
		if ($settings['show_count']) {
			$name_display .= ' <span style="color:gray">:'.$hit_count . '</span>';
		}
		$value = strtolower(str_replace(' ','',$value));
		$local_name_offline = 'img/icon-local.png';
		
		echo '<li class="list-group-item list-group-item-action bg-transparent  border-0">';
		if ($settings['log_clicks']) {
			echo '<a href="refer.php?link=' . $key . '" rel="noopener noreferrer" style="display:block">';
		} else {
			echo '<a href="' . $key . '" rel="noopener noreferrer" style="display:block">';
		}
		if ($settings['show_icon']) {
			$local_name = 'img/icons/'.$value.'.png';

			if (!file_exists($local_name)) {
				file_put_contents($local_name, file_get_contents('https://www.google.com/s2/favicons?domain='.$key.'&sz=256'));
				// sleep(1);
			}

			if (filesize($local_name) == 0) {
				copy($local_name_offline, $local_name);
			}
			echo '<img src="'.$local_name.'" class="icon" style="clear:both" /> ';
		}
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
	return '<img src="' . $randomImage . '"  width="100%" class="img">';
}

// function to load a list of todos from a json file
function load_todo() {
	global $todos;

	// Render the list
	$result = '<ul class="list-group list-group-flush border-0 mb-2 bg-black">';
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
		<link rel="stylesheet" type="text/css" href="css/bootstrap-slate.min.css" >
		<link rel="stylesheet" type="text/css" href="css/style.css">
		<meta http-equiv="refresh" content="<?php echo $settings['refresh_rate']; ?>" />
	</head>
	<body class="bg-black">
	
		<div class="container-fluid">
			<div class="row">
				<div class="col-lg-2">
					<div class="card p-0 m-0">
						<div class="card-body p-0 m-0">
							<?php echo display_random_wallpaper(); ?>
						</div>
					</div>
				</div>
				<div class="col-lg-8">					
					<div class="row">
						<div class="col-lg-2 col-md-3 col-sm-4 p-0">
							<?php 
								show_links($project_links, 'projects'); 
								show_links($links['system'], 'system'); 
								show_links($links['clients'], 'clients');
							?>
						</div>
						<div class="col-lg-2 col-md-3 col-sm-4 p-0 border-left">
							<?php 
								show_links($links['work'], 'work'); 
								show_links($links['projectmgt'], 'project mgt');
								show_links($links['hosting'], 'hosting');
							?>
						</div>
						<div class="col-lg-2 col-md-3 col-sm-4 p-0">
							<?php 
								show_links($links['reading'], 'reading');
								show_links($links['ai'], 'ai');
							?>	
						</div>
						<div class="col-lg-2 col-md-3 col-sm-4 p-0">
							<?php  
								show_links($links['media'], 'media'); 
								show_links($links['graphics'], 'graphics');
							?>
						</div>
						<div class="col-lg-2 col-md-3 col-sm-4 p-0">
							<?php 
								show_links($links['learning'], 'learning');
								show_links($links['games'], 'games');
								show_links($links['sports'], 'sports');
							?>
						</div>
						<div class="col-lg-2 col-md-3 col-sm-4 p-0">
							<?php 
								show_links($links['utilities'], 'utilities'); 
								show_links($links['warez'], 'warez'); 
							?>
						</div>
					</div>
				</div>
				<div class="col-lg-2">
					<div class="card bg-black">
						<div class="card-body">
							<?php echo load_todo(); ?>
						</div>
					</div>
					<div class="card bg-black">
						<div class="card-body">
							<form action="index.php" method="post" class="form">
								<div class="form row align-items-center p-1">
									<div class="col-8"><input type="text" name="todo" class="form-control form-control-sm m-0"></div>
									<div class="col-2"><button type="submit" value="Add" class="btn btn-primary m-0">Add</button></div>
									<input type="hidden" name="action" value="add_todo">
								</div>
															
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		<script src="js/bootstrap.bundle.min.js"></script>
	</body>
</html>
