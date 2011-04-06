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
		$('#setup_litter').html('Setting up your Litter demo...');
		
		$.ajax({url:'createNewSession.php',
				success:function(result){
					if (result == 'success'){
						$('#setup_litter').html('<a href="./index.php?cookie=true">Click here to launch Litter!</a>'); 
					}
				}
		});
	} else 
		setInterval (getNewLitts, 10000);
	
	// AJAX call to get new litts.
	function getNewLitts(){
		var top = $('#top_litt').html();
		
		var cb = function(response){
			if (response.status == 'ok' && response.text != ''){
				var newEl = $('<div></div>').html(response.text).hide();
				$('#litt_space').prepend(newEl);
				newEl.slideDown('slow');
				$('#top_litt').html(response.top);
			}
		}
		
		$.ajax({url:'getLitts.php?before='+top,success:cb, dataType:'json'});
	}
	
	// Load the next 10 Litts at bottom of screen.
	$('#loadNext10').click(function(){
		var bottom = $('#bottom_litt').html();
		
		var cb = function(response){
			if (response.status == 'ok'){
				var newEl = $('<div></div>').html(response.text).hide();
				$('#litt_space').append(newEl);
				newEl.slideDown('slow');
				$('#bottom_litt').html(response.bottom);
			}
		}
		
		$.ajax({url:'getLitts.php?after='+bottom,success:cb, dataType:'json'});
	});
	
	// POST a new litt to the server
	$('#new_litt').click(function(){
		var txt = $('#txt_box').val();
		var replyTo = $('#reply_to').html();
		
		if (txt == '') return;
		
		var params = 'text='+txt+'&reply='+replyTo;
	
		var cb = function(response){
			  if (response.status == 'ok'){
				  $('#txt_box').val('');
				  $('#reply_to').html();
				  $('#top_litt').html(response.id.substr(1));
				  updateCharLimit();
				  var newEl = $('<div></div>').html(response.text).hide();
				  $('#litt_space').prepend(newEl);
			      newEl.slideDown('slow');
			  }
			
		}
		
		$.ajax({type:'POST',url:'newLitt.php',data:params,success:cb, dataType:'json'});
	});
	
	// When user clicks picture, show that user's info.
	var userPane = function(){
		var userId = $(this).find('input').val();
		if (userId){
			var cb = function(response){
				if (response.status == 'ok'){
					$('#user_pane').html(response.text);
				}
			}
			$.ajax({url:'getUserPane.php?id='+userId,success:cb, dataType:'json'});
		}
	};
	$('#user_list').delegate('span', 'click', userPane);
	$('#litt_space').delegate('span,.litt_username', 'click', userPane);
	
	// Update the '140 characters left' message.
	$('#txt_box').keyup(updateCharLimit);
	function updateCharLimit(){
		var txt = document.getElementById('txt_box');
		var tinyText = document.getElementById('tiny_text');
		len = 140 - txt.value.length;
		if (len > 20)
			tinyText.style.color='#000000';
		else
			tinyText.style.color='#FF0000';
		
		if (len > 1)
			tinyText.innerHTML = len + ' charaters left.'; 
		else if (len == 1)
			tinyText.innerHTML = len + ' charater left.'; 
		else{
			tinyText.innerHTML = 'No charaters left.'; 
			while(txt.value.length > 140){
				txt.value=txt.value.replace(/.$/,'');
			}
		}
	}
	
	// Reply to Litt.
	$('#litt_space').delegate('.litt_reply', 'click', function(){
		var id = $(this).find('input').val();
		if (id){
			var s = id.split(',');
			id = s[0];
			var replyTo = s[1];
			$('#reply_to').html('l'+id);
			$('#txt_box').val('@'+ replyTo + ' ' + $('#txt_box').val());
			updateCharLimit();
		}
	});
	
	// Logout confirmation.
	$('#signMeOff').click(function(){
		 return confirm('Are you sure you want to sign out? If you sign out, your demo will over, your data will be deleted, and you will return to the welcome page.');
	});
});