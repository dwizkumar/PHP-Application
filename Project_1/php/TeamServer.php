<?php
    require_once "DB.php";
    require_once "FN.php";
    session_start();  
 
    $db = new DB();
    
    // store the role, the team, and the username
    if(isset($_SESSION['role'])){
       $role = strtolower($_SESSION['role']);    
    }
    
    if(isset($_SESSION['team'])){
       $team = strtolower($_SESSION['team']);    
    }
     
    if(isset($_SESSION['loggedIn'])){
      $username = strtolower($_SESSION['loggedIn']);
      FN::log_message($username." is accessing team page");     
    }

    // query the team table to fetch related record
    if($role == 'admin'){
        $data = $db->do_query("SELECT tea.name, tea.mascot, spo.name AS sport, leg.name AS league, sea.year, sea.description,               picture, homecolor, awaycolor, maxplayers  
                FROM server_team AS tea
                INNER JOIN
                server_sport AS spo	
                ON tea.sport = spo.id
                INNER JOIN server_league AS leg
                ON tea.league = leg.id
                INNER JOIN server_season AS sea
                ON tea.season = sea.id",
                array(),array(),array());
    }else{
        $data = $db->do_query("SELECT tea.name, tea.mascot, spo.name AS sport, leg.name AS league, sea.year, sea.description,               picture, homecolor, awaycolor, maxplayers  
                FROM server_team AS tea
                INNER JOIN
                server_sport AS spo	
                ON tea.sport = spo.id
                INNER JOIN server_league AS leg
                ON tea.league = leg.id
                INNER JOIN server_season AS sea
                ON tea.season = sea.id
                WHERE tea.id = (SELECT team FROM server_user WHERE username = :username AND role NOT IN (1,2))
                UNION
                SELECT tea.name, tea.mascot, spo.name AS sport, leg.name AS league, sea.year, sea.description, picture, homecolor, awaycolor, maxplayers  
                FROM server_team AS tea
                INNER JOIN
                server_sport AS spo	
                ON tea.sport = spo.id
                INNER JOIN server_league AS leg
                ON tea.league = leg.id
                INNER JOIN server_season AS sea
                ON tea.season = sea.id
                WHERE leg.id = (SELECT league FROM server_user WHERE username = :username1 AND role = 2)",
                array(":username",":username1"),array($username,$username),                     array(PDO::PARAM_STR,PDO::PARAM_STR));
    }
    
    // logged json data for the team
    FN::log_message(print_r(json_encode($data),true));
    // send the result in the json format
    echo (json_encode($data));
   
?>