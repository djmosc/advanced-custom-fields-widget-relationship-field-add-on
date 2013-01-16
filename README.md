#About

This add-on to Advanced Custom Fields allows you to filter widgets on a page-by-page basis.



# Installation

Copy `advanced-custom-fields-widget-filter-field-add-on` folder to your `plugins` directory. Activate plugin in WP admin.

#Usage

## Add new ACF Field

Add a new ACF field to your ACF Field Group. Select `Widget Relationship` from the field type option. Set the `Sidebar`, `Inherit From` and `Menu Location` options as desired.

## Configure your widgets

In WP Admin, go to `Appearance`, then `Widgets` and configure ALL widgets you'd like to use. This will be your "pool" of available widgets.

## Select desired widgets from the page level

Assuming you applied the ACF Field Group to the `page` post type, in WP Admin, go to `Pages`, then edit a page. You should have a new relationship field for each sidebar using acf-widget. Select the widgets you'd like to display on the page - select `----Inherit from Parent----` to include all of the parent page's widgets as well. Drag the options around to sort them in your preferred order.

## Edit your template`s sidebar file(s)

In `sidebar.php`, replace `dynamic_sidebar()` with the new `dynamic_widgets()` method to retrieve widgets:

    if ( ! acf_Widget::dynamic_widgets( 'Side Bar' ) ) {

       //fallback to default function if you like
       dynamic_sidebar( 'Side Bar' );

    }




