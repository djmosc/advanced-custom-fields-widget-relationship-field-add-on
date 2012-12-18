<?php

if ( ! class_exists( 'acf_Widget' ) ) {

	class acf_Widget extends acf_Relationship {

		//creating a unique string we can use for inheritance
		const INHERIT_STRING = '--INHERIT--';
		const INHERIT_TITLE  = '-------- Inherit From Parent --------';

		/*--------------------------------------------------------------------------------------
		*
		*	Constructor
		*
		*	@author Dallas Johnson
		*
		*-------------------------------------------------------------------------------------*/
		function __construct( $parent ) {
			parent::__construct( $parent );

			$this->name  = 'widget_field';
			$this->title = __( "Widget List", 'acf' );

			// actions
			add_action( 'wp_ajax_acf_get_widget_results', array( $this, 'acf_get_widget_results' ) );
		}


		/*--------------------------------------------------------------------------------------
		*
		*	acf_get_widget_results
		*
		*	@author Dallas Johnson
		*   @description: Generates HTML for Left column relationship results
		*
		*-------------------------------------------------------------------------------------*/
		function acf_get_widget_results() {
			// vars
			$options = array(
				'sidebar'       => '',
				'inherit_from'  => '',
				'menu_location' => ''
			);

			$ajax = isset( $_POST['action'] ) ? true : false;

			// override options with posted values
			if ( $ajax )
				$options = array_merge( $options, json_decode( stripslashes( $_POST['args'] ), true ) );


			// load the widget list
			$posts  = $this->get_widgets( $options );

			$output = '';

			if ( $posts ) {
				foreach ( $posts as $post ) {
					$output .= '<li><a href="javascript:;" data-post_id="' . $post->ID . '"><span class="relationship-item-info">' . $post->type . '</span>' . $post->title . '<span class="acf-button-add"></span></a></li>';
				}
				echo $output;
			}

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
		function create_field( $field ) {
			// vars
			$defaults = array(
				'max'           => -1,
				'sidebar'       => '',
				'inherit_from'  => '',
				'menu_location' => ''
			);

			$field = array_merge( $defaults, $field );

			// validate types
			$field['max'] = (int) $field['max'];

			// row limit <= 0?
			if ( $field['max'] <= 0 )
				$field['max'] = 9999;

			$args = array(
				'sidebar'       => $field['sidebar'],
				'inherit_from'  => $field['inherit_from'],
				'menu_location' => $field['menu_location']
			);

			$args = htmlspecialchars( json_encode( $args ), ENT_QUOTES, 'UTF-8' );
			?>
        <div class="acf_relationship" data-post_type="widget_field" data-args="<?php echo $args; ?>" data-max="<?php echo $field['max']; ?>" data-s="" data-paged="1" data-taxonomy="" <?php if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
			echo 'data-lang="' . ICL_LANGUAGE_CODE . '"';
		} ?>>

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
                <table class="widefat">
                    <thead>
                    <tr>
                        <th>
                            <label class="relationship_label" for="relationship_<?php echo $field['name']; ?>"><?php _e( "Search", 'acf' ); ?>...</label>
                            <input class="relationship_search" type="text" id="relationship_<?php echo $field['name']; ?>" />

                            <div class="clear_relationship_search"></div>
                        </th>
                    </tr>
                    </thead>
                </table>
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

							echo '<li>
							<a href="javascript:;" class="" data-post_id="' . $post->ID . '"><span class="relationship-item-info">' . $post->type . '</span>' . $post->title . '<span class="acf-button-remove"></span></a>
							<input type="hidden" name="' . $field['name'] . '[]" value="' . $post->ID . '" />
						</li>';
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
		function create_options( $key, $field ) {
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
		function get_value( $post_id, $field ) {
			return parent::get_value( $post_id, $field );
		}


		/*--------------------------------------------------------------------------------------
		 *
		 *	admin_print_scripts / admin_print_styles
		 *
		 *	@author Dallas Johnson
		 *
		 *-------------------------------------------------------------------------------------*/
		function admin_print_scripts() {
			wp_enqueue_script( 'acf-widget', plugin_dir_url(__FILE__) . 'acf_widget.js', array( 'jquery', 'acf-input-actions' ) );
		}


		/*--------------------------------------------------------------------------------------
		 *
		 *	get_widgets
		 *	- this function is called by create_field on edit screens to produce the html for this field
		 *
		 * 	@params
		 *  - $options (array) - field options
		 *
		 *	@author Dallas Johnson
		 *
		 *-------------------------------------------------------------------------------------*/
		function get_widgets( $options ) {
			global $wp_registered_sidebars;

			if ( is_int( $options['sidebar'] ) ):
				$index = 'sidebar-' . $options['sidebar']; else:
				$index = sanitize_title( $options['sidebar'] );
				foreach ( (array) $wp_registered_sidebars as $key => $value ):
					if ( sanitize_title( $value['name'] ) == $index ):
						$index = $key;
						break;
					endif;
				endforeach;
			endif;

			$sidebars_widgets = wp_get_sidebars_widgets();

			//set our default
			$posts = array();

			if ( empty( $wp_registered_sidebars[$index] ) || ! array_key_exists( $index, $sidebars_widgets ) || ! is_array( $sidebars_widgets[$index] ) || empty( $sidebars_widgets[$index] ) )
				return $posts;

			if ( isset( $options['inherit_from'] ) and ! ( $options['inherit_from'] == '' ) )
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
		*	- retrieves build an object with ID, title and type
		*
		* 	@params
		*  	- $id (int) - widget id
		*
		*	@author Dallas Johnson
		*
		*-------------------------------------------------------------------------------------*/
		function get_widget_object( $id ) {
			if ( $id == self::INHERIT_STRING )
				return (object) array(
					'ID'    => self::INHERIT_STRING,
					'title' => self::INHERIT_TITLE,
					'type'  => 'none'
				);

			global $wp_registered_widgets;

			if ( ! isset( $wp_registered_widgets[$id] ) )
				return false;

			$classname = $wp_registered_widgets[$id]['callback'][0]->id_base;
			$instance  = $wp_registered_widgets[$id]['params'][0]['number'];

			if ( ! $option_list = get_option( $classname ) )
				$option_list = get_option( 'widget_' . $classname );

			return (object) array(
				'ID'    => $id,
				'title' => ( strlen( $option_list[$instance]['title'] ) > 0 ) ? $option_list[$instance]['title'] : 'No Title',
				'type'  => $wp_registered_widgets[$id]['name']
			);
		}


		/*--------------------------------------------------------------------------------------
		*
		*	STATIC FUNCTIONS
		*
		*-------------------------------------------------------------------------------------*/


		/*--------------------------------------------------------------------------------------
		 *
		 *	dynamic_widgets
		 *	- this function is called by sidebar.php and retrieves filtered widget list for page
		 *
		 *	@author Dallas Johnson
		 *
		 *-------------------------------------------------------------------------------------*/
		static function dynamic_widgets( $index = 1 ) {
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

			//set defaults
			$acf_field = false;

			//get acf fields for loop
			$acf_fields = get_fields( $post->ID );

			//loop acf fields to get our field key
			if ( $acf_fields ):
				foreach ( $acf_fields as $key => $field ):

					//get acf field key
					$field_key = get_post_meta( $post->ID, '_' . $key, true );

					if ( '' !== $field_key ):

						//if it's an acf field, get the field's acf structure
						$field = $acf->get_acf_field( $field_key );

						//see if it has a "sidebar" option and if it matches our index
						if ( isset( $field['sidebar'] ) and $field['sidebar'] == $index ):

							//this field matches, set $acf_field to this one
							$acf_field = $key;

							//quit the loop, we have our match
							break;
						endif;

					endif;

				endforeach;

				if ( $acf_field ):
					$include_list = array();
					if ( get_field( $acf_field, $post->ID ) ) {
						$parent = 'none';

						if ( isset( $field['inherit_from'] ) ) {

							//menu inheritance
							if ( $field['inherit_from'] == 'menu' )
								$parent = 'menu';

							//page inheritance
							else
								$parent = 'page';

						}

						$page_widgets = self::getPostWidgets( $post, $parent, $field, $include_list );

						if ( is_array( $page_widgets ) )
							$include_list = $page_widgets;

					}
					$sidebars_widgets[$index] = $include_list;
				endif;

			endif;
			/*--------------------------------------------
			* end acf custom
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
		 *	getWidgetsFromParent
		 *	- this function retrieves all inherited widgets for postID
		 *
		 *	@author Dallas Johnson
		 *
		 *-------------------------------------------------------------------------------------*/
		static function getPostWidgets( $post, $parent, $field, &$include_list ) {
			$widgets = get_field( $field['name'], $post->ID );

			if ( $widgets ) {

				$include_list = array_merge( $include_list, (array) $widgets );

				//see if this list inherits
				if ( ( $i = array_search( self::INHERIT_STRING, $widgets ) ) !== false ) {

					unset( $include_list[$i] );

					//it does, find our parent
					switch ( $parent ):

						case 'page':

							//get post ID for parent and run again
							$parent_post = ( $post->post_parent ) ? get_post( $post->post_parent ) : false;
							break;

						case 'menu':

							//get the menu parent
							$parent_post_ID = self::getMenuParentIDFromPostID( $post->ID, $field['menu_location'] );
							$parent_post    = ( $parent_post_ID ) ? get_post( $parent_post_ID ) : false;
							break;

						default:

							//parent should be 'none'
							$parent_post = false;

					endswitch;

					if ( $parent_post ) {
						self::getPostWidgets( $parent_post, $parent, $field, $include_list );
					}

				}
			}
		}


		/*--------------------------------------------------------------------------------------
		 *
		 *	getMenuParentIDFromPostID
		 *	- this function retrieves menu ID for post
		 *
		 *	@author Dallas Johnson
		 *
		 *-------------------------------------------------------------------------------------*/
		static function getMenuParentIDFromPostID( $postID = 0, $menu_location ) {
			$menu_items = self::getMasterMenuItems( $menu_location );

			foreach ( $menu_items as $item ) {
				if ( $item->object_id == $postID ) {
					return self::getPostIDFromMenuID( $item->menu_item_parent, $menu_location );
				}
			}

			return false;
		}


		/*--------------------------------------------------------------------------------------
		*
		*	getPostIDFromMenuID
		*	- this function retrieves post ID for menu
		*
		*	@author Dallas Johnson
		*
		*-------------------------------------------------------------------------------------*/
		static function getPostIDFromMenuID( $menuID = 0, $menu_location ) {
			$menu_items = self::getMasterMenuItems( $menu_location );

			foreach ( $menu_items as $item ) {
				if ( $item->ID == $menuID ) {
					return $item->object_id;
				}
			}

			return false;
		}


		/*--------------------------------------------------------------------------------------
		*
		*	getMasterMenuItems
		*	- this function retrieves and caches menu items for specific menu
		*
		*	@author Dallas Johnson
		*
		*-------------------------------------------------------------------------------------*/
		static function getMasterMenuItems( $menu_location ) {
			$menu_items = '';

			//clear transient if flushing
			if ( isset( $_GET['flush'] ) && false !== get_transient( 'acf-widget-menu-items-' . $menu_location ) )
				delete_transient( 'acf-widget-menu-items-' . $menu_location );

			//check for transient
			if ( false === ( $menu_items = get_transient( 'acf-widget-menu-items-' . $menu_location ) ) ) {

				//get menu ID for the main location
				$menus = get_nav_menu_locations();
				foreach ( $menus as $key => $value ) {
					if ( $key == $menu_location ) {
						$menu_id = $menus[$key];
						break;
					}
				}

				if ( $menu_id )
					$menu_items = wp_get_nav_menu_items( $menu_id );

				//create transient
				if ( ! is_wp_error( $menu_items ) )
					set_transient( 'acf-widget-menu-items-' . $menu_location, $menu_items, 60 * 60 * 24 ); //cache for 1 hour
			}

			return $menu_items;
		}

	}
}
?>
