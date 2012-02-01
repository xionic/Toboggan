$(document).ready(function(){

	$("#loginForm").submit(function(){
		console.log("onsubmit called");
		
		console.log($("#passwordInput").val());
		
		var hash = new jsSHA($("#passwordInput").val()).getHash("SHA-256","B64");
		
		//sha-256 the password and replace the value		
		$("#password").val(	hash );
		//submit the form
		return true;
	});	

});

