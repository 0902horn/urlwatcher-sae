<?php
include_once( 'saemysql.class.php' );
include_once( 'saetaskqueue.class.php' );

function TimingTask()
{
    // load urls
    $mysql = new SaeMysql();
    $sql = "SELECT url FROM `url`";
    $data = $mysql->getData( $sql );
    $mysql->closeDb();

    $queue = new SaeTaskQueue('task_queue_0_2');
    $array = array();
    for($i = 0; $i < sizeof($data); ++$i)
    {
        //$array[] = array('url'=>"http://urlwatcher.sinaapp.com/url_watch.php", "postdata"=>"target=".$data[$i], "prior"=>true);
        $array[] = array('url'=>"http://urlwatcher.sinaapp.com/url_watch.php?target=" . $data[$i]["url"], "postdata"=>NULL, "prior"=>true);
    }
    
    $queue->addTask($array);

    $ret = $queue->push();
    if ($ret === false)
    {
        var_dump($queue->errno(), $queue->errmsg());
        echo "Failed.";
    }
    else
    {    
        echo "Success.";
        print_r($array);
    }
}

TimingTask();

?>