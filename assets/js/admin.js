( function( $ ) {
	'use strict';

	$( document ).ready( function() {
		$( '.ns_featured_posts_icon' ).on( 'click', function() {
			var $this = $( this );
			var $table = $( '#posts-filter' );

			var $postId = $this.data( 'post_id' );
			var $postType = $this.data( 'post_type' );
			var $maxPosts = $this.data( 'max_posts' );

			var $targetStatus = ( $this.hasClass( 'selected' ) ) ? 'no' : 'yes';
			var $unoStatus = ( typeof $this.data( 'uno' ) !== 'undefined' ) ? 1 : 0;
			var $maxStatus = ( typeof $this.data( 'max_status' ) !== 'undefined' ) ? 1 : 0;

			$.post(
				NSFP_OBJ.ajaxurl,
				{
					action: 'nsfeatured_posts',
					post_id: $postId,
					post_type: $postType,
					ns_featured: $targetStatus,
					uno: $unoStatus,
					max_posts: $maxPosts,
					max_status: $maxStatus,
					nonce: NSFP_OBJ.nonce,
				},
				function( data, status ) {
					if ( 'success' === status ) {
						if ( true === data.status ) {
							$this.toggleClass( 'selected' );

							if ( true === data.uno ) {
								$table.find( '.ns_featured_posts_icon.selected' )
									.not( '[data-post_id="' + $postId + '"]' )
									.each( function( i, el ) {
										$( el ).removeClass( 'selected' );
									} );
							}
						} else {
							alert( data.message );
						}
					}
				} );
		} );
	} );
}( jQuery ) );
