(function ( $ ) {
	"use strict";

    $(document).ready(function($) {
        $('.ns_featured_posts_icon').on('click', function() {
            var $this = $(this);

            var $post_id = $this.data('postid');
            var $uno_status = (typeof $this.data('uno') !== 'undefined');

            var $target_status = ( $this.hasClass( 'selected' ) ) ? 'no' : 'yes';
            var $uno_value = ( $uno_status ) ? 1 : 0;

            $.post(
            	NSFP_OBJ.ajaxurl,
            	{
            		"action": "nsfeatured_posts",
            		"post": $post_id,
            		"ns_featured": $target_status,
            		"uno": $uno_value,
            		"nonce": NSFP_OBJ.nonce,
            	},
            	function(data, status) {
            		if ( 'success' == status) {
	            		$this.toggleClass('selected');
            		}
            	} );
        });
    });

}(jQuery));
