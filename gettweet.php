<?php

// If passed a URL, don't freak out, calmly use the same method as
// index.html's javascript to grab the last portion of it.
if (sizeOf(explode("/", $_GET['id'])) >= 2) {
    $temp = explode("/", $_GET['id']);
    $id   = array_pop($temp);
    if ($id == "") {
        $id = array_pop($temp);
    }
    $_GET['id'] = $id;
}

$shellarg = escapeshellarg($_GET['id']);
if (isset($_GET['timestamp'])) {
    $shellarg = $shellarg . "-timestamp";
}
if (isset($_GET['longurl'])) {
    $shellarg = $shellarg . "-longurl";
}
$cachefile = "cache/" . $shellarg . ".html";

if (file_exists($cachefile)) {
    include($cachefile);
    exit;
}


// Check for id param
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("No URL provided!");
}

// Functions from StackOverflow
function get_isgd_url($url)
{
    $ch      = curl_init();
    $timeout = 5;
    curl_setopt($ch, CURLOPT_URL, 'http://is.gd/api.php?longurl=' . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $content = curl_exec($ch);
    curl_close($ch);
    return $content;
}

function time_elapsed_string($ptime)
{
    $etime = time() - $ptime;
    if ($etime < 1) {
        return '0 seconds';
    }
    
    $a = array(
        12 * 30 * 24 * 60 * 60 => 'year',
        30 * 24 * 60 * 60 => 'month',
        24 * 60 * 60 => 'day',
        60 * 60 => 'hour',
        60 => 'minute',
        1 => 'second'
    );
    
    foreach ($a as $secs => $str) {
        $d = $etime / $secs;
        if ($d >= 1) {
            $r = round($d);
            return $r . ' ' . $str . ($r > 1 ? 's' : '') . ' ago';
        }
    }
}
// End StackOverflow functions

// Include the Twitter library.
require_once('TwitterAPIExchange.php');
// Include oauth stuff
include('config.php');
if (!isset($oauth_access_token) || !isset($oauth_access_token_secret) || !isset($consumer_key) || !isset($consumer_key)) {
    die("No config.php");
}
$settings = array(
    'oauth_access_token' => $oauth_access_token,
    'oauth_access_token_secret' => $oauth_access_token_secret,
    'consumer_key' => $consumer_key,
    'consumer_secret' => $consumer_secret
);
// Initialize Twitter
$twitter  = new TwitterAPIExchange($settings);

// I'm not at all sure if is_numeric works, but I'm rolling with it.
if (is_numeric($_GET['id'])) {
    $url           = 'https://api.twitter.com/1.1/statuses/show.json';
    $getfield      = '?id=' . $_GET['id'];
    $requestMethod = 'GET';
    
    $response = $twitter->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest();
    
    $data = json_decode($response);
    if (array_key_exists("user", $data)) {
        $username = $data->user->screen_name;
        $realname = $data->user->name;
        $text     = $data->text;
        $url      = "https://twitter.com/" . $username . "/status/" . $data->id_str;
        if (!isset($_GET['longurl'])) {
            $url = get_isgd_url($url);
        }
        if (isset($_GET['timestamp'])) {
            $ts = " " . time_elapsed_string(strtotime($data->created_at));
        }
        ob_start();
        echo $realname . " (@" . $username . "): \"" . $text . "\"" . $ts . " " . $url;
        // outputs 'Steven Smith (@blha303): "This is a tweet!" 2 hours ago http://is.gd/areallink'
    } else if (array_key_exists("errors", $data)) {
        ob_start();
        echo "Error on response: " . $data->errors[0]->message;
    } else {
        ob_start();
        echo "Unspecified error.";
    }
} else {
    $url           = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
    $getfield      = '?count=1&screen_name=' . $_GET['id'];
    $requestMethod = 'GET';
    
    $response = $twitter->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest();
    
    $data = json_decode($response);
    if (array_key_exists("errors", $data)) {
        die("Error on response: " . $data->errors[0]->message);
    }
    $_GET['id'] = $data[0]->id_str;
    
    $shellarg = escapeshellarg($_GET['id']);
    if (isset($_GET['timestamp'])) {
        $shellarg = $shellarg . "-timestamp";
    }
    if (isset($_GET['longurl'])) {
        $shellarg = $shellarg . "-longurl";
    }
    $cachefile = "cache/" . $shellarg . ".html";
    if (file_exists($cachefile)) {
        include($cachefile);
        exit;
    }
    
    // Yes, this is the same code copied from above.
    $url           = 'https://api.twitter.com/1.1/statuses/show.json';
    $getfield      = '?id=' . $_GET['id'];
    $requestMethod = 'GET';
    
    $response = $twitter->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest();
    
    $data = json_decode($response);
    if (array_key_exists("user", $data)) {
        $username = $data->user->screen_name;
        $realname = $data->user->name;
        $text     = $data->text;
        $url      = "https://twitter.com/" . $username . "/status/" . $data->id_str;
        if (!isset($_GET['longurl'])) {
            $url = get_isgd_url($url);
        }
        if (isset($_GET['timestamp'])) {
            $ts = " " . time_elapsed_string(strtotime($data->created_at));
        }
        ob_start();
        echo $realname . " (@" . $username . "): \"" . $text . "\"" . $ts . " " . $url;
    } else if (array_key_exists("errors", $data)) {
        ob_start();
        echo "Error on response: " . $data->errors[0]->message;
    } else {
        ob_start();
        echo "Unspecified error.";
    }
}

$fp = fopen($cachefile, 'w');
fwrite($fp, ob_get_contents());
fclose($fp);
ob_end_flush();
?>
