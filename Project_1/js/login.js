window.onload = function(){ // execute the file after page loads
    
    // create placeholder for every form input
    var username = document.getElementById('username');
    var password = document.getElementById('password');
    var submit = document.getElementById('login-button');
    var errmsg = document.getElementById('errorMsg');

 
 
 submit.addEventListener('click', () =>{
    
     // delete the content of error message box after submit button is clicked
     while(errmsg.firstChild){
        errmsg.removeChild(errmsg.firstChild); 
        // hide the error message box 
        errmsg.style.display = "none"; 
     }
     
     // validate the username for an empty check
     if(username.value.trim() == ''){
         displayErr('Please enter username');
     }
     
     // validate the password for an empty check
     if(password.value.trim() == ''){
         displayErr('Please enter password');
     }
     
     // check whether recaptcha checkbox is checked on not
     if(grecaptcha.getResponse().length == 0){
         displayErr('Please click the reCAPTCHA checkbox');  
         
     }else{
         // ajax call to validate login information
         var request = new XMLHttpRequest();
         request.onload = () => {
              var response = null;
         
              try{
                 response = JSON.parse(request.responseText);
                 // route to team or admin page based on the user's role
                 if(response.flag){
                        if(response.message.toLowerCase() == 'parent'){
                            location.href = 'php/Team.php';
                        }else{
                            location.href = 'php/Admin.php';
                        }
                 }else{
                     // display error message if present in the response
                     for(var i = 0, len = response.message.length; i < len; i++){
                         displayErr(response.message[i]);
                     }
                 }  
              }catch (e) { // catch parsing error
                  console.log('parsing error');
              }         
        };
        
        // build a query string of parameters 
        var requestData  = 'username='+username.value+'&password='+password.value;
        // send login data to login_validation php page 
        request.open('post','php/login_validation.php');
        // form data is sent as a long query string 
        request.setRequestHeader('Content-type','application/x-www-form-urlencoded'); 
        request.send(requestData); 
     }
     
     // display error block with error message
     function displayErr(msg){
         var li = document.createElement('li');
         li.textContent = msg;
         errmsg.appendChild(li);
         errmsg.style.display = "block";
     }
  });    
    
};
