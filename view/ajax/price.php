<?php
define ('WP_USE_THEMES', false);
include (dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/wp-blog-header.php');
include (dirname(__FILE__) . '/common.php');

global
	$wp_crm_buyer,
	$wp_crm_state,
	$wp_crm_helper;

$wp_crm_buyer = new WP_CRM_Buyer ();
$wp_crm_state = new WP_CRM_State ();
$wp_crm_state->set ('state', WP_CRM_State::AddObject);

list ($class, $id) = $_GET['object'] ? explode ('-', $_GET['object']) : explode ('-', $_POST['object']);
if (!class_exists ($class)) die ('Err.');
if (!is_numeric($id)) die ('Err.');

$object = new $class ((int) $id);
$wp_crm_helper = new WP_CRM_Price ($object);

$structure = new WP_CRM_Form_Structure ($wp_crm_helper);
$form = new WP_CRM_Form ($structure);

$form->set ('state', $wp_crm_state->get());

if ($_POST['object']) $form->action ();
$form->render (TRUE);
?>
