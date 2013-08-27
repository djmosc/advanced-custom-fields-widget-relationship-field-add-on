<?php
/*
Plugin Name: Advanced Custom Fields - Widget Relationship Field add-on
Plugin URI: https://bitbucket.org/djbokka/widget-relationship-field-add-on-for-advanced-custom-fields
Description: This plugin is an add-on for Advanced Custom Fields. It allows you to use a "relationship" field to select widgets at a page level.
Version: 1.3
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

add_action('acf/register_fields', 'acf_field_widget_relationship_field');

function acf_field_widget_relationship_field(){
	include_once('widget-relationship-field-v4.php');
}