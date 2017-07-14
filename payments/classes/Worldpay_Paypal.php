<?php
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use PayPal\Api\PayPal\Api;
use PayPal\Api\ExecutePayment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Sale;
use PayPal\Api\Capture;
use PayPal\Api\Refund;

class Worldpay_Paypal {
	
	private static $product_ids = array();
	/**
	 * Create the paypal payment.
	 * @param WC_Order $order
	 */
	public static function createPayment(WC_Order $order){
		$invoiceId = sprintf('%s%s',WP_Manager()->invoice_prefix, $order->id);
		$payer = new Payer();
		$payer->setPaymentMethod('paypal');
		$itemList = self::getItemsFromOrder($order);
		$details = new Details();
		$details->setShipping($order->get_total_shipping())->setTax($order->get_total_tax())
			->setSubtotal($order->get_subtotal());
		$amount = new Amount();
		$amount->setCurrency(get_woocommerce_currency())->setTotal($order->get_total())->setDetails($details);
		$transaction = new Transaction();
		$transaction->setAmount($amount)->setItemList($itemList)->setDescription('Test Description')->setInvoiceNumber($invoiceId);
		$redirectUrls = new RedirectUrls();
		$redirectUrls->setReturnUrl(get_site_url().'?paypal_payments=success&order_id='.$order->id)
			->setCancelUrl(get_site_url().'?paypal_payments=cancel&order_id='.$order->id);
		$payment = new Payment();
		$payment->setIntent('sale');
		$payment->setRedirectUrls($redirectUrls)->setTransactions(array($transaction))->setPayer($payer); 
		
		try{
			$payment->create(self::generateApiContext());
			WP_Manager()->log->writeToLog(sprintf('Paypal payment API request was successful for order %s', $order->id));
			return $payment->getApprovalLink();
		}
		catch(Exception $e){
			WP_Manager()->log->writeErrorToLog($e->getMessage());
			return false;
		}
	}

	/**
	 * Returns an apiContext object for all requests.
	 */
	public static function generateApiContext(){
		$cred = new OAuthTokenCredential(WP_Manager()->getPaypalClientId(), WP_Manager()->getPaypalSecret());
		$apiContext = new ApiContext($cred);
		$apiContext->setConfig(array(
				'mode'=>WP_Manager()->getPaypalEnvironment(),
				'cache.enabled' => true,
		));
		return $apiContext;
	}
	/**
	 * Returns a failure array for processing by Woocommerce
	 * @return multitype:string
	 */
	private static function returnFailure(){
		return array('result'=>'failure', 'result'=>'');
	}
	
	/**
	 * Loops through all of the items in the order and assign them to a Paypal item.
	 * @param WC_Order $order
	 */
	private static function getItemsFromOrder(WC_Order $order){
		$currency = get_woocommerce_currency();
		$items = $order->get_items();
		$itemList = new ItemList();
		$itemArray = array();
		foreach($items as $item => $meta){
			$product = $order->get_product_from_item($meta);
			self::$product_ids[$product->id][] = $product;
		}
		foreach(self::$product_ids as $product_id => $array){
			$paypalItem = new Item();
			$quantity = count($array);
			$paypalItem->setName($array[0]->get_title());
			$paypalItem->setQuantity($quantity);
			$paypalItem->setCurrency($currency);
			$paypalItem->setSku($array[0]->get_sku());
			$paypalItem->setPrice($array[0]->get_price());
			$itemArray[] = $paypalItem;
		}
		$itemList->setItems($itemArray);
		return $itemList;
	}
	
	public static function process_payment(){
		if(!isset($_GET['paypal_payments'])){
			return;
		}
		$result = $_GET['paypal_payments'];
		$orderId = $_GET['order_id'];
		if($result === 'cancel'){
				wc_add_notice('You have chosen to cancel your order with Paypal.', 'notice');
				wp_redirect(wc_get_page_permalink('checkout'));
				exit();
		}
		elseif($result === 'success'){
			$paymentId = $_GET['paymentId'];
			$payment = Payment::get($paymentId, self::generateApiContext());
			$execution = new PaymentExecution();
			$execution->setPayerId($_REQUEST['PayerID']);
			
			try{
				$response = $payment->execute($execution, self::generateApiContext());
				$transactions = $response->getTransactions();
				$relatedResources = $transactions[0]->getRelatedResources();
				$saleId = $relatedResources[0]->getSale()->getId();
				
				try{
					$sale = Sale::get($saleId, self::generateApiContext());
					self::saveTransactionMeta($orderId, $response);
				}
				catch(Exception $e){
					
				}
				$order = wc_get_order($orderId);
				$order->update_status(WP_Manager()->order_status);
				WC()->cart->empty_cart();
				wp_redirect(self::get_return_url($order));
				exit();
				
			}
			catch(Exception $e){
				WP_Manager()->log->writeErrorToLog($e->getMessage());
				wc_add_notice('There was an error while processing your Paypal payment. If the issue continues, please notify the merchant.', 'error');
				wp_redirect(wc_get_page_permalink('checkout'));
			}
					
		}
	}
	
	/**
	 * Process the refund for the PayPal payment.
	 * @param int $orderId
	 * @param int $amount
	 */
	public static function process_refund($orderId, $total){
		$order = wc_get_order($orderId);
		$refund = new Refund();
		$amount = new Amount();
		$amount->setCurrency(get_woocommerce_currency())->setTotal($total);
		$refund->setAmount($amount);
		
		$sale = new Sale();
		$sale->setId($order->get_transaction_id());
		try {
			$result = $sale->refund($refund, self::generateApiContext());
			$order->add_order_note(sprintf('Order %s was refunded in the amount of %s', $order->id, $amount));
			return true;
		}
		catch(Exception $e){
			return false;
		}
	}
	/**
	 * Save the Paypal transaction meta data to the postmeta. The payment type is set to Paypal in all instances to ensure refunds are 
	 * handled by the proper API.
	 * @param int $orderId
	 */
	private static function saveTransactionMeta($orderId, Payment $payment){
		$transactions = $payment->getTransactions();
		$transaction = $transactions[0];
		$relatedResources = $transaction->getRelatedResources();
		$relatedResource = $relatedResources[0];
		$id = $relatedResource->getSale()->getId();
		update_post_meta($orderId, '_transaction_id', $id);
		$method = $payment->getPayer()->getPaymentMethod();
		if($method === 'paypal'){
			update_post_meta($orderId, '_payment_method_title', 'PayPal - '.$payment->getPayer()->getPayerInfo()->getEmail());
			update_post_meta($orderId, '_payment_type', 'paypal');
		}
		elseif($method === 'credit_card'){
			$fundingInstrument = $payment->getPayer()->getFundingInstruments();
			$instrument = $fundingInstrument[0];
			$card = $instrument->credit_card;
			$title = $card->type.' - '.$card->number;
			update_post_meta($orderId, '_payment_method_title', $title);
			update_post_meta($orderId, '_payment_type', 'paypal');
		}
	}
	
	/**
	 * Returns the thank you page for the specified order.
	 * @param WC_Order $order
	 * @return string
	 */
	public static function get_return_url( $order = null ) {

		if ( $order ) {
			$return_url = $order->get_checkout_order_received_url();
		} else {
			$return_url = wc_get_endpoint_url( 'order-received', '', wc_get_page_permalink( 'checkout' ) );
		}

		if ( is_ssl() || get_option('woocommerce_force_ssl_checkout') == 'yes' ) {
			$return_url = str_replace( 'http:', 'https:', $return_url );
		}

		return apply_filters( 'woocommerce_get_return_url', $return_url, $order );
	}
}
add_action('init', 'Worldpay_Paypal::process_payment');
?>