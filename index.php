<?php 
$exclude_folders = ['icons','assets','css'];
$dirs = array_filter(glob('../*'), 'is_dir');
foreach ($dirs as $value) {
	$value = str_replace('../','',$value);
	if (!in_array($value, $exclude_folders)) {
		$project_links['https://'.$value.'.test'] = $value;
	}
}

$sections = [
    'system'=>'System',
    'readling'=>'reading',
    'work'=>'work',
    'games'=>'games',
    'media'=>'media',
    'warez'=>'warez'
];

$system_links = [
	'http://localhost/phpmyadmin'=>'phpmyadmin',
	'http://localhost:8989'=>'sonarr',
	'http://localhost:8096'=>'emby/jellyfin',
	'https://web.whatsapp.com'=>'whatsapp',
    'http://localhost/?q=info'=>'php info'
];

$reading_links = [
	'https://feedly.com'=>'feedly',
	'https://reddit.com'=>'reddit',
	'https://news.ycombinator.com'=>'hacker news',
	'https://twitter.com'=>'twitter',
	'https://nairaland.com'=>'nairaland',
    'https://chat.openai.com'=>'chatGPT',
    'https://lexica.art/aperture'=>'lexica',
    'https://www.reddit.com/r/Showerthoughts/'=>'shower thoughts',
    'https://labs.openai.com/'=>'dall-e'
];

$work_links = [
	'https://smartwork.ng'=>'smartwork',
	'https://mail.google.com'=>'gmail',
	'https://whogohost.com'=>'whogohost',
	'https://hostforweb.com'=>'hostforweb',
	'https://websguy.com/cpanel'=>'websguy',
	'https://goodday.work'=>'goodday',
	'https://github.com'=>'github',
	'https://softalliance.com'=>'soft alliance',
	'https://campus.college.ch'=>'robert kennedy',
    'https://www.iwebfusion.net/'=>'iwebfusion',
    'https://mymtn.com.ng/dashboard'=>'myMTN',
    'https://storyset.com/'=>'story set'
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
    'https://reddit.com/r/footballhighlights'=>'football highlights'
];

$media_links = [
	'https://youtube.com'=>'youtube',
	'https://tiktok.com'=>'tiktok',
	'https://torrentgalaxy.to'=>'torrent galaxy',
	'https://codelist.cc'=>'codelist',
	'https://wplocker.com'=>'wplocker',
	'https://themelock.com'=>'themelock',
	'https://redi1.soccerstreams.net'=>'soccer streams',
    'https://music.youtube.com'=>'Youtube music',
    'https://steamrip.com'=>'steam rip',
	'https://downloadly.ir/'=>'downloadly',
    'https://bitdownload.ir/'=>'bitdownload'
];

function show_links($links) {
	// asort($links);
    natcasesort($links);
	foreach ($links as $key=>$value) {
		echo '<li class="cell small-6 medium-4 large-12 container__list--item">
			<a class="container__list" href="' . $key . '" rel="noopener noreferrer">
                <img src="https://www.google.com/s2/favicons?domain='.$key.'&sz=128" />
				<span>' . strtolower($value). '</span>
			</a>
		</li>';
	}
}
?>

<!DOCTYPE html>
<html>
	<head>
		<title> ~ esquire </title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/foundation/6.6.3/css/foundation-prototype.min.css" integrity="sha512-rTvrQPQ4IQdQ2Ofv0DXNFCf2O+M9DkfozuYMHOpCJLwmwj+6boSqWRno9j94fp+ZyHAdIDTehr0KlQ0XXK5J4g==" crossorigin="anonymous" />
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;900&display=swap" rel="stylesheet">
		<link rel="stylesheet" type="text/css" href="style.css">
	</head>
	<body>
		<div class="grid-container container">
			<div class="grid-x align-middle container__inner">
				<div class="cell">
					<div class="grid-x grid-padding-x">
						<div class="cell small-12 large-auto">
							<ul class="grid-x">
								<li class="cell"><span class="container__list container__list--title">Projects</span></li>
								<?php show_links($project_links); ?>
							</ul>
						</div>
						<div class="cell small-12 large-auto">
							<ul class="grid-x">
								<li class="cell"><span class="container__list container__list--title">System</span></li>
								<?php show_links($system_links); ?>
							</ul>
						</div>
						<div class="cell small-12 large-auto">
							<ul class="grid-x">
								<li class="cell"><span class="container__list container__list--title">Reading</span></li>
								<?php show_links($reading_links); ?>
							</ul>
						</div>
						<div class="cell small-12 large-auto">
							<ul class="grid-x">
								<li class="cell"><span class="container__list container__list--title">Work</span></li>
								<?php show_links($work_links); ?>
							</ul>
						</div>
						<div class="cell small-12 large-auto">
							<ul class="grid-x">
								<li class="cell"><span class="container__list container__list--title">Media</span></li>
								<?php show_links($media_links); ?>
							</ul>
						</div>
						<div class="cell small-12 large-auto">
							<ul class="grid-x">
								<li class="cell"><span class="container__list container__list--title">Games</span></li>
								<?php show_links($games_links); ?>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
		<script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
	</body>
</html>
