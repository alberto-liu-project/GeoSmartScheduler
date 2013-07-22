<?php
ini_set('display_errors', 1);
set_time_limit(120);
$root=dirname(dirname(__FILE__));
require_once($root.'/utils/config.php'); 
require_once($root.'/dbQuery/db_traces_functions.php');
require_once($root.'/dbQuery/db_pendingTweets_functions.php');
require_once($root.'/utils/utilsLog.php');
require_once($root.'/utils/GCM.php');
require_once($root.'/dbQuery/db_GCM_functions.php');

 //NOTE: We can add a method to get the user registration id from the db and use it instead of the constant 

$gcm = new GCM();
$log = new log();
$dbGCM = new DB_GCM_Functions();
$result = $dbGCM->getLastUserInfo();
$reg_id= mysqli_fetch_assoc($result);
$tweet;
$id_trace = 0;
//create object to use pendingTweets functions
$dbPendingTweetsFunctions = new DB_pendingTweets_Functions();
//Load trace to run the test
$dbTracesFunctions = new DB_Traces_Functions();
$trace = $dbTracesFunctions->getTraceOfTweets($id_trace);
$Trace_nmbTweets = mysqli_num_rows($trace);
//Set last tweet id "LTweetId"
//$LTweetId = $dbTracesFunctions->getLastIdTweet_TraceOfTweets($id_trace);
$CurrentTwtId= null;
$registration_ids;
//Set starting "sleep_time"
$sleep_time = 0.0;
$i=0;
//Load each tweet of the trace is being used to run the test
while ($tweet = $trace->fetch_assoc())
{
	//wait sleep_time and then remain the loop
	//first time sleep_time is =0.0 and the loop does not sleep
	time_sleep_until(time()+$sleep_time);
	
	//send tweet to gcm with the size of the tweet attached  
	$registration_ids= array ($reg_id["gcm_regid"]);
	$message= array("message"=> $tweet['id_twt'] ,"size"=>$tweet['size']);
	$success=$gcm->send_notification($registration_ids, $message);
	//Log action of GCM Notification
	//$log->user("Trace nº".$id_trace."| Notification sent to GCM Server | tweet_id = ".$tweet['id_twt'], "Alberto");
	//If the response is true, the notification was successfully delivered and we can store it in the pending queue
	//Otherwise we stop the test
	if ($success){
		echo "Trace n�".$id_trace."| Notification sent to GCM Server | tweet_id = ".$tweet['id_twt'];
		//store tweet in pending tweets queue
		$dbPendingTweetsFunctions->putPendingTweet($tweet['id_twt']);
		//Log action of put in pending list
		//$log->user("Trace nº".$id_trace."| Tweet almacenado en pending_tweets | tweet_id = ".$tweet['id_twt'], "Alberto");
		echo  "Trace n�".$id_trace."| Tweet almacenado en pending_tweets | tweet_id = ".$tweet['id_twt'];
	
	}
	else{
		break;
	}
	//load next sleep_time
	$sleep_time = $tweet['time_to_next'];
	$CurrentTwtId=$tweet['id_twt'];
	$i++;
	echo "Progres...".(string)(($i/$Trace_nmbTweets)*100);
	
}	
//Release memory of the trace
//$trace->free();
/*if ( $CurrentTwtId == $LTweetId )
{
$log->user("Trace n�".$id_trace."| Impossible to reach last tweet of the trace | Current_tweet_id = ".$CurrentTwtId." Last_Tweet_Id =".$LTweetId, "Alberto");
}*/

?>