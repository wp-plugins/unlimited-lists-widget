<?php
/**
 * Plugin Name: Unlimited Lists Widget
 * Plugin URI: http://austin.passy.co/wordpress-plugins/unlimited-lists-widget
 * Description: Add unlimited lists to your sidebars!
 * Version: 0.1.2
 * Author: Austin Passy
 * Author URI: http://austin.passy.co
 *
 * @copyright 2012 - 2015
 * @author Austin Passy
 * @link http://frosty.media/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package unlimited_lists_widget
 */
 
if ( !class_exists( 'unlimited_lists_widget' ) ) {

	add_action( 'widgets_init', 'register_unlimited_lists_widget' );

	function register_unlimited_lists_widget() {
		register_widget( 'unlimited_lists_widget' );
	}

	class unlimited_lists_widget extends WP_Widget {
	
		var $prefix;
		var $textdomain;
	
		/**
		 * Set up the widget's unique name, ID, class, description, and other options.
		 */
		function __construct() {
			$this->prefix = 'unlimited-lists-widget';
			$this->textdomain = 'unlimited-lists';
	
			$widget_ops = array( 'classname' => 'unlimitedlists', 'description' => __( 'An advanced widget for unlimted listed items.', $this->textdomain ) );
			$control_ops = array( 'width' => 525, 'height' => 350, 'id_base' => "{$this->prefix}-unlimitedlists" );
            parent::__construct( "{$this->prefix}-unlimitedlists", __( 'Unlimited Lists', $this->textdomain ), $widget_ops, $control_ops );
			
			add_action( 'admin_head', array( $this, 'jquery' ), 99 );
		}
	
		/**
		 * Outputs the widget based on the arguments input through the widget controls.
		 */
		function widget( $args, $instance ) {
			extract( $args );
	
			$args = array();
	
			$args['tag'] = $instance['tag'];
			$args['list'] = $instance['list'];
			
			$args['love'] = isset( $instance['love'] ) ? $instance['love'] : true;
			$args['show'] = isset( $instance['show'] ) ? $instance['love'] : true;
			
			$hide = ( !isset( $instance['show'] ) ) ? '' : ' style="display:none;"';
			
			$link_love = ( $args['love'] ) ? sprintf( __( '<p%s><a href="%s">Unlimited Lists</a> widget built by <a href="%s">Frosty</a>.</p>', $this->textdomain ), $hide, 'http://frosty.me/unlimited-lists/', 'http://austin.passy.co/wordpress-plugins/unlimited-lists-widget/' ) : null;
			
			echo $before_widget;
	
			if ( $instance['title'] )
				echo $before_title . apply_filters( 'widget_title', $instance['title'] ) . $after_title;
							
			echo "<{$args['tag']}>\n";
			
			if ( is_array( $args['list'] ) ) {
				
				foreach( $args['list'] as $item ) :
				
					echo "\t<li>{$item}</li>\n";
					
				endforeach;
				
			} else {
				
				echo "\t<li>{$args['list']}</li>\n";
				
			}
			
			echo "</{$args['tag']}>\n";
			
			if ( !is_null( $link_love ) ) echo $link_love;
	
			echo $after_widget;
		}
	
		/**
		 * Updates the widget control options for the particular instance of the widget.
		 */
		function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
	
			$instance = $new_instance;
	
			$instance['tag'] = esc_attr( $new_instance['tag'] );
			
			if ( is_array( $instance['list'] ) ) {
				array_walk_recursive( $instance['list'], 'esc_attr' );
			} else {
				$instance['list'] = esc_attr( $new_instance['list'] );
			}
			
			$instance['love'] = isset( $new_instance['love'] ) ? true : false;
			$instance['show'] = isset( $new_instance['show'] ) ? true : false;
	
			return $instance;
		}
		
		/**
		 * jQuery that handles the cloneing of each setting field.
		 *
		 * @ref http://www.johngadbois.com/adding-your-own-callbacks-to-wordpress-ajax-requests/
		 */
		function jquery() {
			global $pagenow;
			
			if ( 'widgets.php' == $pagenow ) { ?>
            
			<script type="text/javascript">
			function unlimitedlists() {
				jQuery('a.add-item').on('click',function(e) {
					var $this = jQuery(this).parent().parent().parent();
					var clone = jQuery($this.find('div.item-wrapper'));
					var value = $this.find('p.list-item:last label span').text();
					var newValue = parseInt(value) + 1;

					//console.log( clone );
					$this.find('p.list-item:last').clone().appendTo( clone );
					$this.find('p.list-item:last input[type="text"]').val('');
					$this.find('p.list-item:last label span').text(newValue);
					unlimitedlistsclone();
					e.preventDefault();
					return false;
				});
			}
			function unlimitedlistsclone() {
				jQuery('.unlimited-lists-widget-controls p.list-item').each(function() {
					var $item = jQuery(this);
					jQuery(this).children('a.delete-item').on('click',function(e) {
						$item.remove();
						e.preventDefault;
						return false;
					});			
				});
			}
				
			jQuery(document).ready(function($) {
				unlimitedlists();
				unlimitedlistsclone();
			});
			
			jQuery(document).ajaxSuccess(function(e, xhr, settings) {
				var widget_id_base = '<?php echo "{$this->prefix}-unlimitedlists"; ?>';
				
				//console.log( settings );
				if ( settings.data.search('action=save-widget') != -1 && settings.data.search('id_base=' + widget_id_base) != -1 ) {
					unlimitedlists();
					unlimitedlistsclone();
				}
			});
			</script>
			<?php }
		}
	
		/**
		 * Displays the widget control options in the Widgets admin screen.
		 */
		function form( $instance ) {
	
			//Defaults
			$defaults = array(
				'title' 	=> __( '', $this->textdomain ),
				'tag' 	=> 'ul',
				'list'	=> '',
				/* *******************/
				'love'		=> true,
				'show'		=> true,
			);
			$instance = wp_parse_args( (array) $instance, $defaults );
			
			$tag = array( 'ul' => __( 'Unordered (ul)', $this->textdomain ), 'ol' => __( 'Ordered (ol)', $this->textdomain ) );
			
			$items = $instance['list'];
	
			$count = 0;	
			$count = count( $items );
			$lists = $items;
			
			if ( $items[$count] != '' ) {
				$lists = array(
					$items[$count],
				);
			}
			
			$count++;
	
			?>
            
			<div id="list-<?php echo mt_rand(); ?>" class="unlimited-lists-widget-controls">
			
            <p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', $this->textdomain ); ?></label>
				<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
			</p>
			
			<p>
				<label for="<?php echo $this->get_field_id( 'tag' ); ?>"><code><?php _e( 'tag', $this->textdomain ); ?></code></label>
				<select class="widefat" id="<?php echo $this->get_field_id( 'tag' ); ?>" name="<?php echo $this->get_field_name( 'tag' ); ?>">
					<?php foreach ( $tag as $option_value => $option_label ) { ?>
						<option value="<?php echo $option_value; ?>" <?php selected( $instance['tag'], $option_value ); ?>><?php echo $option_label; ?></option>
					<?php } ?>
				</select>
			</p>
            
            <div class="item-wrapper">
            
            <?php if ( $lists ) : ?>
        
				<?php foreach ( $lists as $key => $item ) : ?>
                
                <p class="list-item">
                	<label for="<?php echo $this->get_field_id( 'list' ); ?>"><?php printf( __( 'Item <span>%d</span>:', $this->textdomain ), $key + 1 ); ?></label><br />
                    <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'list' ); ?>" name="<?php echo $this->get_field_name( 'list' ); ?>[]" value="<?php echo esc_attr( $item ); ?>" style="width:94%" />
                    <a class="delete-item button-secondary" href="#" onclick="return false;" title="&times;">&times;</a>
                </p>
                
                <?php endforeach; ?>
            
        	<?php else : ?>
            
                <p class="list-item">
                	<label for="<?php echo $this->get_field_id( 'list' ); ?>"><?php _e( 'Item:', $this->textdomain ); ?></label><br />
                    <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'list' ); ?>" name="<?php echo $this->get_field_name( 'list' ); ?>[]" value="<?php echo esc_attr( $instance['list'] ); ?>" style="width:94%" />
                    <a class="delete-item button-secondary" href="#" onclick="return false;" title="<?php _e( 'Delete', $this->textdomain ); ?>">&times;</a>
                </p>
        
            <?php endif; ?>
            
            </div><!-- .item-wrapper -->
            
            <p>
                <span><a class="add-item button-secondary" href="#" onclick="return false;" title="<?php _e( 'Add Item', $this->textdomain ); ?>"><?php _e( 'Add Item', $this->textdomain ); ?></a></span>
            </p>
            
            <p>
                <label for="<?php echo $this->get_field_id( 'love' ); ?>">
                <input class="checkbox" type="checkbox" <?php checked( $instance['love'], true ); ?> id="<?php echo $this->get_field_id( 'love' ); ?>" name="<?php echo $this->get_field_name( 'love' ); ?>" />
                <span class="description"><?php _e( 'Show some author love.', $this->textdomain ); ?></span></label>
                <br />
                <label for="<?php echo $this->get_field_id( 'show' ); ?>">
                <input class="checkbox" type="checkbox" <?php checked( $instance['show'], true ); ?> id="<?php echo $this->get_field_id( 'show' ); ?>" name="<?php echo $this->get_field_name( 'show' ); ?>" />
                <span class="description"><?php _e( 'Output author love but hide it.', $this->textdomain ); ?></span></label>
            </p>
            
            <p>
                <span class="description"><?php _e( 'Please note that once your save this widget you\'ll have to refresh the page in order for the jQuery to work. This is an AJAX bug I\'ll have to work out.', $this->textdomain ); ?></span>
            </p>
            
            </div>
			<div style="clear:both;">&nbsp;</div>
		<?php
		}
	}
	
};