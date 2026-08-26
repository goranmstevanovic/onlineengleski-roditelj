$(document).ready(function(){  
	$("#submit").click(function(){
	var msg = $("#msg3").val();

	if(msg3==''){
	alert("Treba nesto napisati u poruku da bi je pamtili....!!");   	
	}
	else{
	// Returns successful data submission message when the entered information is stored in database.
	$.post("komentara_iptv.php",{ msg1:msg},
				function(data) {
				alert(data);
				$('#form')[0].reset(); //To reset form fields
				});
		
		}
	});
	});