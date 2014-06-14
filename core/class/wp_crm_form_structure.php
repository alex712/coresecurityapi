<?php
class WP_CRM_Form_Structure {
	private $fields;

	public function __construct ($data = null, $context = null) {
		global $wp_crm_state;

		if (is_object($data) && property_exists($data, 'F'))
			$this->fields = self::_process ($data, $context);
		else
		switch ((string) $data) {
			case WP_CRM_State::NewsRegistered:
			case 'newsregistered':
				$this->fields = array (
					array (
						'class' => 'newsregistered',
						'fields' => array (
							'label' => array (
								'label' => 'Test!',
								'type' => 'label'
								)
							)
						)
					);
				break;
			case WP_CRM_State::News:
			case 'news':
				$this->fields = array (
					array (
						'class' => 'newsletter',
						'fields' => array (
							'first_name' => array (
								'label' => 'Prenume'
								),
							'last_name' => array (
								'label' => 'Nume'
								),
							'phone' => array (
								'label' => 'Telefon'
								),
							'email' => array (
								'label' => 'E-mail',
								'filters' => array (
									'empty' => 'Adresa de E-mail este obligatorie.',
									'email' => 'Adresa de E-mail nu este valida.',
									)
								)
							),
						),
					array (
						'class' => 'buttons',
						'fields' => array (
							'next' => array (
								'type' => 'submit',
								'label' => 'Autentificare! &raquo;',
								'method' => 'post',
								'action' => '',
								'next' => WP_CRM_State::NewsRegistered,
								'callback' => 'WP_CRM::newsletter'
								)
							)
						)
					);
				break;
            case WP_CRM_State::Login:
            case 'login':
                $this->fields = array (
                    array (
                        'class' => 'login-form',
                        'fields' => array (
                            'username' => array (
                                'placeholder' => 'Nume de utilizator',
                                'label' => 'Nume de utilizator',
                                'filters' => array (
                                    'empty' => 'Numele de utilizator este obligatoriu.',
                                    )
                                ),
                            'password' => array (
                                'placeholder' => 'Parola',
                                'label' => 'Parola',
                                'type' => 'password',
                                'filters' => array (
                                    'empty' => 'Parola este obligatorie.',
                                    )
                                )
                            )
                        ),
                    array (
                        'class' => 'login-buttons',
                        'fields' => array (
                            'next' => array (
                                'type' => 'submit',
                                'label' => 'Autentificare &raquo;',
                                'class' => 'btn btn-primary',
                                'method' => 'post',
                                'action' => '',
                                'next' => WP_CRM_State::Logged,
                                'callback' => 'WP_CRM::login'
                                )
                            )
                        )
                    );
                break;
            case WP_CRM_State::Reset:
            case 'reset':
                $this->fields = array (
                    array (
                        'class' => 'login-form',
                        'fields' => array (
                            'email' => array (
                                'placeholder' => 'E-Mail',
                                'label' => 'E-Mail',
                                'filters' => array (
                                    'empty' => 'Numele de utilizator este obligatoriu.',
                                    'email' => 'Adresa de E-mail nu este valida.'
                                    )
                                )
                            )
                        ),
                    array (
                        'class' => 'login-buttons',
                        'fields' => array (
                            'next' => array (
                                'type' => 'submit',
                                'label' => 'Resetare &raquo;',
                                'class' => 'btn btn-primary',
                                'method' => 'post',
                                'action' => '',
                                'next' => WP_CRM_State::Login,
                                'callback' => 'WP_CRM::login'
                                )
                            )
                        )
                    );
                break;
            case WP_CRM_State::SignUp:
            case 'signup':
                $this->fields = array (
                    array (
                        'class' => 'signup-form',
                        'fields' => array (
                            'username' => array (
                                'placeholder' => 'Nume de utilizator',
                                'label' => 'Nume de utilizator',
                                'filters' => array (
                                    'empty' => 'Numele de utilizator este obligatoriu.',
                                    'username_allowed' => 'Numele de utilizator ales este rezervat.',
                                    'username_exists' => 'Numele de utilizator ales este deja folosit de un alt utilizator.',
                                    )
                                ),
                            'first_name' => array (
                                'placeholder' => 'Prenume',
                                'label' => 'Prenume',
                                'filters' => array (
                                    'empty' => 'Mentionarea prenumelui este obligatorie.',
                                    )
                                ),
                            'last_name' => array (
                                'placeholder' => 'Nume',
                                'label' => 'Nume',
                                'filters' => array (
                                    'empty' => 'Mentionarea numelui este obligatorie.',
                                    )
                                ),
                            'phone' => array (
                                'placeholder' => 'Telefon',
                                'label' => 'Telefon',
                                'filters' => array (
                                    'empty' => 'Mentionarea telefonului este obligatorie.',
                                    'phone' => 'Numarul de telefon nu este valid.',
                                    )
                                ),
                            'email' => array (
                                'placeholder' => 'E-Mail',
                                'label' => 'E-Mail',
                                'filters' => array (
                                    'empty' => 'Numele de utilizator este obligatoriu.',
                                    'email' => 'Adresa de E-mail nu este valida.',
                                    'email_exists' => 'Adresa de E-mail este deja folosita de un alt utilizator.',
                                    )
                                ),
                            'password' => array (
                                'placeholder' => 'Parola',
                                'label' => 'Parola',
                                'type' => 'password',
                                'filters' => array (
                                    'empty' => 'Parola este obligatorie.',
                                    )
                                ),
                            /**
                             * SYNTAX: in order to apply confirm filters, the field's key shoud be confirm_{field key to be confirmed}
                             */
                            'confirm_password' => array (
                                'placeholder' => 'Confirma Parola',
                                'label' => 'Confirma Parola',
                                'type' => 'password',
                                'filters' => array (
                                    'empty' => 'Parola este obligatorie.',
                                    'cofirm' => 'Parola introdusa nu a fost confirmata.',
                                    )
                                )
                            )
                        ),
                    array (
                        'class' => 'login-buttons',
                        'fields' => array (
                            'next' => array (
                                'type' => 'submit',
                                'label' => 'Inregistrare &raquo;',
                                'class' => 'btn btn-primary',
                                'method' => 'post',
                                'action' => '',
                                'next' => WP_CRM_State::Login,
                                'callback' => 'WP_CRM::signup'
                                )
                            )
                        )
                    );
                break;
			case WP_CRM_State::Participants:
			case 'participants':
				$this->fields = array ();
				foreach (($wp_crm_state->get('basket')->get('products')) as $product => $quantity) {
					$wp_crm_product = new WP_CRM_Product ((string) $product);
					$this->fields[] = array (
							'class' => 'label',
							'label' => $wp_crm_product->get ('short name'),
							);
					for ($c = 1; $c<=$quantity; $c++) {
						$this->fields[] = array (
							'class' => 'participant',
							'fields' => array (
								strtolower($product).'_'.$c.'_last_name' => array (
									'label' => $c.'. Nume'
									),
								strtolower($product).'_'.$c.'_first_name' => array (
									'label' => $c.'. Prenume'
									),
								strtolower($product).'_'.$c.'_email' => array (
									'label' => $c.'. E-mail'
									),
								strtolower($product).'_'.$c.'_phone' => array (
									'label' => $c.'. Telefon'
									),
								strtolower($product).'_'.$c.'_uin' => array (
									'label' => $c.'. CNP'
									),
								)
							);
						}
					$this->fields[] = array ( 'class' => 'separator' );
					}
				$this->fields[] = array (
					'class' => 'buttons',
					'fields' => array (
						'prev' => array (
							'type' => 'submit',
							'label' => '&laquo; Pasul anterior',
							'method' => 'post',
							'action' => '',
							'next' => WP_CRM_State::AddToCart,
							),
						'next' => array (
							'type' => 'submit',
							'label' => 'Pasul urmator &raquo;',
							'method' => 'post',
							'action' => '',
							'next' => WP_CRM_State::Payment,
							'callback' => 'WP_CRM::buy'
							),
						),
					);
				break;
			case WP_CRM_State::Payment:
			case 'payment':
				$this->fields = array (
					array (
						'class' => 'buttons',
						'fields' => array (
							'prev' => array (
								'type' => 'submit',
								'label' => '&laquo; Pasul anterior',
								'method' => 'post',
								'action' => '',
								'next' => WP_CRM_State::Participants
								)
				/*
				TODO: online payment
				*/
								/*,
							'next' => array (
								'type' => 'submit',
								'label' => 'Pasul urmator &raquo;',
								'method' => 'post',
								'action' => '',
								'next' => WP_CRM_State::AddToCart
								),*/
							),
						),
					);
				break;
			default:
				$this->fields = array (
					array (
						'class' => 'basketwrap',
						'fields' => array (
							'basket' => array (
								'type' => 'basket',
								'options' => array ('coupon'),
								'default' => $wp_crm_state->get ('basket')
								),
					)), array (
						'label' => 'Persoana Fizica',
						'class' => 'tab',
						'name' => 'tabs',
						'fields' => array (
							'p_last_name' => array (
								'label' => 'Nume',
								),
							'p_first_name' => array (
								'label' => 'Prenume',
								),
							'p_uin' => array (
								'label' => 'CNP',
								),
							'p_email' => array (
								'label' => 'E-Mail',
								'filters' => array (
									'empty' => 'Adresa de E-mail este obligatorie.',
									'email' => 'Adresa de E-mail nu este valida.',
									)
								),
							'p_phone' => array (
								'label' => 'Telefon',
								'filters' => array (
									'empty' => 'Numarul de telefon este obligatoriu.',
									'phone' => 'Numarul de telefon nu este valid.',
									)
								),
					)), array (
						'label' => 'Persoana Juridica',
						'class' => 'tab',
						'name' => 'tabs',
						'fields' => array (
							'c_name' => array (
								'label' => 'Companie',
								),
							'c_uin' => array (
								'label' => 'Cod Fiscal',
								),
							'c_rc' => array (
								'label' => 'Reg. Com.',
								),
							'c_address' => array (
								'label' => 'Adresa',
								),
							'c_county' => array (
								'label' => 'Judetul',
								),
							'c_bank' => array (
								'label' => 'Banca',
								),
							'c_account' => array (
								'label' => 'Cont IBAN',
								),
							'c_email' => array (
								'label' => 'E-Mail',
								'filters' => array (
									'empty' => 'Adresa de E-mail este obligatorie.',
									'email' => 'Adresa de E-mail nu este valida.',
									),
								),
							'c_phone' => array (
								'label' => 'Telefon',
								'filters' => array (
									'empty' => 'Numarul de telefon este obligatoriu.',
									'phone' => 'Numarul de telefon nu este valid.',
									),
								),
							'd_label' => array (
								'type' => 'label',
								'label' => 'Delegat:'
								),
							'd_last_name' => array (
								'label' => 'Nume',
								),
							'd_first_name' => array (
								'label' => 'Prenume',
								),
					)), array (
						'class' => 'tos',
						'fields' => array (
							'tos' => array (
								'type' => 'tos',
								'label' => 'Da, sunt de acord cu <a href="http://www.biletedesucces.ro/tos/" class="tos-link" target="_blank" title="Termeni si Conditii">termenii si conditiile</a> acestui serviciu.',
								'filters' => array (
									'empty' => 'Acceptarea <a href="http://www.biletedesucces.ro/tos/" target="_blank" title="Termeni si Conditii">termenilor si conditiilor</a> acestui serviciu este obligatorie!'
									)
								)
							),
					), array (
						'class' => 'buttons',
						'fields' => array (
							'next' => array (
								'type' => 'submit',
								'label' => 'Pasul urmator &raquo;',
								'method' => 'post',
								'action' => '',
								'next' => WP_CRM_State::Participants
								),
							),
						));
			}
		}

	private static function _field (&$fields, $object, $info, $label) {
		if (!empty($info) && (strpos ($info, '?') !== FALSE)) list ($info, $cond) = explode ('?', $info);
		list ($key, $type) = explode (':', $info);
		if (!empty($type) && (strpos ($type, ';') !== FALSE)) list ($type, $opts) = explode (';', $type);
		switch ((string) $type) {
			case 'html':
				$fields[$key] = array (
					'label' => $label,
					'type' => 'textarea',
					'default' => $object->get ($key)
					);
				break;
			case 'bool':
				$fields[$key] = array (
					'label' => $label,
					'type' => 'radio',
					'options' => array (
						0 => 'Da',
						1 => 'Nu'
						),
					'default' => $object->get ($key)
					);
				break;
			case 'buyer':
				$fields[$key] = array (
					'label' => $label,
					'type' => 'buyer',
					'default' => is_object ($object->buyer) ? $object->buyer : null
					);
				break;
			case 'seller':
				$fields[$key] = array (
					'label' => $label,
					'type' => 'seller',
					'default' => $object->get ($key)
					);
				break;
			case 'product':
				$fields[$key] = array (
					'label' => $label,
					'type' => 'product',
					'options' => array (
							'iid' => $object->get ()
							),
					'default' => $object->get ($key)
					);
				break;
			case 'company':
				$companies = new WP_CRM_List ('WP_CRM_Company');
				$options = array ();
				foreach ($companies->get () as $company) $options[$company->get()] = $company->get ('name');
				$fields[$key] = array (
					'label' => $label,
					'type' => 'select',
					'options' => $options,
					'default' => $object->get ($key)
					);
				break;
			case 'mailer':
				$mails = new WP_CRM_List ('WP_CRM_Mail');
				$options = array ();
				if (!$mails->is ('empty'))
				foreach ($mails->get () as $mail) $options[$mail->get ()] = $mail->get ('email');
				$fields[$key] = array (
					'label' => $label,
					'type' => 'select',
					'options' => $options,
					'default' => $object->get ($key)
					);
				break;
			case 'hidden':
				$fields[$key] = array (
					'type' => 'hidden',
					'default' => $object->get ($key)
					);
				break;
			case 'textarea':
				$fields[$key] = array (
					'label' => $label,
					'type' => 'textarea',
					'default' => $object->get ($key)
					);
				break;
			case 'array':
				$fields[$key] = array (
					'label' => $label,
					'type' => 'select',
					'default' => $object->get ($key),
					'options' => $object->get ($opts)
					);
				break;
			case 'multi':
				$fields[$key] = array (
					'label' => $label,
					'type' => 'select',
					'default' => $object->get ($key),
					'options' => $object->get ($opts),
					'multiple' => TRUE
					);
				break;
			case 'date':
				$data = $object->get ($key);
				$data = is_numeric($data) ? $data : strtotime($data);
				$fields[$key] = array (
					'label' => $label,
					'type' => 'date',
					'default' => date('d-m-Y', $data ? $data : time())
					);
				break;
			case 'templates':
				$data = $object->get ($key);
				$structure = $object::$S[$key];
				$list = new WP_CRM_List ('WP_CRM_Template');
				$templates = array (0 => 'Model Nou');
				if (!$list->is ('empty'))
				foreach ($list->get () as $template)
					$templates[$template->get()] = $template->get ('comment') ? ($template->get ('comment') . (' (Model #' . $template->get ('id') . ')')) : ('Model #' . $template->get ('id'));

				if (is_array($structure))
					foreach ($structure as $ka => $va) {
						if (is_array($va)) {
							if (!empty($va))
								foreach ($va as $kb => $vb) {
									$fields[$key . '_' . $ka . '_' . $kb] = array (
										'label' => $vb,
										'type' => 'select',
										'default' => $data[$ka][$kb],
										'options' => $templates
										);
									}
							}
						else
							$fields[$key . '_' . $ka] = array (
								'label' => $va,
								'type' => 'select',
								'default' => $data[$ka],
								'options' => $templates
								);
						}
				else
					$fields[$key] = array (
						'label' => $label,
						'type' => 'select',
						'default' => $data,
						'options' => $templates
						);

				break;
			case 'shopcart':
				$data = $object->get ($key);
				$fields[$key] = array (
					'label' => $label,
					'type' => 'shopcart',
					'default' => $data,
					'options' => $object::$S[$key]
					);
				break;
			case 'card':
				$cards = $object->get ($key);
				$options = array ();

				foreach ($cards as $card)
					$options[$card->get ('email')] = $card->get ('fn');
					
				$fields[$key] = array (
					'type' => 'checkbox',
					'label' => $label,
					'options' => $options
					);
				break;
			case 'attachment':
				$attachments = $object->get ($key);
				$options = array ();
				foreach ($attachments as $attachment)
					$options[$attachment] = $attachment;
					
				$fields[$key] = array (
					'type' => 'checkbox',
					'label' => $label,
					'options' => $options
					);
				break;
			case 'matrix':
				$fields[$key] = array (
					'label' => $label,
					'type' => 'matrix',
					'default' => $object->get ($key)
					);
				break;
			case 'spread':
				$fields[$key] = array (
					'label' => $label,
					'type' => 'spread',
					'default' => $object->get ($key),
					'options' => $object::$F['opts']
					);
				break;
			case 'file':
				$fields[$key] = array (
					'label' => $label,
					'type' => 'file',
					'default' => $object->get ($key),
					'options' => $object::$F['opts']
					);
				break;
			case 'seats':
				$fields[$key] = array (
					'label' => $label,
					'type' => 'seats',
					'default' => $object->get ($key),
					'options' => $object::$F['opts']
					);
				break;
			case 'contact':
				$fields[$key] = array (
					'label' => $label,
					'type' => 'contact',
					'default' => $object->get ($key)
					);
				break;
			case 'switch':
				$fields[$key] = array (
					'label' => $label,
					'type' => 'switch',
					'default' => $object->get ($key)
					);
				break;
			default:
				$fields[$key] = array (
					'label' => $label,
					'default' => $object->get ($key)
					);
			}
		/**
		 * Condition is written as key=value
		 * If the "key"-named field has value "value", then
		 * Show the current field. Else, hide it.
		 */
		if (!empty ($cond)) $fields[$key]['condition'] = $cond;
		}

	private static function _process ($object, $context = null) {
		if (get_class ($object) == 'WP_CRM_List') {
			switch ($object->get ('class')) {
				case 'WP_CRM_Requirement':
					$fields = array (
						array (
							'class' => 'requirements',
							'fields' => array (
								)
							),
						array (
							'class' => 'buttons',
							'fields' => array (
								'close' => array (
									'type' => 'close',
									'label' => 'Anuleaza &raquo;',
									),
								'next' => array (
									'type' => 'submit',
									'label' => 'Actualizeaza &raquo;',
									'method' => 'post',
									'action' => '',
									'callback' => 'WP_CRM::save',
									'next' => WP_CRM_State::SaveObject
									)
								)
							)
						);

					if (is_object ($context)) {
						$fields[0]['fields']['object'] = array (
							'type' => 'hidden',
							'default' => get_class ($context) . '-' . $context->get ()
							);
						}

					if ($object->is ('empty')) return $fields;
					foreach ($object->get() as $requirement) {
						if (is_object ($context) && !$requirement->is ('met', $context)) {
							$type = $context->field ($requirement->get ('key'));
							self::_field ($fields[0]['fields'], $context, $type['info'], $type['label']);
							}
						}

					if (count ($fields[0]['fields']) < 2) $fields = array ();
					break;
				}
			return $fields;
			}

		$fields = array (
			array (
				'class' => strtolower(get_class($object)),
				'fields' => array (
					'object' => array (
						'type' => 'hidden',
						'default' => get_class ($object) . '-' . $object->get ()
						)
					)
				),
			array (
				'class' => 'buttons',
				'fields' => array (
					'close' => array (
						'type' => 'close',
						'label' => 'Anuleaza &raquo;',
						),
					'next' => array (
						'type' => 'submit',
						'label' => 'Actualizeaza &raquo;',
						'method' => 'post',
						'action' => '',
						'callback' => 'WP_CRM::save',
						'next' => WP_CRM_State::SaveObject
						)
					)
				)
			);

		if (isset($object::$F['edit']) && !empty($object::$F['edit']))
		foreach ($object::$F['edit'] as $info => $label)
			self::_field ($fields[0]['fields'], $object, $info, $label);
		else
		if (isset($object::$F['new']) && !empty($object::$F['new']))
		foreach ($object::$F['new'] as $info => $label)
			self::_field ($fields[0]['fields'], $object, $info, $label);
		else
		foreach ($object::$F['public'] as $info => $label)
			self::_field ($fields[0]['fields'], $object, $info, $label);

		return $fields;
		}

	public function append ($hidden = null) {
		if (empty ($hidden)) return;
		foreach ($hidden as $key => $value)
			$this->fields[0]['fields'][$key] = array (
				'type' => 'hidden',
				'default' => $value
				);
		}

	public function get ($key = null, $options = null) {
		return $this->fields;
		}

	public function is ($key = null, $opts = null) {
		switch ((string) $key) {
			case 'empty':
				return empty ($this->fields) ? TRUE : FALSE;
				break;
			}
		}

	public function __toString () {
		return serialize ($this->fields);
		}

	public function __destruct () {
		}
	};
?>
