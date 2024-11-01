jQuery(document).on('click', '.switch', function(){	
//jQuery('.switch-ch').change(function(){
	var url = location.href;
	url = url.substring(0, url.lastIndexOf('/'));
	if(jQuery('.switch-ch').is(":checked")){
		var checkbox = 1;
		window.location.href = url+'/index.php?clearmenu=on';		
    }else{
		var checkbox = 0;
		window.location.href = url+'/index.php?clearmenu=off';
	}
	
	//execute ajax call
	/*var ajaxurl = 'https://alfadevelopers.ro/wp-leo/wp-admin/admin-ajax.php ';
	var data = {
	    'action': 'change_menu_state_function',
	    'checkbox': checkbox
	}; 
	jQuery.post(ajaxurl, data, function(response) {
		console.log('Got this from the server: ' + response);         
	}); */
});


jQuery(document).ready(function(){
	jQuery('.ad-un').delay(3000).fadeOut("slow");
});
	