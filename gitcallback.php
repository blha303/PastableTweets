<?php
 
// TODO Handle tagged releases
// TODO Check current branch is checked out before pulling
// TODO Store sites in separate config file
// TODO Allow config file to be in folder above root.
// TODO Allow updating via $_GET?
// TODO Make sure sane git status before pulling?
 
// You can't view this file directly
if ( ! isset( $_POST['payload'] ) ) {
 
	header( 'Status: 301' );
	header( 'Location: http://pastabletweets.pw/' );
	exit;
 
}
 
global $github;
 
$github = json_decode( $_POST['payload'] );
 
/**
 * Array of sites and their github details
 *
 * Format is array( [github repo name] => array( [site directory], [pull from branch_name or tag] );
 *
 */
$sites = array();
 
$sites['PastableTweets']	= array(
	'dir'	=>	'dev.pastabletweets.pw',
	'ref'	=>	'testing'
);
 
define( 'ABSPATH', dirname( dirname( __FILE__ ) ) );
 
// Check the payload is for one of the configured sites
if ( isset( $sites[$github->repository->name] ) && $site = $sites[$github->repository->name] ) {
	
	// If the directory doesn't exist then bail
	if ( ! file_exists( ABSPATH . '/' . $site['dir'] ) )
		return;
 
	$response = '';
 
	// If we're set to pull from a branch and that branch was part of the payload
	if ( $site['ref'] != 'tag' && $github->ref == 'refs/heads/' . $site['ref'] )
 
		// Then Git Pull
		$response = git_pull( $site['dir'] );
}
 
 
/**
 * Perform a git pull & git submodule update command.
 * 
 * @access public
 * @param string $dir
 * @return void
 */
function git_pull( $dir ) {
	return shell_exec( 'cd ' . escshellarg( ABSPATH . '/' . $dir ) . ' ; git pull ; git submodule update --init --recursive' );
}
