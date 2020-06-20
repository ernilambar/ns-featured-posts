(function ( $ ) {
	"use strict";

    jQuery(document).ready(function($){
            jQuery('.ns_featured_posts_icon').click(function() {
                var selected = 'yes';
                if ( jQuery(this).hasClass( 'selected' ) ){
                    jQuery(this).removeClass( 'selected' );
                    selected = 'no';
                } else { jQuery(this).addClass( 'selected' ); }
                // get id
                var tempID = jQuery(this).attr( 'id' );
                    tempID = tempID.split( '_' );
                jQuery.post( ajaxurl, 'action=nsfeatured_posts&post='+tempID[1]+'&ns_featured='+selected );

            });
        });

}(jQuery));
