<?php
class WP_CRM_State {
	const AddToCart		= 0;
	const Participants	= 1;
	const Payment		= 2;

	const News		= 4;
	const NewsRegistered	= 5;
	const NewsConfirm	= 6;

	const Login		= 7;
	const Logged		= 3;
	const SignUp		= 12;
	const Forgot		= 13;
    const Reset         = 14;

	const AddObject		= 8;
	const EditObject	= 9;
	const SaveObject	= 10;

	const CartActions	= 11;

	private $ID;
	private $basket;
	private $data;
	private $site;

	public static function debug () {
		echo "<pre><div>\n";
		echo "<h3>POST:</h3>\n";
		print_r($_POST);
		echo "</div>\n";
		echo "<div>\n";
		echo "<h3>GET:</h3>\n";
		print_r($_GET);
		echo "</div>\n";
		echo "<div>\n";
		echo "<h3>SESSION: ".session_id()."</h3>\n";
		print_r($_SESSION);
		echo "<br/>\n";
		print_r(unserialize($_SESSION[__CLASS__]));
		echo "</div>\n";
		echo "<div>\n";
		echo "<h3>COOKIE:</h3>\n";
		print_r($_COOKIE);
		echo "</div></pre>\n";
		}

	private function _pack () {
		if (!session_id()) return;
		$_SESSION[__CLASS__] = serialize (array (
			'id' => $this->ID,
			'basket' => $this->basket,
			'data' => $this->data,
			'site' => $this->site
			));
		}

	private function _unpack () {
		if (!session_id()) return;
		if (isset ($_SESSION[__CLASS__]))
			$data = unserialize ($_SESSION[__CLASS__]);
		if (isset($data['id']))
			$this->ID = $data['id'];
		if (isset($data['basket']))
			$this->basket = $data['basket'];
		if (isset($data['data']))
			$this->data = $data['data'];
		if (isset($data['site']))
			$this->site = $data['site'];
		}

	public function __construct ($set = null) {
		session_start ();

		$this->ID = 0;
		$this->basket = new WP_CRM_Basket ();
		$this->data = array ();
		$this->site = 0;

		//$this->_pack ();
		$this->_unpack ();

		if (!is_null ($set))
			switch ((int) $set) {
				case self::CartActions:
					if (($this->ID != self::AddToCart) && ($this->ID != self::Participants) && ($this->ID != self::Payment)) {
						$this->ID = 0;
						$this->_pack ();
						}
					break;
				}
		}

	public function buy ($product, $quantity = null) {
		/*
		TODO: should check if the product is valid
		*/
		if ($product) $this->basket->add ($product, $quantity, TRUE);

		$this->_pack ();
		}

	public function get ($key = null, $opts = null) {
		switch ((string) $key) {
			case 'basket':
				return $this->basket;
				break;
			case 'products':
				return $this->basket->get ('products');
				break;
			case 'data':
				return $this->data;
				break;
			case 'site':
				return $this->site;
				break;
			}
		return $this->ID;
		}

	public function set ($key = null, $value = null) {
		if ($key == 'ID' || $key == 'state') $this->ID = (int) $value;
		if ($key == 'basket') {
			$this->basket = $value;
			}
		if ($key == 'data') {
			$this->data = is_array($this->data) ? $this->data : array ();
			if (is_array ($value) && !empty ($value))
				foreach ($value as $key => $val) {
					$this->data[$key] = $val;
					}
			}
		if ($key == 'site') {
			$this->site = $value;
			}
		$this->_pack ();
		}

	public function delete () {
		$_SESSION = array ();
		session_unset ();
		session_destroy ();
		}

	public function __destruct () {
		//self::debug();
		}
	};
?>
