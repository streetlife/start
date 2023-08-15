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
	// 'https://web.whatsapp.com'=>'whatsapp',
	// 'https://web.telegram.org'=>'telegram',
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
		$hit_count = intval($logs[$key]);

		// $name_display = $value;
		// if ($hit_count > 0) {
		// 	$name_display .= ' - '.$hit_count;
		// }
		
		$name_display = $value; // . ' - ' . $hit_count;
		$value = strtolower(str_replace(' ','',$value));
		$local_name = 'img/icon-local.png';
		if (strpos($key,'test')==0) {
			$local_name = 'img/icons/'.$value.'.png';
		}
		if (isset($_GET['refresh_icons'])) {
			get_site_icon($local_name, $key);
		}
		if (!file_exists($local_name)) {
			get_site_icon($local_name, $key);
		}
		
		echo '<li class="list-group-item list-group-item-action bg-transparent  border-0">';
		if ($settings['log_clicks']) {
			echo '<a href="refer.php?link=' . $key . '" rel="noopener noreferrer" style="display:block">';
		} else {
			echo '<a href="' . $key . '" rel="noopener noreferrer" style="display:block">';
		}
		if ($settings['show_icon']) {
			echo '<img src="'.$local_name.'" class="icon" /> <span>' . $name_display. '</span></a>';
		} else {
			echo '<span>' . $name_display. '</span></a>';
		}
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
	<svg version="1.1" xmlns="http://www.w3.org/2000/svg"
		xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="100%" height="100%" viewBox="0 0 1600 900" preserveAspectRatio="xMidYMax slice">
		<defs>
			<linearGradient id="bg">
				<stop offset="0%" style="stop-color:rgba(130, 158, 249, 0.06)"></stop>
				<stop offset="50%" style="stop-color:rgba(76, 190, 255, 0.6)"></stop>
				<stop offset="100%" style="stop-color:rgba(115, 209, 72, 0.2)"></stop>
			</linearGradient>
			<path id="wave" fill="url(#bg)" d="M-363.852,502.589c0,0,236.988-41.997,505.475,0
	s371.981,38.998,575.971,0s293.985-39.278,505.474,5.859s493.475,48.368,716.963-4.995v560.106H-363.852V502.589z" />
		</defs>
		<g>
			<use xlink:href='#wave' opacity=".3">
				<animateTransform
          attributeName="transform"
          attributeType="XML"
          type="translate"
          dur="10s"
          calcMode="spline"
          values="270 230; -334 180; 270 230"
          keyTimes="0; .5; 1"
          keySplines="0.42, 0, 0.58, 1.0;0.42, 0, 0.58, 1.0"
          repeatCount="indefinite" />
			</use>
			<use xlink:href='#wave' opacity=".6">
				<animateTransform
          attributeName="transform"
          attributeType="XML"
          type="translate"
          dur="8s"
          calcMode="spline"
          values="-270 230;243 220;-270 230"
          keyTimes="0; .6; 1"
          keySplines="0.42, 0, 0.58, 1.0;0.42, 0, 0.58, 1.0"
          repeatCount="indefinite" />
			</use>
			<use xlink:href='#wave' opacty=".9">
				<animateTransform
          attributeName="transform"
          attributeType="XML"
          type="translate"
          dur="6s"
          calcMode="spline"
          values="0 230;-140 200;0 230"
          keyTimes="0; .4; 1"
          keySplines="0.42, 0, 0.58, 1.0;0.42, 0, 0.58, 1.0"
          repeatCount="indefinite" />
			</use>
		</g>
	</svg>
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<a href="index.php?refresh_icons">refresh icons</a></div>
			</div>
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
		<script src="js/bootstrap.bundle.min.js"></script>
		<script type="text/javascript"> 
			function display_c(){
				var refresh=1000; // Refresh rate in milli seconds
				mytime=setTimeout('display_ct()',refresh)
			}

			function display_ct() {
				var x = new Date();
				// var x1=x.toUTCString();// changing the display to UTC string

				document.getElementById('ct').innerHTML = x;
				display_c();
 			}
		</script>

	</body>
</html>
