<?php
/**
 * Popular Posts with Thumbnail widget.
 *
 * @package    mysocial
 * @author     HappyThemes
 * @copyright  Copyright (c) 2016, HappyThemes
 * @license    http://www.gnu.org/licenses/gpl-2.0.html
 * @since      1.0.0
 */
class mysocial_Popular_Widget extends WP_Widget {

	/**
	 * Sets up the widgets.
	 *
	 * @since 1.0.0
	 */
	function __construct() {

		// Set up the widget options.
		$widget_options = array(
			'classname'   => 'widget-mysocial-popular widget_posts_thumbnail',
			'description' => __( 'Display popular posts by comments with thumbnails.', 'mysocial' )
		);

		// Create the widget.
		parent::__construct(
			'mysocial-popular',                                   // $this->id_base
			__( '&raquo; Popular Posts', 'mysocial' ), // $this->name
			$widget_options                                       // $this->widget_options
		);

		// Flush the transient.
		add_action( 'save_post'   , array( $this, 'flush_widget_transient' ) );
		add_action( 'deleted_post', array( $this, 'flush_widget_transient' ) );
		add_action( 'switch_theme', array( $this, 'flush_widget_transient' ) );

	}

	/**
	 * Outputs the widget based on the arguments input through the widget controls.
	 *
	 * @since 1.0.0
	 */
	function widget( $args, $instance ) {
		extract( $args );

		// Output the theme's $before_widget wrapper.
		echo $before_widget;

		// If the title not empty, display it.
		if ( $instance['title'] ) {
			echo $before_title . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) . $after_title;
		}

		// Display the popular posts.
		if ( false === ( $popular = get_transient( 'mysocial_popular_widget_' . $this->id ) ) ) {

			// Posts query arguments.
			$args = array(
				'post_type'      => 'post',
				'posts_per_page' => $instance['limit'],
				'orderby'        => 'comment_count'
			);

			// The post query
			$popular = new WP_Query( $args );

			// Store the transient.
			set_transient( 'mysocial_popular_widget_' . $this->id, $popular );

		}

		global $post;
		if ( $popular->have_posts() ) {
			echo '<ul>';

				while ( $popular->have_posts() ) : $popular->the_post();

					echo '<li class="clear">';
						if ( has_post_thumbnail() ) {

							echo '<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . '<div class="thumbnail-wrap">';
								the_post_thumbnail();  
							echo '</div>' . '</a>';
							
						}

						echo '<div class="entry-wrap"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . esc_attr( get_the_title() ) . '</a>'; 

						if ( $instance['show_views'] ) :
							echo '<div class="entry-meta">' . get_the_date() . '</div>';
						endif;
					echo '</div></li>';

				endwhile;

			echo '</ul>';
		}

		// Reset the query.
		wp_reset_postdata();

		// Close the theme's widget wrapper.
		echo $after_widget;

	}

	/**
	 * Updates the widget control options for the particular instance of the widget.
	 *
	 * @since 1.0.0
	 */
	function update( $new_instance, $old_instance ) {

		$instance = $new_instance;
		$instance['title']     = strip_tags( $new_instance['title'] );
		$instance['limit']     = (int) $new_instance['limit'];
		$instance['show_views'] = isset( $new_instance['show_views'] ) ? (bool) $new_instance['show_views'] : false;

		// Delete our transient.
		$this->flush_widget_transient();

		return $instance;
	}

	/**
	 * Flush the transient.
	 *
	 * @since  1.0.0
	 */
	function flush_widget_transient() {
		delete_transient( 'mysocial_popular_widget_' . $this->id );
	}

	/**
	 * Displays the widget control options in the Widgets admin screen.
	 *
	 * @since 1.0.0
	 */
	function form( $instance ) {

		// Default value.
		$defaults = array(
			'title'     => esc_html__( 'Popular Posts', 'mysocial' ),
			'limit'     => 5,
			'show_views' => true
		);

		$instance = wp_parse_args( (array) $instance, $defaults );
	?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">
				<?php _e( 'Title', 'mysocial' ); ?>
			</label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'limit' ); ?>">
				<?php _e( 'Number of posts to show', 'mysocial' ); ?>
			</label>
			<input class="small-text" id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" type="number" step="1" min="0" value="<?php echo (int)( $instance['limit'] ); ?>" />
		</p>

		<p>
			<input class="checkbox" type="checkbox" <?php checked( $instance['show_views'] ); ?> id="<?php echo $this->get_field_id( 'show_views' ); ?>" name="<?php echo $this->get_field_name( 'show_views' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_views' ); ?>">
				<?php _e( 'Display post date?', 'mysocial' ); ?>
			</label>
		</p>

	<?php

	}

}
