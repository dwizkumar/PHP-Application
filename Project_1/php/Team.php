<?php
    session_start();  
     
    // redirect the user to login page if not already loggedin
    if(!isset($_SESSION['loggedIn'])){
        header("Location: ../index.html");
    }elseif(isset($_GET['logout'])){ 
        //unset and destroy all the session variables after user logs out
        session_unset();
        session_destroy();
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
  <script src="../js/team.js"></script>    
</head>
<body>
 
<div class="header">
        <img class="ipl" src="../images/ipl.png" alt="ipl image">  
        <img class="epl" src="../images/epl.png" alt="epl image"> 
        <h1>Welcome to the <span style="color:#F68A1F;">Team Page</span></h1>
</div>
<div id='cssmenu'>
  <ul>
    <li>
        <?php
            if(isset($_SESSION['role'])){
               //display admin tab if user is not a parent    
               if(strtolower($_SESSION['role']) != 'parent'){
                  echo "<a href='Admin.php'>Admin</a>";
               }
            }   
        ?>    
    </li>
    <!--mark team as an active page-->  
    <li class='active'><a href='#'>Team</a></li>
    <!--redirect to the schedule page if schedule tab is pressed-->  
    <li><a href='Schedule.php'>Schedule</a></li>
    <li style="float:right; margin-right:4px;"><a href='Team.php?logout=true'>Logout</a></li>
 </ul>
</div>  
<div class="team">
    <h1>Teams</h1>
    <br/>
</div>   
</body>
</html>