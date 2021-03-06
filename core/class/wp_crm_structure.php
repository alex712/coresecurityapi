<?php
/**
 * Core of WP_CRM_*
 */

/**
 * Abstract class for defining object dynamic structures.
 *
 * @category Abstract
 * @package WP_CRM
 * @copyright Core Security Advisers SRL
 * @author Bogdan Dobrica <bdobrica @ gmail.com>
 * @version 0.1
 *
 */
abstract class WP_CRM_Structure {
	/**
	 * The attached database table. No prefix.
	 * @var string
	 */
	public static $T = '';
	/**
	 * The parent object class.
	 * @var string
	 */
	public static $CLS = '';
	/**
	 * The attached database table structure. The ID and PID columns are added by default.
	 * !Important! Seems like columns TYPE (referenced class name) and RID (referenced id)
	 * should be added by default to the structure.
	 * PID links WP_CRM_Structure pieces together into a tree.
	 * @var array
	 */
	protected static $K = array ();
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
	 * List of column declarations for the table structure. The ID and PID columns are not added by default.
	 * @var array
	 */
	protected static $Q;
	/**
	 * The parent object id.
	 * @var int
	 */
	private $OID;
	/**
	 * The hierarchy.
	 * @var array
	 */
	private $tree;
	private $list;

	public function __construct ($data = NULL) {
		global $wpdb;

		$this->tree = array ();
		$this->list = array (
			0 => static::$F['new']
			);

		if (is_object ($data)) {
			if (get_class ($data) !== static::$CLS) throw new WP_CRM_Exception (WP_CRM_Exception::Unknown_Object);
			$this->OID = (int) $data->get ();
			}
		else
		if (is_numeric ($data)) {
			$this->OID = (int) $data;
			}
		else
			throw new WP_CRM_Exception (WP_CRM_Exception::Unknown_Object);

		$leaves = array ();

		if ($this->OID) {
			$sql = $wpdb->prepare ('select * from `' . $wpdb->prefix . static::$T . '` where oid=%d;', $this->OID);
			$objs = $wpdb->get_results ($sql);
			}
		else
			$objs = NULL;
		/**
		 * Building the tree from SQL results. Works only with OBJECT query output
		 * as it is passed by reference, not by value.
		 */
		if ($objs) {
			foreach ($objs as $obj) {
				$class = $obj->type;
				try {
					$reference = $class ? new $class ($obj->rid) : null;
					}
				catch (WP_CRM_Exception $wp_crm_exception) {
					$reference = null;
					}
				
				$leaves[$obj->pid][] = $obj;
				foreach (static::$F['new'] as $key => $value) {
					if (in_array ($key, static::$K))
						$this->list[$obj->id][$key] = $obj->$key;
					else
						$this->list[$obj->id][$key] = is_null ($reference) ? null : $reference->get ($key);
					}
				}
			foreach ($objs as $obj)
				if (isset ($leaves[$obj->id]))
					$obj->leaves = $leaves[$row->id];
			}
		$this->tree = isset ($leaves[0]) ? $leaves[0] : NULL;
		}

	public function set ($key = null, $opts = null) {
		global $wpdb;
		if (is_array ($key)) {
			if (!empty ($key)) {
				$ids = array ();
				foreach ($key as $_key => $_value) {
					if ($_key == 0) {
						/**
						 * Here the new objects are added inside the structure
						 */
						foreach ($_value as $_values) {
							$inserts = array ();
							$values = array ();
							$escape = array ();
							$class = $_values['type'];
							foreach (static::$K as $slug) {
								if (isset ($_values[$slug])) {
									$inserts[] = $slug;
									$values[] = $_values[$slug];
									$escape[] = '%s';
									unset ($_values[$slug]);
									}
								}

							try {
								$reference = new $class ($_values);
								$reference->save ();
								
								$inserts[] = 'oid';
								$values[] = $this->OID;
								$escape[] = '%d';
								
								$inserts[] = 'rid';
								$values[] = $reference->get ();
								$escape[] = '%d';

								$sql = $wpdb->prepare ('insert into `' . $wpdb->prefix . static::$T . '` (' . implode ($inserts, ',') . ') values (' . implode ($escape, ',') . ');', $values);
								$wpdb->query ($sql);
								if ($wpdb->insert_id)
									$ids[] = $wpdb->insert_id;
								}
							catch (WP_CRM_Exception $wp_crm_exception) {
								}
							}
						}
					else {
						/**
						 * Here the old objects are updated inside the structure
						 */
						$ids[] = $_key;

						$updates = array ();
						$values = array ();
						foreach (static::$K as $slug) {
							if (isset ($_value[$slug])) {
								$updates[] = $slug . '=%s';
								$values[] = $_value[$slug];
								unset ($_value[$slug]);
								}
							}
						if (!empty ($updates)) {
							$values[] = $_key;
							$values[] = $this->OID;

							$sql = $wpdb->prepare ('update ' . $wpdb->prefix . static::$T . ' set ' . implode ($updates, ',') . ' where id=%d and oid=%d;', $values);
							$wpdb->query ($sql);
							}
						
						$class = $this->list[$_key]['type'];
						if (class_exists ($class)) {
							$reference = new $class ($this->list[$_key]['rid']);
							$reference->set ($_value);
							}
						}
					}
				$sql = $wpdb->prepare ('select id from `' . $wpdb->prefix . static::$T . ' where oid=%d;', $this->OID);
				$old = $wpdb->get_col ($sql);
				if (!empty ($old)) $del = array_diff ($old, $ids);
				if (!empty ($del)) {
					$sql = $wpdb->prepare ('delete from `' . $wpdb->prefix . static::$T . ' where id in (' . implode (',', $del) . ') and oid=%d;', $this->OID);
					$wpdb->query ($sql);
					}
				}
			}
		else {
			$sql = $wpdb->prepare ('delete from `' . $wpdb->prefix . static::$T . ' where oid=%d;', $this->OID);
			$wpdb->query ($sql);
			}
		}

	public function get ($key = null, $opts = null) {
		if (is_null ($key))
			return $this->list;
		switch ((string) $key) {
			case 'size':
				return count ($this->list) -1;
				break;
			}
		return FALSE;
		}

	public function is ($key = null) {
		switch ((string) $key) {
			case 'empty':
				return count ($this->list) > 1 ? FALSE : TRUE;
				break;
			}
		return FALSE;
		}

	public static function install ($uninstall = FALSE) {
		global $wpdb;

		if (empty (static::$Q)) return;

		$sql = $uninstall ?
			'drop table `' . $wpdb->prefix . static::$T . '`;' :
			'create table `' . $wpdb->prefix . static::$T . '` (' . implode (',', static::$Q) . ') engine=MyISAM default charset=utf8;';

		if ($wpdb->get_var ('show tables like \'' . $wpdb->prefix . static::$T . '\';') != ($wpdb->prefix . static::$T)) {
			#echo $sql;
			$wpdb->query ($sql);
			}
		}
	}
?>
