<?php
global
	$wp_crm_buyer,
	$wp_crm_state,
	$wp_crm_menu,
	$current_user;

$wp_crm_buyer = new WP_CRM_Buyer ();
$wp_crm_state = new WP_CRM_State ();

if (!is_user_logged_in()) {
	$static = WP_CRM_Theme::client_url ();

	switch ($static) {
		case 'signup':
			$wp_crm_state->set ('state', WP_CRM_State::SignUp);
			break;
        case 'forgot':
            $wp_crm_state->set ('state', WP_CRM_State::Forgot);
            break;
        case 'reset':
            $wp_crm_state->set ('state', WP_CRM_State::Reset);
            break;
		default:
			$wp_crm_state->set ('state', WP_CRM_State::Login);
		}

	$structure = new WP_CRM_Form_Structure ($wp_crm_state->get());
	$form = new WP_CRM_Form ($structure);
	$form->set ('state', $wp_crm_state->get());

	$form->action ();
	if ($current_user->ID && !in_array ($wp_crm_state->get(), array (
                WP_CRM_State::SignUp
                ))) { header ('Location: /'); exit (1); }
	}
else {
	$current_user = wp_get_current_user ();
	$wp_crm_menu = new WP_CRM_Menu ($current_user->ID);
	
	$static = WP_CRM_Theme::parse_url ();
	}

get_header ();

if ($current_user->ID && !in_array ($wp_crm_state->get(), array (
		WP_CRM_State::SignUp
		))) {
	
	$wp_crm_user = new WP_CRM_User ($current_user->ID);

	$wp_crm_offices = get_user_meta ($current_user->ID, '_wp_crm_offices', TRUE);
	$wp_crm_office_query = is_numeric ($wp_crm_offices) ? sprintf ('oid=%d', $wp_crm_offices) : (!empty($wp_crm_offices) ? sprintf ('oid in (%s)', implode (',', $wp_crm_offices)) : '');


	include (dirname (__FILE__) . '/template/user-header.tpl');


	if (!is_null ($static) && file_exists (dirname(__FILE__) . '/static/' . $static . '.php') && (current_user_can ('wp_crm_work') || current_user_can ('wp_crm_shop') || current_user_can ('add_users'))) { ?>
		<div class="wp-crm-view-data">
		<?php
			ob_flush ();
			include (dirname(__FILE__) . '/static/' . $static . '.php');
		?>
			<div class="wp-crm-view-separator"></div>
		</div>
		<?php }
	else {
		//$view = new WP_CRM_View ($wp_crm_menu);
		//unset ($view);

		include (dirname (__FILE__) . '/template/dashboard.tpl');
		}

	include (dirname (__FILE__) . '/template/user-footer.tpl');
	}
else {
	if (!is_null ($static) && file_exists (dirname(__FILE__) . '/clients/' . $static . '.php')) { ?>
		<div class="wp-crm-view-client">
		<?php
			ob_flush ();
			include (dirname(__FILE__) . '/clients/' . $static . '.php');
		?>
			<div class="wp-crm-view-separator"></div>
		</div>
		<?php }
	else {
		if ($static == 'activate') {
			$sql = $wpdb->prepare ('select ID from `' . $wpdb->prefix . 'users` where user_login=%s;', urldecode($_GET['l']));
			$user_id = $wpdb->get_var ($sql);
			if ($user_id) {
				$user = new WP_User ($user_id);
				if (strtolower ($_GET['h']) == md5 ($user_id . $user->user_email)) {
					if ($user->has_cap ('wp_crm_wakeup')) {
						$user->set_role ('wp_crm_customer');
						$awaken = TRUE;
						}
					}
				}
			else
				$awaken = FALSE;
			}
		include (dirname (__FILE__) . '/template/login.tpl');
		}
	}

get_footer ();
?>
