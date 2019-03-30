<?php
    require_once "DB.php";
    require_once "FN.php";
    session_start();  
 
    $db = new DB();
    
    // store the role, the team and the username 
    if(isset($_SESSION['role'])){
       $role = strtolower($_SESSION['role']);    
    }
    
    if(isset($_SESSION['team'])){
       $team = strtolower($_SESSION['team']);    
    }
     
    if(isset($_SESSION['loggedIn'])){
      $username = strtolower($_SESSION['loggedIn']);
      FN::log_message($username." is accessing schedule page");    
    }
    
    // query database to get the schedule details
    if($role == 'admin'){
         $data = $db->do_query("SELECT spo.name as sport,leg.name as league, sea.description, tea.name as hometeam, tea1.name as             awayteam, sch.homescore, sch.awayscore, sch.scheduled, sch.completed from 
                 server_schedule as sch
                 inner join
                 server_sport as spo
                 on spo.id =  sch.sport
                 inner join server_league as leg
                 on sch.league = leg.id
                 inner join server_season as sea
                 on sch.season = sea.id
                 inner join server_team as tea
                 on sch.hometeam = tea.id 
                 inner join server_team as tea1
                 on sch.awayteam = tea1.id",array(),array(),array());

    }else if($role == 'league manager'){
        $data = $db->do_query("SELECT spo.name as sport,leg.name as league, sea.description, tea.name as hometeam, tea1.name as             awayteam, sch.homescore, sch.awayscore, sch.scheduled, sch.completed from 
                server_schedule as sch
                inner join
                server_sport as spo
                on spo.id =  sch.sport
                inner join server_league as leg
                on sch.league = leg.id
                inner join server_season as sea
                on sch.season = sea.id
                inner join server_team as tea
                on sch.hometeam = tea.id 
                inner join server_team as tea1
                on sch.awayteam = tea1.id
                where leg.id = (select league from server_user where username= :username)",array(":username"),array($username),array(PDO::PARAM_STR)); 
    
    }else{
        
         $data = $db->do_query("select spo.name as sport,leg.name as league, sea.description, tea.name as hometeam, tea1.name as             awayteam, sch.homescore, sch.awayscore, sch.scheduled, sch.completed from 
                 server_schedule as sch
                 inner join
                 server_sport as spo
                 on spo.id =  sch.sport
                 inner join server_league as leg
                 on sch.league = leg.id
                 inner join server_season as sea
                 on sch.season = sea.id
                 inner join server_team as tea
                 on sch.hometeam = tea.id 
                 inner join server_team as tea1
                 on sch.awayteam = tea1.id
                 where sch.hometeam = (select team from server_user where username=:username)
                 or
                 sch.awayteam = (select team from server_user where username=:username1)",array(":username",":username1"),array($username,$username),array(PDO::PARAM_STR,PDO::PARAM_STR));         
    }

     // logged json data for the team
    FN::log_message(print_r(json_encode($data),true));
    
    //send the result in the json format
    echo (json_encode($data));
   
?>