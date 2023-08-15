<?php 
ini_set('display_errors', 0);
$projects_folder = '..';
$dirs = array_filter(glob($projects_folder . '/*'), 'is_dir');
foreach ($dirs as $value) {
	$value = strtolower(str_replace('../','',$value));
	$project_links['https://'.$value.'.test'] = $value;
}

$log_file = 'hits.log';
$logs = json_decode(file_get_contents($log_file), true);

$settings = [
	'show_icon'=>true,
	'log_clicks'=>false,
];

$project1_links = [
	'https://webz.com.ng/awd/' => 'awd',
	'https://webz.com.ng/twaccg/' => 'twaccg',
];

$system_links = [
	'http://localhost/phpmyadmin'=>'phpmyadmin',
	'http://esquire-xps15:8989'=>'sonarr',
	'http://esquire-xps15:8097'=>'jellyfin',
	'https://web.whatsapp.com'=>'whatsapp',
	'https://web.telegram.org'=>'telegram',
	// 'http://esquire-xps15:9117'=>'jackett',
	// 'http://esquire-xps15:7878'=>'radarr',
];

$reading_links = [
	'https://feedly.com'=>'feedly',
	'https://reddit.com'=>'reddit',
	'https://twitter.com'=>'twitter',
	'https://nairaland.com'=>'nairaland',
];

$work_links = [
	'https://smartwork.ng'=>'smartwork',
	'https://mail.google.com'=>'gmail',
	// 'https://goodday.work'=>'goodday',
	// 'https://github.com'=>'github',
	// 'https://keep.google.com'=>'keep',
	// 'https://drive.google.com'=>'drive',
	'https://pmi.org'=>'pmi',
	'https://projectmanagementdocs.com'=>'project mgt docs',
	'https://www.projectmanagement.com'=>'project mgt',
	'https://www.stakeholdermap.com/project-templates/project-management-templates.html'=>'project mgt templates',
	'https://outlook.live.com'=>'outlook',
];

$media_links = [
	'https://youtube.com'=>'youtube',
	// 'https://tiktok.com'=>'tiktok',
	// 'https://photos.google.com'=>'photos',
	// 'https://instagram.com'=>'instagram',
	'https://www.wcofun.net'=>'wcofun',
	'https://trakt.tv'=>'trakt',
	'https://music.youtube.com'=>'youtube music',
];

$games_links = [
	'https://crazygames.com'=>'crazy games',
	'https://epicgames.com'=>'epic games',
	'https://gog.com'=>'gog',
	'https://playclassic.games'=>'play classic games',
];

$sports_links = [
	'https://livescore.com'=>'livescores',
	'https://www.arsenal.com'=>'arsenal',
	'https://premierleague.com'=>'premier league',
	'https://kikgoal.com'=>'kik goal',
];

$learning_links = [
	'https://exercism.org/dashboard'=>'exercism',
	'https://campus.college.ch'=>'robert kennedy',
	'https://coursera.org'=>'coursera',
	'https://udemy.com'=>'udemy',
	'https://freecodecamp.org'=>'freecodecamp',
	'https://www.mygreatlearning.com'=>'great learning'
];

$utilities_links = [
	'https://123apps.com'=>'123apps',
	'https://sejda.com/pdf-editor'=>'sejda-pdf',
	'https://alternativeto.net'=>'alternativeto',
	'https://convertio.co'=>'convertio',
];

$warez_links = [
	'https://wplocker.com'=>'wplocker',
	'https://torrentgalaxy.to'=>'torrent galaxy',
	'https://codelist.cc'=>'codelist',
	'https://themelock.com'=>'themelock',
    'https://steamrip.com'=>'steamrip',
	'https://downloadly.ir'=>'downloadly',
    'https://bitdownload.ir'=>'bitdownload',
	'https://www.seriezloaded.com.ng/movies/nollywood-movies/'=>'nollywood movies',
];

$ai_links = [
    'https://chat.openai.com'=>'chatGPT',
    'https://lexica.art/aperture'=>'lexica',
    'https://labs.openai.com'=>'dall-e',
	'https://bing.com/new'=>'bing ai',
	'https://bard.google.com'=>'bard',
	'https://www.futurepedia.io/'=>'futurepedia',
	'https://agentgpt.reworkd.ai/'=>'agent gpt',
];

$graphics_links = [
	'https://themeforest.net'=>'themeforest',
	'https://codecanyon.net'=>'codecanyon',
	'https://canva.com'=>'canva',
    'https://storyset.com'=>'story set',
	'https://slidesgo.com'=>'slides go',
	'https://slidescarnival.com'=>'slides carnival',
	'https://wepik.com'=>'wepik',
	'https://www.videvo.net/'=>'videvo',
	'https://mixkit.co/'=>'mixkit',
];

$hosting_links = [
	'https://whogohost.com'=>'whogohost',
	'https://hostforweb.com'=>'hostforweb',
	'https://websguy.com:2083'=>'websguy',
	'https://ionos.com'=>'ionos',
    'https://iwebfusion.net'=>'iwebfusion',
];

$clients_links = [
	'https://portal.inceltourism.com'=>'incel portal',
	'https://zerostore.com.ng'=>'zero store',
	'https://lagosstate.gov.ng'=>'lagos state',
];

// $log_file = 'hits.txt';
// $logs = json_decode(file_get_contents($log_file), true);

function show_links($links, $title) {
	global $logs;
	global $settings;

	natcasesort($links);
	echo '
	<div class="card mb-2 bg-transparent border-0 border-left-2">
		<div class="card-body p-0 bg-transparent border-0">
			<div class="card-header bg-transparent"><span class="title">'.$title.'</span></div>
			<ul class="list-group list-group-flush border-0">';
	foreach ($links as $key=>$value) {
		
		// $hit_count = intval($logs[$key]);

		// $name_display = $value;
		// if ($hit_count > 0) {
		// 	$name_display .= ' - '.$hit_count;
		// }
		
		$name_display = $value; // . ' - ' . $hit_count;
		$value = strtolower(str_replace(' ','',$value));
		$local_name_offline = 'img/icon-local.png';
		
		echo '<li class="list-group-item list-group-item-action bg-transparent  border-0">';
		if ($settings['log_clicks']) {
			echo '<a href="refer.php?link=' . $key . '" rel="noopener noreferrer" style="display:block">';
		} else {
			echo '<a href="' . $key . '" rel="noopener noreferrer" style="display:block">';
		}
		if ($settings['show_icon']) {
			$local_name = $local_name_offline;
			if (strpos($key,'test')==0) {
				$local_name = 'img/icons/'.$value.'.png';
				if (filesize($local_name) == 0) {
					$local_name = $local_name_offline;
				}	
			}
			if (isset($_GET['refresh_icons'])) {
				get_site_icon($local_name, $key);
			}
			if (!file_exists($local_name)) {
				get_site_icon($local_name, $key);
			}
			echo '<img src="'.$local_name.'" class="icon" /> ';
		}

		echo '<span>' . $name_display. '</span></a>';
		echo '</li>';
	}
	echo '
			</ul>
		</div>
	</div>';
}

function get_site_icon($local_name, $key) {
	file_put_contents($local_name, file_get_contents('https://www.google.com/s2/favicons?domain='.$key.'&sz=256'));

}

// function to randomly select a picture from a folder
function display_random_wallpaper() {
	// Path to the folder containing images
	$imageFolder = 'img/wallpapers/';

	// Get all image files from the folder
	$imageFiles = glob($imageFolder . '*.{jpg,jpeg,png}', GLOB_BRACE);
	// die(print_r($imageFiles));

	// Select a random image from the array
	$randomImage = $imageFiles[array_rand($imageFiles)];

	// Display the random image on the webpage
	return '<img src="' . $randomImage . '"  width="100%" class="img">';
}
?>

<!DOCTYPE html>
<html>
	<head>
		<title> ~ esquire </title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
		<link rel="stylesheet" type="text/css" href="css/bootstrap-slate.min.css" >
		<link rel="stylesheet" type="text/css" href="css/style.css">
	</head>
	<body class="bg-black bg-image">
	
		<div class="container-fluid">
			<div class="row">
				<div class="col-lg-4">
					<div class="card p-0 m-0">
						<div class="card-body p-0 m-0">
							<?php echo display_random_wallpaper(); ?>
						</div>
					</div>
					<div class="card">
						<div class="card-body p-0 m-0">
							<?php echo display_random_wallpaper(); ?>
						</div>
					</div>
				</div>
				<div class="col-lg-8">
					
					<div class="row">
						<div class="col-lg-2 col-md-3 col-sm-4 p-0">
							<?php 
								show_links($project_links, 'projects local'); 
								show_links($project1_links, 'projects online'); 
								show_links($system_links, 'system'); 
							?>
						</div>
						<div class="col-lg-2 col-md-3 col-sm-4 p-0 border-left">
							<?php 
								show_links($work_links, 'work'); 
								show_links($hosting_links, 'hosting');
							?>
						</div>
						<div class="col-lg-2 col-md-3 col-sm-4 p-0">
							<?php 
								show_links($clients_links, 'clients');
								show_links($reading_links, 'reading');
								show_links($ai_links, 'ai');
							?>	
						</div>
						<div class="col-lg-2 col-md-3 col-sm-4 p-0">
							<?php  
								show_links($media_links, 'media'); 
								show_links($graphics_links, 'graphics');
							?>
						</div>
						<div class="col-lg-2 col-md-3 col-sm-4 p-0">
							<?php 
								show_links($learning_links, 'learning');
								show_links($games_links, 'games');
								show_links($sports_links, 'sports');
							?>
						</div>
						<div class="col-lg-2 col-md-3 col-sm-4 p-0">
							<?php 
								show_links($utilities_links, 'utilities'); 
								show_links($warez_links, 'warez'); 
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<script src="js/bootstrap.bundle.min.js"></script>
	</body>
</html>
