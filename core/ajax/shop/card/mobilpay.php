<?php
define ('WP_CRM_MOBILPAY_TEST', TRUE);
function wp_crm_mobilpay_payment ($invoice, $echo = FALSE) {
	include (dirname(__FILE__).'/Mobilpay/Payment/Request/Abstract.php');
	include (dirname(__FILE__).'/Mobilpay/Payment/Request/Card.php');
	include (dirname(__FILE__).'/Mobilpay/Payment/Invoice.php');
	include (dirname(__FILE__).'/Mobilpay/Payment/Address.php');


	$paymentUrl = defined('WP_CRM_MOBILPAY_TEST') ?
			'http://sandboxsecure.mobilpay.ro' :
			'https://secure.mobilpay.ro';

	$x509FilePath 	= dirname(__FILE__).'/security/'.$invoice->seller->get().'.cer';
	try {
		srand((double) microtime() * 1000000);
		$objPmReqCard 					= new Mobilpay_Payment_Request_Card();
		#merchant account signature - generated by mobilpay.ro for every merchant account
		#semnatura contului de comerciant - mergi pe www.mobilpay.ro Admin -> Conturi de comerciant -> Detalii -> Setari securitate
		$objPmReqCard->signature 			= $invoice->seller->get('mobilpay');
		#you should assign here the transaction ID registered by your application for this commercial operation
		#order_id should be unique for a merchant account
		$objPmReqCard->orderId 				= $invoice->get('invoice_series').$invoice->get('invoice_number');
		#supply return_url and/or confirm_url only if you want to overwrite the ones configured for the service/product when it was created
		#if you don't want to supply a different return/confirm URL, just let it null
		$objPmReqCard->confirmUrl 			= WP_CRM_URL . '/ajax/actions/card-paid.php?inv=' . $invoice->get('id');
		$objPmReqCard->returnUrl 			= WP_CRM_URL . '/ajax/actions/card-return.php?inv=' . $invoice->get('id');
		
		#detalii cu privire la plata: moneda, suma, descrierea
		#payment details: currency, amount, description
		$objPmReqCard->invoice = new Mobilpay_Payment_Invoice();
		$objPmReqCard->invoice->currency	= 'RON';
		$objPmReqCard->invoice->amount		= $invoice->get('value');
		$objPmReqCard->invoice->installments	= '1';
		$objPmReqCard->invoice->details		= $invoice->get('invoice_series').$invoice->get('invoice_number');
		
		#detalii cu privire la adresa posesorului cardului
		#details on the cardholder address
		$billingAddress 			= new Mobilpay_Payment_Address();
		$billingAddress->type			= $invoice->buyer->get('type');
		$billingAddress->firstName		= $invoice->buyer->get('first_name');
		$billingAddress->lastName		= $invoice->buyer->get('last_name');
		$billingAddress->fiscalNumber		= $invoice->buyer->get('uin');
		$billingAddress->identityNumber		= $invoice->buyer->get('type') == 'person' ? '' : $invoice->buyer->get('rc');
		$billingAddress->country		= 'ROMANIA';
		$billingAddress->county			= $invoice->buyer->get('county');
		$billingAddress->city			= '';
		$billingAddress->zipCode		= '';
		$billingAddress->address		= $invoice->buyer->get('address');
		$billingAddress->email			= $invoice->buyer->get('email');
		$billingAddress->mobilePhone		= $invoice->buyer->get('phone');
		$billingAddress->bank			= '';
		$billingAddress->iban			= '';
		$objPmReqCard->invoice->setBillingAddress($billingAddress);

		#detalii cu privire la adresa de livrare
		#details on the shipping address
		$objPmReqCard->invoice->setShippingAddress($billingAddress);

		//print_r($objPmReqCard);
		
		$objPmReqCard->encrypt($x509FilePath);
		}
	catch(Exception $e) {
		}

	if (!isset($e) || !($e instanceof Exception)) {
		$out .= '<p>Ai posibilitatea sa platesti <strong>chiar acum</strong>, online, factura primita pe email, cu ajutorul cardului. Apasa butonul <strong>PLATESTE ACUM</strong> si urmeaza pasii, completand cu atentie toate campurile indicate. Plata online se realizeaza securizat prin intermediul <a href="https://www.mobilpay.ro" target="_blank" title="MobilPay">MobilPay</a>. Pentru a plati online, trebuie sa fii de acord cu <a href="/ro/termeni-si-conditii" target="_blank">termenii si conditiile Extreme Training</a> pentru plata online!</p>
<p>Daca vrei sa platesti mai tarziu, conform instructiunilor primite deja prin email, ai urmatoarele posibilitati:
<ul type="square">
        <li>prin ordin de plata sau virament bancar</li>
        <li>prin numerar sau card, la sediul companiei noastre</li>
</ul>
<p>Iti multumim pentru increderea acordata Extreme Training!</p>';
		$out .= '<form method="post" action="'.$paymentUrl.'" target="_blank"><input type="hidden" name="env_key" value="'.$objPmReqCard->getEnvKey().'" /><input type="hidden" name="data" value="'.$objPmReqCard->getEncData().'" /><input type="submit" name="submit" value="PLATESTE ACUM!" /></form><div style="text-align: center;"><img src="'.WP_CRM_URL.'/images/mobilpay.png" /></div>';
		}
	else {
		$out .= '<p>Pentru moment, sistemul de plata online nu este functional.</p>';
		}

	if ($echo) echo $out;
	return $out;
	}
?>
