(function($) {
    tinymce.PluginManager.add('st_button_get_gallery', function( editor, url ) {
    	
    	var shortcodeValues = [];
        jQuery.each(list_gallery_name, function(i)
        {
            shortcodeValues.push({text: list_gallery_name[i], value:list_gallery_id[i]});
        });
    	
        editor.addButton( 'st_button_get_gallery', {
            title: 'Insert gallery from ST Gallery WP',
            type: 'button',
            icon: 'icon st_button_get_gallery',
           	onclick: function() {
		   		editor.windowManager.open( {
		        	title: 'ST Gallery WP - Insert Gallery To Post',
		        	width: 500,
		        	height: 100,
		       		body: [{
		            	type: 'listbox', 
		            	name: 'galleryID', 
		            	label: 'Select Gallery: ', 
		            	'values': shortcodeValues,
		        	}],
        			onsubmit: function( e ) {
            			editor.insertContent( '[st-gallery id="' + e.data.galleryID + '"]');
        			}
    			});
			}
        });
    });
})(jQuery);