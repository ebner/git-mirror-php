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

// a directory use to cache git clones, to avoid a full clone on every commit
define('LOCAL_CACHE', '/srv/git-mirror-cache/repo');

// Time limit in seconds for each command
if (!defined('TIME_LIMIT')) define('TIME_LIMIT', 60);

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Git mirror script</title>
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

// https://help.github.com/articles/duplicating-a-repository

// Clone the repository into the TMP_DIR
if (!is_dir(LOCAL_CACHE)) {
	$commands[] = sprintf('git clone --bare %s %s', SOURCE_REPOSITORY, LOCAL_CACHE);
} else {
	$commands[] = sprintf('git --git-dir=%s fetch --prune origin', LOCAL_CACHE);
}

$commands[] = sprintf('git --git-dir=%s push --mirror %s', LOCAL_CACHE, TARGET_REPOSITORY);

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
