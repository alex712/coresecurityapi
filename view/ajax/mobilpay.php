<?php
define ('WP_USE_THEMES', false);
include (dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp-blog-header.php');
include (dirname(dirname(dirname(dirname(__FILE__)))) . '/plugins/wp-crm/class/Mobilpay/Payment/Request/Abstract.php');
include (dirname(dirname(dirname(dirname(__FILE__)))) . '/plugins/wp-crm/class/Mobilpay/Payment/Request/Card.php');
include (dirname(dirname(dirname(dirname(__FILE__)))) . '/plugins/wp-crm/class/Mobilpay/Payment/Request/Notify.php');
include (dirname(dirname(dirname(dirname(__FILE__)))) . '/plugins/wp-crm/class/Mobilpay/Payment/Invoice.php');
include (dirname(dirname(dirname(dirname(__FILE__)))) . '/plugins/wp-crm/class/Mobilpay/Payment/Address.php');

$logfile = dirname(__FILE__).'/mobilpay.txt';

if ($_GET['inv']) {
	$invoice = new WP_CRM_Invoice ((int) $_GET['inv']);
	$payload = unserialize ($invoice->get('payload'));

	if (isset($_POST['env_key']) && isset($_POST['data'])) { # mobilpay
		$errorMessage = '';

		$privateKeyFilePath = dirname(dirname(dirname(dirname(__FILE__)))) . '/plugins/wp-crm/class/Mobilpay/Security/' . $invoice->seller->get() . '.key';

		try {
			$objPmReq = Mobilpay_Payment_Request_Abstract::factoryFromEncrypted($_POST['env_key'], $_POST['data'], $privateKeyFilePath);

			file_put_contents ($logfile, date('d-m-Y H:i:s')."\t".$_SERVER['REMOTE_ADDR']."\t".$_SERVER['HTTP_REFERER']."\taction=".$objPmReq->objPmNotify->action."\terrorCode=".$objPmReq->objPmNotify->errorCode."\n", FILE_APPEND);
	
			switch ($objPmReq->objPmNotify->action) {
				case 'confirmed':
					# cand action este confirmed avem certitudinea ca banii au plecat din contul posesorului de card
					# si facem update al starii comenzii si livrarea produsului
					$errorMessage .= $objPmReq->objPmNotify->getCrc();

					$payment = new WP_CRM_Payment (array (
						'iid' => (int) $invoice->get (),
						'cid' => (int) $invoice->seller->get (),
						'type' => WP_CRM_Payment::Card,
						'amount' => $invoice->get ('value'),
						'details' => $objPmReq->orderId . "\n" .
								'Status: ' . ($objPmReq->objPmNotify->errorCode ? 'rejected' : 'confirmed') . "\n" .
								'Error Code: ' . $objPmReq->objPmNotify->errorCode . "\n" .
								'Mentions: ' . $errorMessage,
						'stamp' => time()
						));
					$payment->save ();

					$invoice->set ('payload', serialize(array (
						'mobilpay' => $objPmReq->objPmNotify->errorCode ? 'rejected' : 'confirmed',
						'errcode' => $objPmReq->objPmNotify->errorCode
						)));

					break;
				case 'confirmed_pending':
					#cand action este confirmed_pending inseamna ca tranzactia este in curs de verificare antifrauda.
					#Nu facem livrare/expediere. In urma trecerii de aceasta verificare se va primi o noua notificare
					#pentru o actiune de confirmare sau anulare.
					$errorMessage = $objPmReq->objPmNotify->getCrc();

					$payment = new WP_CRM_Payment (array (
						'iid' => (int) $invoice->get (),
						'cid' => (int) $invoice->seller->get (),
						'type' => WP_CRM_Payment::Card,
						'amount' => 0,
						'details' => $objPmReq->orderId . "\n" .
								'Status: ' . ($objPmReq->objPmNotify->errorCode ? 'rejected' : 'confirmed_pending') . "\n" .
								'Error Code: ' . $objPmReq->objPmNotify->errorCode . "\n" .
								'Mentions: ' . $errorMessage,
						'stamp' => time()
						));
					$payment->save ();

					$invoice->set ('payload', serialize(array (
						'mobilpay' => $objPmReq->objPmNotify->errorCode ? 'rejected' : 'confirmed_pending',
						'errcode' => $objPmReq->objPmNotify->errorCode
						)));

					break;
				case 'paid_pending':
					#cand action este paid_pending inseamna ca tranzactia este in curs de verificare.
					#Nu facem livrare/expediere. In urma trecerii de aceasta verificare se va primi
					#o noua notificare pentru o actiune de confirmare sau anulare.
					$errorMessage = $objPmReq->objPmNotify->getCrc();

					$payment = new WP_CRM_Payment (array (
						'iid' => (int) $invoice->get (),
						'cid' => (int) $invoice->seller->get (),
						'type' => WP_CRM_Payment::Card,
						'amount' => 0,
						'details' => $objPmReq->orderId . "\n" .
								'Status: ' . ($objPmReq->objPmNotify->errorCode ? 'rejected' : 'paid_pending') . "\n" .
								'Error Code: ' . $objPmReq->objPmNotify->errorCode . "\n" .
								'Mentions: ' . $errorMessage,
						'stamp' => time()
						));
					$payment->save ();

					$invoice->set ('payload', serialize(array (
						'mobilpay' => $objPmReq->objPmNotify->errorCode ? 'rejected' : 'paid_pending',
						'errcode' => $objPmReq->objPmNotify->errorCode
						)));

					break;
				case 'paid':
					#cand action este paid inseamna ca tranzactia este in curs de procesare. Nu facem livrare/expediere.
					#In urma trecerii de aceasta procesare se va primi o noua notificare pentru o actiune de confirmare sau anulare.
					$errorMessage = $objPmReq->objPmNotify->getCrc();

					$payment = new WP_CRM_Payment (array (
						'iid' => (int) $invoice->get (),
						'cid' => (int) $invoice->seller->get (),
						'type' => WP_CRM_Payment::Card,
						'amount' => 0,
						'details' => $objPmReq->orderId . "\n" .
								'Status: ' . ($objPmReq->objPmNotify->errorCode ? 'rejected' : 'paid') . "\n" .
								'Error Code: ' . $objPmReq->objPmNotify->errorCode . "\n" .
								'Mentions: ' . $errorMessage,
						'stamp' => time()
						));
					$payment->save ();

					$invoice->set ('payload', serialize(array (
						'mobilpay' => $objPmReq->objPmNotify->errorCode ? 'rejected' : 'paid',
						'errcode' => $objPmReq->objPmNotify->errorCode
						)));

					break;
				case 'canceled':
					#cand action este canceled inseamna ca tranzactia este anulata. Nu facem livrare/expediere.
					$errorMessage = $objPmReq->objPmNotify->getCrc();

					$payment = new WP_CRM_Payment (array (
						'iid' => (int) $invoice->get (),
						'cid' => (int) $invoice->seller->get (),
						'type' => WP_CRM_Payment::Card,
						'amount' => 0,
						'details' => $objPmReq->orderId . "\n" .
								'Status: ' . ($objPmReq->objPmNotify->errorCode ? 'rejected' : 'canceled') . "\n" .
								'Error Code: ' . $objPmReq->objPmNotify->errorCode . "\n" .
								'Mentions: ' . $errorMessage,
						'stamp' => time()
						));
					$payment->save ();

					$invoice->set ('payload', serialize(array (
						'mobilpay' => $objPmReq->objPmNotify->errorCode ? 'rejected' : 'canceled',
						'errcode' => $objPmReq->objPmNotify->errorCode
						)));

					break;
				case 'credit':
					#cand action este credit inseamna ca banii sunt returnati posesorului de card.
					#Daca s-a facut deja livrare, aceasta trebuie oprita sau facut un reverse. 
					$errorMessage = $objPmReq->objPmNotify->getCrc();

					$payment = new WP_CRM_Payment (array (
						'iid' => (int) $invoice->get (),
						'cid' => (int) $invoice->seller->get (),
						'type' => WP_CRM_Payment::Card,
						'amount' => 0,
						'details' => $objPmReq->orderId . "\n" .
								'Status: ' . ($objPmReq->objPmNotify->errorCode ? 'rejected' : 'credit') . "\n" .
								'Error Code: ' . $objPmReq->objPmNotify->errorCode . "\n" .
								'Mentions: ' . $errorMessage,
						'stamp' => time()
						));
					$payment->save ();

					$invoice->set ('payload', serialize(array (
						'mobilpay' => $objPmReq->objPmNotify->errorCode ? 'rejected' : 'credit',
						'errcode' => $objPmReq->objPmNotify->errorCode
						)));

					break;
				default:
					$errorType = Mobilpay_Payment_Request_Abstract::CONFIRM_ERROR_TYPE_PERMANENT;
					$errorCode = Mobilpay_Payment_Request_Abstract::ERROR_CONFIRM_INVALID_ACTION;
					$errorMessage = 'mobilpay_refference_action paramaters is invalid';

					$payment = new WP_CRM_Payment (array (
						'iid' => (int) $invoice->get (),
						'cid' => (int) $invoice->seller->get (),
						'type' => WP_CRM_Payment::Card,
						'amount' => 0,
						'details' => $objPmReq->orderId . "\n" .
								'Status: ' . ($objPmReq->objPmNotify->errorCode ? 'rejected' : 'invalid') . "\n" .
								'Error Code: ' . $objPmReq->objPmNotify->errorCode . "\n" .
								'Mentions: ' . $errorMessage,
						'stamp' => time()
						));
					$payment->save ();

					$invoice->set ('payload', serialize(array (
						'mobilpay' => $objPmReq->objPmNotify->errorCode ? 'rejected' : 'invalid',
						'errcode' => $objPmReq->objPmNotify->errorCode
						)));

					break;
				}
			}
		catch (Exception $e) {
			$errorType = Mobilpay_Payment_Request_Abstract::CONFIRM_ERROR_TYPE_TEMPORARY;
			$errorCode = $e->getCode();
			$errorMessage = $e->getMessage();

			$payment = new WP_CRM_Payment (array (
				'iid' => (int) $invoice->get (),
				'cid' => (int) $invoice->seller->get (),
				'type' => WP_CRM_Payment::Card,
				'amount' => 0,
				'details' => $objPmReq->orderId . "\n" .
						'Status: ' . ($objPmReq->objPmNotify->errorCode ? 'exception' : 'invalid') . "\n" .
						'Error Code: ' . $objPmReq->objPmNotify->errorCode . "\n" .
						'Mentions: ' . $errorMessage,
				'stamp' => time()
				));
			$payment->save ();

			$invoice->set ('payload', serialize(array (
				'mobilpay' => $objPmReq->objPmNotify->errorCode ? 'exception' : 'invalid',
				'errcode' => $objPmReq->objPmNotify->errorCode
				)));

			file_put_contents ($logfile, date('d-m-Y H:i:s')."\t".$_SERVER['REMOTE_ADDR']."\t".$_SERVER['HTTP_REFERER']."\taction=".$objPmReq->objPmNotify->action."\terrorCode=".$objPmReq->objPmNotify->errorCode."\n", FILE_APPEND);
			}

		$invoice->set ('mentions', date('d-m-Y H:i:s')."\n".$errorMessage);

		header('Content-type: application/xml');
		echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
		echo $errorCode == 0 ?
				('<crc>'.$errorMessage.'</crc>') :
				('<crc error_type="'.$errorType.'" error_code="'.$objPmReq->objPmNotify->errorcode.'">'.$errorMessage.'</crc>');
		exit (0);
		}
	elseif ($_POST['TransId']) {
		$mentions = '';
		foreach ($_POST as $key => $val)
			$mentions .= $key.' = '.$val."\n";

		$payment = new WP_CRM_Payment (array (
			'iid' => (int) $invoice->get (),
			'cid' => (int) $invoice->seller->get (),
			'type' => WP_CRM_Payment::Card,
			'amount' => $invoice->get ('value'),
			'details' => $_POST['TransId'],
			'stamp' => time()
			));
		$payment->save ();

		$invoice->set ('payload', serialize(array (
			'mobilpay' => $objPmReq->objPmNotify->errorCode ? 'rejected' : 'confirmed',
			'errcode' => $objPmReq->objPmNotify->errorCode
			)));

		file_put_contents ($logfile, date('d-m-Y H:i:s')."\t".$_SERVER['REMOTE_ADDR']."\t".$_SERVER['HTTP_REFERER']."\tTransID=".$_POST['TransId']."\n", FILE_APPEND);

		echo 'Plata dumneavoastra a fost inregistrata cu succes. Apasati <a href="/">aici</a> pentru a reveni pe site-ul Extreme Training.';
		exit (0);
		}
	else {
		$errorType 		= Mobilpay_Payment_Request_Abstract::CONFIRM_ERROR_TYPE_PERMANENT;
		$errorCode		= Mobilpay_Payment_Request_Abstract::ERROR_CONFIRM_INVALID_POST_PARAMETERS;
		$errorMessage 	= 'mobilpay.ro posted invalid parameters';
		header('Content-type: application/xml');
		echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
		echo $errorCode == 0 ?
				('<crc>'.$errorMessage.'</crc>') :
				('<crc error_type="'.$errorType.'" error_code="'.$errorCode.'">'.$errorMessage.'</crc>');

		$payment = new WP_CRM_Payment (array (
			'iid' => (int) $invoice->get (),
			'cid' => (int) $invoice->seller->get (),
			'type' => WP_CRM_Payment::Card,
			'amount' => 0,
			'details' => $objPmReq->orderId . "\n" .
					'Status: ' . ($errorCode ? 'missing' : 'invalid') . "\n" .
					'Error Code: ' . $errorCode . "\n" .
					'Mentions: ' . $errorMessage,
			'stamp' => time()
			));
		$payment->save ();

		$invoice->set ('payload', serialize(array (
			'mobilpay' => $errorCode ? 'missing' : 'invalid',
			'errcode' => $errorCode
			)));

		file_put_contents ($logfile, date('d-m-Y H:i:s')."\t".$_SERVER['REMOTE_ADDR']."\t".$_SERVER['HTTP_REFERER']."\terrorCode=".$errorCode."\n", FILE_APPEND);
		exit (0);
		}
	}
else {
	$errorCode 		= 0;
	$errorType		= Mobilpay_Payment_Request_Abstract::CONFIRM_ERROR_TYPE_NONE;
	$errorMessage	= '';
	header('Content-type: application/xml');
	echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
	echo $errorCode == 0 ?
			('<crc>'.$errorMessage.'</crc>') :
			('<crc error_type="'.$errorType.'" error_code="'.$errorCode.'">'.$errorMessage.'</crc>');
	exit (0);
	}
?>
