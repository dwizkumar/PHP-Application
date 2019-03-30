<?php
 
  require_once "FN.php";

class DB {
    
  private $dbh;
  private $error;         //error
  private $results = array(); //results of query 

   // constructor to check database connection
  function __construct() {
    require_once ("db_conn.php");
    
    try {
      //open a connection
      $this->dbh = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
      
      //change error reporting
      $this->dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);		
    } 
    catch (PDOException $e) {
      FN::log_message($e->getMessage());
      //echo $e->getMessage();
      die();			
    }
  }	

   
    // query to all the database operations
   function do_query( $query, $names = array(), $vars=array(), $types=array() ) {
    
    //determine which type of query: select, insert, update, delete
    $select = false;
    $delete = false;
    $insert = false;
    $update = false;
    $field_cnt = 0;
    $this->results = array();
    $this->error = "";
    $this->colum_info = array();
       
    //first get the command and convert to lower case
    $command = strtolower( substr( trim( $query ), 0, strpos( $query, " " ) ) );
    switch ( $command ) {
      case "select": 
        $select = true;
        break;
      case "insert": 
        $insert = true;
        break;
      case "update": 
        $update = true;
        break;
      case "delete": 
        $delete = true;
        break;			
     }

     // data integrity check: ensure that the number of parameters and specified data types matches the query
    if ( substr_count( $query, ":" ) != count( $vars ) || count( $vars ) != count( $types ) || count( $names ) != count( $vars ) ) {
      $this->error = "Wrong number of parameters for query";
      FN::log_message($this->error);
      //return $this->error;
    }else{
        
            try{
                // create a prepare statement
                $stmt = $this->dbh->prepare($query);
                
                // bind all the parameters to the values and types
                for($i=0 ; $i < count( $vars ) ; $i++){
                   $stmt->bindParam($names[$i],$vars[$i],$types[$i]);    
                }
                
                // run the PDO query to produce result
                $stmt->execute();
                
                // fetch the resultset in case of select 
                if($select){
                    
                    // use fetch_assoc to fetch only associative array list
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
                        $this->results[] = $row;
                    }
                }
           }catch(PDOException $e){  // catch all exception
               //echo $e->getMessage();
                
                // log the exception messages into a log file
                FN::log_message($e->getMessage());
               die();   
            }
    }
    return (count($this->results)>0)? $this->results : '';   // return the result if present    
   }
    
} //class
?>