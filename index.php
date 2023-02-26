<?php 
ini_set('display_errors', 0);
$projects_folder = 'c:/esquire/projecs/web';
$projects_folder = '..';
$dirs = array_filter(glob($projects_folder . '/*'), 'is_dir');
foreach ($dirs as $value) {
	$value = str_replace('../','',$value);
	$project_links['https://'.$value.'.test'] = $value;
}

$system_links = [
	'http://localhost/phpmyadmin'=>'phpmyadmin',
	'http://localhost:8989'=>'sonarr',
	'http://localhost:8096'=>'jellyfin',
	'https://web.whatsapp.com'=>'whatsapp',
	'https://web.telegram.org'=>'telegram',
];

$reading_links = [
	'https://feedly.com'=>'feedly',
	'https://reddit.com'=>'reddit',
	'https://twitter.com'=>'twitter',
	'https://nairaland.com'=>'nairaland',
	'https://reddit.com/r/blackpeopletwitter'=>'blackpeopletwitter',
	'https://reddit.com/r/selfhosted'=>'selfhosted',
];

$work_links = [
	'https://smartwork.ng'=>'smartwork',
	'https://mail.google.com'=>'gmail',
	'https://goodday.work'=>'goodday',
	'https://github.com'=>'github',
	'https://keep.google.com'=>'keep',
	'https://drive.google.com'=>'drive',
	'https://untools.co/'=>'untools',
	'https://calendar.google.com/calendar/u/0/r/week'=>'calendar',
	'https://projectmanagementdocs.com'=>'project mgt docs',
	'https://pmi.org'=>'pmi',
	'https://www.projectmanagement.com/'=>'project mgt',
	'https://www.stakeholdermap.com/project-templates/project-management-templates.html'=>'project mgt templates',

];

$media_links = [
	'https://youtube.com'=>'youtube',
	'https://tiktok.com'=>'tiktok',
    'https://music.youtube.com'=>'youtube music',
	'https://photos.google.com'=>'google photos',
];

$games_links = [
	'https://crazygames.com'=>'crazy games',
	'https://epicgames.com'=>'epic games',
	'https://wordfinderx.com'=>'wordfinderx',
	'https://livescore.com'=>'livescores',
    'https://reddit.com/r/footballhighlights'=>'football highlights',
	'https://www.arsenal.com'=>'arsenal',
	'https://gog.com/'=>'gog',
	'https://premierleague.com'=>'premier league',
	'https://playclassic.games/'=>'play classic games',
];

$learning_links = [
	'https://exercism.org/dashboard'=>'exercism',
	'https://campus.college.ch'=>'robert kennedy',
	'https://coursera.org'=>'coursera',
	'https://udemy.com'=>'udemy',
	'https://freecodecamp.org'=>'freecodecamp',
	'https://laracasts.com/'=>'laracasts',
	'https://phptherightway.com/'=>'phptherightway',
];

$utilities_links = [
	'https://123apps.com/'=>'123apps',
    'https://mymtn.com.ng/dashboard'=>'myMTN',
    'https://canarytokens.org'=>'canary tokens',
	'https://sejda.com/pdf-editor'=>'sejda-pdf',
];

$warez_links = [
	'https://wplocker.com'=>'wplocker',
	'https://torrentgalaxy.to'=>'torrentgalaxy',
	'https://codelist.cc'=>'codelist',
	'https://themelock.com'=>'themelock',
    'https://steamrip.com'=>'steamrip',
	'https://downloadly.ir/'=>'downloadly',
    'https://bitdownload.ir/'=>'bitdownload',
];

$ai_links = [
    'https://chat.openai.com'=>'chatGPT',
    'https://lexica.art/aperture'=>'lexica',
    'https://labs.openai.com/'=>'dall-e',
];

$graphics_links = [
	'https://unsplash.com/'=>'unsplash',
	'https://www.pexels.com/'=>'pexels',
    'https://storyset.com/'=>'story set',
	'https://slidesgo.com/'=>'slides go',
	'https://slidescarnival.com'=>'slides carnival',
	'https://wepik.com/'=>'wepik',
	'https://canva.com'=>'canva',
];

$hosting_links = [
	'https://whogohost.com'=>'whogohost',
	'https://hostforweb.com'=>'hostforweb',
	'https://websguy.com:2083'=>'websguy',
	'https://ionos.com'=>'ionos',
    'https://iwebfusion.net/'=>'iwebfusion',
];

$fun_links = [
	'https://9gag.com/'=>'9gag',
	'https://boredpanda.com/'=>'boredpanda',
];

function show_links($links, $title) {
	$str_links = '';
	natcasesort($links);
	foreach ($links as $key=>$value) {
		$value = strtolower(str_replace(' ','',$value));
		$local_name = 'img/icons/'.$value.'.png';
		if (!file_exists($local_name)) {
			if (strpos($key,'test')>0) {
				$local_name = 'img/icon-local.png';
			} else {
				file_put_contents($local_name, file_get_contents('https://www.google.com/s2/favicons?domain='.$key.'&sz=128'));
			}
		}
		$str_links .= 
		'<li class="list-group-item list-group-item-action bg-transparent  border-0">
			<a href="refer.php?link=' . $key . '" rel="noopener noreferrer" style="display:block">
			<img src="'.$local_name.'" class="icon" />
				<span>' . $value. '</span>
			</a>
		</li>';
	}
	echo 
	'<div class="card mb-2 bg-transparent border-0">
		<div class="card-body p-0 bg-transparent border-0">
			<div class="card-header bg-transparent"><span class="title">'.$title.'</span></div>
			<ul class="list-group list-group-flush border-0">
			'.$str_links.'
			</ul>
		</div>
	</div>';
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
	<body class="bg-gray" onload=display_ct();>
		<div class="container">
			<div class="row">
				<div class="col-lg-2 col-md-3 col-sm-4 p-0">
					<?php 
						show_links($project_links, 'projects'); 
						show_links($system_links, 'system'); 
					?>
				</div>
				<div class="col-lg-2 col-md-3 col-sm-4 p-0">
					<?php 
						show_links($work_links, 'work'); 
					?>
				</div>
				<div class="col-lg-2 col-md-3 col-sm-4 p-0">
					<?php 
						show_links($reading_links, 'reading');
						show_links($hosting_links, 'hosting');
					?>	
				</div>
				<div class="col-lg-2 col-md-3 col-sm-4 p-0">
					<?php  
						show_links($ai_links, 'ai');
						show_links($media_links, 'media'); 
						show_links($graphics_links, 'graphics');
					?>
				</div>
				<div class="col-lg-2 col-md-3 col-sm-4 p-0">
					<?php 
						show_links($learning_links, 'learning');
						show_links($games_links, 'games');
					?>
				</div>
				<div class="col-lg-2 col-md-3 col-sm-4 p-0">
					<?php 
						show_links($fun_links, 'fun');
						show_links($utilities_links, 'utilities'); 
						show_links($warez_links, 'warez'); 
					?>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
				<span id='ct'></span>
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
