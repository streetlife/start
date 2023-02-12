<?php 
$exclude_folders = ['icons','assets','css'];
$dirs = array_filter(glob('../*'), 'is_dir');
foreach ($dirs as $value) {
	$value = str_replace('../','',$value);
	if (!in_array($value, $exclude_folders)) {
		$project_links['https://'.$value.'.test'] = $value;
	}
}

$system_links = [
	'http://localhost/phpmyadmin'=>'phpmyadmin',
	'http://localhost:8989'=>'sonarr',
	'http://localhost:8096'=>'jellyfin',
	'https://web.whatsapp.com'=>'whatsapp',
	'https://web.telegram.org'=>'telegram',
    'http://localhost/?q=info'=>'php info'
];

$reading_links = [
	'https://feedly.com'=>'feedly',
	'https://reddit.com'=>'reddit',
	'https://news.ycombinator.com'=>'hacker news',
	'https://twitter.com'=>'twitter',
	'https://nairaland.com'=>'nairaland',
    'https://www.reddit.com/r/Showerthoughts/'=>'shower thoughts'
];

$work_links = [
	'https://smartwork.ng'=>'smartwork',
	'https://mail.google.com'=>'gmail',
	'https://whogohost.com'=>'whogohost',
	'https://hostforweb.com'=>'hostforweb',
	'https://websguy.com/cpanel'=>'websguy',
	'https://goodday.work'=>'goodday',
	'https://github.com'=>'github',
    'https://www.iwebfusion.net/'=>'iwebfusion',
    'https://storyset.com/'=>'story set',
];

$games_links = [
	'https://discord.com'=>'discord',
	'https://steamcommunity.com'=>'steam',
	'https://crazygames.com'=>'crazy games',
	'https://epicgames.com'=>'epic games',
	'https://wordfinderx.com'=>'wordfinderx',
	'https://livescore.com'=>'livescores',
    'https://www.premierleague.com/'=>'premier league',
    'https://www.arsenal.com/'=>'arsenal',
    'https://reddit.com/r/footballhighlights'=>'football highlights',
	'https://redi1.soccerstreams.net'=>'soccer streams',
];

$media_links = [
	'https://youtube.com'=>'youtube',
	'https://tiktok.com'=>'tiktok',
    'https://music.youtube.com'=>'Youtube music',
];

$ai_links = [
	'https://ai.google.com'=>'google ai',
    'https://chat.openai.com'=>'chatGPT',
    'https://lexica.art/aperture'=>'lexica',
    'https://labs.openai.com/'=>'dall-e'
];

$warez_links = [
	'https://wplocker.com'=>'wplocker',
	'https://torrentgalaxy.to'=>'torrent galaxy',
	'https://codelist.cc'=>'codelist',
	'https://themelock.com'=>'themelock',
    'https://steamrip.com'=>'steam rip',
	'https://downloadly.ir/'=>'downloadly',
    'https://bitdownload.ir/'=>'bitdownload'
];
$learning_links = [
	'https://exercism.org/dashboard'=>'exercism',
	'https://campus.college.ch'=>'robert kennedy',
];
$utilities_links = [
	'https://123apps.com/'=>'123apps',
    'https://mymtn.com.ng/dashboard'=>'myMTN',
];

function show_links($links, $title) {
	$str_links = '';    
	natcasesort($links);
	foreach ($links as $key=>$value) {
		$str_links .= '<li class="list-group-item list-group-item-action">
			<a class="" href="' . $key . '" rel="noopener noreferrer" style="display:block">
                <img src="https://www.google.com/s2/favicons?domain='.$key.'&sz=128" class="icon" />
				<span>' . strtolower($value). '</span>
			</a>
		</li>';
	}
	echo '<div class="col-md-2 p-1">
			<div class="card m-2 bg-dark">
				<div class="card-body p-0">
					<div class="card-header bg-dark">'.$title.'</div>
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
		<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" >
		<link rel="stylesheet" type="text/css" href="style.css">
	</head>
	<body>
		<div class="container">
			<div class="row" data-masonry='{"percentPosition": true }'>
			<?php 
				show_links($project_links, 'projects');
				show_links($system_links, 'system');
				show_links($work_links, 'work'); 
				show_links($reading_links, 'reading');
				show_links($media_links, 'media'); 
				show_links($games_links, 'games'); 
				show_links($learning_links, 'learning'); 
				show_links($ai_links, 'ai tools'); 
				show_links($utilities_links, 'utilities'); 
				show_links($warez_links, 'warez'); 
			?>
			</div>
		</div>
		<script src="js/bootstrap.bundle.min.js"></script>
		<script src="js/jquery-3.6.3.min.js"></script>
		<script src="js/masonry.pkgd.min.js" async></script>
	</body>
</html>
