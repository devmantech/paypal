<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';
require FCPATH . 'vendor/autoload.php';

use Restserver\Libraries\REST_Controller;
use PayPal\Api\Payment;

class Pay extends CI_Controller	{
	use REST_Controller { 
		REST_Controller::__construct as private __resTraitConstruct; 
	}
	function __construct()	{
		parent::__construct();
		$this->__resTraitConstruct();
		$this->load->model('mdl_user');
		$this->apiContext = new \PayPal\Rest\ApiContext(
		new \PayPal\Auth\OAuthTokenCredential(
			$this->config->item('client_id'),
			$this->config->item('client_secret')
			)
		);
	}
	function index_get()	{
		$this->response('Welcome');
	}
	function subscriber_post()	{
		//get user id
		$user_id = $this->get('user_id') ? $this->get('user_id') : 1;
		if(isset($user_id))	{
			//validate user in against database
			$record = $this->mdl_user->get_user_by_id($user_id);
			if(!empty($record))	{
				$paypal = $this->paypal();
				$res = array(
				'status_code' => 0,
				'status' => 'success',
				'link' => $paypal
			);
			$this->response($res);
			}
		}	else	{
			$res = array(
				'status_code' => 0,
				'status' => 'fail',
				'message' => 'User Id required'
			);
			$this->response($res);
		}
	}
	function paypal()	{
		// After Step 2
		$payer = new \PayPal\Api\Payer();
		$payer->setPaymentMethod('paypal');

		$amount = new \PayPal\Api\Amount();
		$amount->setTotal('1.99');
		$amount->setCurrency('USD');

		$transaction = new \PayPal\Api\Transaction();
		$transaction->setAmount($amount);

		$redirectUrls = new \PayPal\Api\RedirectUrls();
		$redirectUrls->setReturnUrl("http://localhost/paypal/api/pay/authorize")
				->setCancelUrl("http://localhost/paypal/api/pay/authorize");

		$payment = new \PayPal\Api\Payment();
		$payment->setIntent('sale')
				->setPayer($payer)
				->setTransactions(array($transaction))
				->setRedirectUrls($redirectUrls);
		try {
				$payment->create($this->apiContext);
				return $payment->getApprovalLink();
		}
		catch (\PayPal\Exception\PayPalConnectionException $ex) {
				return $ex->getData();
		}
	}
	function authorize_get()	{
		if($this->get('paymentId') !== null || $this->get('PayerID') !== null)	{
			$payment_id = $this->get('paymentId');
			try {
				$payment = Payment::get($payment_id, $this->apiContext);
			} catch (Exception $ex) {
				echo '<pre>';
				print_r($ex);
				exit(1);
			}
			echo '<pre>';
			print_r($payment);
		}	else	{
			$this->response('you cancelled payment');
		}
	}
}