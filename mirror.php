<?php
/**
 * Git mirror script
 *
 * Author: Hannes Ebner <hannes@ebner.se>, 2014
 *
 * Clones and fetches a Git repository to push it to another mirror-repository.
 * Uses "clone --bare", "fetch --prune" and "push --mirror" in different steps.
 *
 * Possible sources of failure:
 *
 * - The public key of www-data has to have read access to the source repository
 *   as well as write access to the target repository.
 * - The connection will fail if the SSH server is not trusted, to avoid this the
 *   server should be access from the command line at least once to get its
 *   fingerprint into the local SSH configuration.
 *
 * License
 *
 * Hannes Ebner licenses this work under the terms of the Apache License 2.0
 * (the "License"); you may not use this file except in compliance with the
 * License. See the LICENSE file distributed with this work for the full License.
 */

// goes into the "token"-URL parameter, recommended to use "uuid" command
define('ACCESS_TOKEN', '6dc058c8-af99-11e3-89f6-3c970e88a290');

// which repository to fetch
define('SOURCE_REPOSITORY', 'git@bitbucket.org:org/repo.git');

// which repository to push to
define('TARGET_REPOSITORY', 'git@github.com:org/repo.git');

// a directory use to cache git clones, to avoid a full clone on every commit
define('LOCAL_CACHE', '/srv/git-mirror-cache/repo.git');

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
if (!isset($_GET['token']) || $_GET['token'] !== ACCESS_TOKEN) {
	die('Access denied');
}
?>

<pre>
<?php
// The commands
$commands = array();

// Clone the repository into the TMP_DIR
if (!is_dir(sprintf('%s/%s', LOCAL_CACHE, 'refs'))) {
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
	printf('$ %s <br/>%s<br/>', htmlentities(trim($command)), htmlentities(trim(implode("\n", $tmp))));
	flush();

	if ($return_code !== 0) {
		printf('Error encountered! Script stopped to prevent data loss.');
		break;
	}
}
?>

Done.
</pre>
</body>
</html>
