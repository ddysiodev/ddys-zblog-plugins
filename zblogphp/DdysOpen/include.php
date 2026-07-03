<?php
if (!defined('ZBP_PATH')) {
    exit;
}

require_once dirname(__FILE__) . '/function.php';

RegisterPlugin('DdysOpen', 'ActivePlugin_DdysOpen');

function ActivePlugin_DdysOpen()
{
    Add_Filter_Plugin('Filter_Plugin_Index_Begin', 'DdysOpen_IndexBegin');
    Add_Filter_Plugin('Filter_Plugin_ViewPost_Template', 'DdysOpen_ViewPostTemplate');
    Add_Filter_Plugin('Filter_Plugin_Admin_Header', 'DdysOpen_AdminHeader');
    Add_Filter_Plugin('Filter_Plugin_Admin_SettingMng_SubMenu', 'DdysOpen_SettingSubMenu');
    Add_Filter_Plugin('Filter_Plugin_Admin_LeftMenu', 'DdysOpen_LeftMenu');
}

function InstallPlugin_DdysOpen()
{
    DdysOpen_Install();
}

function UninstallPlugin_DdysOpen()
{
    DdysOpen_Uninstall();
}

function UpdatePlugin_DdysOpen()
{
    DdysOpen_Install();
}

function DdysOpen_Updated()
{
    DdysOpen_Install();
}
