window.onload = function(){

         var request = new XMLHttpRequest();
         request.onload = () => {
              var response = null;
         
              try{
                 response = JSON.parse(request.responseText);
                 console.log(response);  
                 var display = '';
                 $(document).ready(function(){
                        display += "<table border='1'><tr><th>Sport</th><th>League</th><th>Hometeam</th><th>Awayteam</th><th>Homescore</th><th>Awayscore</th><th>Scheduled</th><th>Completed</th>";
                      
                        $.each(response, function(i, item){
                            display += "<tr><td>"+item.sport+"</td><td>"+item.league+"</td><td>"+item.hometeam+"</td><td>"+item.awayteam+"</td><td>"+item.homescore+"</td><td>"+item.awayscore+"</td><td>"+item.scheduled+"</td><td>"+item.completed+"</td></tr>";
                        })
                        
                        display += "</table>";
                        $(".sdul").append(display);
                    }); 
                  
              }catch (e) {
                  console.log('parsing error');
              }  
        };
         
        request.open('post','../php/ScheduleServer.php');
        request.setRequestHeader('Content-type','application/x-www-form-urlencoded'); 
        request.send(); 
    
};
