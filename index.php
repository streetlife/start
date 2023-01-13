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
	'https://techmeme.com'=>'techmeme',
	'https://nairaland.com'=>'nairaland',
    'https://chat.openai.com'=>'chatGPT',
    'https://lexica.art/aperture'=>'lexica'
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
    'https://mymtn.com.ng/dashboard'=>'myMTN'
];

$games_links = [
	'https://discord.com'=>'discord',
	'https://steamcommunity.com'=>'steam',
	'https://crazygames.com'=>'crazy games',
	'https://epicgames.com'=>'epic games',
	'https://wordfinderx.com'=>'wordfinderx',
	'https://livescores.com'=>'livescores'
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
	'https://downloadly.ir/'=>'downloadly'
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
:root {
    /* Margins */
    --padding: 20px;
    --large-padding: 10vh;
    --foundation: 0.625rem;

    /* Fonts */
    --font-family: "Inter", sans-serif;
    --normal: 500;
    --bold: 900;

    /* Colors */
    --white: #fff;
    --black: #11110e;
    --gray: #0d0d0d;
    --grey: #353535;

    /* Animation */
    --animation: all 0.3s ease-in-out;
    --animation-long: all 0.6s ease-in-out;
}

body {
    background-color: --white;
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

* {
    -webkit-transition: var(--animation);
    -moz-transition: var(--animation);
    -o-transition: var(--animation);
    transition: var(--animation);
}

body,
a,
p,
h1,
h2,
h3,
h4,
h5,
h6 {
    font-family: var(--font-family);
}

p {
    font-weight: var(--normal);
    color: var(--black);
}

a {
    color: var(--black);
    opacity: 0.5;
}

a:hover,
a:focus {
    color: var(--gray);
    opacity: 1;
}

a:hover img,
a:focus img {
    filter: grayscale(0);
    -webkit-filter: grayscale(0);
}

.container {
    padding: var(--padding);
}

@media screen and (max-width: 40em) {
    .container {
        padding: calc(var(--padding) * 3) var(--padding);
    }
}

.container,
.container__inner {
    min-height: calc(100vh - 40px);
}

ul {
    margin: 0 0 var(--padding);
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
    background: rgba(255, 255, 255, 0.025);
    box-shadow: none;
    border: none;
    outline: none;
    color: var(--black);
    border-radius: 8px;
    padding: 0 10px;
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
        margin-bottom: var(--padding);
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
    font-family: var(--font-family);
    text-transform: uppercase;
    letter-spacing: 10px;
    font-size: 0.9rem;
    font-weight: var(--bold);
    color: var(--grey);
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
.container input {
    position: relative;
    background: transparent;
    box-shadow: none;
    border: none;
    padding: 0;
    margin: 0;
    height: auto;
    width: calc(100% - 36px);
    opacity: 0.5;
    border-radius: 10px;
    line-height: 3.25rem;
    color: var(--gray);
    font-family: var(--font-family);
    font-weight: var(--bold);
    outline: none;
    cursor: zoom-in;
}

.container input:focus {
    background: rgba(255, 255, 255, 0.025);
    box-shadow: none;
    border: none;
    outline: none;
    /*color: var(--white);*/
    border-radius: 8px;
    padding: 0 14px;
    opacity: 1;
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
