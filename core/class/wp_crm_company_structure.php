<?php
/**
 * Describes a WP_CRM_Company internal structure.
 * Building blocks are DEPARTMENTS and EMPLOYEES.
 */
class WP_CRM_Company_Structure extends WP_CRM_Structure {
	const Department	= 0;
	const Employee		= 1;
	/**
	 * The attached database table. No prefix.
	 * @var string
	 */
	public static $T = 'company_structure';
	/**
	 * The parent object class.
	 * @var string
	 */
	public static $CLS = 'WP_CRM_Company';
	/**
	 * The attached database table structure. The ID, PID and OID columns are added by default.
	 * PID links WP_CRM_Structure pieces together into a tree.
	 * @var array
	 */
	protected static $K = array (
		'rid',			/* = Reference ID, the object that stores information. */
		'type',			/* = specifies the class of the Reference */
		'title',		/* = specifies the job or department title*/
		'description'		/* = specifies the job or department description */
		);
	/**
	 * Pair of (actions, form elements).
	 * Form elements are defined as name[:type] => label, where
	 * 	name is a string containing the database key,
	 *	type is a string containing the type prefixed by :
	 *	label is a string containing the displayed label
	 *	Common types are: text (default), basket, checkbox, radio, select, button, submit, close, textarea, email, password, hidden, label, tos, date, seller, buyer, product, spread, matrix, file
	 * @see WP_CRM_Form::_render()
	 * @var array
	 */
	public static $F = array (
		'new' => array (
			'title' => 'Functie',
			'first_name' => 'Prenume',
			'last_name' => 'Nume',
			'email' => 'E-mail',
			'phone' => 'Telefon'
			),
		'edit' => array (
			),
		'view' => array (
			),
		'safe' => array (
			),
		'excerpt' => array (
			),
		'group' => array (
			)
		);
	/**
	 * List of column declarations for the table structure. The ID and PID columns are added by default.
	 * @var array
	 */
	protected static $Q = array (
		'`id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT',
		'`pid` int(11) NOT NULL DEFAULT 0',
		'`oid` int(11) NOT NULL DEFAULT 0',
		'`rid` int(11) NOT NULL DEFAULT 0',
		'`type` varchar(64) NOT NULL DEFAULT \'\'',
		'`title` text NOT NULL',
		'`description` text NOT NULL',
		);
	}
?>
