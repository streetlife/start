<?php 
$dirs = array_filter(glob('../*'), 'is_dir');
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
];

$work_links = [
	'https://smartwork.ng'=>'smartwork',
	'https://mail.google.com'=>'gmail',
	'https://whogohost.com'=>'whogohost',
	'https://hostforweb.com'=>'hostforweb',
	'https://websguy.com/cpanel'=>'websguy',
	'https://goodday.work'=>'goodday',
	'https://github.com'=>'github',
    'https://iwebfusion.net/'=>'iwebfusion',
    'https://storyset.com/'=>'story set',
	'https://keep.google.com'=>'keep',
];

$media_links = [
	'https://youtube.com'=>'youtube',
	'https://tiktok.com'=>'tiktok',
    'https://music.youtube.com'=>'Youtube music',
];

$games_links = [
	'https://crazygames.com'=>'crazy games',
	'https://epicgames.com'=>'epic games',
	'https://wordfinderx.com'=>'wordfinderx',
	'https://livescore.com'=>'livescores',
    'https://reddit.com/r/footballhighlights'=>'football highlights',
];

$learning_links = [
	'https://exercism.org/dashboard'=>'exercism',
	'https://campus.college.ch'=>'robert kennedy',
	'https://coursera.org'=>'coursera',
	'https://udemy.com'=>'udemy',
	'https://freecodecamp.org'=>'freecodecamp',
];

$utilities_links = [
	'https://123apps.com/'=>'123apps',
    'https://mymtn.com.ng/dashboard'=>'myMTN',
    'https://canarytokens.org'=>'canary tokens',
];

$warez_links = [
	'https://wplocker.com'=>'wplocker',
	'https://torrentgalaxy.to'=>'torrentgalaxy',
	'https://codelist.cc'=>'codelist',
	'https://themelock.com'=>'themelock',
    'https://steamrip.com'=>'steamrip',
	'https://downloadly.ir/'=>'downloadly',
    'https://bitdownload.ir/'=>'bitdownload'
];

$ai_links = [
    'https://chat.openai.com'=>'chatGPT',
    'https://lexica.art/aperture'=>'lexica',
    'https://labs.openai.com/'=>'dall-e',

];

function show_links($links, $title, $newtab=false) {
	$str_links = '';    
	$str_target = '';
	if ($newtab) {
		$str_target = ' target="_blank"';
	}
	natcasesort($links);
	foreach ($links as $key=>$value) {
		$str_links .= 
		'<li class="list-group-item list-group-item-action">
			<a class="" href="' . $key . '" rel="noopener noreferrer" style="display:block" '.$str_target.'>
                <img src="https://www.google.com/s2/favicons?domain='.$key.'&sz=128" class="icon" />
				<span>' . strtolower($value). '</span>
			</a>
		</li>';
	}
	echo 
	'<div class="col-md-2 p-1">
		<div class="card m-1">
			<div class="card-body p-0">
				<div class="card-header">'.$title.'</div>
				<ul class="list-group list-group-flush ">
				'.$str_links.'
				</ul>
			</div>
		</div>
	</div>';

}

?>

<!DOCTYPE html>
<html>
	<head>
		<title> ~ esquire </title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
		<!-- <link rel="stylesheet" type="text/css" href="css/bootstrap-cyborg.min.css" > -->
		<!-- <link rel="stylesheet" type="text/css" href="css/bootstrap-darkly.min.css" > -->
		<!-- <link rel="stylesheet" type="text/css" href="css/bootstrap-flatly.min.css" > -->
		<!-- <link rel="stylesheet" type="text/css" href="css/bootstrap-lux.min.css" > -->
		<!-- <link rel="stylesheet" type="text/css" href="css/bootstrap-minty.min.css" > -->
		<link rel="stylesheet" type="text/css" href="css/bootstrap-slate.min.css" >
		<!-- <link rel="stylesheet" type="text/css" href="css/bootstrap-superhero.min.css" > -->
		<!-- <link rel="stylesheet" type="text/css" href="css/bootstrap-united.min.css" > -->
		<!-- <link rel="stylesheet" type="text/css" href="css/bootstrap-vapor.min.css" > -->
		<link rel="stylesheet" type="text/css" href="style.css">
	</head>
	<body>
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<h1> ~ esquire </h1>
				</div>
			</div>
			<div class="row" data-masonry='{"percentPosition": true }'>
			<?php 
				show_links($project_links, 'projects');
				show_links($system_links, 'system');
				show_links($work_links, 'work'); 
				show_links($reading_links, 'reading');
				show_links($learning_links, 'learning');
				show_links($media_links, 'media'); 
				show_links($utilities_links, 'utilities', true); 
				show_links($warez_links, 'warez', true); 
				show_links($ai_links, 'ai');
				show_links($games_links, 'games');
			?>
			</div>
		</div>
		<script src="js/bootstrap.bundle.min.js"></script>
		<script src="js/jquery-3.6.3.min.js"></script>
		<script src="js/masonry.pkgd.min.js" async></script>
	</body>
</html>
