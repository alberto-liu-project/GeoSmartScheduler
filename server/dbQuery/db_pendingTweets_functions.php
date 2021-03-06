<?php
class DB_pendingTweets_Functions {
	
 private $db;
 private $dblink;

    //put your code here
    // constructor
    function __construct() {
        $root=dirname(dirname(__FILE__));
        //include_once (__ROOT__.'/dbQuery/db_connect.php');
        // connecting to database
       // $this->db = new DB_Connect();
       // $this->dblink=$this->db->connect();
        require_once($root.'/utils/config.php');
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
        if ($mysqli->connect_errno) {
        	echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
        }
        $this->dblink= $mysqli;
    }

    // destructor
    function __destruct() {
        mysqli_close($this->dblink);
    }

 	/**
     * Get tweets from pending tweets queue
     * 
     * @param string $id_twt id of the tweet to be get from the pendings tweets
     * @return Return set of tweets from pending tweets if it succes or "false" if it fails
     */
    public function getPendingTweet($id_twt) {
    	//get a set of rows from the database
        $result = mysqli_query($this->dblink, "SELECT * FROM `pending_tweets` WHERE `id_twt` =".$id_twt)
        or die(mysqli_error($this->dblink));
        return $result;
    }
    
    /**
     * 
     * @param int $NumTweets number of tweets requested
     * @param array $array_id_twt associative array with the id of the tweets requested. 
     * 		the associative array should be like id0 => id_tweet
     * @return Return set of tweets from pending tweets if it succes or "false" if it fails
     */
    public function getArrayPendingTweet($NumTweets, $array_id_twt) {
    	$list_id_twt=null;
    	for ($i=0; $i<$NumTweets; $i++){
    			if(!$i) {
    				$list_id_twt = $array_id_twt['id'.$i];
    			}
    			else{
    				$list_id_twt = $list_id_twt.",".$array_id_twt['id'.$i];
    			}	
    	}
    	//get a set of rows from the database
    	$query= "SELECT * FROM `pending_tweets` WHERE `id_twt` IN (".$list_id_twt.")";
    	$result = mysqli_query($this->dblink, $query )
    	or die(mysqli_error($this->dblink));
    	return $result;
    }
    /**
     * Delete a tweet from pending tweets queue
     * 
     * @param  string $id_twt id of the tweet to be get from the pendings tweets
     * @return Return "true" if succes or "false" if it fails
     */
	public function deletePendingTweet($id_twt) {
    	//get a set of rows from the database
        $result = mysqli_query($this->dblink, "DELETE FROM `pending_tweets` WHERE `id_twt`= ".$id_twt)
        or die(mysqli_error($this->dblink));
        return $result;
    }

    /**
     * 
     * Put a tweet into the pending tweets queue 
     * @param string $id_twt id of the tweet to be get from the pendings tweets
     * @return Return "true" if it succes or "false" if it fails
     */
    public function putPendingTweet( $id_twt ) {
    	//insert a row into the database
        $result = mysqli_query($this->dblink, "INSERT INTO `pending_tweets`(`created_at`, `id_twt`, `text`) VALUES ((SELECT `created_at` FROM `twitter_trace` WHERE `id_twt`=  '$id_twt'), '$id_twt', (SELECT `text` FROM `twitter_trace` WHERE `id_twt`='$id_twt'))")
        or die(mysqli_error($this->dblink));
        return $result;
    }
    

    
    
}