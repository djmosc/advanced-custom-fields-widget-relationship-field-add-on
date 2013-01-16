<?php
/*
Plugin Name: Advanced Custom Fields - Widget Relationship Field add-on
Plugin URI: https://bitbucket.org/djbokka/widget-relationship-field-add-on-for-advanced-custom-fields
Description: This plugin is an add-on for Advanced Custom Fields. It allows you to use a "relationship" field to select widgets at a page level.
Version: 1.0
Author: Dallas Johnson
License: GPL3
*/

/*  Copyright 2012 Dallas Johnson  (email : dallasjohnson@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 3, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if( ! class_exists( 'acf_Widget' ) && class_exists( 'acf_Relationship' ) ) {

	class acf_Widget extends acf_Relationship {

		//creating a unique string we can use for inheritance
		const INHERIT_STRING = '--INHERIT--';
		const INHERIT_TITLE  = '-------- Inherit From Parent --------';

		//variables for loading files
		var $dir, $path;

		/*--------------------------------------------------------------------------------------
		*
		*	Constructor
		*
		*	@author Dallas Johnson
		*
		*-------------------------------------------------------------------------------------*/
		public function __construct( $parent ) {

			//set paths
			$this->path = plugin_dir_path(__FILE__);
			$this->dir = plugins_url('',__FILE__);

			parent::__construct( $parent );

			$this->name  = 'widget_field';
			$this->title = __( "Widget List", 'acf' );

			// actions
			add_action( 'wp_ajax_acf_get_widget_results', array( &$this, 'acf_get_widget_results' ) );
			add_action( 'admin_print_scripts', array( &$this, 'admin_print_scripts' ) );
			add_action( 'admin_print_styles', array( &$this, 'admin_print_styles' ) );

		}


		/*--------------------------------------------------------------------------------------
		*
		*	acf_get_widget_results
		*
		*	@author Dallas Johnson
		*   @description: Generates HTML for Left column relationship results
		*
		*-------------------------------------------------------------------------------------*/
		public function acf_get_widget_results() {

			// vars
			$options = array(
				'sidebar'       => '',
				'inherit_from'  => '',
				'menu_location' => '',
				'posts_per_page' => 10,
				'paged' => 0
			);

			$ajax = isset( $_POST['action'] ) ? true : false;

			// override options with posted values
			if ( $ajax ) {

				//we're using our own 'args' variable instead of the built-in data attributes
				$options = array_merge( $options, json_decode( stripslashes( $_POST['args'] ), true ) );

				//set the paged data-attribute (only default attribute we're keeping)
				if( array_key_exists('paged', $_POST) )
					$options['paged'] = $_POST['paged']-1;

			}

			// load the widget list
			$paging = array_chunk( $this->get_widgets( $options ), $options['posts_per_page'] );
			$current_page = $paging[$options['paged']];

			$output = '';

			foreach ( $current_page as $post ) {
				$output .= '<li><a href="javascript:;" data-post_id="' . $post->ID . '"><span class="relationship-item-info">' . $post->type . '</span>' . $post->title . '<span class="acf-button-add"></span></a></li>';
			}

			echo $output;

			// die?
			if ( $ajax ) {
				die();
			}

		}


		/*--------------------------------------------------------------------------------------
		 *
		 *	create_field
		 *
		 *	@author Dallas Johnson
		 *
		 *-------------------------------------------------------------------------------------*/
		public function create_field( $field ) {

			// vars
			$defaults = array(
				'sidebar'       => '',
				'inherit_from'  => '',
				'menu_location' => ''
			);

			$field = array_merge( $defaults, $field );

			$args = array(
				'sidebar'       => $field['sidebar'],
				'inherit_from'  => $field['inherit_from'],
				'menu_location' => $field['menu_location']
			);

			$args = htmlspecialchars( json_encode( $args ), ENT_QUOTES, 'UTF-8' );
			?>
		<div class="acf_relationship" data-post_type="widget_field" data-args="<?php echo $args; ?>" data-paged="1">

			<!-- Hidden Blank default value -->
			<input type="hidden" name="<?php echo $field['name']; ?>" value="" />

			<!-- Template for value -->
			<script type="text/html" class="tmpl-li">
				<li>
					<a href="#" data-post_id="{post_id}">{title}<span class="acf-button-remove"></span></a>
					<input type="hidden" name="<?php echo $field['name']; ?>[]" value="{post_id}" />
				</li>
			</script>
			<!-- / Template for value -->

			<!-- Left List -->
			<div class="relationship_left">
				<ul class="bl relationship_list">
					<li class="load-more">
						<div class="acf-loading"></div>
					</li>
				</ul>
			</div>
			<!-- /Left List -->

			<!-- Right List -->
			<div class="relationship_right">
				<ul class="bl relationship_list">
					<?php
					if ( $field['value'] ) {

						foreach ( $field['value'] as $widget_id ) {

							$post = $this->get_widget_object( $widget_id );

							echo '<li><a href="javascript:;" class="" data-post_id="' . $post->ID . '"><span class="relationship-item-info">' . $post->type . '</span>' . $post->title . '<span class="acf-button-remove"></span></a><input type="hidden" name="' . $field['name'] . '[]" value="' . $post->ID . '" /></li>';

						}

					}
					?>
				</ul>
			</div>
			<!-- / Right List -->

		</div>
		<?php

		}


		/*--------------------------------------------------------------------------------------
		*
		*	create_options
		*
		*	@author Dallas Johnson
		*
		*-------------------------------------------------------------------------------------*/
		public function create_options( $key, $field ) {

			// vars
			$defaults = array(
				'max'                => '',
				'sidebar'            => '',
				'inherit_from'       => '',
				'menu_location'      => ''
			);

			$field = array_merge( $defaults, $field );
			?>
		<tr class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label for=""><?php _e( "Sidebar", 'acf' ); ?></label>
			</td>
			<td>
				<?php
				global $wp_registered_sidebars;
				$sidebars = array();

				foreach ( (array) $wp_registered_sidebars as $sidebar ) {
					if ( ! is_active_sidebar( $sidebar['id'] ) )
						continue;

					$sidebars[$sidebar['id']] = $sidebar['name'];
				}

				$this->parent->create_field( array(
					'type'         => 'select',
					'name'         => 'fields[' . $key . '][sidebar]',
					'value'        => $field['sidebar'],
					'choices'      => $sidebars,
					'multiple'     => '0',
				) );

				?>
			</td>
		</tr>
		<tr class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label><?php _e( "Inherit From", 'acf' ); ?></label>
			</td>
			<td>
				<?php
				$options = array(
					''          => 'None',
					'page'      => 'Page Structure',
					'menu'      => 'Menu Structure'
				);

				$this->parent->create_field( array(
					'type'      => 'select',
					'name'      => 'fields[' . $key . '][inherit_from]',
					'value'     => $field['inherit_from'],
					'choices'   => $options
				) );
				?>
			</td>
		</tr>
		<tr class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label><?php _e( "If \"Menu\" inheritance, select menu location to use", 'acf' ); ?></label>
			</td>
			<td>
				<?php
				//get menu ID for the main location
				$menus   = get_nav_menu_locations();
				$options = array( '' => 'None' );

				foreach ( $menus as $menu_key => $menu_value ) {
					$options[$menu_key] = ucwords( $menu_key );
				}

				$this->parent->create_field( array(
					'type'      => 'select',
					'name'      => 'fields[' . $key . '][menu_location]',
					'value'     => $field['menu_location'],
					'choices'   => $options
				) );
				?>
			</td>
		</tr>
		<?php

		}


		/*--------------------------------------------------------------------------------------
		 *
		 *	get_value
		 *	- the Relationship field (parent) method performs additional actions we don't need.
		 *    We just want to return the value.
		 *
		 *	@params
		 *	- $post_id (int) - the post ID which your value is attached to
		 *	- $field (array) - the field object.
		 *
		 *	@author Dallas Johnson
		 *
		 *-------------------------------------------------------------------------------------*/
		public function get_value( $post_id, $field ) {

			return parent::get_value( $post_id, $field );

		}


		/*--------------------------------------------------------------------------------------
		 *
		 *	admin_print_scripts / admin_print_styles
		 *  - include our proxy js function and styles
		 *
		 *	@author Dallas Johnson
		 *
		 *-------------------------------------------------------------------------------------*/
		public function admin_print_scripts() {

			// proxy function for acf.relationship_update_results
			wp_enqueue_script( 'advanced-custom-fields-widget-filter-field-add-on', trailingslashit($this->path) . 'advanced-custom-fields-widget-filter-field-add-on.js', array( 'jquery', 'acf-input-actions' ) );

		}


		/*--------------------------------------------------------------------------------------
		 *
		 *	admin_print_styles
		 *  - include our styles
		 *
		 *	@author Dallas Johnson
		 *
		 *-------------------------------------------------------------------------------------*/
		public function admin_print_styles() {

			// styles to account for no search box
			wp_enqueue_style( 'advanced-custom-fields-widget-filter-field-add-on', trailingslashit($this->path) . 'advanced-custom-fields-widget-filter-field-add-on.css', 'acf-input' );

		}


		/*--------------------------------------------------------------------------------------
		 *
		 *	dynamic_widgets
		 *	- this function is called by sidebar.php and retrieves filtered widget list for page
		 *
		 *  @params
		 *  - $index (string or int) - index of sidebar
		 *
		 *	@author Dallas Johnson
		 *
		 *-------------------------------------------------------------------------------------*/
		public static function dynamic_widgets( $index = 1 ) {

			global $wp_registered_sidebars, $wp_registered_widgets;

			if ( is_int( $index ) ) {
				$index = "sidebar-$index";
			} else {
				$index = sanitize_title( $index );
				foreach ( (array) $wp_registered_sidebars as $key => $value ) {
					if ( sanitize_title( $value['name'] ) == $index ) {
						$index = $key;
						break;
					}
				}
			}

			$sidebars_widgets = wp_get_sidebars_widgets();
			if ( empty( $sidebars_widgets ) )
				return false;

			if ( empty( $wp_registered_sidebars[$index] ) || ! array_key_exists( $index, $sidebars_widgets ) || ! is_array( $sidebars_widgets[$index] ) || empty( $sidebars_widgets[$index] ) )
				return false;

			$sidebar = $wp_registered_sidebars[$index];


			/*--------------------------------------------
			* dynamic_widgets (like dynamic_sidebars) uses the sidebar index.
			* the sidebar option isn't pulled by default from acf's get_field so
			* we need to loop through our acf fields and find the fields with "sidebar" options.
			* the field with the sidebar option that matches our index is what we're after.
			* we can use get_field from that point to retrieve our widget list
			* and remove the widgets that aren't in our list.
			* everything else in this function is default wp dynamic_sidebar function
			*---------------------------------------------*/
			global $acf;
			$post = get_queried_object();

			//set default
			$acf_field = false;

			//get acf fields for loop
			$acf_fields = get_fields( $post->ID );

			//loop acf fields to get our field key
			if ( $acf_fields ) {

				foreach ( $acf_fields as $key => $field ):

					//get acf field key
					$field_key = get_post_meta( $post->ID, '_' . $key, true );

					if ( ! empty( $field_key ) ) {

						//it's an acf field, get the field's acf structure
						$field = $acf->get_acf_field( $field_key );

						//see if it has a "sidebar" option and if it matches our index
						if ( isset( $field['sidebar'] ) && $field['sidebar'] == $index ) {

							//this field matches, set $acf_field to this one
							$acf_field = $key;

							//quit the loop, we have our match
							break;

						}

					}

				endforeach;

				if ( $acf_field ) {

					//set default
					$include_list = array();

					if ( get_field( $acf_field, $post->ID ) ) {

						//build our include list
						$include_list = self::buildIncludeList( $post, $field );

					}

					//set the function's normal array for looping
					$sidebars_widgets[$index] = $include_list;

				}

			}
			/*--------------------------------------------
			* end custom
			*---------------------------------------------*/


			$did_one = false;
			foreach ( (array) $sidebars_widgets[$index] as $id ) {

				if ( ! isset( $wp_registered_widgets[$id] ) ) continue;

				$params = array_merge(
					array( array_merge( $sidebar, array( 'widget_id' => $id, 'widget_name' => $wp_registered_widgets[$id]['name'] ) ) ),
					(array) $wp_registered_widgets[$id]['params']
				);

				// Substitute HTML id and class attributes into before_widget
				$classname_ = '';
				foreach ( (array) $wp_registered_widgets[$id]['classname'] as $cn ) {
					if ( is_string( $cn ) )
						$classname_ .= '_' . $cn;
					elseif ( is_object( $cn ) )
						$classname_ .= '_' . get_class( $cn );
				}
				$classname_                 = ltrim( $classname_, '_' );
				$params[0]['before_widget'] = sprintf( $params[0]['before_widget'], $id, $classname_ );

				$params = apply_filters( 'dynamic_sidebar_params', $params );

				$callback = $wp_registered_widgets[$id]['callback'];

				do_action( 'dynamic_sidebar', $wp_registered_widgets[$id] );

				if ( is_callable( $callback ) ) {
					call_user_func_array( $callback, $params );
					$did_one = true;
				}
			}

			return $did_one;

		}


		/*--------------------------------------------------------------------------------------
		 *
		 *	get_widgets
		 *	- this function is called by create_field on edit screens to produce the html for this field
		 *    most of this function is pulled from the WP dynamic_sidebars function
		 *
		 * 	@params
		 *  - $options (array) - field options
		 *
		 *	@author Dallas Johnson
		 *
		 *-------------------------------------------------------------------------------------*/
		private function get_widgets( $options ) {

			global $wp_registered_sidebars;

			if ( is_int( $options['sidebar'] ) ) {

				$index = 'sidebar-' . $options['sidebar'];

			} else {

				$index = sanitize_title( $options['sidebar'] );

				foreach ( (array) $wp_registered_sidebars as $key => $value ):

					if ( sanitize_title( $value['name'] ) == $index ) {
						$index = $key;
						break;
					}

				endforeach;

			}

			$sidebars_widgets = wp_get_sidebars_widgets();

			//set default
			$posts = array();

			if ( empty( $wp_registered_sidebars[$index] ) || ! array_key_exists( $index, $sidebars_widgets ) || ! is_array( $sidebars_widgets[$index] ) || empty( $sidebars_widgets[$index] ) )
				return $posts;

			if ( isset( $options['inherit_from'] ) and ! empty( $options['inherit_from'] ) )
				$posts[] = $this->get_widget_object( self::INHERIT_STRING );

			//loop through widgets in sidebar, add them to posts
			foreach ( (array) $sidebars_widgets[$index] as $id ) :

				$widget = $this->get_widget_object( $id );

				if ( empty( $widget->title ) )
					continue;

				$posts[] = $widget;

			endforeach;

			return $posts;

		}


		/*--------------------------------------------------------------------------------------
		*
		*	get_widget_object
		*	- builds and returns an object with ID, title and type
		*
		* 	@params
		*  	- $id (int) - widget id
		*
		*	@author Dallas Johnson
		*
		*-------------------------------------------------------------------------------------*/
		private function get_widget_object( $id ) {

			if ( $id == self::INHERIT_STRING )
				return (object) array(
					'ID'    => self::INHERIT_STRING,
					'title' => self::INHERIT_TITLE,
					'type'  => 'none'
				);

			global $wp_registered_widgets;

			if ( ! isset( $wp_registered_widgets[$id] ) )
				return false;

			$classname   = $wp_registered_widgets[$id]['callback'][0]->id_base;
			$instance    = $wp_registered_widgets[$id]['params'][0]['number'];
			$option_list = get_option( $classname );

			if ( empty( $option_list ) )
				$option_list = get_option( 'widget_' . $classname );

			return (object) array(
				'ID'    => $id,
				'title' => ( strlen( $option_list[$instance]['title'] ) > 0 ) ? $option_list[$instance]['title'] : 'No Title',
				'type'  => $wp_registered_widgets[$id]['name']
			);

		}


		/*--------------------------------------------------------------------------------------
		 *
		 *	buildIncludeList
		 *	- this function retrieves all inherited widgets for postID
		 *
		 *  @params
		 *  - $post (object) - post object
		 *  - $field (object) - field object for this acf item
		 *
		 *	@author Dallas Johnson
		 *
		 *-------------------------------------------------------------------------------------*/
		private static function buildIncludeList( $post, $field ) {

			$widgets = get_field( $field['name'], $post->ID );

			if ( $widgets ) {

				//see if this list inherits
				if ( isset( $field['inherit_from'] ) && false !== ( $i = array_search( self::INHERIT_STRING, $widgets ) ) ) {

					//it does, see what we're inheriting from
					switch ( $field['inherit_from'] ):

						case 'page':

							//get parent ID from post and run again
							$parent_post = ( $post->post_parent ) ? get_post( $post->post_parent ) : false;
							break;

						case 'menu':

							//get parent ID from menu item and run again
							$parent_post_ID = self::getMenuParentIDFromPostID( $post->ID, $field['menu_location'] );
							$parent_post    = ( $parent_post_ID ) ? get_post( $parent_post_ID ) : false;
							break;

						default:

							//parent should be 'none'
							$parent_post = false;

					endswitch;

					//this allows for custom parent options outside of PAGE and MENU
					$parent_post = apply_filters( 'advanced-custom-fields-widget-filter-field-add-on-parent-post', $parent_post, $post );

					if ( $parent_post )
						array_splice( $widgets, $i, 1, self::buildIncludeList( $parent_post, $field ) );

				}

			}

			return $widgets;

		}


		/*--------------------------------------------------------------------------------------
		 *
		 *	getMenuParentIDFromPostID
		 *	- this function retrieves menu ID for post
		 *
		 * 	@params
		 *  - $post_id (int) - post ID of item to retrieve menu ID for
		 *  - $menu_location (string) - location of the menu to use for inheritance
		 *
		 *	@author Dallas Johnson
		 *
		 *-------------------------------------------------------------------------------------*/
		private static function getMenuParentIDFromPostID( $post_id = 0, $menu_location ) {

			$menu_items = self::getMasterMenuItems( $menu_location );

			foreach ( $menu_items as $item ) :

				if ( $item->object_id == $post_id ) {

					return self::getPostIDFromMenuID( $item->menu_item_parent, $menu_location );

				}

			endforeach;

			return false;

		}


		/*--------------------------------------------------------------------------------------
		*
		*	getPostIDFromMenuID
		*	- this function retrieves post ID for menu
		*
		* 	@params
		*  	- $menu_id (int) - menu ID of item to retrieve post ID for
		*   - $menu_location (string) - location of the menu to use for inheritance
		*
		*	@author Dallas Johnson
		*
		*-------------------------------------------------------------------------------------*/
		private static function getPostIDFromMenuID( $menu_id = 0, $menu_location ) {

			$menu_items = self::getMasterMenuItems( $menu_location );

			foreach ( $menu_items as $item ) :

				if ( $item->ID == $menu_id ) {

					return $item->object_id;

				}

			endforeach;

			return false;

		}


		/*--------------------------------------------------------------------------------------
		*
		*	getMasterMenuItems
		*	- this function retrieves and caches menu items for specific menu
		*
		* 	@params
		*  	- $menu_location (string) - location of the menu to use for inheritance
		*
		*	@author Dallas Johnson
		*
		*-------------------------------------------------------------------------------------*/
		private static function getMasterMenuItems( $menu_location ) {

			//check for cache
			if ( false === ( $menu_items = wp_cache_get( 'advanced-custom-fields-widget-filter-field-add-on-menu-items-' . $menu_location ) ) ) {

				//set default
				$menu_id = false;

				//get menu ID for the main location
				$menus = get_nav_menu_locations();
				foreach ( $menus as $key => $value ) :

					if ( $key == $menu_location ) {

						$menu_id = $menus[$key];
						break;

					}

				endforeach;

				//get items for desired menu
				if ( $menu_id )
					$menu_items = wp_get_nav_menu_items( $menu_id );

				//cache result
				if ( ! is_wp_error( $menu_items ) )
					wp_cache_set( 'advanced-custom-fields-widget-filter-field-add-on-menu-items-' . $menu_location, $menu_items );
			}

			return $menu_items;

		}

	}

}

function acf_widget_register_field(){
	if( function_exists( 'register_field' ) ) {
		register_field( 'acf_Widget', __FILE__ );
	}
}
add_action( 'init', 'acf_widget_register_field' );
?>
