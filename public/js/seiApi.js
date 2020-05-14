/**
 * @author Arsen Leontijevic
 */
var seiApi = function(data, processback, url) {
	if(data !== Object(data))
	{
		data = {}
	}
	var params = Object.keys(data).map(function(key) {
		  return key + '=' + data[key];
		}).join('&');
	
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
	if (this.readyState == 4)
	    {
	        if (this.status == 200)
	         {
	        	 try {
		        	 //console.log(this.responseText);
	        	        json = JSON.parse(this.responseText);
	        	        
	        	    } catch(e) {
		        	    console.log(e);
	        	    	processback("Error processing server response, please try later", this.responseText);
						return false;
		        	}
	        	    if(json.status == "success")
        	        {
	        	    	//Set access_token cookie for 1 day after login
	        	    	if(['login', 'signup'].includes(data.action))
	        	    	{
	        	    		setCookie("access_token", json.result.access_token, 1);
	        	    	}
					  	processback(false, json);
        	        }else{
					  	processback(json.message, json);
        	        }
			 }else{
				processback("Server unreachable, please try later", this.responseText);
			 }
		};
	}
	xhttp.open("POST", url, true);
	xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.send(params); 
	return false;
};

/**
 * 
 * @param name
 * @param value
 * @param days
 * @returns
 */
function setCookie(name,value,days) {
	//console.log(value);
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "")  + expires + ";secure path=/";
}

/**
 * 
 * @param name
 * @returns
 */
function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}

/**
 * 
 * @param name
 * @returns
 */
function eraseCookie(name) {   
    document.cookie = name+'=; Max-Age=-99999999;';  
}

apiCall = function(data, callback) {
	var url = "/application/api/json";
	if(!['login', 'signup'].includes(data.action))
	{
		data.access_token = getCookie("access_token");
	}
	var api = new seiApi(data, callback, url);
	
	return api;
}
