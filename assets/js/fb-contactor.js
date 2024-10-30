jQuery(document).ready(function () {
     
     /**
    * verify the api code
    * @since 1.0
    */
    jQuery(document).on('click', '#save-fb-contactor', function () {
        jQuery( ".loading-sign" ).addClass( "loading" );
        var data = {
        action: 'cf7fb_contactor_verify_integation',
        appid: jQuery('#fb-app-id').val(),
        appsecret: jQuery('#fb-app-secret').val(),
        security: jQuery('#gs-ajax-nonce').val()
      };
      
      jQuery.post(ajaxurl, data, function (response ) {
          if( ! response.success ) { 
            jQuery( ".loading-sign" ).removeClass( "loading" );
            jQuery( "#gs-validation-message" ).empty();
            jQuery("<span class='error-message'>App ID and Secret cannot be empty</span>").appendTo('#gs-validation-message');
          } else {
            jQuery( ".loading-sign" ).removeClass( "loading" );
            jQuery( "#gs-validation-message" ).empty();
            jQuery("<span class='gs-valid-message'>App ID and Secret saved. Thanks.</span>").appendTo('#gs-validation-message'); 
          }
      });
      
    });   
    
    
    jQuery(document).on('click', '#contact-with-facebook', function () {
    	  //alert('i am in');
    	  
    	  var apiResult;
    	  
    	  /*FB.getLoginStatus(function(response) {
  				if (response.status === 'connected') {
			    	console.log('Logged in.');
			    	
			    	FB.logout(function(response) {
  						// user is now logged out
  						console.log('Logged OUT now.');
  						console.log(response);
					});
			  	}
		  });*/
    	  
    	  FB.getLoginStatus(function(response) {
  				if (response.status === 'connected') {
			    	//console.log('Logged in.');
			  	}
			  	else {
			  		console.log('NOT logged in. Will ask for permissions');
			    	apiResult = FB.login(function(){
			    		FB.api('/me', {fields: 'id, name, email, link, location, relationship_status, birthday'}, function(response) {
  							console.log(response);
  							if (response.link !== 'undefined') { 
	  							jQuery("input[name='your-fb']").each(function() {
		    						this.value = response.link;
								});
							} else if (response.id !== 'undefined') { 
	  							jQuery("input[name='your-fb']").each(function() {
		    						this.value = 'https://www.facebook.com/app_scoped_user_id/' + response.id + '/';
								});
							} 
  						    
  							if (response.name !== 'undefined') { 
  								jQuery("input[name='your-name']").each(function() {
		    						this.value = response.name;
								});
							
								jQuery("input[name='your-subject']").each(function() {
		    						this.value = 'Interview with ' + response.name;
								});
							}
							
							if (response.email !== 'undefined') { 
	  							jQuery("input[name='your-email']").each(function() {
		    						this.value = response.email;
								});
							}
							
							var msg = "";
							if (response.location !== 'undefined') { msg = msg + '\r\n' + 'Location: ' + response.location; }
							if (response.gender !== 'undefined') { msg = msg + '\r\n' + 'Gender: ' + response.gender; }
							if (response.relationship_status !== 'undefined') { msg = msg + '\r\n' + 'Relationship: ' + response.relationship_status; }
							if (response.birthday !== 'undefined') { msg = msg + '\r\n' + 'Birthday: ' + response.birthday; }
							if (response.age_range !== 'undefined') { msg = msg + '\r\n' + 'Age range: ' + response.age_range; }
							jQuery("input[name='your-message']").each(function() {
		    					this.value = msg ;
							});
						});
			    	}, {scope: 'email,user_age_range,user_location,user_birthday,user_gender,name,picture,first_name,last_name', return_scopes: true});
			  	}
		  });
		  
		  FB.getLoginStatus(function(response) {
  				if (response.status === 'connected') {
			    	console.log('Logged in.');
			    	//, profile_pic
			    	FB.api('/me', {fields: 'id, name, email, link, location, relationship_status, birthday'}, function(response) {
  						console.log(response);
  						if (response.link !== 'undefined') { 
	  						jQuery("input[name='your-fb']").each(function() {
		    					this.value = response.link;
							});
						} else if (response.id !== 'undefined') { 
	  						jQuery("input[name='your-fb']").each(function() {
		    					this.value = 'https://www.facebook.com/app_scoped_user_id/' + response.id + '/';
							});
						} 
  						    
  						if (response.name !== 'undefined') { 
  							jQuery("input[name='your-name']").each(function() {
		    					this.value = response.name;
							});
							
							jQuery("input[name='your-subject']").each(function() {
		    					this.value = 'Interview with ' + response.name;
							});
						}
							
						if (response.email !== 'undefined') { 
	  						jQuery("input[name='your-email']").each(function() {
		    					this.value = response.email;
							});
						}
						
						var msg = "";
						if (response.location !== 'undefined') { msg = msg + '\r\n' + 'Location: ' + response.location; }
						if (response.gender !== 'undefined') { msg = msg + '\r\n' + 'Gender: ' + response.gender; }
						if (response.relationship_status !== 'undefined') { msg = msg + '\r\n' + 'Relationship: ' + response.relationship_status; }
						if (response.birthday !== 'undefined') { msg = msg + '\r\n' + 'Birthday: ' + response.birthday; }
						if (response.age_range !== 'undefined') { msg = msg + '\r\n' + 'Age range: ' + response.age_range; }
						jQuery("input[name='your-message']").each(function() {
	    					this.value = msg ;
						});
					});
			  	}
		  });
    	        
    }); 
         
});
