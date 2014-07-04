<?php
if ( ! class_exists( 'acf_Widget' ) && class_exists( 'acf_field' ) ) {

	class acf_Widget extends acf_field {

		//create a unique string for inheritance
		const INHERIT_STRING = '--INHERIT--';
		const INHERIT_TITLE  = '-------- Inherit From Parent --------';

		var $settings, $defaults;

		/*--------------------------------------------------------------------------------------
		*
		*	Constructor
		*
		*	@author DJ Mosca
		*
		*-------------------------------------------------------------------------------------*/
		public function __construct() {
			
			//set vars
			$this->name  = 'widget_field';
			$this->label = 'Widgets';
			$this->category = 'relational'; // Basic, Content, Choice, etc
			$this->defaults = array(
				'sidebar'       => '',
				'inherit_from'  => '',
				'menu_location' => ''
			);

			$this->l10n = array(
				'max'		=> __("Maximum values reached ( {max} values )",'acf'),
				'loading'	=> __('Loading','acf'),
				'empty'		=> __('No matches found','acf'),
				'tmpl_li'	=> '<li>
									<input type="hidden" name="<%= name %>[]" value="<%= value %>" />
									<span data-id="<%= value %>" class="acf-relationship-item">
										<%= text %>
										<a href="#" class="acf-icon small dark"><i class="acf-sprite-remove"></i></a>
									</span>
								</li>'
			);

			// actions
			add_action('wp_ajax_acf/fields/widget_field/query',			array($this, 'ajax_query'));
			add_action('wp_ajax_nopriv_acf/fields/widget_field/query',	array($this, 'ajax_query'));

			parent::__construct(null);

		}

		/*--------------------------------------------------------------------------------------
		*
		*	ajax_query
		*
		*	@author DJ Mosca
		*   @description: Generates HTML for Left column relationship results
		*
		*-------------------------------------------------------------------------------------*/
		public function ajax_query() {

			// vars
			$r = array(
				// 'next_page_exists' => 1,
				// 'html' => ''
			);

			//options
			$options = acf_parse_args( $_GET, array(
				'sidebar'        => '',
				's'				=> '',
				'lang'			=> false,
				'field_key'		=> '',
				'paged'          => 1,
				'nonce'			=> '',
				'posts_per_page' => 5
			));

			$options          = array_merge( $options, $_POST );

			//validate
			if ( ! wp_verify_nonce( $options['nonce'], 'acf_nonce' ) )
				die();

			// load the widget list
			$paging       = array_chunk( $this->get_widgets( $options ), $options['posts_per_page'] );
			$current_page = (array) $paging[$options['paged']-1];

			foreach ( $current_page as $post ) {
				$r[] = array(
					'id' => $post->ID,
					'text' => $post->title
				);
			}

			// if((int)$options['paged'] >= count($paging))
			// 	$r['next_page_exists'] = 0;

			echo json_encode($r);

			die();
		}

		/*--------------------------------------------------------------------------------------
		*
		*	render_field
		*
		*	@author DJ Mosca
		*
		*-------------------------------------------------------------------------------------*/
		public function render_field( $field ) {

			// vars
			$field = array_merge( $this->defaults, $field );

			$atts = array(
				'id'			=> $field['id'],
				'class'			=> "acf-relationship {$field['class']}",
				'data-sidebar'       => $field['sidebar'],
				'data-inherit_from'  => $field['inherit_from'],
				'data-menu_location' => $field['menu_location'],
				'data-paged' => 1,
				'data-post_type' => 'widget_relationship_field',
				'data-field_key' => $field['key']
			);
			?>
			<div <?php acf_esc_attr_e($atts); ?>>

				<!-- Hidden Blank default value -->
				<div class="acf-hidden">
					<input type="hidden" name="<?php echo $field['name']; ?>" value="" />
				</div>

				<div class="selection acf-cf">
					<div class="choices">
						<ul class="acf-bl list">
							
						</ul>
					</div>
					<div class="values">
						<ul class="acf-bl list">
							<?php
							if ( $field['value'] ) {

								foreach ( $field['value'] as $widget_id ) {

									$post = $this->get_widget_object( $widget_id );
									?>
									<li>
										<input type="hidden" name="<?php echo $field['name']; ?>[]" value="<?php echo $post->ID; ?>" />
										<span data-id="<?php echo $k; ?>" class="acf-relationship-item">
											<span class="relationship-item-info"><?php echo $post->type; ?></span>
											<?php echo $post->title; ?>
											<a href="#" class="acf-icon small dark"><i class="acf-sprite-remove"></i></a>
										</span>
									</li>
									<?php
								}
							}
							?>
						</ul>
					</div>
				</div>

			</div>
		<?php

		}

		/*--------------------------------------------------------------------------------------
		*
		*	render_field_settings
		*
		*	@author DJ Mosca
		*
		*-------------------------------------------------------------------------------------*/
		function render_field_settings( $field ) {

			// vars
			$field = array_merge( $this->defaults, $field );
			$key   = $field['name'];

	 		global $wp_registered_sidebars;
			$sidebars = array();

			if ( ! empty( $wp_registered_sidebars ) ) {
				foreach ( (array) $wp_registered_sidebars as $sidebar ) {
					if ( ! is_active_sidebar( $sidebar['id'] ) )
						continue;

					$sidebars[$sidebar['id']] = $sidebar['name'];
				}
			}


			acf_render_field_setting( $field, array(
				'label'			=> __('Sidebar','acf-widget'),
				'instructions'	=> '',
				'type'			=> 'number',
				'name'			=> 'sidebar',
				'type'			=> 'select',
				'choices'		=> $sidebars,
				'ui'			=> 1,
				'multiple'		=> 0,
				'allow_null'	=> 0
			));


			acf_render_field_setting( $field, array(
				'label'			=> __('Inherit From','acf-widget'),
				'type'     => 'select',
				'name'     => 'inherit_from',
				'choices'  => array(
					''     => 'None',
					'page' => 'Page Structure',
					'menu' => 'Menu Structure'
				),
				'ui'			=> 1,
				'multiple'		=> 0,
				'allow_null'	=> 0
			) );

			$menus = get_nav_menu_locations();
			$options = array(
				'' => 'None'
			);

			if ( ! empty( $menus ) ) {
				foreach ( $menus as $menu_key => $menu_value ) {
					$options[$menu_key] = ucwords( $menu_key );
				}
			}

			acf_render_field_setting( $field, array(
				'label'			=> __('Menu location','acf-widget'),
				'instructions'	=> __('If "Menu" inheritance, select menu location to use','acf-widget'),
				'type'     		=> 'select',
				'name'     		=> 'menu_location',
				'choices'  		=> $options,
				'ui'			=> 1,
				'multiple'		=> 0,
				'allow_null'	=> 0
			) );

		}

		/*--------------------------------------------------------------------------------------
		*
		*	field_group_admin_enqueue_scripts
		*
		*	@author DJ Mosca
		*
		*-------------------------------------------------------------------------------------*/
		function input_admin_enqueue_scripts(){
			$dir = plugin_dir_url( __FILE__ );

			wp_register_script('acf-input-widget-relationship-field', $dir . 'js/input.js', array('acf-input'));
			wp_register_style('acf-input-widget-relationship-field', $dir . 'css/input.css', array('acf-input'));

			wp_enqueue_script(array('acf-input-widget-relationship-field'));
			wp_enqueue_style(array('acf-input-widget-relationship-field'));
		}

		/*
		 *	dynamic_widgets()
		 *
		 *  This function is called by sidebar.php and retrieves filtered widget list for page
		 *
		 *  @param $index - index of sidebar
		 *
		 *	@author Dallas Johnson
		 *
		 */
		public static function dynamic_widgets( $index = 1, $post_id = false, $requested_field = false ) {

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

			if ( empty($post_id) ) {
				$post = get_queried_object();
				if ( !is_a( $post, 'WP_Post' ) )
					return false;
				$post_id = $post->ID;
			}

			//set defaults
			$acf_field    = false;
			$include_list = array();

			//get acf fields for loop
			$acf_fields = get_fields( $post_id );

			//loop acf fields to get our field key
			if ( $acf_fields ) {

				foreach ( $acf_fields as $key => $field ):

					if( strpos($key, 'options_') !== false ) {
						$key = str_replace( '_options_', '', $key );

						if ($key != $requested_field) {
							$key = '';
						}
					}

					//get acf field key
					$field_key = acf_get_field_reference( $key, $post_id );

					if ( ! empty( $field_key ) ) {

						//it's an acf field, get the field's acf structure
						$field = get_field_object( $field_key, $post_id );

						$condition = (false === $requested_field) ? isset( $field['sidebar'] ) && $field['sidebar'] == $index : isset( $field['sidebar'] ) && $field['sidebar'] == $index && $key == $requested_field;

						//see if it has a "sidebar" option and if it matches our index
						if ( $condition ) {

							//this field matches, set $acf_field to this one
							$acf_field = $key;

							//quit the loop, we have our match
							break;

						}

					}

				endforeach;

				if ( $acf_field ) {

					if ( get_field( $acf_field, $post_id ) ) {

						//build our include list
						$include_list = self::buildIncludeList( $post_id, $field );

					}

				}

			}

			//set the function's normal array for looping
			$sidebars_widgets[$index] = $include_list;
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
		public function get_widgets( $options ) {

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
		public function get_widget_object( $id ) {

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
		 *  - $post_id (object) - post id
		 *  - $field (object) - field object for this acf item
		 *
		 *	@author Dallas Johnson
		 *
		 *-------------------------------------------------------------------------------------*/
		public static function buildIncludeList( $post_id, $field ) {

			$widgets = get_field( $field['name'], $post_id );

			if ( $widgets ) {

				//see if this list inherits
				if ( isset( $field['inherit_from'] ) && is_numeric($post_id) && false !== ( $i = array_search( self::INHERIT_STRING, $widgets ) ) ) {

					$post = get_post( $post_id );

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
					$parent_post = apply_filters( 'acf_widget/parent-post', $parent_post, $post );

					if ( $parent_post )
						array_splice( $widgets, $i, 1, self::buildIncludeList( $parent_post->ID, $field ) );

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
		public static function getMenuParentIDFromPostID( $post_id = 0, $menu_location ) {

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
		public static function getPostIDFromMenuID( $menu_id = 0, $menu_location ) {

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
		public static function getMasterMenuItems( $menu_location ) {

			//check for cache
			if ( false === ( $menu_items = wp_cache_get( 'acf-widget-menu-items-' . $menu_location ) ) ) {

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
					wp_cache_set( 'acf-widget-menu-items-' . $menu_location, $menu_items );
			}

			return $menu_items;

		}

		/*
		 *	format_value()
		 *
		 *  The Relationship field (parent) method performs additional actions we don't need. We just want to return the value.
		 *
		 *	@param $value  - the value which was loaded from the database
		 *	@param $field  - the field array holding all the field options
		 *
		 *	@return  $value  - the modified value
		 *
		 */
		public function format_value( $value, $field ) {
			return $value;
		}

		/*
		 *  format_value_for_api()
		 *
		 *  This filter is applied to the $value after it is loaded from the db and before it is passed back to the api functions such as the_field
		 *
		 *  @param $value  - the value which was loaded from the database
		 *  @param $field  - the field array holding all the field options
		 *
		 *  @return  $value  - the modified value
		 *
		 */
		public function format_value_for_api( $value, $field ) {
			return $this->format_value( $value, $field );
		}

	}

	new acf_Widget();

}