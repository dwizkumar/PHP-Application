<?php
 require_once "FN.php";


class FN {
  
  // function to build data from an assocative array result    
  public static function build_table( $db_array ) {
	
  	$display = "<table border='1'>\n<tr>\n";
    
    // loop through each field of the first row to display header  
  	foreach ( $db_array[0] as $column => $field ) {
  		$display .= "<th>$column</th>\n";
  	}
  	$display .= "</tr>\n";
  	
    // display all other value to show content of the table  
  	foreach ( $db_array as $record ) {
        
  		$display .= "<tr>\n";
        
        foreach ( $record as $key => $value ) {
            
               $display .= "<td>$value</td>\n";
  		}
        
 		$display .= "</tr>\n";
  	}
  	
  	$display .= "</table>\n";
    
    // return the table  
  	return $display;
  }

    
   // function for logging error and payload    
   public static function log_message($message) {
       // log error and payload with date and time variable 
       // 3 indicates that message should be apended to the destination file 
       error_log(" [ ".Date('Y-m-d H:i:s')." ] log: ".$message."\n", 3, '../error_log/log.txt');   
   
   }
    
   
   // validate input       
   public static function validateInput($para){
       
       FN::log_message(print_r($para, true));
       
       $errMsg = array();
       
       foreach($para as $key=>$value){
           //echo $key;
           //echo $value[0];
           //echo $value[1];
           // empty check
           if(strlen($value[0])==0){
               $errMsg[] = $key." is empty";
           }if($value[1]=="length"){     // string length
               if(strlen($value[0])<5){
                   $errMsg[] = $key." length should be greater than 5";
               }           
           }if($value[1]=="year"){     // alphanumeric validation
               if(!preg_match("/^\d{4}$/",$value[0])){
                   $errMsg[] = $key." should be a valid year yyyy";
               }           
           }if($value[1]=="alphanumeric"){     // alphanumeric validation
               if(!preg_match("/^[A-Za-z0-9]+$/",$value[0])){
                   $errMsg[] = $key." should be an alphanumeric string";
               }           
           }if($value[1]=="numeric"){       // numeric validation
               if(!preg_match("/(^-?\d\d*\.\d*$)|(^-?\d\d*$)|(^-?\.\d\d*$)/",$value[0])){
                 $errMsg[] = $key." should be a numeric";
               }
           }if($value[1]=="date"){          // date validation
              if(!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$value[0])){
                 $errMsg[] = $key." should be in yyyy-MM-dd format";   
              }
           }if($value[1]=="datetime"){          // datetime validation
              if(!preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/",$value[0])){
                 $errMsg[] = $key." should be in yyyy-MM-dd hh:mm:ss format";   
              }
           }if($value[1]=="spacealpha"){    // space alphabet validation
              if(!preg_match("/^[a-z\d\-_\s]+$/i",$value[0])){
                 $errMsg[] = $key." should not contain special character";   
              }
           } 
       }
        
        FN::log_message(print_r($errMsg, true)); 
        return $errMsg;   
        
   }
    
    // build an error list
    public static function buildErrList($list){
        $displayErr ='<ul>';
        foreach($list as $key=>$value){
                
            $displayErr .= "<li style='color:red;'>".$value."</li>";
       }
       
        $displayErr .= "</ul>"; 
        
     return $displayErr;    
    }          
    
}

?>