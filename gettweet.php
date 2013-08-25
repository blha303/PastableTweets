<?php

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("No URL provided!");
}

function get_isgd_url($url)  
{  
	//get content
	$ch = curl_init();  
	$timeout = 5;  
	curl_setopt($ch,CURLOPT_URL,'http://is.gd/api.php?longurl='.$url);  
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);  
	$content = curl_exec($ch);  
	curl_close($ch);
	
	//return the data
	return $content;  
}

function time_elapsed_string($ptime)
{
    $etime = time() - $ptime;

    if ($etime < 1)
    {
        return '0 seconds';
    }

    $a = array( 12 * 30 * 24 * 60 * 60  =>  'year',
                30 * 24 * 60 * 60       =>  'month',
                24 * 60 * 60            =>  'day',
                60 * 60                 =>  'hour',
                60                      =>  'minute',
                1                       =>  'second'
                );

    foreach ($a as $secs => $str)
    {
        $d = $etime / $secs;
        if ($d >= 1)
        {
            $r = round($d);
            return $r . ' ' . $str . ($r > 1 ? 's' : '') . ' ago';
        }
    }
}

require_once('TwitterAPIExchange.php');

include('config.php');
if (!isset($oauth_access_token) || !isset($oauth_access_token_secret) || 
    !isset($consumer_key) || !isset($consumer_key)) {
    die("No config.php");
}
$settings = array(
    'oauth_access_token' => $oauth_access_token,
    'oauth_access_token_secret' => $oauth_access_token_secret,
    'consumer_key' => $consumer_key,
    'consumer_secret' => $consumer_secret
);

$twitter = new TwitterAPIExchange($settings);

if (is_numeric($_GET['id'])) {
    $url = 'https://api.twitter.com/1.1/statuses/show.json';
    $getfield = '?id='.$_GET['id'];
    $requestMethod = 'GET';

    $response = $twitter->setGetfield($getfield)
                        ->buildOauth($url, $requestMethod)
                        ->performRequest();

    $data = json_decode($response);
    //echo $response;
    if (array_key_exists("user", $data)) {
        $username = $data->user->screen_name;
        $realname = $data->user->name;
        $text = $data->text;
        $url = "https://twitter.com/".$username."/status/".$data->id_str;
        if (!isset($_GET['longurl'])) {
            $url = get_isgd_url($url);
        }
        if (isset($_GET['timestamp'])) {
            $ts = " ".time_elapsed_string(strtotime($data->created_at));
        }
        echo $realname." (@".$username."): \"".$text."\"".$ts." ".$url;
    } else if (array_key_exists("errors", $data)) {
        echo "Error on response: ".$data->errors[0]->message;
    } else {
        echo "Unspecified error.";
    }
} else {
    $url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
    $getfield = '?count=1&screen_name='.$_GET['id'];
    $requestMethod = 'GET';

    $response = $twitter->setGetfield($getfield)
                        ->buildOauth($url, $requestMethod)
                        ->performRequest();

    $data = json_decode($response);
    if (array_key_exists("errors", $data)) {
        die("Error on response: ".$data->errors[0]->message);
    }
    $_GET['id'] = $data[0]->id_str;

    $url = 'https://api.twitter.com/1.1/statuses/show.json';
    $getfield = '?id='.$_GET['id'];
    $requestMethod = 'GET';

    $response = $twitter->setGetfield($getfield)
                        ->buildOauth($url, $requestMethod)
                        ->performRequest();

    $data = json_decode($response);
    //echo $response;
    if (array_key_exists("user", $data)) {
        $username = $data->user->screen_name;
        $realname = $data->user->name;
        $text = $data->text;
        $url = "https://twitter.com/".$username."/status/".$data->id_str;
        if (!isset($_GET['longurl'])) {
            $url = get_isgd_url($url);
        }
        if (isset($_GET['timestamp'])) {
            $ts = " ".time_elapsed_string(strtotime($data->created_at));
        }
        echo $realname." (@".$username."): \"".$text."\"".$ts." ".$url;
    } else if (array_key_exists("errors", $data)) {
        echo "Error on response: ".$data->errors[0]->message;
    } else {
        echo "Unspecified error.";
    }
}
?>
