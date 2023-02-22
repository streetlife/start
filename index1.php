<?php 
// localhost projects
// $db = new SQLite3('links.db');

// $categories = $db->query('select distinct category from links');

// foreach ($categories as $category) {
//     $links = $db->query('select link, link_name from links where category = "'.$category.'"');

//     foreach ($links as $link) {

//     }
// }

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
	'https://campus.college.ch/thesis_repository'=>'robert kennedy',
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
	'https://livescores.com'=>'livescores',
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

<?php

// // Replace YOUR_ACCESS_KEY with your actual access key
// $accessKey = 'WYzi0x5lKoriA9SGnjuzf6kTHXqeUFreH6-RopUJ-9k';

// // Query the API to get a list of photos
// $url = 'https://api.unsplash.com/photos?client_id=' . $accessKey;
// $response = file_get_contents($url);
// $photos = json_decode($response, true);

// // Select a random photo from the list
// $randomIndex = array_rand($photos);
// $randomPhoto = $photos[$randomIndex];
// // print_r($photos);
// // print_r($randomPhoto);
// // die();

// // Set the background image of the body element to the URL of the random photo

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

<style>
/* Variables */

body {
    background-color: #122c3a;
    font-size: 17px;
}

@media screen and (max-width: 40em) {
    body {
        font-size: 18px;
    }
}

.container input {
    font-size: 26px;
}

.grid-container {
    max-width: 90rem;
}

/* * {
    -webkit-transition: var(--animation);
    -moz-transition: var(--animation);
    -o-transition: var(--animation);
    transition: var(--animation);
} */

body,
a,
p,
h1,
h2,
h3,
h4,
h5,
h6 {
    font-family: sans-serif;
}

p {
    font-weight: 500;
    color: #fff;
}

a {
    color: #ffffff;
    opacity: 0.5;
}

a:hover,
a:focus {
    color: #ffffff;
    opacity: 1;
}

a:hover img,
a:focus img {
    filter: grayscale(0);
    -webkit-filter: grayscale(0);
}

.container {
    padding: 20px;
}

@media screen and (max-width: 40em) {
    .container {
        padding: calc(20px * 3) 20px;
    }
}

.container,
.container__inner {
    min-height: calc(100vh - 40px);
}

ul {
    margin: 0 0 20px;
    padding: 0;
}

li {
    list-style: none;
}

.container__list--item {
    letter-spacing: -0.0225rem;
}

.container__list--item:hover {
    letter-spacing: -0.05rem;
}

.container__list--item:hover {
    /* background: rgba(255, 255, 255, 0.025); */
    box-shadow: none;
    border: none;
    outline: none;
    color: #000000;
    border-radius: 8px;
    /* padding: 0 10px; */
    opacity: 1;
    letter-spacing: -0.0225rem;
}

.container__list {
    display: flex;
    align-items: center;
    padding: 5px 5px 5px 0;
}

@media screen and (max-width: 64em) {
    .container__list {
        padding: 10px 10px 10px 0;
    }
}

@media screen and (max-width: 30em) {
    .container__list span {
        font-size: 0.9725rem;
    }
}

@media screen and (min-width: 40em) {
    .container form {
        margin-bottom: 20px;
    }
}

.container__list span {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.container__list img {
    width: 100%;
    margin-right: 0.5rem;
    max-width: 23px;
    max-height: 23px;
    border-radius: 5px;
    filter: grayscale(0.75);
    -webkit-filter: grayscale(0.75);
}

.container__list--title {
    font-family: sans-serif;
    text-transform: uppercase;
    letter-spacing: 10px;
    font-size: 0.9rem;
    font-weight: bold;
    color: #ffffff;
    padding: 10px 10px 10px 0;
}

@media screen and (min-width: 40em) {
    .container__list--title {
        padding: 5px 5px 10px 0;
    }
}

.container form {
    display: flex;
    width: 100%;
    opacity: 0.5;
}

.container form.focus {
    opacity: 1;
}

.container img.search {
    width: 100%;
    min-width: 36px;
    padding-right: 5px;
}

.container label {
    width: 36px;
    margin-right: 10px;
    display: flex;
    align-items: center;
    /*filter: grayscale(100%) brightness(25%);*/
}

.container label {
    cursor: context-menu;
}

</style>
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