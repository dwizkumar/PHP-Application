<?php
    session_start();  
    
    // redirect to login page if user is not logged in  
    if(!isset($_SESSION['loggedIn'])){
        header("Location: ../index.html");
    }elseif(isset($_GET['logout'])){
        // unset and destroy all the session variables
        session_unset();
        session_destroy();
        // redirect to the login page
        header("Location: ../index.html");
    }
?>
<!DOCTYPE html>
<html lang="en" >
<head>
  <meta charset="UTF-8">
  <title>Team Page</title>
  <script src='http://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>     
  <link rel="stylesheet" href="../css/style.css"> 
  <script src="../js/schedule.js"></script>    
</head>
<body>
 
<div class="header">
        <img class="ipl" src="../images/ipl.png" alt="ipl image">  
        <img class="epl" src="../images/epl.png" alt="epl image"> 
        <h1>Welcome to the <span style="color:#F68A1F;">Schedule Page</span></h1> 
</div>     
<div id='cssmenu'>
  <ul>
    <li>
        <?php
            if(isset($_SESSION['role'])){
               // admin tab appears if the user is not a parent
               if(strtolower($_SESSION['role']) != 'parent'){
                  echo "<a href='Admin.php'>Admin</a>";
               }
            }   
        ?>    
    </li>
    <li><a href='Team.php'>Team</a></li>
    <!-- make the schedule tab as a current page-->   
    <li class='active'><a href='#'>Schedule</a></li>
    <li style="float:right; margin-right:4px;"><a href='Team.php?logout=true'>Logout</a></li>
 </ul>
</div>     
<div class="sdul">
    <h1>Team's Schedule</h1><br/>    
</div>  
</body>
</html>