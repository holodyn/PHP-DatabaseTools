<?php

/*
  ** Modified **
    2010 Webuddha, Holodyn Inc.

  ** Original Source **
    http://webdigity.wordpress.com/2006/06/09/automatically-optimize-all-tables-in-a-mysql-database/

  ** SECURITY NOTE **
    For public servers it is advisable to create a new user with the following restricted permissions

    MySQL User Permissions Required for Repair / Optimization
      SELECT, INSERT
      SHOW VIEW
      SHOW DATABASES
      LOCK TABLES
*/

/** Connection variables **/
  include 'MySQL-Config.php';

/** Runtime Unlimited **/
  set_time_limit(0);

/** Track Execution **/
  $time = microtime();
  $time = explode(' ', $time);
  $time = $time[1] + $time[0];
  $start = $time;

/** Start Output **/
  PostMsg('<pre>');

/** Collect Database List **/
  $db_link = mysqli_connect($host,$user,$pass);
  $res = mysqli_query($db_link, 'SHOW DATABASES');
  if( !$res ){ PostMsg('MySQL Error: ' . mysqli_error($db_link)); exit; }
  PostMsg('Found '. mysqli_num_rows( $res ) . ' databases');
  $db_list = array();
  while( $rec = mysqli_fetch_array($res) ){
    $db_list[] = $rec[0];
  }

/** Loop Database List / Review Tables / Optimize **/
  foreach( $db_list as $db_name ){
    PostMsg('Database: '.$db_name);
    $res = mysqli_query($db_link, "SHOW TABLE STATUS FROM `" . $db_name . "`");
    if( !$res ){ PostMsg('MySQL Error: ' . mysqli_error($db_link)); exit; }
    /** Collect Table List ** ONLY MyISAM w/Overhead **/
    $to_optimize = array();
    while ( $rec = mysqli_fetch_array($res) ){
      if( $rec['Engine'] == 'MyISAM' && $rec['Data_free'] > 0 ){
        $to_optimize[] = $rec['Name'];
      }
    }
    /** Optimize Table List **/
    if ( count ( $to_optimize ) > 0 ){
      foreach ( $to_optimize as $tbl ){
        PostMsg(' -- Optimizing Table: '.$tbl);
        $res = mysqli_query($db_link, "OPTIMIZE TABLE `". $db_name ."`.`" . $tbl ."`");
        if( !$res ){ PostMsg('MySQL Error: ' . mysqli_error()); exit; }
      }
    }
  }

/** Report Completed Execution **/
  $time = microtime();
  $time = explode(' ', $time);
  $time = $time[1] + $time[0];
  $finish = $time;
  $total_time = round(($finish - $start), 6);
  PostMsg('Parsed in ' . $total_time . ' secs');

/** Echo / NewLine / Flush **/
  function PostMsg($msg){
    echo $msg . "\n"; flush();
  }

?>
