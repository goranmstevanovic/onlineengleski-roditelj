$(document).ready(function(){  
	$("#submit").click(function(){
	var msg = $("#msg2").val();

	if(msg2==''){
	alert("Treba nesto napisati u poruku da bi je pamtili....!!");   	
	}
	else{
	// Returns successful data submission message when the entered information is stored in database.
	$.post("komentara_kredit.php",{ msg1:msg},
				function(data) {
				alert(data);
				$('#form')[0].reset(); //To reset form fields
				});
		
		}
	});
	});