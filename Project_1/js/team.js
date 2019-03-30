window.onload = function(){
  
   //call ajax to populate the team page content 
   var request = new XMLHttpRequest();
   request.onload = () => {
   var response = null;
         
   try{
       
      response = JSON.parse(request.responseText);
     
      var display = '';
      $(document).ready(function(){
            // store the html content in the variable
          
             display += "<table border='1'><tr><th>TeamName</th><th>Mascot</th><th>Sport</th><th>League</th><th>Year</th><th>Description</th><th>Picture</th><th>Homecolor</th><th>Awaycolor</th><th>Maxplayers</th>";
                      
             $.each(response, function(i, item){
                display += "<tr><td>"+item.name+"</td><td>"+item.mascot+"</td><td>"+item.sport+"</td><td>"+item.league+"</td><td>"+item.year+"</td><td>"+item.description+"</td><td><img src="+item.picture+" alt='team logo'/></td><td>"+item.homecolor+"</td><td>"+item.awaycolor+"</td><td>"+item.maxplayers+"</td></tr>";
             })
                        
             display += "</table>";
          
            // append the stored html content to the team div
             $(".team").append(display);
          });  
                  
        }catch(e) {
            // catch the parsing exception   
            console.log('parsing error');
        }  
    };
    
    // call the team server to fetch database result set
    request.open('post','../php/TeamServer.php');
    request.setRequestHeader('Content-type','application/x-www-form-urlencoded'); 
    request.send(); 
    
};
