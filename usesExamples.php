<?php
  require_once('./HttpSilentlySender.php');

  // update example
  $query="UPDATE fake_table
          SET some_data = $data
  WHERE id=$id";

  $httpSender = HttpSilentlySender::getInstance();
  $affected = $someDBConnection->exec( $query); //execute the statement through backend database connection (could be pdo or mysql_query)
  $httpSender->getRecordsData($query); // gets the updated data 
  $httpSender->sendData( $affected);

  // insert example
  $query="INSERT INTO fake_table (description, date)
           VALUES ($someDescription, $someDate)";

  $httpSender = HttpSilentlySender::getInstance();
  $affected = $someDBConnection->exec( $query); //execute the statement through backend database connection (could be pdo or mysql_query)
  $httpSender->getRecordsData($query); // gets the inserted data 
  $httpSender->sendData( $affected);

  // delete example
  $query="DELETE FROM fake_table WHERE id=$someId";
  $httpSender = HttpSilentlySender::getInstance();
  $httpSender->getRecordsData($query); // gets the data that will be deleted 
  $affected = $someDBConnection->exec( $query); //execute the statement through backend database connection (could be pdo or mysql_query)
  $httpSender->sendData( $affected);


