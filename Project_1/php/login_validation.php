<?php
   require_once "sanitization.php";
   require_once "DB.php";
   require_once "FN.php";
   
   // start the session
   session_start();

   $username = isset($_POST['username']) ? $_POST['username'] : '';
   $password = isset($_POST['password']) ? $_POST['password'] : '';
   
   // sanitize the username and password
   $username = Sanitize::sanitizeData($username);
   $password = Sanitize::sanitizeData($password);
   
   $db = new DB(); //create a new DB object
   
   // store the username in the session variable as loggedIn
   if(!isset($_SESSION['loggedIn'])) {
       $_SESSION['loggedIn'] = $username;
       //log session username
       FN::log_message($username." is loggedIn");
   }

   $flag = true;
   $message = array();
   
   // validate for empty username and password
   if( !isset($username) || empty($username) ){
       $flag = false;
       $message[] = 'Please enter username';
       // log user name empty message
       FN::log_message('Please enter username');
   }

   if( !isset($password) || empty($password) ){
       $flag = false;
       $message[] = 'Please enter password';
       FN::log_message('Please enter password');
   }
   
   // exceute the database query if username and password is not empty 
   if($flag){
       
         $data = $db->do_query("SELECT username, role, password, team FROM server_user WHERE username = :username",
                array(":username"),array($username),array(PDO::PARAM_STR));
         //print_r($data);
       
         if($data){
           // check if username and password matches with the database user  
           if( $username === $data[0]['username'] && password_verify($password, $data[0]['password'])){
               
               FN::log_message('Successful login for user: '.$username);
               // fetch the role of the user
               $role =  $db->do_query("SELECT name FROM server_roles WHERE id = :id",
                array(":id"),array($data[0]['role']),array(PDO::PARAM_INT));
 
               $flag = true;
               // store team and role name in the session 
               $message = $role[0]['name'];
               $_SESSION['team'] = $data[0]['team'];
               $_SESSION['role'] = $role[0]['name'];
               FN::log_message('User Role: '.$role[0]['name']);
           }
        }else{
           // store error message if validation fails  
           $flag = false;
           $message[] = 'Wrong username-password';
           FN::log_message('Wrong username-password for user'.$data[0]['username']); 
       }
   }
   
   // send the result in the json format
   echo json_encode(
     array(
         'flag' => $flag,
         'message' => $message
     )
   );
?>