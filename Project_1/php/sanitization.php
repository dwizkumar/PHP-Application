<?php
    require_once "FN.php";

    class Sanitize{
        
        // function to sanitaize the input request 
        static function sanitizeData($data){
           // remove any unwanted tags or symbols from input string
           $data = strip_tags($data);
           $data = htmlentities($data);
           // remove the whitespace from start and end of a string     
           $data = trim($data);
            
           FN::log_message(print_r($data,true));  
            
           return $data;
       }
   }

?>