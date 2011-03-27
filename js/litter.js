/**
 *  litter.js
 *  
 * Litter is coded by Scott VonSchilling. He needs a job. If you like
 * what you see, please email scottvonschilling [at] gmail [dot] com.
 *  
 */
$(document).ready(function(){
	

	
	// Setup Litter on Welcome page.
	
	if ($('#setup_litter').length != 0){
		$('#setup_litter').html("Setting up your Litter demo...");
		
		$.ajax({url:"createNewSession.php",
				success:function(result){
					if (result == "success"){
						$('#setup_litter').html('<a href="./index.php?cookie=true">Click here to launch Litter!</a>'); 
					}
				}
		});
	} else 
		setInterval (getNewLitts, 10000);
	
	function getNewLitts(){
		var top = $("#top_litt").html();
		
		var cb = function(response){
			if (response.status == "ok" && response.text != ""){
				var newEl = $('<div></div>').html(response.text).hide();
				$('#litt_space').prepend(newEl);
				newEl.slideDown("slow");
				$('#top_litt').html(response.top);
			}
		}
		
		$.ajax({url:"getLitts.php?before="+top,success:cb, dataType:"json"});
	}
	
	$("#loadNext10").click(function(){
		var bottom = $("#bottom_litt").html();
		
		var cb = function(response){
			if (response.status == "ok"){
				var newEl = $('<div></div>').html(response.text).hide();
				$('#litt_space').append(newEl);
				newEl.slideDown("slow");
				$("#bottom_litt").html(response.bottom);
			}
		}
		
		$.ajax({url:"getLitts.php?after="+bottom,success:cb, dataType:"json"});
	});
	
	
	$('#new_litt').click(function(){
		var txt = $("#txt_box").val();
		var replyTo = $("#reply_to").html();
		
		if (txt == "") return;
		
		var params = "text="+txt+"&reply="+replyTo;
	
		var cb = function(response){
			  if (response.status == "ok"){
				  $("#txt_box").val("");
				  $("#reply_to").html();
				  $("#top_litt").html(response.id.substr(1));
				  //updateCharLimit();
				  var newEl = $('<div></div>').html(response.text).hide();
				  $('#litt_space').prepend(newEl);
			      newEl.slideDown("slow");
			  }
			
		}
		
		$.ajax({type:"POST",url:"newLitt.php",data:params,success:cb, dataType:"json"});
		
	});
	
	function changeUserPane(userId){
		var cb = function(response){
			if (response.status == "ok"){
				$("#user_pane").html(response.text);
			}
		}
		$.ajax({url:"getUserPane.php?id="+userId,success:cb, dataType:"json"})
	}
	
	$("#txt_box").keyup(updateCharLimit);
	function updateCharLimit(){
		var txt = document.getElementById("txt_box");
		var tinyText = document.getElementById("tiny_text");
		len = 140 - txt.value.length;
		if (len > 20)
			tinyText.style.color="#000000";
		else
			tinyText.style.color="#FF0000";
		
		if (len > 1)
			tinyText.innerHTML = len + " charaters left."; 
		else if (len == 1)
			tinyText.innerHTML = len + " charater left."; 
		else{
			tinyText.innerHTML = "No charaters left."; 
			while(txt.value.length > 140){
				txt.value=txt.value.replace(/.$/,'');
			}
		}
	}
	
	function replyTo(username, id){
		var txt = document.getElementById("txt_box");
		var replySpace = document.getElementById("reply_to");
		replySpace.innerHTML = id;
		txt.value="@"+ username + " " + txt.value;
		updateCharLimit();
	}
	
	
	
	
	

	// Animated loading message.
	var loading = {
		dom:null,
		phase:1,
		go:1,
		message:"Loading",
		timer:null,
		tick:function(){
			if (loading.go){
				switch(loading.phase){
					case 1:
						loading.phase = 2;
						loading.dom.innerHTML = loading.message;
						break;
					case 2:
						loading.phase = 3;
						loading.dom.innerHTML = loading.message + " .";
						break;
					case 3:
						loading.phase = 4;
						loading.dom.innerHTML = loading.message + " ..";
						break;
					case 4:
						loading.phase = 1;
						loading.dom.innerHTML = loading.message + " ...";
						break;
				}
				
			} else 
				clearInterval ( loading.timer );
		},
		init:function(domObj, message){
			loading.dom = document.getElementById(domObj);
			loading.dom.innerHTML = message;
			loading.message = message;
			loading.timer = setInterval (loading.tick, 500);
		}
	}
});