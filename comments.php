<?php
/**
 * The template for displaying Comments.
 *
 * The area of the page that contains both current comments
 * and the comment form. The actual display of comments is
 * handled by a callback to generate_comment() which is
 * located in the inc/template-tags.php file.
 *
 * @package GeneratePress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}

/**
 * generate_before_comments hook.
 *
 * @since 0.1
 */
do_action( 'generate_before_comments' );
?>
<div id="comments">

	<?php
	/**
	 * generate_inside_comments hook.
	 *
	 * @since 1.3.47
	 */
	do_action( 'generate_inside_comments' );
	
	?>
	<h3 class="comments-title">
		<span>
			<?php
		if (!have_comments()) {
			echo "댓글 남기기";
	}
	else {
			$comments_number = get_comments_number();
			echo $comments_number . " 개 댓글";
	}
			?>
			</span>
		</h3>
				
			<?php
	comment_form();				

	if ( have_comments() ) : ?>
		

		<?php
		/**
		 * generate_below_comments_title hook.
		 *
		 * @since 0.1
		 */
		do_action( 'generate_below_comments_title' );

		if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
			<nav id="comment-nav-above" class="comment-navigation" role="navigation">
				<h2 class="screen-reader-text"><?php esc_html_e( 'Comment navigation', 'generatepress' ); ?></h2>
			
			<?php paginate_comments_links( array('prev_text' => '&laquo; PREV', 'next_text' => 'NEXT &raquo;') ); ?>
			
			</nav><!-- #comment-nav-above -->
		<?php endif; ?>

		<ol class="comment-list">
			<?php
			/*
			 * Loop through and list the comments. Tell wp_list_comments()
			 * to use generate_comment() to format the comments.
			 * If you want to override this in a child theme, then you can
			 * define generate_comment() and that will be used instead.
			 * See generate_comment() in inc/template-tags.php for more.
			 */
			wp_list_comments( array(
				'callback' => 'generate_comment',
			) );
			?>
		</ol><!-- .comment-list -->

		<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
			<nav id="comment-nav-below" class="comment-navigation" role="navigation">
				<h2 class="screen-reader-text"><?php esc_html_e( 'Comment navigation', 'generatepress' ); ?></h2>

			<?php paginate_comments_links( array('prev_text' => '&laquo; PREV', 'next_text' => 'NEXT &raquo;') ); ?>


			</nav><!-- #comment-nav-below -->
		<?php endif;

	endif;
	


	// If comments are closed and there are comments, let's leave a little note, shall we?
	if ( ! comments_open() && '0' != get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
		<p class="no-comments"><?php _e( 'Comments are closed.', 'generatepress' ); // WPCS: XSS OK. ?></p>
	<?php endif;


	?>

</div><!-- #comments -->
