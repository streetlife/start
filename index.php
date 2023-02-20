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
	'https://reddit.com/r/startpages'=>'startpages',
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
	'https://keep.google.com'=>'keep',
	'https://drive.google.com'=>'drive',
	'https://untools.co/'=>'untools',
	'https://calendar.google.com/calendar/u/0/r/week'=>'calendar',
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

$graphics_links = [
	'https://unsplash.com/'=>'unsplash',
	'https://www.pexels.com/'=>'pexels',
    'https://storyset.com/'=>'story set',
	'https://slidesgo.com/'=>'slides go',
	'https://slidescarnival.com'=>'slides carnival',
	'https://wepik.com/'=>'wepik',
	'https://www.pixiv.net/'=>'pixiv',
	'https://canva.com'=>'canva',
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
		'<li class="list-group-item list-group-item-action bg-transparent  border-0">
			<a href="' . $key . '" rel="noopener noreferrer" '.$str_target.' style="display:block">
                <img src="https://www.google.com/s2/favicons?domain='.$key.'&sz=128" class="icon" />
				<span>' . strtolower($value). '</span>
			</a>
		</li>';
	}
	echo 
	'<div class="col-lg-2 col-md-3 col-sm-4 p-1">
		<div class="card m-1 bg-transparent border-0">
			<div class="card-body p-0 bg-transparent border-0">
				<div class="card-header bg-transparent">'.$title.'</div>
				<ul class="list-group list-group-flush border-0">
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
		<link rel="stylesheet" type="text/css" href="css/bootstrap-slate.min.css" >
		<link rel="stylesheet" type="text/css" href="css/style.min.css">
	</head>
	<body class="bg-gray">
		<div class="container">
			<div class="row" data-masonry='{"percentPosition": true }'>
			<?php 
				show_links($project_links, 'projects');
				show_links($system_links, 'system');
				show_links($work_links, 'work'); 
				show_links($reading_links, 'reading');
				show_links($ai_links, 'ai');
				show_links($learning_links, 'learning');
				show_links($media_links, 'media'); 
				show_links($games_links, 'games');
				show_links($graphics_links, 'graphics');
				show_links($utilities_links, 'utilities', true); 
				show_links($warez_links, 'warez', true); 
			?>
			</div>
		</div>
		<script src="js/bootstrap.bundle.min.js"></script>
		<script src="js/jquery-3.6.3.min.js"></script>
		<script src="js/masonry.pkgd.min.js" async></script>
	</body>
</html>
