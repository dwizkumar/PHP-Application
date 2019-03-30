<?php
    ob_start();
    require_once "DB.php";
    require_once "sanitization.php";
    require_once "FN.php";
    error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
     
    // staring session
    session_start(); 

    $db = new DB();
    
    // redirect the user to login page if not already logged in
    if(!isset($_SESSION['loggedIn'])){
        header("Location: ../index.html");
    } 
    
    // unset and destroy all session variables if user logs out
    if(isset($_GET['logout'])){
        FN::log_message("logout the user: ".$_SESSION['loggedIn']);
        session_unset();
        session_destroy();
        header("Location: ../index.html");
    } 

    // redirect the user to the login page if users' role is parent
    if(isset($_SESSION['loggedIn']) ){
        if(strtolower($_SESSION['role']) == 'parent'){
           header("Location: ../index.html");
        }    
    } 
?>

<!DOCTYPE html>
<html lang="en" >
<head>
  <meta charset="UTF-8">
  <title>Admin Page</title>
  <script src='http://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>     
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
 
<div class="header">
    <img class="ipl" src="../images/ipl.png" alt="ipl image">  
    <img class="epl" src="../images/epl.png" alt="epl image">
    <h1 style="font-family:'Open Sans', sans-serif;font-weight: bold; color:white;">Welcome to the <span style="color:#F68A1F;">Admin Page</span></h1>    
</div>   
<div id='cssmenu'>
  <ul>
    <li class='active'><a href='#'>Admin</a></li>
    <li><a href='Team.php'>Team</a></li>
    <li><a href='Schedule.php'>Schedule</a></li>
    <li style="float:right; margin-right:4px;"><a href='Admin.php?logout=true'>Logout</a></li>
 </ul>
</div>       
</body>
</html>

<?php    

    // store the role, the team, and the username fields 
    if(isset($_SESSION['role'])){
       $role = strtolower($_SESSION['role']);   
    }
    
    if(isset($_SESSION['team'])){
       $team = strtolower($_SESSION['team']);     
    }
     
    if(isset($_SESSION['loggedIn'])){
      $username = strtolower($_SESSION['loggedIn']);    
    }
    
    // allow the following operations is the user is an admin or a coach or a team manager 
    if($role == 'admin' || $role == 'coach' || $role =='team manager' )
    {
         echo "<div class='wrap'>";    
        
         // performing add and view players position
         FN::log_message("Start of player position section");
         // fetch position name from position table
         $viewpos = $db->do_query("SELECT spos.name as PositionName
                    FROM server_position AS spos",array(),array(),array());
         
        // build view table by calling function build_table   
         echo  "<div class='viewpos'><h2>Player's Position</h2>".FN::build_table($viewpos)."</div>";
        
         // create a form to add a new position into the table
         echo '<div class="addpos"><h2>Add Position</h2><form method="post" action=""><br/>PositionName: <input name="positionName"/><br/><br/><input type="submit" class="addpos-button" name="submitpos" value="Add Position"/>
             </form></div>';
         
         // add position to submit the position
         if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submitpos'])){
               
               $positionName = isset($_POST['positionName']) ? $_POST['positionName'] : '';
               
               // sanitize the position name
               $positionName = Sanitize::sanitizeData($positionName);
               
               // validate position name
               $param = array( 
                            "Position name" => array($positionName,"alphanumeric")
                         );    
               
               $result = FN::validateInput($param);
               
                // check if any error message present
                if(sizeof($result)>0){
                    echo "<div>".FN::buildErrList($result)."</div>";
                }
               
               // insert position in the server_position table if not empty
               else{
                
                   $addpos =  $db->do_query("INSERT into server_position(name) VALUES(:name)", array(":name"),
        			   array($positionName),array(PDO::PARAM_STR));
                   
                   FN::log_message($positionName." position of player added successfully...");
                   // refresh page to display the added value
                   header("Location: Admin.php");
               }
               FN::log_message("End of player position section...");
            }
        
         echo "</div><div class='banner'></div>";
        
         echo "<div class='wrap'>";
        
         // performing view, add, edit, and delete players
         if($role == 'admin'){
             
            FN::log_message("Start of player section...");
             
            $viewplayer = $db->do_query("SELECT ply.id AS Id ,ply.firstname AS FirstName, ply.lastname AS LastName, ply.dateofbirth               AS DateofBirth, ply.jerseynumber AS JerseyNumber, tea.name AS Team
                          FROM server_player AS ply
                          INNER JOIN server_team AS tea
                          ON ply.team = tea.id",array(),array(),array()); 
          }else{
             
            $viewplayer = $db->do_query("SELECT ply.id AS Id ,ply.firstname AS FirstName, ply.lastname AS LastName, ply.dateofbirth               AS DateofBirth, ply.jerseynumber AS JerseyNumber, tea.name AS Team
                          FROM server_player AS ply
                          INNER JOIN server_team AS tea
                          ON ply.team = tea.id
                          WHERE ply.team = (SELECT team from server_user WHERE username=:username)",array(":username"),array($username),array(PDO::PARAM_STR)); 
          }
          echo "<div class='viewplayer'><h2>Player's List</h2><form method='POST' action=''>";
          
          // add a radio button to the table and make all other field as editable
          foreach($viewplayer as &$record){
              
                $id = $record["Id"];
                $firstname = $record["FirstName"];
                $lastname =  $record["LastName"];
                $dateofbirth = $record["DateofBirth"];
                $jerseynumber = $record["JerseyNumber"];
                
                $record["Id"] = "<input type='radio' name='Id' value= ".$id.">";
                $record["FirstName"] = "<input type='text' name='firstname".$id."' value=".$firstname.">";
                $record["LastName"] = "<input type='text' name='lastname".$id."' value=".$lastname.">";
                $record["DateofBirth"] = "<input type='text' name='dateofbirth".$id."' value=".$dateofbirth.">";
                $record["JerseyNumber"] = "<input type='text' name='jerseynumber".$id."' value=".$jerseynumber.">";
              
             }
           
           // build the table
           echo  FN::build_table($viewplayer);
           
           // buttons to delete and edit players
           echo  "<br/><input type='submit' class='delplayer-button' name='delplayer' value='Delete Player'/>&nbsp;&nbsp;<input type='submit' class='editplayer-button' name='editplayer' value='Edit Player'/></form></div>";   
        
          // delete player
          if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delplayer']) && isset($_POST['Id'])){
                
               // fetch the id from the request form
               $id = $_POST['Id'];
                
               // delete query to delete the player from the server_player table
               $delply = $db->do_query("DELETE FROM server_player WHERE id = :id", array(":id"),array($id), 
                          array(PDO::PARAM_INT));
               
                FN::log_message("Successfully deleted player id: ".$id);
               // refresh the page
               header("Location: Admin.php");
           }
          
           // edit player
           if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editplayer']) && isset($_POST['Id'])){
                
               // fetch firstname, lastname, dob, and jersey number of the specific player by using filtering 
               // technique
               $id = $_POST['Id'];
               $firstname = $_POST['firstname'.$id];
               $lastname = $_POST['lastname'.$id];
               $dateofbirth = $_POST['dateofbirth'.$id];
               $jerseynumber = $_POST['jerseynumber'.$id];
               
               // removing unnecessary characters from inputs
               $firstname = Sanitize::sanitizeData($firstname);
               $lastname = Sanitize::sanitizeData($lastname);
               $dateofbirth = Sanitize::sanitizeData($dateofbirth);
               $jerseynumber = Sanitize::sanitizeData($jerseynumber);
               
               // checking firstname, lastname, dateofbirth, jerseyno
               $param = array( 
                            "FirstName" => array($firstname,"alphanumeric"),
                            "LastName" => array($lastname,"alphanumeric"),
                            "Dob" => array($dateofbirth,"date"),
                            "Jerseyno" => array($jerseynumber,"numeric")
                         );    
               // call input validator function
               $result = FN::validateInput($param);
               
                // check for error array
                if(sizeof($result)>0){
                    echo "<div>".FN::buildErrList($result)."</div>";
                }

               
               // allow update only if value is not empty
               else{
                   $editply = $db->do_query("UPDATE server_player SET firstname = :firstname, lastname = :lastname, dateofbirth = :dateofbirth, jerseynumber = :jerseynumber where id = :id", array(":firstname",":lastname", ":dateofbirth","jerseynumber",":id"),array($firstname,$lastname,$dateofbirth,$jerseynumber,$id), array(PDO::PARAM_STR,PDO::PARAM_STR,PDO::PARAM_STR,PDO::PARAM_STR,PDO::PARAM_INT));
               
                   FN::log_message("Sucessfully edited player with id: ".$id);
                   
                   // refresh the page   
                   header("Location: Admin.php");
               }
               
           }
          
          // create add player form to take users input for adding a new player 
          $fetchplayer = $db->do_query("SELECT tea.id AS TeamId, tea.name AS TeamName
                         FROM server_team AS tea
                         INNER JOIN server_user AS user
                         ON tea.id = user.team
                         WHERE user.username = :username AND user.role in (3,4,5)
                         UNION
                         SELECT tea.id AS TeamId, tea.name AS TeamName
                         FROM server_team AS tea, server_user AS user
                         WHERE user.username = :username1 AND user.role in (1)",
                         array(":username",":username1"),array($username,$username),                     array(PDO::PARAM_STR,PDO::PARAM_STR));
           
           //user interface for add player
           echo '<div class="addplayer"><h2>Add Player</h2><form method="post" action=""><br/>
                FirstName: <input name="firstname"><br/>
                Last Name: <input name="lastname"/><br/>
                Date of birth: <input name="dateofbirth"/><br/>
                Jersey Number: <input name="jerseynumber"/><br/>
                Team Name: <select class= "custom-select" name="teamname">';
                   // create a select dropdown
                   foreach($fetchplayer as $fetchrecord){
                        echo '<option value='.$fetchrecord["TeamId"].'>'.$fetchrecord["TeamName"].'</option><br/>';
                    }
            
            echo '</select><br/><br/><input type="submit" class="addplayer-button" name="submitplayer" value="Add Player"/>
                 </form></div>';   
         
          // handle add player request 
          if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submitplayer'])){
              
               // receives the user request and sanitize it     
               $firstname = isset($_POST['firstname']) ? $_POST['firstname'] : '';
               $lastname = isset($_POST['lastname']) ? $_POST['lastname'] : '';
               $dob = isset($_POST['dateofbirth']) ? $_POST['dateofbirth'] : '';
               $jerseynumber = isset($_POST['jerseynumber']) ? $_POST['jerseynumber'] : '';
               $teamname = isset($_POST['teamname']) ? $_POST['teamname'] : '';    
              
               $firstname = Sanitize::sanitizeData($firstname);
               $lastname = Sanitize::sanitizeData($lastname);
               $dob = Sanitize::sanitizeData($dob);
               $jerseynumber = Sanitize::sanitizeData($jerseynumber);
               $teamname = Sanitize::sanitizeData($teamname);
               
               // validate input: firstname, lastname, dob, jersey no, and team name
               $param = array( 
                            "FirstName" => array($firstname,"alphanumeric"),
                            "LastName" => array($lastname,"alphanumeric"),
                            "Dob" => array($dob,"date"),
                            "Jerseyno" => array($jerseynumber,"numeric"),
                            "TeamName" => array($teamname,"alphanumeric")
                         );    
               
                $result = FN::validateInput($param);
                
                // build list of error 
                if(sizeof($result)>0){
                    echo "<div>".FN::buildErrList($result)."</div>";
                }
              
                else{
                   // insert player's information in server_player table 
                   $addplayer = $db->do_query("INSERT into server_player(firstname, lastname, dateofbirth, jerseynumber, team ) VALUES(:firstname, :lastname, :dateofbirth, :jerseynumber, :team)", array(":firstname",":lastname", ":dateofbirth", ":jerseynumber", ":team"),array($firstname,$lastname,$dob,$jerseynumber, $teamname),array(PDO::PARAM_STR, PDO::PARAM_STR, PDO::PARAM_STR, PDO::PARAM_STR, PDO::PARAM_INT));

                    FN::log_message("Successfully added a new player: ".$firstname." ".$lastname);
                   // refresh the page
                   header("Location: Admin.php");
                }
              FN::log_message("End of player section");
          }

         echo "</div><div class='banner'></div>";
           
    }
    
    // for the role of admin and league manager
    if($role == 'admin' || $role == 'league manager' ){
          
           FN::log_message("Start of season section");   
          // performing add, edit, update, and delete operation on season
          $viewseason = $db->do_query("SELECT sea.id AS SeasonId, sea.year AS Year, sea.description AS Description FROM server_season AS sea",array(),array(),array()); 
        
          echo "<div class='wrap'>";
        
          echo "<div class='viewseason'><h2>Season's List</h2><form method='POST' action=''>";
          
           // loop through all seasons and create a form
          foreach($viewseason as &$record){
              
                $seasonid = $record["SeasonId"];
                $year = $record["Year"];
                $description =  $record["Description"];
                
                $record["SeasonId"] = "<input type='radio' name='seasonid' value= ".$seasonid.">";
                $record["Year"] = "<input type='text' name='year".$seasonid."' value=".$year.">";
                $record["Description"] = "<input type='text' name='description".$seasonid."' value='".$description."'>";
              
             }
          
          // build table from query resultset
          echo  FN::build_table($viewseason);
          echo  "<br/><input type='submit' class='delseason-button' name='delseason' value='Delete Season'/>&nbsp;&nbsp;<input type='submit' class='editseason-button' name='editseason' value='Edit Season'/></form></div>";   
        
          // delete season
          if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delseason']) && isset($_POST['seasonid'])){
       
               $id = $_POST['seasonid'];
               
               // delete the season based on the season id
               $delseason = $db->do_query("DELETE FROM server_season WHERE id = :id", array(":id"),array($id), 
                          array(PDO::PARAM_INT));
              
               FN::log_message("Successfully deleted session with id: ".$id);
               // refresh the page
               header("Location: Admin.php");
           }
          
          // edit season
          if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editseason']) && isset($_POST['seasonid'])){
                
               // fetch the correct user data and sanitize it 
               $id = $_POST['seasonid'];
               $year = $_POST['year'.$id];
               $description = $_POST['description'.$id];

               $year = Sanitize::sanitizeData($year);
               $description = Sanitize::sanitizeData($description);
               
               // validate parameters year as a yyyy-MM-dd format
               // description length >=5
               $param = array( 
                            "Year" => array($year,"year"),
                            "Description" => array($description,"length")
                         );    
               
                $result = FN::validateInput($param);
                
                // check for the array size
                if(sizeof($result)>0){
                    echo "<div>".FN::buildErrList($result)."</div>";
                }
              
               // update the season only if year and description are empty
               else{
                   $editseason = $db->do_query("UPDATE server_season SET year = :year, description = :description where id = :id", array(":year",":description",":id"),array($year,$description,$id), array(PDO::PARAM_INT,PDO::PARAM_STR,PDO::PARAM_INT));
                   
                    FN::log_message("Successfully edited session having id: ".$id);
                   // refresh the page
                   header("Location: Admin.php");
               }   
           }
        
         // add a season
         echo '<div class="addseason"><h2>Add Season</h2><form method="post" action=""><br/>Season year: <input name="year"><br/>Season description: <input name="description"/><br/><br/><input type="submit" class="addseason-button" name="submitseason" value="Add Season"/>
             </form></div>';   
         
          if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submitseason'])){
              
               // store the season input inserted by the user and sanitize it
               $year = isset($_POST['year']) ? $_POST['year'] : '';
               $description = isset($_POST['description']) ? $_POST['description'] : '';
               
               $year = Sanitize::sanitizeData($year);
               $description = Sanitize::sanitizeData($description);
               
               //validate year, description field
               $param = array( 
                            "year" => array($year,"year"),
                            "description" => array($description,"length")
                         );    
               
               $result = FN::validateInput($param);
               
               if(sizeof($result)>0){
                    echo "<div>".FN::buildErrList($result)."</div>";
               }
               
              else{
                   // insert the season data in the season table
                   $addseason = $db->do_query("INSERT into server_season(year, description ) VALUES(:year, :description)", array(":year",":description"),array($year,$description),array(PDO::PARAM_INT, PDO::PARAM_STR));
                   
                    FN::log_message("Successfully added a new season");
                   // refresh the page to reflect the changes
                   header("Location: Admin.php");
              }
              
              FN::log_message("End of season section");
          }
        
         echo "</div><div class='banner'></div>";
        
         echo "<div class='wrap'>";
         
          FN::log_message("Start of sport, league, season combination section");
         // performing operations on sport, league, and season   
         $viewslseason = $db->do_query("SELECT concat(sls.sport, '_', sls.league,'_',sls.season) AS SlseasonId, spo.name AS Sport,          lea.name AS League, concat(sea.year,'_',sea.description) AS Year
                FROM server_slseason sls
                INNER JOIN server_sport AS spo 
                ON sls.sport = spo.id
                INNER JOIN server_league AS lea
                ON sls.league = lea.id
                INNER JOIN server_season AS sea 
                ON sls.season = sea.id",array(),array(),array()); 
          
           //fetch sports name, league name, and description necessary to build select dropdown
          $slsportname = $db->do_query("SELECT spo.name AS Sport FROM server_sport AS spo",array(),array(),array());
            
          $slleaguename = $db->do_query("SELECT leg.name AS League FROM server_league AS leg",array(),array(),array());  

          $slyeardesc = $db->do_query("SELECT concat(sea.year,'_',sea.description) AS Year FROM server_season AS sea", array(),                 array(),array());
          
          echo "<div class='viewslseason'><h2>Sports-Season List</h2><form method='POST' action=''>";
          
          // build form table to edit 
          foreach($viewslseason as &$record){
              
                $slseasonid = $record["SlseasonId"];
                $slsport = $record["Sport"];
                $slleague =  $record["League"];
                $slyear = $record["Year"];
                
                 // bild dropdowns for sport name, league name, and concatenated value of year & description 
                $record["SlseasonId"] = "<input type='radio' name='slseasonid' value= ".$slseasonid.">";
                $record["Sport"] = "<select name='slsport".$slseasonid."'>";
                    foreach($slsportname as &$spname){
                             
                             // match the current value with the fetched sportname and mark selected for the
                             // matching value
                            foreach($spname as $key=>$value){
                                if($slsport === $value){
                                    $record["Sport"] .= "<option value='".$value."' selected>".$value."</option>";
                                }else{
                                    $record["Sport"] .= "<option value='".$value."'>".$value."</option>";
                                }
                            }
                         }
                                      
                $record["Sport"] .= "</select>";             
                $record["League"] = "<select name='slleague".$slseasonid."'>";
                        
                         foreach($slleaguename as &$lgname){
                             // match the current value with the fetched leaguename and mark selected for the
                             // matching value
                            foreach($lgname as $key=>$value){
                                if($slleague === $value){
                                    $record["League"] .= "<option value='".$value."' selected>".$value."</option>";
                                }else{
                                    $record["League"] .= "<option value='".$value."'>".$value."</option>";
                                }
                            }
                         }
                                      
                $record["League"] .= "</select>";
                $record["Year"] = "<select name='slyear".$slseasonid."'>";
                             // match the current value with the fetched year description field and mark selected for the
                             // matching value
                         foreach($slyeardesc as &$slyr){
                             
                            foreach($slyr as $key=>$value){
                                if($slyear === $value){
                                    $record["Year"] .= "<option value='".$value."' selected>".$value."</option>";
                                }else{
                                    $record["Year"] .= "<option value='".$value."'>".$value."</option>";
                                }
                            }
                         }
                                      
                $record["Year"] .= "</select>";              
             }
        
         // build a table with the selected dropdown values
         echo  FN::build_table($viewslseason);
        
         echo  "<br/><input type='submit' class='delslseason-button' name='delslseason' value='Delete Slseason'/>&nbsp;&nbsp;<input type='submit' class='editslseason-button' name='editslseason' value='Edit Slseason'/></form></div>";   
        
        // edit slseason for the request
        if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editslseason']) && isset($_POST['slseasonid'])){
               
               // fetch the season data based on the row selected and sanitize the input 
               $slseasonid = $_POST['slseasonid'];
               $slsport = $_POST['slsport'.$slseasonid];
               $slleague = $_POST['slleague'.$slseasonid];
               $slyear = $_POST['slyear'.$slseasonid];
               
               $slsport = Sanitize::sanitizeData($slsport);
               $slleague = Sanitize::sanitizeData($slleague);
               $slyear = Sanitize::sanitizeData($slyear);
               
               // Build an array to validate input 
               $param = array( 
                            "Sport" => array($slsport,"alphanumeric"),
                            "League" => array($slleague,"spacealpha"),
                            "Description" => array($slyear,"length")
                         );    
            
                // call a function to validate
                $result = FN::validateInput($param);
               
                if(sizeof($result)>0){
                    echo "<div>".FN::buildErrList($result)."</div>";
                }
            
               // empty check for sports, league, year, and description
               else{
                   
                   // split the primary key to filter out sportId, leagueId, and seasonId field
                   //$slseasonid =  '1_201_101';
                   $arr = explode("_", $slseasonid, 3);
                   $sportid = $arr[0];
                   $leagueid = $arr[1];
                   $seasonid = $arr[2];
                   
                   //echo $sportid;
                   //echo $leagueid;
                   //echo $seasonid;
                   
                   // fetch sport id, league id, and the season id to update the season details 
                   $getslsportsId = $db->do_query("SELECT spo.id AS id FROM server_sport AS spo WHERE spo.name = :name                                ",array(":name"),array($slsport),array(PDO::PARAM_STR));
                   //echo $getslsportsId[0]['id'];
                   
                   $getslleagueId = $db->do_query("SELECT leg.id AS id FROM server_league AS leg WHERE leg.name = :name                              ",array(":name"),array($slleague),array(PDO::PARAM_STR));
                   //echo $getslleagueId[0]['id'];
                   
                   $getslseasonId = $db->do_query("SELECT sea.id AS id FROM server_season AS sea WHERE                                                concat(sea.year,'_',sea.description) = :name                                                                      ",array(":name"),array($slyear),array(PDO::PARAM_STR));
                   //echo $getslseasonId[0]['id'];
                   
                   // update season detail based on the season season id
                   $updslseason = $db->do_query("UPDATE server_slseason SET sport = :sport, league = :league, season = :season 
                                     WHERE sport = :sport1 AND league = :league1 AND season = :season1",                   array(":sport",":league",":season",":sport1",":league1",":season1"),array($getslsportsId[0]['id'],$getslleagueId[0]['id'],$getslseasonId[0]['id'],$sportid,$leagueid,$seasonid), array(PDO::PARAM_INT,PDO::PARAM_INT,PDO::PARAM_INT,PDO::PARAM_INT,PDO::PARAM_INT,PDO::PARAM_INT));
                   
                    FN::log_message("Successfully updated sport: ".$getslsportsId[0]['id']." league: ".$getslleagueId[0]['id']." season: ".$getslseasonId[0]['id']);
                   
                   // refresh the page
                   header("Location: Admin.php");
               }
               
           }
        
          // delete sport, league, and season details
          if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delslseason']) && isset($_POST['slseasonid'])){
                
               // fetch the primary attribute
               $delslseasonid = $_POST['slseasonid'];
               
               // break it to get sports id, league id, and season id field 
               $arr = explode("_", $delslseasonid, 3);
               $sportid = $arr[0];
               $leagueid = $arr[1];
               $seasonid = $arr[2];
              
               //echo $sportid;
               //echo $leagueid;
               //echo $seasonid;
                 
               // delete season based on  season id
               $delseason = $db->do_query("DELETE FROM server_slseason WHERE sport = :sport AND league = :league AND season =                    :season", array(":sport",":league",":season"),array($sportid,$leagueid,$seasonid), 
                            array(PDO::PARAM_INT,PDO::PARAM_INT,PDO::PARAM_INT));
                
               FN::log_message("Successfully deleted sport id: ".$sportid." league id: ".$leagueid." season id: ".$seasonid);
              
               header("Location: Admin.php");
           }
        
          
          // add sports, league and season to their respective tables 
          echo '<div class="addslseason"><h2>Add Sport-Season</h2>
                <form method="post" action=""><br/>
                Sport: <select class= "custom-select" name="slsport">';
                    // make dropdowns of sport, league, year, 
                    // and description based on the user's permission
                    foreach($slsportname as &$fetchspo){
                        
                        foreach($fetchspo as $key=>$value){
                            echo "<option value='".$value."'>".$value."</option>";
                        }
                    }

          echo '</select><br/>League: <select class= "custom-select" name="slleague">';
            
                    foreach($slleaguename as &$fetchleg){

                        foreach($fetchleg as $key=>$value){
                            echo "<option value='".$value."'>".$value."</option>";
                         }
                    }
                
           echo '</select><br/> Year: <select class= "custom-select" name="slyear">';
            
                    foreach($slyeardesc as &$fetchyear){

                        foreach($fetchyear as $key=>$value){
                            echo "<option value='".$value."'>".$value."</option>";
                         }
                    }
                
           echo '</select><br/><br/>';  
            
           echo '<input type="submit" class="addslseason-button" name="submitslseason" value="Add Slseason"/>
                </form></div>';   
          
           // if submit button is pressed
          if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submitslseason'])){
              
               // fetch and sanitize the post data
               $slsport = isset($_POST['slsport']) ? $_POST['slsport'] : '';
               $slleague = isset($_POST['slleague']) ? $_POST['slleague'] : '';
               $slyear = isset($_POST['slyear']) ? $_POST['slyear'] : '';
              
               $slsport = Sanitize::sanitizeData($slsport);
               $slleague = Sanitize::sanitizeData($slleague);
               $slyear = Sanitize::sanitizeData($slyear);
               
               // validate sport, league, and year-description field
               $param = array( 
                            "Sport" => array($slsport,"alphanumeric"),
                            "League" => array($slleague,"spacealpha"),
                            "Description" => array($slyear,"length")
                         );    
               
                $result = FN::validateInput($param);
               
                if(sizeof($result)>0){
                    echo "<div>".FN::buildErrList($result)."</div>";
                }else{
               
                   // fetch the newly inserted sport ids based on names, year and description
                    $getslsportsId = $db->do_query("SELECT spo.id AS id FROM server_sport AS spo WHERE spo.name = :name                                ",array(":name"),array($slsport),array(PDO::PARAM_STR));
                    //echo $getslsportsId[0]['id'];

                    $getslleagueId = $db->do_query("SELECT leg.id AS id FROM server_league AS leg WHERE leg.name = :name                              ",array(":name"),array($slleague),array(PDO::PARAM_STR));
                    //echo $getslleagueId[0]['id'];

                    $getslseasonId = $db->do_query("SELECT sea.id AS id FROM server_season AS sea WHERE                                                concat(sea.year,'_',sea.description) = :name                                                                      ",array(":name"),array($slyear),array(PDO::PARAM_STR));

                    //echo $getslseasonId[0]['id'];
                    // insert the data into associated table
                   $addslseason = $db->do_query("INSERT into server_slseason(sport, league, season) VALUES(:sport, :league,                        :season)", array(":sport",":league", ":season"),array($getslsportsId[0]['id'],
                                  $getslleagueId[0]['id'],$getslseasonId[0]['id']),array(PDO::PARAM_INT, PDO::PARAM_INT, PDO::PARAM_INT));
                   
                    FN::log_message("Successfully added sport: ".$getslsportsId." league: ".$getslleagueId." season: ".$getslseasonId);
                    
                   // refresh the page 
                   header("Location: Admin.php");
                }
                
              FN::log_message("End of sport, league,and season combined section");
          }
          
          echo "</div><div class='banner'></div>";
          
          echo "<div class='wrap'>";
        
          // performing add, edit, delete operation on team        
          if($role == 'admin'){
            
             FN::log_message("Start of team section");
              
            // fetch information about teams for a admin user 
            $viewteam = $db->do_query("SELECT tea.id AS TeamId, tea.name AS TeamName, tea.mascot AS Mascot, spo.name AS Sport,                   leg.name AS League, concat(sea.year,'_',sea.description) AS Year, tea.homecolor AS Homecolor, tea.awaycolor             AS Awaycolor, tea.maxplayers AS Maxplayers, tea.picture AS Picture
                        FROM server_team AS tea
                        INNER JOIN
                        server_sport AS spo	
                        ON tea.sport = spo.id
                        INNER JOIN server_league AS leg
                        ON tea.league = leg.id
                        INNER JOIN server_season AS sea
                        ON tea.season = sea.id",
                        array(),array(),array());
            
            // select sport, league, year, and description  
            $sportname = $db->do_query("SELECT spo.name AS Sport FROM server_sport AS spo",array(),array(),array());
            
            $leaguename = $db->do_query("SELECT leg.name AS League FROM server_league AS leg",array(),array(),array());  

            $yeardesc = $db->do_query("SELECT concat(sea.year,'_',sea.description) AS Year FROM server_season AS sea", array(),                 array(),array());
              
          }else{
            
             // fetch information about teams for a league manager    
             $viewteam = $db->do_query("SELECT tea.id AS TeamId, tea.name AS TeamName, tea.mascot AS Mascot, spo.name AS                        Sport,leg.name AS League, concat(sea.year,'_',sea.description) AS Year, tea.homecolor AS Homecolor,                    tea.awaycolor AS Awaycolor, tea.maxplayers AS Maxplayers, tea.picture AS Picture   
                        FROM server_team AS tea
                        INNER JOIN
                        server_sport AS spo	
                        ON tea.sport = spo.id
                        INNER JOIN server_league AS leg
                        ON tea.league = leg.id
                        INNER JOIN server_season AS sea
                        ON tea.season = sea.id
                        WHERE leg.id = (SELECT league FROM server_user WHERE username = :username AND role = 2)",
                        array(":username"),array($username), array(PDO::PARAM_STR));
            
            // fetch sport name, league name, and year-description from sport, league, and season table
            $sportname = $db->do_query("SELECT distinct(spo.name) AS Sport FROM server_sport AS spo 
                        INNER JOIN server_team AS tea
                        ON spo.id = tea.sport
                        WHERE tea.league in (SELECT league FROM server_user WHERE username=:username)",array(":username"),array($username),array(PDO::PARAM_STR));
              
            $leaguename = $db->do_query("SELECT lea.name AS League 
                         FROM server_league AS lea WHERE lea.id in (SELECT league FROM server_user WHERE username=:username)",array(":username"),array($username),array(PDO::PARAM_STR)); 
              
            $yeardesc = $db->do_query("SELECT distinct(concat(sea.year,'_',sea.description)) AS Year FROM server_season AS sea
                        INNER JOIN server_team AS tea
                        ON sea.id = tea.season
                        WHERE tea.league in (SELECT league FROM server_user WHERE username=:username)",array(":username"),array($username),array(PDO::PARAM_STR));  
               
            }

          echo "<div class='viewteam'><h2>Team's List</h2><form method='POST' action=''>";
          
          // iterate on each team record and create a editable tabluar form
          foreach($viewteam as &$record){
              
                $teamid = $record["TeamId"];
                $teamname = $record["TeamName"];
                $mascot = $record["Mascot"];
                $sport = $record["Sport"];
                $league = $record["League"];
                $year = $record["Year"];
                $homecolor =  $record["Homecolor"];
                $awaycolor = $record["Awaycolor"];
                $maxplayers = $record["Maxplayers"];
                $picture = $record["Picture"];
                
                // build fields of type radiobutton, textbox, and dropdown 
                $record["TeamId"] = "<input type='radio' name='teamid' value= ".$teamid.">";
                $record["TeamName"] = "<input type='text' name='teamname".$teamid."' value='".$teamname."'>";
                $record["Mascot"] = "<input type='text' name='mascot".$teamid."' value='".$mascot."'>";
                $record["Sport"] = "<select name='sportname".$teamid."'>";
                         
                         // match sport names and mark the matching sport as selected
                         foreach($sportname as &$spname){
                             
                            foreach($spname as $key=>$value){
                                if($sport === $value){
                                    $record["Sport"] .= "<option value='".$value."' selected>".$value."</option>";
                                }else{
                                    $record["Sport"] .= "<option value='".$value."'>".$value."</option>";
                                }
                            }
                         }
                                      
                $record["Sport"] .= "</select>"; 
                $record["League"] = "<select name='leaguename".$teamid."'>";
                        
                          // match league names and mark the matching league as selected
                         foreach($leaguename as &$lgname){
                             
                            foreach($lgname as $key=>$value){
                                if($league === $value){
                                    $record["League"] .= "<option value='".$value."' selected>".$value."</option>";
                                }else{
                                    $record["League"] .= "<option value='".$value."'>".$value."</option>";
                                }
                            }
                         }
                                      
                $record["League"] .= "</select>"; 
                $record["Year"] = "<select name='year".$teamid."'>";
                         
                         // match year-description and mark the matching value as selected
                         foreach($yeardesc as &$yr){
                             
                            foreach($yr as $key=>$value){
                                if($year === $value){
                                    $record["Year"] .= "<option value='".$value."' selected>".$value."</option>";
                                }else{
                                    $record["Year"] .= "<option value='".$value."'>".$value."</option>";
                                }
                            }
                         }
                                      
                $record["Year"] .= "</select>";  
              
                $record["Homecolor"] = "<input type='text' name='homecolor".$teamid."' value='".$homecolor."'>";
                $record["Awaycolor"] = "<input type='text' name='awaycolor".$teamid."' value='".$awaycolor."'>";
                $record["Maxplayers"] = "<input type='text' name='maxplayers".$teamid."' value='".$maxplayers."'>";
                $record["Picture"] = "<img src= ".$picture." alt='team image' height='20' width='20' name='picture".$teamid."' value='".$picture."'>";
                
             }
           
           // build team table
           echo  FN::build_table($viewteam);
           echo  "<br/><input type='submit' class='delteam-button' name='delteam' value='Delete Team'/>&nbsp;&nbsp;<input type='submit' class='editteam-button' name='editteam' value='Edit Team'/></form></div>";   
        
           //delete team
           if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delteam']) && isset($_POST['teamid'])){
       
               $id = $_POST['teamid'];
               //echo $id;
               // delete team based on team id 
               $delteam = $db->do_query("DELETE FROM server_team WHERE id = :id", array(":id"),array($id), 
                          array(PDO::PARAM_INT));
               
               FN::log_message("Successfully deleted team having id: ".$id);
               
               header("Location: Admin.php");
           }
          
          // edit team
          if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editteam']) && isset($_POST['teamid'])){
                  
               $id = $_POST['teamid'];
               $teamname = $_POST['teamname'.$id];
               $mascot = $_POST['mascot'.$id];
               $sportname = $_POST['sportname'.$id];
               $leaguename = $_POST['leaguename'.$id];
               $year = $_POST['year'.$id];
               $homecolor = $_POST['homecolor'.$id];
               $awaycolor = $_POST['awaycolor'.$id];
               $maxplayers = $_POST['maxplayers'.$id];
              
               // sanitize the input
               $teamname = Sanitize::sanitizeData($teamname);
               $mascot = Sanitize::sanitizeData($mascot);
               $sportname = Sanitize::sanitizeData($sportname);
               $leaguename = Sanitize::sanitizeData($leaguename);
               $homecolor = Sanitize::sanitizeData($homecolor);
               $awaycolor = Sanitize::sanitizeData($awaycolor);
               $maxplayers = Sanitize::sanitizeData($maxplayers);
               
               // validate team name, mascot, sport name, league name, home color,
               // away color, and max players
               $param = array( 
                            "Team name" => array($teamname,"spacealpha"),
                            "Mascot" => array($mascot,"spacealpha"),
                            "Sport" => array($sportname,"alphanumeric"),
                            "League" => array($leaguename,"spacealpha"),
                            "Homecolor" => array($homecolor,"spacealpha"),
                            "Awaycolor" => array($awaycolor,"spacealpha"),
                            "Maxplayers" => array($maxplayers,"numeric")
                         );    
               
                // call to a validator function
                $result = FN::validateInput($param);
               
                if(sizeof($result)>0){
                    echo "<div id='validation'>".FN::buildErrList($result)."</div>";
                }
              
               // update team if teamname, mascot, homecolor, awaycolor, and maxplayers fields are successfully validated 
               else{
                   
                   // fetch sports, league and season's id based on their respective names
                   $getsportsId = $db->do_query("SELECT spo.id AS id FROM server_sport AS spo WHERE spo.name = :name                       ",array(":name"),array($sportname),array(PDO::PARAM_STR));
                   //echo $getsportsId[0]['id'];
                   
                   $getleagueId = $db->do_query("SELECT leg.id AS id FROM server_league AS leg WHERE leg.name = :name                       ",array(":name"),array($leaguename),array(PDO::PARAM_STR));
                   //echo $getleagueId[0]['id'];
                   
                   $getseasonId = $db->do_query("SELECT sea.id AS id FROM server_season AS sea WHERE concat(sea.year,'_',sea.description) = :name                       ",array(":name"),array($year),array(PDO::PARAM_STR));
                   
                   //echo $getseason[0]['id'];
                   
                   //update the server_team field
                   $editteam = $db->do_query("UPDATE server_team SET name = :name, mascot = :mascot, sport = :sport, league = :league, season = :season, homecolor = :homecolor, awaycolor = :awaycolor, maxplayers = :maxplayers  where id = :id", array(":name",":mascot",":sport",":league",":season",":homecolor",":awaycolor",":maxplayers",":id"),array($teamname,$mascot,$getsportsId[0]['id'],$getleagueId[0]['id'],$getseasonId[0]['id'],$homecolor, $awaycolor,$maxplayers,$id), array(PDO::PARAM_STR,PDO::PARAM_INT,PDO::PARAM_INT,PDO::PARAM_INT,PDO::PARAM_STR,PDO::PARAM_STR,PDO::PARAM_STR,PDO::PARAM_INT,PDO::PARAM_INT));
                   
                   FN::log_message("Successfully updated sport id: ".$getsportsId[0]['id']." league id: ".$getleagueId[0]['id']." season id: ".$getseason[0]['id']);
                   
                   FN::log_message("Successfully edited the team having sport id: ".$getsportsId[0]['id']." league: ".$getleagueId[0]['id']." season: ".$getseasonId[0]['id']);
                   
                   // refresh the page to refelect the changes
                   header("Location: Admin.php");
               }   
           }
           
          // add team 
          echo '<div class="addteam"><h2>Add Team</h2>
                <form method="post" action=""><br/>
                Team Name: <input name="teamname"><br/> 
                Team Mascot: <input name="teammascot"/><br/>
                Sport: <br/><select class= "custom-select" name="sportname">';
                    
                    // form sport's dropdown for the particular user
                    foreach($sportname as &$fetchsport){

                        foreach($fetchsport as $key=>$value){
                                echo "<option value='".$value."'>".$value."</option>";
                      }
                    }
        
            echo '</select><br/>League: <select class= "custom-select" name="leaguename">';
                    
                    // form a league dropdown
                    foreach($leaguename as &$fetchleg){

                        foreach($fetchleg as $key=>$value){
                                echo "<option value='".$value."'>".$value."</option>";
                      }
                    }
                
           echo '</select><br/>Year: <select class= "custom-select" name="year">';
                   // form an year dropdown
                   foreach($yeardesc as &$fetchyear){

                      foreach($fetchyear as $key=>$value){
                            echo "<option value='".$value."'>".$value."</option>";
                        }
                }
                
            echo '</select><br/>';
            echo 'Home Color: <input name="teamhomecolor"/><br/>
                  Away Color: <input name="teamawaycolor"/><br/>
                  Max Players: <input name="teammaxply"/><br/><br/>
                  <input type="submit" class="addteam-button" name="submitteam" value="Add Team"/></form></div>';   
          
          // process the submitted request
          if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submitteam'])){
                
                // fetch the team information such as sports, league, and season 
               $teamname = isset($_POST['teamname']) ? $_POST['teamname'] : '';
               $teammascot = isset($_POST['teammascot']) ? $_POST['teammascot'] : '';
               $teamsport = isset($_POST['sportname']) ? $_POST['sportname'] : '';
               $teamleague = isset($_POST['leaguename']) ? $_POST['leaguename'] : '';
               $teamseason = isset($_POST['year']) ? $_POST['year'] : '';
               $teampicture = '../images/sports.png';
               $teamhomecolor = isset($_POST['teamhomecolor']) ? $_POST['teamhomecolor'] : '';
               $teamawaycolor = isset($_POST['teamawaycolor']) ? $_POST['teamawaycolor'] : '';
               $teammaxply = isset($_POST['teammaxply']) ? $_POST['teammaxply'] : '';
               
               // call to sanitaizeData function to clean the data
               $teamname = Sanitize::sanitizeData($teamname);
               $teammascot = Sanitize::sanitizeData($teammascot);
               $teamsport = Sanitize::sanitizeData($teamsport);
               $teamleague = Sanitize::sanitizeData($teamleague);
               $teamhomecolor = Sanitize::sanitizeData($teamhomecolor);
               $teamawaycolor = Sanitize::sanitizeData($teamawaycolor);
               $teammaxply = Sanitize::sanitizeData($teammaxply);
               
               // validate parameter
               $param = array( 
                            "Team name" => array($teamname,"spacealpha"),
                            "Mascot" => array($teammascot,"spacealpha"),
                            "Sport" => array($teamsport,"alphanumeric"),
                            "League" => array($teamleague,"spacealpha"),
                            "Homecolor" => array($teamhomecolor,"spacealpha"),
                            "Awaycolor" => array($teamawaycolor,"spacealpha"),
                            "Maxplayers" => array($teammaxply,"numeric")
                         );    
                
                // call to the validator function
                $result = FN::validateInput($param);
                
                // check array size to find if any error is present 
                if(sizeof($result)>0){
                    echo "<div id='validation'>".FN::buildErrList($result)."</div>";
                }
                
               else{
                   // fetched required data to insert 
                   $getsportsId = $db->do_query("SELECT spo.id AS id FROM server_sport AS spo WHERE spo.name = :name                       ",array(":name"),array($teamsport),array(PDO::PARAM_STR));
                       //echo $getsportsId[0]['id'];

                   $getleagueId = $db->do_query("SELECT leg.id AS id FROM server_league AS leg WHERE leg.name = :name                       ",array(":name"),array($teamleague),array(PDO::PARAM_STR));
                       //echo $getleagueId[0]['id'];

                   $getseasonId = $db->do_query("SELECT sea.id AS id FROM server_season AS sea WHERE concat(sea.year,'_',sea.description) = :name                       ",array(":name"),array($teamseason),array(PDO::PARAM_STR));

                   // insert the sanitize data in the team table
                   $addteam = $db->do_query("INSERT into server_team(name, mascot, sport, league, season, picture, homecolor, awaycolor, maxplayers) VALUES(:name, :mascot, :sport, :league, :season, :picture, :homecolor, :awaycolor, :maxplayers)", array(":name",":mascot",":sport",":league",":season",":picture",":homecolor",":awaycolor",":maxplayers"),array( $teamname,$teammascot,$getsportsId[0]['id'],$getleagueId[0]['id'],$getseasonId[0]['id'],$teampicture,$teamhomecolor,$teamawaycolor,$teammaxply),array(PDO::PARAM_STR, PDO::PARAM_STR, PDO::PARAM_INT, PDO::PARAM_INT, PDO::PARAM_INT, PDO::PARAM_STR, PDO::PARAM_STR, PDO::PARAM_STR, PDO::PARAM_INT));
                   
                   FN::log_message("Successfully added team having sport id: ".$getsportsId[0]['id']." league id: ".$getleagueId[0]['id']." season: ".$getseasonId[0]['id']);
                   
                   // refresh the page to display changes
                   header("Location: Admin.php");
               }
          }

         echo "</div><div class='banner'></div>";
        
         echo "<div class='wrap'>";
        
         // performing edit, add, delete, and view on scheduled data
        
      if($role == 'admin'){
          
          FN::log_message("Start of schedule section");
          // fetch scheduled data for admin
         $viewschedule = $db->do_query("SELECT concat(sch.sport, '_', sch.league,'_',sch.season, '_', sch.hometeam,'_',sch.awayteam)                 AS ScheduleId, spo.name AS Sport,leg.name AS League, concat(sea.Year,'_',sea.Description) AS Year, tea.name                 AS Hometeam,tea1.name AS Awayteam, sch.Homescore ,sch.Awayscore, sch.Scheduled, sch.Completed
                         FROM server_schedule AS sch
                         INNER JOIN
                         server_sport AS spo
                         ON spo.id =  sch.sport
                         INNER JOIN server_league AS leg
                         ON sch.league = leg.id
                         INNER JOIN server_season AS sea
                         ON sch.season = sea.id
                         INNER JOIN server_team AS tea
                         ON sch.hometeam = tea.id 
                         INNER JOIN server_team AS tea1
                         ON sch.awayteam = tea1.id",
                         array(),array(),array()); 
          
              $sportname = $db->do_query("SELECT spo.name AS Sport FROM server_sport AS spo",array(),array(),array());

              $leaguename = $db->do_query("SELECT leg.name AS League FROM server_league AS leg",array(),array(),array());  

              $yeardesc = $db->do_query("SELECT concat(sea.year,'_',sea.description) AS Year FROM server_season AS sea", array(),                 array(),array());          

              $teamname =  $db->do_query("SELECT tea.name AS Team FROM server_team AS tea", array(), array(),array());          

        }else{
          
          // fetch scheduled data for league manager 
         $viewschedule = $db->do_query("SELECT concat(sch.sport, '_', sch.league,'_',sch.season, '_',                                               sch.hometeam,'_',sch.awayteam) AS ScheduleId, spo.name AS Sport,leg.name AS League,                                         concat(sea.Year,'_',sea.Description) AS Year, tea.name AS Hometeam, tea1.name AS Awayteam, sch.Homescore                   ,sch.Awayscore, sch.Scheduled, sch.Completed
                         FROM server_schedule AS sch
                         INNER JOIN
                         server_sport AS spo
                         ON spo.id =  sch.sport
                         INNER JOIN server_league AS leg
                         ON sch.league = leg.id
                         INNER JOIN server_season AS sea
                         ON sch.season = sea.id
                         INNER JOIN server_team AS tea
                         ON sch.hometeam = tea.id 
                         INNER JOIN server_team AS tea1
                         ON sch.awayteam = tea1.id 
                         where leg.id = (select league from server_user where username= :username)",array(":username"),array($username),
                         array(PDO::PARAM_STR));
           
            $sportname = $db->do_query("SELECT distinct(spo.name) AS Sport FROM server_sport AS spo 
                        INNER JOIN server_team AS tea
                        ON spo.id = tea.sport
                        WHERE tea.league in (SELECT league FROM server_user WHERE username=:username)",array(":username"),array($username),array(PDO::PARAM_STR));
              
            $leaguename = $db->do_query("SELECT lea.name AS League 
                         FROM server_league AS lea WHERE lea.id in (SELECT league FROM server_user WHERE username=:username)",array(":username"),array($username),array(PDO::PARAM_STR)); 
              
            $yeardesc = $db->do_query("SELECT distinct(concat(sea.year,'_',sea.description)) AS Year FROM server_season AS sea
                        INNER JOIN server_team AS tea
                        ON sea.id = tea.season
                        WHERE tea.league in (SELECT league FROM server_user WHERE username=:username)",array(":username"),array($username),array(PDO::PARAM_STR));
          
            $teamname =  $db->do_query("SELECT tea.name AS Team 
                        FROM server_team AS tea	
                        WHERE tea.league in (SELECT league FROM server_user WHERE username=:username)",array(":username"),array($username),array(PDO::PARAM_STR));
        }
          echo "<div class='viewschedule'><h2>Schedule's List</h2><form method='POST' action=''>";
          
          // loop through each of the schedule record and create a form table
          foreach($viewschedule as &$record){
                                
                $scheduleid = $record["ScheduleId"];  
                $sport = $record["Sport"];
                $league = $record["League"];
                $year = $record["Year"];
                $hometeam = $record["Hometeam"];
                $awayteam = $record["Awayteam"];
                $homescore = $record["Homescore"];
                $awayscore = $record["Awayscore"];
                $scheduled = $record["Scheduled"];
                $completed = $record["Completed"];
                
                 // form a editable scheduled table
                $record["ScheduleId"] = "<input type='radio' name='scheduleid' value= ".$scheduleid.">";
                $record["Sport"] = "<select name='sportname".$scheduleid."'>";        
                         foreach($sportname as &$spname){
                            
                            // build a sports dropdown  
                            foreach($spname as $key=>$value){
                                if($sport === $value){
                                    $record["Sport"] .= "<option value='".$value."' selected>".$value."</option>";
                                }else{
                                    $record["Sport"] .= "<option value='".$value."'>".$value."</option>";
                                }
                            }
                         }
                                      
                $record["Sport"] .= "</select>"; 
                $record["League"] = "<select name='leaguename".$scheduleid."'>";
                         
                          // build a league dropdown
                         foreach($leaguename as &$lgname){
                             
                            foreach($lgname as $key=>$value){
                                if($league === $value){
                                    $record["League"] .= "<option value='".$value."' selected>".$value."</option>";
                                }else{
                                    $record["League"] .= "<option value='".$value."'>".$value."</option>";
                                }
                            }
                         }
                                      
                $record["League"] .= "</select>";
                $record["Year"] = "<select name='year".$scheduleid."'>";
                        
                          // build an year dropdwown
                         foreach($yeardesc as &$yr){
                             
                            foreach($yr as $key=>$value){
                                if($year === $value){
                                    $record["Year"] .= "<option value='".$value."' selected>".$value."</option>";
                                }else{
                                    $record["Year"] .= "<option value='".$value."'>".$value."</option>";
                                }
                            }
                         }
                                      
                $record["Year"] .= "</select>";
                $record["Hometeam"] = "<select name='hometeam".$scheduleid."'>";
                        
                          // build a hometeam dropdown
                         foreach($teamname as &$hometm){
                             
                            foreach($hometm as $key=>$value){
                                if($hometeam === $value){
                                    $record["Hometeam"] .= "<option value='".$value."' selected>".$value."</option>";
                                }else{
                                    $record["Hometeam"] .= "<option value='".$value."'>".$value."</option>";
                                }
                            }
                         }
                                      
                $record["Hometeam"] .= "</select>";
                $record["Awayteam"] = "<select name='awayteam".$scheduleid."'>";
                         
                         // build awayteam dropdown
                         foreach($teamname as &$awaytm){
                             
                            foreach($awaytm as $key=>$value){
                                if($awayteam === $value){
                                    $record["Awayteam"] .= "<option value='".$value."' selected>".$value."</option>";
                                }else{
                                    $record["Awayteam"] .= "<option value='".$value."'>".$value."</option>";
                                }
                            }
                         }
                                      
                $record["Awayteam"] .= "</select>";
                $record["Homescore"] = "<input type='text' name='homescore".$scheduleid."' value=".$homescore.">";
                $record["Awayscore"] = "<input type='text' name='awayscore".$scheduleid."' value='".$awayscore."'>";
                $record["Scheduled"] = "<input type='text' name='scheduled".$scheduleid."' value='".$scheduled."'>";
                $record["Completed"] = "<input type='text' name='completed".$scheduleid."' value='".$completed."'>";
                
             }
        
           echo  FN::build_table($viewschedule);
           echo  "<br/><input type='submit' class='delschedule-button' name='delschedule' value='Delete Schedule'/>&nbsp;&nbsp;<input type='submit' class='editschedule-button' name='editschedule' value='Edit Schedule'/></form></div>";   
        
           // edit schedule
           if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editschedule']) && isset($_POST['scheduleid'])){
               
               // store post data
               $scheduleid = $_POST['scheduleid'];
               $sportname = $_POST['sportname'.$scheduleid];
               $leaguename = $_POST['leaguename'.$scheduleid];
               $seasonyear = $_POST['year'.$scheduleid];
               $hometeam = $_POST['hometeam'.$scheduleid];
               $awayteam = $_POST['awayteam'.$scheduleid];
               $homescore = $_POST['homescore'.$scheduleid];
               $awayscore = $_POST['awayscore'.$scheduleid];
               $scheduled = $_POST['scheduled'.$scheduleid];
               $completed = $_POST['completed'.$scheduleid];
               
               // sanitize the submitted parameters
               $sportname = Sanitize::sanitizeData($sportname);
               $leaguename = Sanitize::sanitizeData($leaguename);
               $seasonyear = Sanitize::sanitizeData($seasonyear);
               $hometeam = Sanitize::sanitizeData($hometeam);
               $awayteam = Sanitize::sanitizeData($awayteam);
               $homescore = Sanitize::sanitizeData($homescore);
               $awayscore = Sanitize::sanitizeData($awayscore);
               $scheduled = Sanitize::sanitizeData($scheduled);
               $completed = (int)Sanitize::sanitizeData($completed);
               
               // validate the submitted parameters
               $param = array( 
                            "Sport" => array($sportname,"alphanumeric"),
                            "League" => array($leaguename,"spacealpha"),
                            "Year" => array($seasonyear,"length"),
                            "Hometeam" => array($hometeam,"spacealpha"),
                            "Awayteam" => array($awayteam,"spacealpha"),
                            "Homescore" => array($homescore,"numeric"),
                            "Awayscore" => array($awayscore,"numeric"),
                            "Scheduled" => array($scheduled,"datetime"),
                            "completed" => array($completed,"numeric")
                         );    
                
                // build the validated input
                $result = FN::validateInput($param);
               
                if(sizeof($result)>0){
                    echo "<div id='validation'>".FN::buildErrList($result)."</div>";
                }
               
               // check for the empty value and update if all the entries are appropriate
               else{
                 
                   $arr = explode("_", $scheduleid, 5);
                   $sportid = $arr[0];
                   $leagueid = $arr[1];
                   $seasonid = $arr[2];
                   $hometeamid = $arr[3];
                   $awayteamid = $arr[4];
                   
                   // update the individual table record as well as update schedule table 
                   $getsportsId = $db->do_query("SELECT spo.id AS id FROM server_sport AS spo WHERE spo.name = :name                                ",array(":name"),array($sportname),array(PDO::PARAM_STR));
                   //echo $getsportsId[0]['id'];
                   
                   $getleagueId = $db->do_query("SELECT leg.id AS id FROM server_league AS leg WHERE leg.name = :name                              ",array(":name"),array($leaguename),array(PDO::PARAM_STR));
                   //echo $getleagueId[0]['id'];
                   
                   $getseasonId = $db->do_query("SELECT sea.id AS id FROM server_season AS sea WHERE                                                concat(sea.year,'_',sea.description) = :name                                                                      ",array(":name"),array($seasonyear),array(PDO::PARAM_STR));
   
                   $gethometeamId = $db->do_query("SELECT tea.id AS Hometeam, tea1.id AS Awayteam FROM server_team AS tea, server_team AS tea1 WHERE tea.name = :hometeam AND tea1.name =:awayteam ", array(":hometeam",":awayteam"),array($hometeam,$awayteam), array(PDO::PARAM_STR,PDO::PARAM_STR)); 
                    
                   // update schedule value
                   $updschedule = $db->do_query("UPDATE server_schedule SET sport = :sport, league = :league, season = :season, hometeam = :hometeam, awayteam = :awayteam, homescore = :homescore, awayscore = :awayscore, scheduled = :scheduled,
                   completed = :completed where sport = :sport1 AND league = :league1 AND season = :season1 AND hometeam = :hometeam1 AND awayteam = :awayteam1", array(":sport",":league",":season",":hometeam",":awayteam",":homescore",":awayscore",":scheduled",":completed",":sport1",":league1",":season1",":hometeam1",":awayteam1"),array($getsportsId[0]['id'],$getleagueId[0]['id'],$getseasonId[0]['id'],$gethometeamId[0]['Hometeam'],$gethometeamId[0]['Awayteam'],$homescore,$awayscore,$scheduled,$completed,$sportid,$leagueid,$seasonid,$hometeamid,$awayteamid), array(PDO::PARAM_INT,PDO::PARAM_INT,PDO::PARAM_INT,PDO::PARAM_INT,PDO::PARAM_INT,PDO::PARAM_INT,PDO::PARAM_INT,PDO::PARAM_STR,PDO::PARAM_INT,PDO::PARAM_INT,PDO::PARAM_INT,PDO::PARAM_INT,PDO::PARAM_INT,PDO::PARAM_INT));
                   
                   FN::log_message("Successfully edited schedule having sport id: ".$getsportsId[0]['id']." league: ".$getleagueId[0]['id']." season id: ".$getseasonId[0]['id']." home team id: ".$gethometeamId[0]['Hometeam']." away team id: ".gethometeamId[0]['Awayteam']);
                   // refresh the page
                   header("Location: Admin.php");
               }
               
           }
        
           // delete the schedule table entry based on the selected id 
           if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delschedule']) && isset($_POST['scheduleid'])){
       
               $scheduleid = $_POST['scheduleid'];
                
               $arr = explode("_", $scheduleid, 5);
               $sportid = $arr[0];
               $leagueid = $arr[1];
               $seasonid = $arr[2];
               $hometeamid = $arr[3];
               $awayteamid = $arr[4];
               
               $delschedule = $db->do_query("DELETE FROM server_schedule WHERE sport = :sport AND league = :league AND 
               season = :season AND hometeam = :hometeam AND awayteam = :awayteam", array(":sport",":league",":season",":hometeam",":awayteam"),array($sportid,$leagueid,$seasonid,$hometeamid,$awayteamid), array(PDO::PARAM_INT,PDO::PARAM_INT,PDO::PARAM_INT,PDO::PARAM_INT,PDO::PARAM_INT));
               
               FN::log_message("Successfully deleted schedule having sport id: ".$sportid." league id: ".$leagueid." season id:".$seasonid." home team id: ".$hometeamid." away team id: ".$awayteamid);
               // refresh the page
               header("Location: Admin.php");
           }
          
           // add a schedule record
           echo '<div class="addschedule"><h2>Add Schedule</h2>
                <form method="post" action=""><br/>
                 Sport: <br/><select class= "custom-select" name="sportname">';
                   // create select drop dwon for sport, league, year-description, the hometeam, and the awayteam
                   foreach($sportname as &$fetchsport){
                        
                        foreach($fetchsport as $key=>$value){
                            echo "<option value='".$value."'>".$value."</option>";
                        }
                    }
            echo '</select><br/>League:<br/><select class= "custom-select" name="leaguename">';
                   foreach($leaguename as &$fetchleague){
                        
                        foreach($fetchleague as $key=>$value){
                            echo "<option value='".$value."'>".$value."</option>";
                        }
                    }      
            echo  '</select><br/>Year:<br/><select class= "custom-select" name="seasonyear">';
                   foreach($yeardesc as &$fetchyear){
                        
                        foreach($fetchyear as $key=>$value){
                            echo "<option value='".$value."'>".$value."</option>";
                        }
                    }  
                
            echo '</select><br/>HomeTeam: <select class= "custom-select" name="hometeam">';
                   foreach($teamname as &$fetchhmteam){
                        
                        foreach($fetchhmteam as $key=>$value){
                            echo "<option value='".$value."'>".$value."</option>";
                        }
                    }  
            echo '</select><br/>AwayTeam: <select class= "custom-select" name="awayteam">';
                   foreach($teamname as &$fetchawteam){
                        
                        foreach($fetchawteam as $key=>$value){
                            echo "<option value='".$value."'>".$value."</option>";
                        }
                    } 
            echo '</select><br/>HomeScore: <input name="homescore"/><br/>AwayScore: <input name="awayscore"/><br/>Schedule: <input name="schedule"/><br/>Completed: <br/><select class= "custom-select" name="completed"><option value="0">0</option><option value="1">1</option></select><br/><br/><input type="submit" class="addsport-button" name="submitschedule" value="Add Schedule"/></form></div>';   
         
          // add a sport scheuled entry
          if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submitschedule'])){
               
               $sportname = isset($_POST['sportname']) ? $_POST['sportname'] : '';
               $leaguename = isset($_POST['leaguename']) ? $_POST['leaguename'] : '';
               $seasonyear = isset($_POST['seasonyear']) ? $_POST['seasonyear'] : '';
               $hometeam = isset($_POST['hometeam']) ? $_POST['hometeam'] : '';
               $awayteam = isset($_POST['awayteam']) ? $_POST['awayteam'] : '';
               $homescore = isset($_POST['homescore']) ? $_POST['homescore'] : '';
               $awayscore = isset($_POST['awayscore']) ? $_POST['awayscore'] : '';
               $schedule = isset($_POST['schedule']) ? $_POST['schedule'] : '';
               $completed = isset($_POST['completed']) ? $_POST['completed'] : '';
               
               // sanitize the request
               $sportname = Sanitize::sanitizeData($sportname);
               $leaguename = Sanitize::sanitizeData($leaguename);
               $seasonyear = Sanitize::sanitizeData($seasonyear);
               $hometeam = Sanitize::sanitizeData($hometeam);
               $awayteam = Sanitize::sanitizeData($awayteam);
               $homescore = Sanitize::sanitizeData($homescore);
               $awayscore = Sanitize::sanitizeData($awayscore);
               $schedule = Sanitize::sanitizeData($schedule);
               $completed = (int)Sanitize::sanitizeData($completed); // convert completed to end
               
               //validate all the input fields 
               $param = array( 
                            "Sport" => array($sportname,"alphanumeric"),
                            "League" => array($leaguename,"spacealpha"),
                            "Year" => array($seasonyear,"length"),
                            "Hometeam" => array($hometeam,"spacealpha"),
                            "Awayteam" => array($awayteam,"spacealpha"),
                            "Homescore" => array($homescore,"numeric"),
                            "Awayscore" => array($awayscore,"numeric"),
                            "Scheduled" => array($schedule,"datetime"),
                            "completed" => array($completed,"numeric")
                         );    
               
                // call validate function              
                $result = FN::validateInput($param);
               
                if(sizeof($result)>0){
                    echo "<div id='validation'>".FN::buildErrList($result)."</div>";
                }
               
               else{ 
                   // fetch respective id for the sport, league, season, hometeam, and the awayteam 

                   $getsportsId = $db->do_query("SELECT spo.id AS id FROM server_sport AS spo WHERE spo.name = :name                       ",array(":name"),array($sportname),array(PDO::PARAM_STR));
                       //echo $getsportsId[0]['id'];

                   $getleagueId = $db->do_query("SELECT leg.id AS id FROM server_league AS leg WHERE leg.name = :name                       ",array(":name"),array($leaguename),array(PDO::PARAM_STR));
                       //echo $getleagueId[0]['id'];

                   $getseasonId = $db->do_query("SELECT sea.id AS id FROM server_season AS sea WHERE                                                    concat(sea.year,'_',sea.description) = :name                                                                          ",array(":name"),array($seasonyear),array(PDO::PARAM_STR)); 

                   $gethomeId =  $db->do_query("SELECT tea.id AS id FROM server_team AS tea WHERE tea.name = :name                                   ",array(":name"),array($hometeam),array(PDO::PARAM_STR)); 

                   $getawayId =  $db->do_query("SELECT tea.id AS id FROM server_team AS tea WHERE tea.name = :name                                   ",array(":name"),array($awayteam),array(PDO::PARAM_STR)); 

                   $sportid = $getsportsId[0]['id'];
                   $leagueid = $getleagueId[0]['id'];
                   $seasonid = $getseasonId[0]['id'];
                   $hometeamid = $gethomeId[0]['id'];
                   $awayteamid = $getawayId[0]['id'];

                   // update the table based on the fected id and the input provided by the user
                   $addteam = $db->do_query("INSERT into server_schedule(sport, league, season, hometeam, awayteam, homescore, awayscore, scheduled, completed) VALUES(:sport, :league, :season, :hometeam, :awayteam, :homescore, :awayscore, :scheduled, :completed)", array(":sport",":league",":season",":hometeam",":awayteam",":homescore",":awayscore",":scheduled",":completed"),array( $sportid,$leagueid,$seasonid,$hometeamid,$awayteamid,$homescore,$awayscore,$schedule,$completed),array(PDO::PARAM_INT, PDO::PARAM_INT, PDO::PARAM_INT, PDO::PARAM_INT, PDO::PARAM_INT, PDO::PARAM_INT, PDO::PARAM_INT, PDO::PARAM_STR, PDO::PARAM_INT));
                    
                    FN::log_message("Sucessfully added team with sport id: ".$sportid." league id: ".$leagueid." season id: ".$seasonid." home team id: ".$hometeamid." away team id: ".$awayteamid);
                   //refresh the page 
                   header("Location: Admin.php");
               }
              
              FN::log_message("End of team section");
          }
        
         echo "</div><div class='banner'></div>";
                   
      }
     
     // for admin user
     if($role == 'admin'){
         
          echo "<div class='wrap'>";
         
          FN::log_message("Start of sports section");
          // performing add, edit, delete, and view operations on sports
          $viewsport = $db->do_query("SELECT ssport.id as SportId, ssport.name as SportName
                    FROM server_sport AS ssport",array(),array(),array());
 
          echo "<div class='viewsport'><h2>Sport's List</h2><form method='POST' action=''>";
          
          // loop through each sport
          foreach($viewsport as &$record){
              
                $sportid = $record["SportId"];
                $sportname = $record["SportName"];
                
                $record["SportId"] = "<input type='radio' name='sportid' value= ".$sportid.">";
                $record["SportName"] = "<input type='text' name='sportname".$sportid."' value=".$sportname.">";
             }
        
          // build table
          echo  FN::build_table($viewsport);
          echo  "<br/><input type='submit' class='delsport-button' name='delsport' value='Delete Sport'/>&nbsp;&nbsp;<input type='submit' class='editsport-button' name='editsport' value='Edit Sport'/></form></div>";   
         
          // delete sport detail based on sport id
         if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delsport']) && isset($_POST['sportid'])){
       
               $id = $_POST['sportid'];

               $delseason = $db->do_query("DELETE FROM server_sport WHERE id = :id", array(":id"),array($id), 
                          array(PDO::PARAM_INT));
               
               FN::log_message(" Successfully deleted sport id: ".$id);    
               header("Location: Admin.php");
           }
         
          
          // edit sport based on sport id
          if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editsport']) && isset($_POST['sportid'])){
                  
               $id = $_POST['sportid'];
               $sportname = $_POST['sportname'.$id];
               
               // sanitize and validate sport name
               $sportname = Sanitize::sanitizeData($sportname);
               
               $param = array( 
                            "Sport" => array($sportname,"alphanumeric")
                         );    
               
                $result = FN::validateInput($param);
               
                if(sizeof($result)>0){
                    echo "<div id='validation'>".FN::buildErrList($result)."</div>";
                }
              
               else{
                   
                   // update if validation is sucessful
                   $editsport = $db->do_query("UPDATE server_sport SET name = :name  where id = :id", array(":name",":id"),array($sportname,$id), array(PDO::PARAM_STR,PDO::PARAM_INT));
                
                   FN::log_message("Successfully edited id: ".$id." with name: ".$sportname);
                   
                   header("Location: Admin.php");
               }   
           }
         
          // add a new sport with the sport name        
          echo '<div class="addsport"><h2>Add Sport</h2><form method="post" action=""><br/>SportName: <input name="sportName"/><br/><br/><input type="submit" class="addsport-button" name="submitsport" value="Add Sport"/>
             </form></div>';
          
          // submit sports
          if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submitsport'])){
               
               $sportName = isset($_POST['sportName']) ? $_POST['sportName'] : '';
               
               // sanitize and validated sports name
               $sportName = Sanitize::sanitizeData($sportName);
               
               $param = array( 
                            "Sport" => array($sportName,"alphanumeric")
                         );    
                
                // call validator function
                $result = FN::validateInput($param);
               
                if(sizeof($result)>0){
                    echo "<div id='validation'>".FN::buildErrList($result)."</div>";
                }
              
               else{
                   
                    // add sports if data is proper
                   $addsport =  $db->do_query("INSERT into server_sport(name) VALUES(:name)", array(":name"),
        			   array($sportName),array(PDO::PARAM_STR));  
                   
                   FN::log_message("Successfully added sport name: ".$sportName);
                   // refresh the page
                   header("Location: Admin.php");
               } 
              
               FN::log_message("End of sport section");
            }
           
          echo '</div><div class="banner"></div>';

     }

     
       // perform add, delete, edit, and view any user
          if($role == 'admin'){
             
             FN::log_message("Start of user section");  
             // fetch data for the admin role i.e fetch all the data  
             $viewuser = $db->do_query("SELECT usr.username AS UserName, rol.name AS Role, tea.name AS Team, lea.name AS League 
                          FROM server_user AS usr
                          INNER JOIN server_roles AS rol
                          ON usr.role = rol.id
                          INNER JOIN server_team AS tea
                          ON usr.team = tea.id
                          INNER JOIN server_league AS lea
                          ON usr.league = lea.id",array(),array(),array());
              
              $rolename = ['Admin','League Manager','Team Manager','Coach','Parent'];
              
              $leaguename = $db->do_query("SELECT lea.name AS League 
                           FROM server_league AS lea",array(),array(),array());
              
              $teamname =  $db->do_query("SELECT tea.name AS Team 
                           FROM server_team AS tea",array(),array(),array());
              
               //print_r($leaguename);
              
          }elseif($role == 'league manager'){
              
               // fetch data for a league manager
              $viewuser = $db->do_query("SELECT usr.username AS UserName, rol.name AS Role, tea.name AS Team, lea.name AS League  
                         FROM server_user AS usr
                         INNER JOIN server_roles AS rol
                         ON usr.role = rol.id
                         INNER JOIN server_team AS tea
                         ON usr.team = tea.id
                         INNER JOIN server_league AS lea
                         ON usr.league = lea.id
                         WHERE rol.name in ('Team Manager','Coach')
                         AND lea.id in (SELECT league FROM server_user WHERE username=:username)",array(":username"),array($username),array(PDO::PARAM_STR)); 
              
              $rolename = ['Team manager','Coach'];
              
              $leaguename = $db->do_query("SELECT lea.name AS League 
                           FROM server_league AS lea WHERE lea.id in (SELECT league FROM server_user WHERE username=:username)",array(":username"),array($username),array(PDO::PARAM_STR));
              
              $teamname =  $db->do_query("SELECT tea.name AS Team 
                                FROM server_team AS tea
                                WHERE tea.league in (SELECT league FROM server_user WHERE username=:username)",array(":username"),array($username),array(PDO::PARAM_STR));
              
              
          }else{
              
              // fetch data for team manager, coach, and parent role
              $viewuser = $db->do_query("SELECT usr.username AS UserName, rol.name AS Role, tea.name AS Team, lea.name AS League  
                          FROM server_user AS usr
                          INNER JOIN server_roles AS rol
                          ON usr.role = rol.id
                          INNER JOIN server_team AS tea
                          ON usr.team = tea.id
                          INNER JOIN server_league AS lea
                          ON usr.league = lea.id
                          WHERE rol.name in ('Team Manager','Coach','Parent')
                          AND usr.team = :team",array(":team"),array($team),array(PDO::PARAM_INT));
              
              $rolename = ['Team manager','Coach', 'Parent'];
              
              $leaguename = $db->do_query("SELECT lea.name AS League 
                           FROM server_league AS lea WHERE lea.id in (SELECT league FROM server_user WHERE username=:username)",array(":username"),array($username),array(PDO::PARAM_STR));
              
              $teamname =  $db->do_query("SELECT tea.name AS Team FROM server_team AS tea WHERE tea.id in (SELECT team FROM                      server_user WHERE username= :username)",array(":username"),array($username),array(PDO::PARAM_STR));

          }

          echo "<div class='wrap'>";
        
          echo "<div class='viewuser'><h2>User's List</h2><form method='POST' action=''>";
        
          //$rolename = strtolower($rolename);
          // loop through each user record
          foreach($viewuser as &$record){
              
                $usrname = $record["UserName"];
                $usrrole = $record["Role"];
                $usrteam = $record["Team"];
                $usrleague = $record["League"];
                
                // creating an editable user table 
                $record["UserName"] = "<input type='radio' name='usrname' value= ".$usrname.">".$usrname;
                $record["Role"] = "<select name='role".$usrname."'>";
                         // looping through each record to find the selected role 
                         foreach($rolename as &$role){
                            
                            if($usrrole === $role){
                                //echo $role;
                                $record["Role"] .= "<option value='".$role."' selected>".$role."</option>";
                            }else{
                                $record["Role"] .= "<option value='".$role."'>".$role."</option>";
                            }

                         }
                $record["Role"] .= "</select>";

                $record["League"] = "<select name='league".$usrname."'>";
                         // loop through each league to find the selected league
                         foreach($leaguename as &$league){
                             
                            foreach($league as $key=>$value){
                                if($usrleague === $value){
                                    $record["League"] .= "<option value='".$value."' selected>".$value."</option>";
                                }else{
                                    $record["League"] .= "<option value='".$value."'>".$value."</option>";
                                }
                            }
                         }
                                      
                $record["League"] .= "</select>";
              
                $record["Team"] = "<select name='team".$usrname."'>";
                         
                         // loop through each team to find the team the user belongs to
                        foreach($teamname as &$tea){
                             
                            foreach($tea as $key=>$value){
                                if( $usrteam === $value){
                                    $record["Team"] .= "<option value='".$value."' selected>".$value."</option>";
                                }else{
                                    $record["Team"] .= "<option value='".$value."'>".$value."</option>";
                                }
                            }
                         }
                          
                
                $record["Team"] .= "</select>";
             }
          
         // build user table
         echo  FN::build_table($viewuser);
         
         echo  "<br/><input type='submit' class='deluser-button' name='deluser' value='Delete User'/>&nbsp;&nbsp;<input      type='submit' class='edituser-button' name='edituser' value='Edit User'/></form></div>";
         
         // edit user
         if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edituser']) && isset($_POST['usrname'])){
               
               $edituser = $_POST['usrname'];
               $editrole = $_POST['role'.$edituser];
               $editleague = $_POST['league'.$edituser];
               $editteam =  $_POST['team'.$edituser];
               
               // sanitize all the input 
               $edituser = Sanitize::sanitizeData($edituser);
               $editrole = Sanitize::sanitizeData($editrole);
               $editleague = Sanitize::sanitizeData($editleague);
               $editteam = Sanitize::sanitizeData($editteam);
                
                // validate the user name, role, league, and the team
                $param = array( 
                            "Username" => array($edituser,"alphanumeric"),
                            "Role" => array($editrole,"spacealpha"),
                            "League" => array($editleague,"spacealpha"),
                            "Team" => array($editteam,"spacealpha")
                         );    
               
                $result = FN::validateInput($param);
               
                if(sizeof($result)>0){
                    echo "<div id='validation'>".FN::buildErrList($result)."</div>";
                }
             
               // fetch roleId, leagueId, and teamId for the selected user and update the specific record
               else{
                   
                    $editroleId = $db->do_query("SELECT id FROM server_roles WHERE name = :name", array(":name"),
        			array($editrole),array(PDO::PARAM_STR));
                   
                    $editleagueId = $db->do_query("SELECT id FROM server_league WHERE name = :name", array(":name"),
        			array($editleague),array(PDO::PARAM_STR));
                   
                    $editteamId = $db->do_query("SELECT id FROM server_team WHERE name = :name", array(":name"),
        			array($editteam),array(PDO::PARAM_STR));
                   
                    $editusr = $db->do_query("UPDATE server_user SET role = :role, league = :league, team = :team where username = :username", array(":role",":league", ":team", ":username"),array($editroleId[0]['id'],$editleagueId[0]['id'],$editteamId[0]['id'],$edituser), array(PDO::PARAM_INT,PDO::PARAM_INT,PDO::PARAM_INT,PDO::PARAM_STR));
                
                   FN::log_message("Successfully edited user ".$edituser." with role id: ".$editroleId[0]['id']." league id: ".$editleagueId[0]['id']." team id: ".$editteamId[0]['id']);
                   // refresh the page
                   header("Location: Admin.php");
               }
               
           }
         
         // delete user     
         if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['deluser']) && isset($_POST['usrname'])){
              
               $deluser = $_POST['usrname'];
              
               $delusr = $db->do_query("DELETE FROM server_user WHERE username = :username",                                                    array(":username"),array($deluser), array(PDO::PARAM_STR));
               
               FN::log_message("Successfully deleted the user: ".$deluser);
             
               header("Location: Admin.php");
           }
           
            //print_r($leaguename);
            // add user
            echo '<div class="adduser"><h2>Add New User</h2><form method="post" action=""><br/>
            Username: <input name="usrname"><br/>
            Password: <input type="password" name="usrpasswd"/><br/>
            Role: <br/><select class= "custom-select" name="usrrole">';
            
            // build the role dropdown by the selected user
            foreach($rolename as $fetchrole){
                echo "<option value='".$fetchrole."'>".$fetchrole."</option><br/>";
            }
            
            
            echo '</select><br/>League: <select class= "custom-select" name="usrleg">';
            
            foreach($leaguename as &$fetchleg){
                // build league dropdown        
                foreach($fetchleg as $key=>$value){
                    echo "<option value='".$value."'>".$value."</option>";
                    }
            }
                
           echo '</select><br/>Team: <select class= "custom-select" name="usrteam">';
               // build the team dropdown
           foreach($teamname as &$fetchteam){
                        
                  foreach($fetchteam as $key=>$value){
                    echo "<option value='".$value."'>".$value."</option>";
                    }
            }

           echo '</select><br/><br/><input type="submit" class="adduser-button" name="submituser" value="Add User"/></form></div>';

            // submit a new user's information
           if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submituser'])){
              
               $usrname = isset($_POST['usrname']) ? $_POST['usrname'] : '';
               $usrpasswd = isset($_POST['usrpasswd']) ? $_POST['usrpasswd'] : '';
               $usrrole = isset($_POST['usrrole']) ? $_POST['usrrole'] : '';
               $usrleg = isset($_POST['usrleg']) ? $_POST['usrleg'] : '';
               $usrteam = isset($_POST['usrteam']) ? $_POST['usrteam'] : '';
               
               //sanitize the user input
               $usrname = Sanitize::sanitizeData($usrname);
               $usrpasswd = Sanitize::sanitizeData($usrpasswd);
               $usrrole = Sanitize::sanitizeData($usrrole);
               $usrleg = Sanitize::sanitizeData($usrleg);
               $usrteam = Sanitize::sanitizeData($usrteam); 
               
               // validate the password before hashing it
               if(strlen($usrpasswd)==0){
                   echo "<div id='validation'><span style='color:red;'>Empty password<span></div>";
               }
                
               // convert plain password to the hashed password
               $usrpasswd = password_hash($usrpasswd, PASSWORD_DEFAULT);
               
               // validate all other parameters
               $param = array( 
                            "Username" => array($usrname,"alphanumeric"),
                            "Role" => array($usrrole,"spacealpha"),
                            "League" => array($usrleg,"spacealpha"),
                            "Team" => array($usrteam,"spacealpha")
                         );    
               
               $result = FN::validateInput($param);
               
               // check for the error message
               if(sizeof($result)>0){
                    echo "<div id='validation'>".FN::buildErrList($result)."</div>";
                }    
               //add a new user in the user table
               else{
                   
                   // if no error is present then add the value to the table
                   $addroleId = $db->do_query("SELECT id FROM server_roles WHERE name = :name", array(":name"),
                        array($usrrole),array(PDO::PARAM_STR));

                   $addleagueId = $db->do_query("SELECT id FROM server_league WHERE name = :name", array(":name"),
                        array($usrleg),array(PDO::PARAM_STR));
                   
                   $addteamId = $db->do_query("SELECT id FROM server_team WHERE name = :name", array(":name"),
                        array($usrteam),array(PDO::PARAM_STR));

                   $addplayer = $db->do_query("INSERT into server_user(username, role, password, team, league) VALUES(:username, :role, :password, :team, :league)", array(":username",":role", ":password", ":team", ":league"),array($usrname,$addroleId[0]['id'],$usrpasswd,$addteamId[0]['id'],$addleagueId[0]['id']),array(PDO::PARAM_STR, PDO::PARAM_INT, PDO::PARAM_STR, PDO::PARAM_STR, PDO::PARAM_INT));
                   
                   FN::log_message("Successfully added user ".$usrname." with role id ".$addroleId[0]['id']." team id".$addteamId[0]['id']." league id: ".$addleagueId[0]['id']);
                   
                   header("Location: Admin.php");
               }       
               
               FN::log_message("End of user section");
          }
        
          echo "</div><div class='banner'></div>";
         
?>