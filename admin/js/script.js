(function($) {
	$(document).ready(function() {
		/*
		 * Add photo item
		 */
		$(".st-upload").live('click', function() {

			var fileFrame = wp.media.frames.file_frame = wp.media({
				multiple : true
			});

			fileFrame.on('select', function() {
				var attachment = fileFrame.state().get('selection').toJSON();
				var i = $('.item').length;
				
				$.each(attachment, function(index) {
					i = i+1;
	  				$('#appendImages').append('<div class="col col-4" id="item-' + i + '">'
						+'<div class="item">'
							
							+'<div class="image">'
								+'<input class="hiddenUrl" type="text" name="image[' + i + '][url]" value="' + attachment[index].url + '" />'
								+'<img src="' + attachment[index].url + '" />'
							+'</div>'
							+'<div class="actions">'
								+'<div class="action edit" id="' + i + '"><div class="dashicons dashicons-edit"></div> Edit</div>'
								+'<div class="action st-remove" id="' + i + '"><div class="dashicons dashicons-trash"></div>Delete</div>'
							+'</div>'
							+'<div class="note">'
								+'<div class="note-content">'
									+'<div class="dashicons dashicons-sort"></div>'+st.note
								+'</div>'
							+'</div>'
							+'<div class="info" id="info-' + i + '">'
								+'<label for="title">' + st.title + ':</label><input type="text" name="image[' + i + '][title]" value="' + attachment[index].title + '" />'
								+'<label for="caption">' + st.caption + ':</label><textarea rows="3" name="image[' + i + '][caption]">' + attachment[index].caption + '</textarea>'
								+'<label for="url">' + st.url + ':</label><input type="url" name="image[' + i + '][url_2]" value="" />'
							+'</div>'
							
						+'</div>'
						
					+'</div>');
	  
				});
			});

			fileFrame.open();

		});

		/*
		 * Remove photo item
		 */
		$(".item .action.st-remove").live('click', function() {
			var id = $(this).attr('id');
			$('#item-' + id).remove();
		});
		
		
		/*
		 * Show info image for edit
		 */
		$(".item .action.edit").live('click', function() {
			var id = $(this).attr('id');
			if ($(this).hasClass('active')){
				$('#info-' + id).removeClass('show');
				$(this).removeClass('active');
			}else{
				$('#info-' + id).addClass('show');
				$(this).addClass('active');
			}
			
			
		});

		
		
		/*
		 * Show/Hide category to insert images
		 */
		$(".st-show-category").live('click', function() {
			if ($('.view-category').hasClass('active')){
				$('.view-category').removeClass('active');
			}else{
				$('.view-category').addClass('active');
				$('#cat_id').attr('multiple' , 'multiple');
			}
		});
		

		/*
		 * Remove gallery
		 */
		$('span.action.remove').click(function() {
			var gID = $(this).attr('id');
			 $( "#remove-dialog-confirm" ).dialog({
		      resizable: false,
		      height: 180,
		      width: 400,
		      modal: true,
		      dialogClass: 'st_gallery_dialog',
		      buttons: {
		        "Remove" : function() {
		        	var data = {
						'action' : 'remove_gallery',
						'id' : gID,
					};
					$.post(ajaxurl, data, function(response) {
						if ($('#message').length){
							$('#message').html('<p>'+st.gallery_removed+'</p>');
						}else{
							$('.wrap.st_gallery_wp').find('h2').append('<div id="message" class="updated below-h2"><p>'+st.gallery_removed+'</p></div>');
						}
						$('#'+$.trim(response)).remove();
						$( '#remove-dialog-confirm' ).dialog( "close" );
					});
		         
		        },
		        Cancel: function() {
		          $( this ).dialog( "close" );
		        }
		      }
		    });
		});
		
		
		/*
		 * Load RSS
		 */
		$(window).load(function() {
      		var feedURL = 'http://beautiful-templates.com/evo/category/products/feed/';
        	$.ajax({
		        type: "GET",
		        url: document.location.protocol + '//ajax.googleapis.com/ajax/services/feed/load?v=1.0&num=1000&callback=?&q=' + encodeURIComponent(feedURL),
		        dataType: 'json',
		        success: function(xml){
		            var item = xml.responseData.feed.entries;
		            
		            var html = "<ul>";
		            $.each(item, function(i, value){
		            	html+= '<li><a href="'+value.link+'">'+value.title+'</a></li>';
		            	if (i===9){
		            		return false;
		            	}
		            });
		             html+= "</ul>";
		             $('.st_load_rss').html(html);
		        }
		        
		    });
      });
		
		
		/*
		 * Validate form
		 */
		$("#stForm").validate({
			rules: {
			    name: "required",
			    width: "required",
			    height: "required",
			    limit: "required",
			    transition_speed: "required",
			    image_delay: "required",
			  },
			  messages: {
			    	name: {
				      	required: "*",
				    },
				    width: {
			      		required: "*",
			    	},
			    	height: {
			      		required: "*",
			    	},
			    	limit: {
			      		required: "*",
			    	},
			    	transition_speed: {
			      		required: "*",
			    	},
			    	image_delay: {
			      		required: "*",
			    	}
			},
			
		});
		
		/*
		 * Sortable
		 */
		if ($('div#appendImages').length>0){
			$( "#appendImages" ).sortable();
   	 		$( "#appendImages" ).disableSelection();
		}
		
		
		
		/*
		 * Enable Tooltipsy
		 */
		$('.tip').tooltipsy();

		/**
		 * Background color picker 
		 */
		var iris_options = {
		    color: false,
		    mode: 'hsl',
		    controls: {
		        horiz: 's', // horizontal defaults to saturation
		        vert: 'l', // vertical defaults to lightness
		        strip: 'h' // right strip defaults to hue
		    },
		    hide: true, // hide the color picker by default
		    border: true, // draw a border around the collection of UI elements
		    target: false, // a DOM element / jQuery selector that the element will be appended within. Only used when called on an input.
		    width: 200, // the width of the collection of UI elements
		    palettes: true, // show a palette of basic colors beneath the square.
		    change: function(event, ui) {
		        $(this).css('background-color', ui.color.toString());
		    }
		};

		$('#bgcolor').css('background-color', $('#bgcolor').val());
		$('#bgcolor').iris(iris_options);
		
		//hide iris
		$('#setting_bar').click(function (e) {
	        if (!$(e.target).is("#bgcolor, .iris-picker, .iris-picker-inner")) {
	            $('#bgcolor').iris('hide');
	        }
	    });
	    //show iris
	     $('#bgcolor').click(function (event) {
	         $('#bgcolor').iris('hide');
	         $(this).iris('show');
	     });
		
		
		$(".tabs-menu a").click(function(event) {
	        event.preventDefault();
	        $(this).parent().addClass("current");
	        $(this).parent().siblings().removeClass("current");
	        var tab = $(this).attr("href");
	        $(".tab-content").not(tab).css("display", "none");
	        $(tab).fadeIn();
	    });

		

		$( "#setting_bar" ).accordion({
			heightStyle: "content",
			active: 0,
		});
		
		
			
		$("#style").change(function(){
			var style = $(this).val();
			if (style=='gallery'){
				$('.gallery-setting').css('display','block');
				$('.skitter-setting').css('display','none');
				$( "#setting_bar" ).accordion({
					heightStyle: "content",
					active: 1,
				});
			}else if (style=='skitter'){
				$('.skitter-setting').css('display','block');
				$('.gallery-setting').css('display','none');
				$( "#setting_bar" ).accordion({
					heightStyle: "content",
					active: 2,
				});
			}
		});
		
	});
 	
})(jQuery);
function st_gallery_edit(el){
	var selectID = jQuery(el).attr('id');
	var galleryID = jQuery('#'+selectID).val();
	window.open('admin.php?page=st_gallery&action=edit&id='+galleryID);
}

function st_gallery_insert(el){
	 var selectID = jQuery(el).attr('id');
	 var galleryID = jQuery('#'+selectID).val();
	 var id = parseInt(selectID.replace(/[^0-9]/gi, ''),10);
	 var textarea = jQuery('#widget-st_gallery_widget-'+id+'-widgetHTML').val();
	
	 var newtext = textarea+'[st-gallery id="'+galleryID+'"]';
	 jQuery('#widget-st_gallery_widget-'+id+'-widgetHTML').val(newtext);
}
