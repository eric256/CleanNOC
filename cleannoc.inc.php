<?php
// Simple NOC Screen Component
// Eric Hodges 7/14/2015

require_once(dirname(__FILE__) . '/../componenthelper.inc.php');
$cleannoc_component_name = "cleannoc";

// run the initialization function
cleannoc_component_init();

////////////////////////////////////////////////////////////////////////
// COMPONENT INIT FUNCTIONS
////////////////////////////////////////////////////////////////////////

function cleannoc_component_init()
{
    global $cleannoc_component_name;
    $versionok = cleannoc_component_checkversion();

    $desc = "";
    if (!$versionok)
        $desc = "<br><b>" . gettext("Error: This component requires Nagios XI 2009R1.4 or later.") . "</b>";

    $args = array(

        // need a name
        COMPONENT_NAME => $cleannoc_component_name,
        COMPONENT_VERSION => "1.2.0",

        // informative information
        COMPONENT_AUTHOR => "Eric Hodges",
        COMPONENT_DESCRIPTION => gettext("Provides an network operations screen that can be used to display a status overview on a NOC monitor. ") . $desc,
        COMPONENT_TITLE => "Network Operations Center",
    );

    register_component($cleannoc_component_name, $args);

    if ($versionok) {
        register_callback(CALLBACK_MENUS_INITIALIZED, 'cleannoc_component_addmenu');
    }
}


///////////////////////////////////////////////////////////////////////////////////////////
// VERSION CHECK FUNCTIONS
///////////////////////////////////////////////////////////////////////////////////////////

function cleannoc_component_checkversion()
{

    if (!function_exists('get_product_release'))
        return false;
    if (get_product_release() < 125)
        return false;

    return true;
}


///////////////////////////////////////////////////////////////////////////////////////////
// MENU FUNCTIONS
///////////////////////////////////////////////////////////////////////////////////////////

function cleannoc_component_addmenu($arg = null)
{
    global $cleannoc_component_name;

    $urlbase = get_component_url_base($cleannoc_component_name);


    $mi = find_menu_item(MENU_HOME, "menu-home-tacticaloverview", "id");
    if ($mi == null)
        return;

    $order = grab_array_var($mi, "order", "");
    if ($order == "")
        return;

    $neworder = $order + 0.1;
    add_menu_item(MENU_HOME, array(
        "type" => "link",
        "title" => gettext("Clean NOC"),
        "id" => "menu-home-cleannoc",
        "order" => $neworder,
        "opts" => array(
            "href" => $urlbase . "/index.php",
        )
    ));

}


?>