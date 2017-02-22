<?php
/**
 * Copyright {copyyear}, {copyright}
 * {authorsite}
 *
 * {extraplugininfo}  
 */

if(!defined("IN_MYBB"))
{	
    die("Nope.");
}

define( '{pluginnameUppercase}_PLUGIN_NAME', '{pluginname}' );
define( '{pluginnameUppercase}_PLUGIN_FRIENDLY_NAME', '{pluginfriendlyname}' );

// Plugin Information
function {pluginname}_info()
{
    global $plugins_cache;	
    $pluginfilename = basename( __FILE__, ".php" );

    $plugininfo = array(
                         "name"             => {pluginnameUppercase}_PLUGIN_FRIENDLY_NAME,
                         "description"      => '{plugindescription}',
                         "website"          => '{pluginwebsite}',
                         "author"           => '{author}',
                         "authorsite"       => '{authorsite}',
                         "version"          => '{defaultversion}',
                         "compatibility"    => '{compatibility}'
    );
	
	if(is_array($plugins_cache) && is_array($plugins_cache['active']) && $plugins_cache['active'][$pluginfilename])
    {
		$plugininfo['description'] = {pluginnameUppercase}_PLUGIN_FRIENDLY_NAME . " is installed and working properly.";
	}
	
    return $plugininfo;
}

/**
 * Called when the plugin is installed
 */
function {pluginname}_install()
{
	global $db, $lang;

	$lang->load( {pluginnameUppercase}_PLUGIN_NAME );

	// Templates
	$templatesArray = array( 
		'home' => '',
	);

	// Insert them in Global Templates
	foreach( $templatesArray AS $name => $data )
	{
		$template = array(
			'title' 		=> $db->escape_string( {pluginnameUppercase}_PLUGIN_NAME . '_' . $name),
			'template' 		=> $db->escape_string( $data ),
			'version' 		=> 1,
			'sid' 			=> -1,
			'dateline' 		=> TIME_NOW
		);

		$db->insert_query( 'templates', $template );
	}

	$name = {pluginnameUppercase}_PLUGIN_NAME . '_setting_group_name';
	$desc = {pluginnameUppercase}_PLUGIN_NAME . '_setting_group_desc';

	$group = array(
		'name' 			=> {pluginnameUppercase}_PLUGIN_NAME,
		'title' 		=> $db->escape_string( $lang->{$name} ),
		'description' 	=> $db->escape_string( $lang->{$desc} ),
		'isdefault' 	=> 0
	);

	// Check if the group already exists.
	$query = $db->simple_select('settinggroups', 'gid', "name='".{pluginnameUppercase}_PLUGIN_NAME."'");

	if($gid = (int)$db->fetch_field($query, 'gid'))
	{
		// We already have a group. Update title and description.
		$db->update_query( 'settinggroups', $group, "gid='{$gid}'" );
	}
	else
	{
		// We don't have a group. Create one with proper disporder.
		$query = $db->simple_select('settinggroups', 'MAX(disporder) AS disporder');

		$group['disporder'] = (int)$db->fetch_field($query, 'disporder');
		$group['disporder']++;

		$gid = (int)$db->insert_query('settinggroups', $group);
	}

	// Deprecate all the old entries in settings.
	$db->update_query('settings', array('description' => '{pluginnameUppercase}DELETE'), "gid='{$gid}'");

	// New settings
	$settings = array(
		// Max Wager
		'setting1'	=> array(
			'optionscode'	=> 'text',
			'value'			=> '100'
		),
	);

	$disporder = 0;

	// Create and/or update settings.
	foreach($settings AS $key => $setting)
	{
		// Prefix all keys with group name.
		$title = {pluginnameUppercase}_PLUGIN_NAME."_setting_{$key}";
		$description = {pluginnameUppercase}_PLUGIN_NAME."_setting_{$key}_desc";

		$key = {pluginnameUppercase}_PLUGIN_NAME."_{$key}";

		$setting['title'] = $lang->{$title};
		$setting['description'] = $lang->{$description};

		// Filter valid entries.
		$setting = array_intersect_key($setting,
			array(
				'title' 		=> 0,
				'description' 	=> 0,
				'optionscode' 	=> 0,
				'value' 		=> 0,
			)
		);

		// Escape input values.
		$setting = array_map(array($db, 'escape_string'), $setting);

		// Add missing default values.
		++$disporder;

		$setting = array_merge(
			array(
				'description' 	=> '',
				'optionscode' 	=> 'yesno',
				'value' 		=> 0,
				'disporder' 	=> $disporder
			),
			$setting
		);

		$setting['name'] = $db->escape_string($key);
		$setting['gid'] = $gid;

		// Check if the setting already exists.
		$query = $db->simple_select('settings', 'sid', "gid='{$gid}' AND name='{$setting['name']}'");

		if($sid = $db->fetch_field($query, 'sid'))
		{
			// It exists, update it, but keep value intact.
			unset($setting['value']);
			$db->update_query('settings', $setting, "sid='{$sid}'");
		}
		else
		{
			// It doesn't exist, create it.
			$db->insert_query('settings', $setting);
		}
	}

	// Delete deprecated entries.
	$db->delete_query('settings', "gid='{$gid}' AND description='{pluginnameUppercase}DELETE'");
	
	// This is required so it updates the settings.php file as well and not only the database - they must be synchronized!
	rebuild_settings();
}

// Activate the plugin
function {pluginname}_activate()
{
}

// Deactivate the plugin
function {pluginname}_deactivate()
{	
} 

// Uninstall the plugin
function {pluginname}_uninstall()
{
	global $db, $mybb;

	// Show confirmation
	if($mybb->request_method != 'post')
	{
		global $page, $lang;
		$lang->load( {pluginnameUppercase}_PLUGIN_NAME );

		$page->output_confirm_action('index.php?module=config-plugins&action=deactivate&uninstall=1&plugin={pluginname}', $lang->{pluginname}_uninstall_message, $lang->{pluginname}_uninstall);
	}

	// Delete Columns
	//$db->query( 'ALTER TABLE '.TABLE_PREFIX.'users DROP COLUMN `level`' );

	// Drop Tables
	//$db->drop_table( 'mylevels' );

	// Delete settings group
	$gid = $db->fetch_field( $db->simple_select( 'settinggroups', 'gid', 'name=\''.{pluginnameUppercase}_PLUGIN_NAME.'\'' ), 'gid' );
	$db->delete_query('settinggroups', "name='".{pluginnameUppercase}_PLUGIN_NAME."'");

	// Remove settings
	$db->delete_query( 'settings', 'gid=\''.intval($gid).'\'' );
	rebuild_settings();

	// Remove Templates
	$db->delete_query( 'templates', 'title LIKE \''.{pluginnameUppercase}_PLUGIN_NAME.'%\'' );
}

// Plugin Installed?
function {pluginname}_is_installed()
{
	global $db;
}

?>