#About

This add-on to Advanced Custom Fields allows you to filter widgets on a page-by-page basis.



# Installation

Create a new `fields` directory in your theme and copy the `acf-widget` directory to it. In your `functions.php` file, add the following:

    if ( function_exists( 'register_field' ) )
        register_field( 'acf_Widget', plugin_dir_path(__FILE__) . 'fields/acf-widget/acf-widget.php' );


##Note
If you put the directory somewhere else, you can use a filter to specify the path*:

    function change_the_path( $dir ) {
        return PATH_TO_ACF-WIDGET_DIRECTORY;
    }
    add_filter( 'acf-widget-directory', 'change_the_path' );

*Don't forget to update the path in the `register_field` function as well.



#Usage

## Add new ACF Field

Add a new ACF field to your ACF Field Group. Select `Widget List` from the field type option. Set the `Sidebar`, `Inherit From` and `Menu Location` options as desired.

## Configure your widgets

In WP Admin, go to `Appearance`, then `Widgets` and configure ALL widgets you'd like to use. This will be your "pool" of available widgets.

## Select desired widgets from the page level

Assuming you applied the ACF Field Group to the `page` post type, in WP Admin, go to `Pages`, then edit a page. You should have a new relationship field for each sidebar using acf-widget. Select the widgets you'd like to display on the page - select `----Inherit from Parent----` to include all of the parent page's widgets as well. Drag the options around to sort them in your preferred order.

## Edit your template`s sidebar file(s)

In `sidebar.php`, replace `dynamic_sidebar()` with the new `dynamic_widgets()` function to retrieve widgets:

    if ( ! acf_Widget::dynamic_widgets( 'Side Bar' ) ) {

       //fallback to default function if you like
       dynamic_sidebar( 'Side Bar' );

    }




