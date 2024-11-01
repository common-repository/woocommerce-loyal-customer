jQuery(document).ready(function() {
	
	jQuery('input#current-page-selector').keypress(function(){
		 setTimeout(function() {
               // alert(jQuery('input#current-page-selector').val());
		jQuery('input#current-page-selector').attr('value',jQuery('input#current-page-selector').val());
		var prev_val = jQuery('input#current-page-selector').val();
		if (jQuery('.pagination-links .prev-page').length) {
		
		
		var prev_page = jQuery('input#current-page-selector').parents('.pagination-links').find('.prev-page').attr('href');
		var prev_page_page_left = prev_page.split("=");
		var prev_page_page_paged = prev_page_page_left[(prev_page_page_left.length)-1];
		var prev_pages = jQuery('input#current-page-selector').parents('.pagination-links').find('.next-page').attr('href');
		var prev_page_pages_left = prev_pages.split("=");
		var prev_page_pages_paged = prev_page_pages_left[(prev_page_pages_left.length)-1];
		

			prev_page_page_left[(prev_page_page_left.length)-1] = prev_val-1;
			var prev_page_href = prev_page_page_left.join("=");
			prev_page_pages_left[(prev_page_pages_left.length)-1] = parseInt(prev_val)+parseInt(1);
			var prev_pages_href = prev_page_pages_left.join("=");
			//console.log(prev_page_href);
			jQuery('input#current-page-selector').parents('.pagination-links').find('.prev-page').attr('href',prev_page_href);
			jQuery('input#current-page-selector').parents('.pagination-links').find('.next-page').attr('href',prev_pages_href);
		
			}
			
			if (jQuery('.pagination-links .next-page').length) {
		var prev_pages = jQuery('input#current-page-selector').parents('.pagination-links').find('.next-page').attr('href');
		var prev_page_pages_left = prev_pages.split("=");
		var prev_page_pages_paged = prev_page_pages_left[(prev_page_pages_left.length)-1];
		

			prev_page_pages_left[(prev_page_pages_left.length)-1] = parseInt(prev_val)+parseInt(1);
			var prev_pages_href = prev_page_pages_left.join("=");
			//console.log(prev_page_href);
			jQuery('input#current-page-selector').parents('.pagination-links').find('.next-page').attr('href',prev_pages_href);
		}
			
               
            }, 200);
		
	});

//email list csv generation
jQuery('.trs_wclc_emaillist_csv').click(function(){
	jQuery.post(ajaxurl, {
                    action: "trs_wc_loyal_emaillist_csv",
                }, function(response) {
					//console.log(response);
					response = response.substring(0,response.length - 1);
					 var blob=new Blob([response]);
            var link=document.createElement('a');
            link.href=window.URL.createObjectURL(blob);
            link.download="report_"+new Date()+".csv";
            link.click();
                });
		 
	});
});
