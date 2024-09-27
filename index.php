<?php
ini_set('display_errors', 0);
define('SHOW_ICON', true);
define('REFRESH_RATE', 600);
define('TODO_FILE', 'data/todo.json');
define('LINKS_FILE', 'data/links.json');

$column_css = 'col-sm-6 col-md-4 col-lg-4 col-xl-2 listColumn border-0';

include('functions.php');

$todos = json_decode(file_get_contents(TODO_FILE), true);
$menuData = json_decode(file_get_contents(LINKS_FILE), true);

$projects_folder = '..';
$dirs = array_filter(glob($projects_folder . '/*'), 'is_dir');
foreach ($dirs as $value) {
	$value = strtolower(str_replace('../','',$value));
	// $project_links['https://'.$value.'.test'] = $value;
	$project_links[$value] = 'https://'.$value.'.test';
}

$menuData['dev projects'] = $project_links;
$menuData = array_merge(array('dev projects' => $menuData['dev projects']), array_diff_key($menuData, array('dev projects' => $menuData['dev projects'])));

$css_form = load_css_files();
$selected_style = get_selected_style();

check_new_todo($todos);
check_delete_todo($todos);

?>
<!DOCTYPE html>
<html lang="en">
<head>
		<title> ~ esquire </title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
		<link rel="stylesheet" type="text/css" href="<?php echo $selected_style; ?>" >
		<link rel="stylesheet" type="text/css" href="css/style.min.css">
		<meta http-equiv="refresh" content="<?php echo REFRESH_RATE; ?>" />
    <style>
        /* Basic styling for the menu */
        li {
            list-style-type: none;
        }
        .hidden {
            display: none;
        }
        #search {
            margin-bottom: 20px;
            padding: 10px;
            width: 300px;
            font-size: 16px;
        }
        .link {
            padding: 0;
            margin: 0;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-10">
            <div class="card">
                <div class="card-body">
                    <form id="search-form" method="get" onsubmit="return handleSearch();" class="form">
                        <input type="text" id="search-box" name="q" placeholder="Filter menu items or Search Google or Enter URL" class="form-control form-control-sm m-0" required>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <nav class="nav" id="navMenu">
                        <?php createMenu($menuData); ?>
                    </nav>
                </div>
            </div>
        </div>
        <div class="col-2">
            <div class="card">
                <div class="card-body">
                    <?php echo $css_form; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="card-header"><h6>Nigeria</h6></div>
                    <div id="currentTime"><?php echo date('H:i:s A'); ?></div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="card-header"><h6>Canada</h6></div>
                    <div id="currentTime2"><?php echo date('H:i:s A'); ?></div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <?php echo load_todo(); ?>
                    <form action="index.php" method="post" class="form">
                        <div class="form p-1">
                        <input type="text" name="todo" class="form-control form-control-sm m-0">
                            <input type="hidden" name="action" value="add_todo">
                        </div>
                                                    
                    </form>
                </div>
            </div>
        </div>
    </div>
    

    <script src="js/bootstrap.bundle.min.js"></script>
		<script>
			window.onload = function() {
				document.getElementById('search-box').focus();
			};

			function isValidURL(string) {
				try {
					new URL(string);
					return true;
				} catch (_) {
					return false;
				}
			}

			function handleSearch() {
				var query = document.getElementById('search-box').value.trim();

				if (isValidURL(query)) {
					window.location.href = query.startsWith("http") ? query : "http://" + query;
				} else {
					// Perform a Google search
					var form = document.getElementById('search-form');
					form.action = "https://www.google.com/search";
					form.submit();
				}

				return false; // Prevent default form submission
			}
			function updateTime() {
            // Specify the timezone you want to display
            var timezone = 'Africa/Lagos'; // Change to your desired timezone

            // Get the current time in the specified timezone
            var options = { timeZone: timezone, hour: '2-digit', minute: '2-digit', second: '2-digit' };
            var currentTime = new Intl.DateTimeFormat('en-US', options).format(new Date());

            // Update the HTML element
            document.getElementById("currentTime").innerHTML = currentTime;

			var timezone = 'America/Thule';

			// Get the current time in the specified timezone
			var options = { timeZone: timezone, hour: '2-digit', minute: '2-digit', second: '2-digit' };
			var currentTime = new Intl.DateTimeFormat('en-US', options).format(new Date());

			// Update the HTML element
			document.getElementById("currentTime2").innerHTML = currentTime;

        }

        // Update the time every second
        setInterval(updateTime, 1000);

        // Function to search through the menu
        document.getElementById("search-box").addEventListener("input", function() {
            var filter = this.value.toLowerCase();
            var listItems = document.querySelectorAll("#navMenu li");

            listItems.forEach(function(item) {
                // Check if the item contains the search term
                var text = item.textContent.toLowerCase();
                if (text.includes(filter)) {
                    item.style.display = "";
                } else {
                    item.style.display = "none";
                }
            });
        });
    </script>

</body>
</html>
