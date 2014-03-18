<?php
/**
 * Git mirror script, Hannes Ebner <hannes@ebner.se>
 */

// goes into the "token"-URL parameter, recommended to use "uuid" command
define('ACCESS_TOKEN', '7270c876-ae13-11e3-aef2-3c970e88a290');

// which repository to fetch
define('SOURCE_REPOSITORY', 'git@github.com:ebner/git-mirror-php.git');

// which repository to push to
define('TARGET_REPOSITORY', 'git@bitbucket.org:ebner/git-mirror-php.git');

// Time limit for each command.
if (!defined('TIME_LIMIT')) define('TIME_LIMIT', 30);

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Git mirror script</title>
	<style>
		body { padding: 0 1em; background: #222; color: #fff; }
		h2, .error { color: #c33; }
		.prompt { color: #6be234; }
		.command { color: #729fcf; }
		.output { color: #999; }
	</style>
</head>
<body>
<?php
if (!isset($_GET['token']) || $_GET['token'] !== SECRET_ACCESS_TOKEN) {
	die('<h2>Access denied</h2>');
}
?>
<pre>

<?php
// The commands
$commands = array();

// Clone the repository into the TMP_DIR
$commands[] = sprintf(
	'git clone --depth=1 --branch %s %s %s'
	, BRANCH
	, REMOTE_REPOSITORY
	, TMP_DIR
);

// Update the submodules
$commands[] = sprintf(
	'git submodule update --init --recursive'
);

// run commands
foreach ($commands as $command) {
	set_time_limit(TIME_LIMIT); // Reset the time limit for each command
	$tmp = array();
	exec($command.' 2>&1', $tmp, $return_code); // Execute the command
	// Output the result
	printf('
<span class="prompt">$</span> <span class="command">%s</span>
<div class="output">%s</div>
'
		, htmlentities(trim($command))
		, htmlentities(trim(implode("\n", $tmp)))
	);
	flush(); // Try to output everything as it happens

	// Error handling and cleanup
	if ($return_code !== 0) {
		printf('<div class="error">Error encountered! Script stopped to prevent data loss.</div>');
		break;
	}
}
?>

Done.
</pre>
</body>
</html>
