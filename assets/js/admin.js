(function ( $ ) {
	"use strict";

    $(document).ready(function($) {
        $('.ns_featured_posts_icon').on('click', function() {
            var $this = $(this);

            var $post_id = $this.data('postid');

            var $target_status = ( $this.hasClass( 'selected' ) ) ? 'no' : 'yes';

            $.post(
            	NSFP_OBJ.ajaxurl,
            	{
            		"action": "nsfeatured_posts",
            		"post": $post_id,
            		"ns_featured": $target_status,
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
