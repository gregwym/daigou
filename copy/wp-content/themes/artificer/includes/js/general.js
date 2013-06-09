/*-----------------------------------------------------------------------------------*/
/* GENERAL SCRIPTS */
/*-----------------------------------------------------------------------------------*/
jQuery(document).ready(function(){

	// Table alt row styling
	jQuery( '.entry table tr:odd' ).addClass( 'alt-table-row' );
	
	// Alt class on widget sidebars
	jQuery( '#sidebar .widget:odd' ).addClass( 'alt' );
		
	// FitVids - Responsive Videos
	jQuery( ".post, .widget, .panel" ).fitVids();
	
	// Add class to parent menu items with JS until WP does this natively
	jQuery("ul.sub-menu").parents('li').addClass('parent');
	
	// Wrap ampersands in spans
	jQuery("p:contains('&')").each(function(){
		jQuery(this).html(jQuery(this).html().replace(/&amp;/, "<span class='ampersand'>&amp;</span>"))
	});
	
	// Responsive Navigation (switch top drop down for select)
	jQuery('ul#top-nav').mobileMenu({
		switchWidth: 767,                   //width (in px to switch at)
		topOptionText: 'Select a page',     //first option text
		indentString: '&nbsp;&nbsp;&nbsp;'  //string for indenting nested items
	});
  	
  	
  	
  	// Show/hide the main navigation
  	jQuery('.nav-toggle').click(function() {
	  jQuery('#navigation').slideToggle('fast', function() {
	  	return false;
	    // Animation complete.
	  });
	});
	
	// Stop the navigation link moving to the anchor (Still need the anchor for semantic markup)
	jQuery('.nav-toggle a').click(function(e) {
        e.preventDefault();
    });
    
    // Add relevent classes to featured products
    jQuery("ul li:first-child").addClass("first");
	jQuery(".featured-products li").eq(1).addClass("second");
	jQuery(".featured-products li").eq(2).addClass("third");
	jQuery(".featured-products li").eq(3).addClass("fourth");
	jQuery(".featured-products li").eq(4).addClass("fifth");
	jQuery(".featured-products li").eq(5).addClass("sixth");
	jQuery(".featured-products li").eq(6).addClass("seventh");
		
});