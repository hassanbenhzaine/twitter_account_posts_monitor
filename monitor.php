<?php

// Errors logging
if(file_exists('1337.log')) unlink('1337.log');
ini_set('log_errors', 1);
ini_set('error_log', '1337.log');

// Refresh rate in seconds
const sleepRate = 2;

// Twilio configuration
const twilioUrl = "https://api.twilio.com/2010-04-01/Accounts/xxxxxxxxxxxxxxxxxxxxxxx/Calls.json";
const twilioSID = "";
const twilioAuth = "";
const twilioFromNumber = "+212111111111";
const twilioToNumber = "+212111111111";


// Twitter configuration
const twitterAccountId = "3456789076546789";
const twitterEndpoint = "https://api.twitter.com/2/users/".twitterAccountId."/tweets";
const twitterHeaders = array(
    "Authorization: Bearer xxxxxxxxxxxxxxxxxxxxxxxxxxx",
 );

$chTwitter = curl_init();
curl_setopt($chTwitter, CURLOPT_HTTPHEADER, twitterHeaders);
curl_setopt($chTwitter, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($chTwitter, CURLOPT_SSL_VERIFYPEER, false);

function makeCall(){
    $chTwilio = curl_init();
    curl_setopt($chTwilio, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($chTwilio, CURLOPT_URL, twilioUrl);
    curl_setopt($chTwilio, CURLOPT_POST, true);
    curl_setopt($chTwilio, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($chTwilio, CURLOPT_USERPWD, twilioSID.':'.twilioAuth);
    curl_setopt($chTwilio, CURLOPT_POSTFIELDS, "From=".twilioFromNumber."&To=".twilioToNumber."&Url=http://demo.twilio.com/docs/voice.xml");
    curl_exec($chTwilio);
    curl_close($chTwilio);

    echo date("[Y/m/d H:i:s]")." New tweet found, calling ".twilioToNumber." from ".twilioFromNumber."\n";

    checkNewTweet();
}

function getNewestTweetId() {
    global $chTwitter;
    $maxResultsQuery = "max_results=5";
    curl_setopt($chTwitter, CURLOPT_URL, twitterEndpoint.'?'.$maxResultsQuery);
    
    $response = curl_exec($chTwitter);
    $response = json_decode($response, true);

    return $response['meta']['newest_id'];
}

// Program starting point
checkNewTweet();

function checkNewTweet(){
    global $chTwitter;
    $sinceIdQuery = "since_id=".getNewestTweetId();
    curl_setopt($chTwitter, CURLOPT_URL, twitterEndpoint.'?'.$sinceIdQuery);

    echo date("[Y/m/d H:i:s]")." Checking for new tweets from Twitter ID: ".twitterAccountId."\n";
    
    $response = null;
    do {
        $response = curl_exec($chTwitter);
        $response = json_decode($response, true);
        sleep(sleepRate);
    } while ($response['meta']['result_count'] == 0);

    makeCall();
}

curl_close($chTwitter);


?>