function logout(){ 
		$.get('/wp-handler.php?__class=Site&__proc=__userLogout',{ajax:  "1"},
					function(data){
						window.location = "/";
						}
						
				);
		}
function setLocationCookie(loc){
	$.get('/wp-handler.php?__class=Site&__proc=__setLocationCookie',{ajax:  "1",loc: loc},
		function(data){
			if(loc == "/"){
				window.location = "/";
				} else {
				window.location = "/classifieds/"+loc;
				}
			}
			
	);
}
function setCookie(c_name,value,expiredays) {
	var exdate=new Date();
	exdate.setDate(exdate.getDate()+expiredays);
	document.cookie=c_name+ "=" +escape(value)+
	((expiredays==null) ? "" : ";expires="+exdate.toUTCString());
}
function getCookie(c_name)
{
if (document.cookie.length>0)
  {
  c_start=document.cookie.indexOf(c_name + "=");
  if (c_start!=-1)
    {
    c_start=c_start + c_name.length+1;
    c_end=document.cookie.indexOf(";",c_start);
    if (c_end==-1) c_end=document.cookie.length;
    return unescape(document.cookie.substring(c_start,c_end));
    }
  }
return "all";
}
$(function(){
	
	var validator = $("#login_form").validate({ 
		rules: {
			email: {
				required:true
			},
			password: {
				required:true
			}		
		},
		messages: {
			email: {
				required: "Please enter username"
			},
			password: {
				required: "Please enter password"						
			}
		},
		errorElement: "em",
		success: function(label) {
			label.hide();
		},
		submitHandler: function() {
			var AJAX_URL = '/wp-handler.php?__ajax_request=1';
			var serial = $('#login_form').serialize();
			var url = AJAX_URL + '&__class=Site&__proc=__userLogin&' + serial;	
			$.post(url, {
				ajax:  "1"},
					function(data){	redirect = $('#__redirect').val();
						if(data == 'Invalid login, please try again') {
							$('.message').html(data);
							} else {
								$('.loginbox').html(data);
								window.location = redirect;
								}
						
					 }
			);
		} 
	});
});