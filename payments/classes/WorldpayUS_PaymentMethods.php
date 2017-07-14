<?php
class WorldpayUS_PaymentMethods {
	public static $url = WORLDPAYUS_ASSETS;

	public static function payment_methods(){
		return array(	'amex' => array(
						'type'=>'img',
						'src'=>self::$url.'images/amex.png',
						'class'=>'payment-method-img',
						'value'=>'American Express'
					),
			'china_union_pay' => array(
						'type'=>'img',
						'src'=>self::$url.'images/china_union_pay.png',
						'class'=>'payment-method-img',
						'value'=>'China UnionPay'
					),
			'diners_club_international' => array(
						'type'=>'img',
						'src'=>self::$url.'images/diners_club_international.png',
						'class'=>'payment-method-img',
						'value'=>'Diner\'s Club'
					),
			'discover' => array(
						'type'=>'img',
						'src'=>self::$url.'images/discover.png',
						'class'=>'payment-method-img',
						'value'=>'Discover'
					),
			'jcb' => array(
						'type'=>'img',
						'src'=>self::$url.'images/jcb.png',
						'class'=>'payment-method-img',
						'value'=>'JCB'
					),
			'maestro' => array(
						'type'=>'img',
						'src'=>self::$url.'images/maestro.png',
						'class'=>'payment-method-img',
						'value'=>'Maestro'
					),
			'master_card' => array(
						'type'=>'img',
						'src'=>self::$url.'images/master_card.png',
						'class'=>'payment-method-img',
						'value'=>'MasterCard'
					),
			'solo' => array(
						'type'=>'img',
						'src'=>self::$url.'images/solo.png',
						'class'=>'payment-method-img',
						'value'=>'Solo'
					),
			'switch_type' => array(
						'type'=>'img',
						'src'=>self::$url.'images/switch_type.png',
						'class'=>'payment-method-img',
						'value'=>'Switch'
					),
			'visa' => array(
						'type'=>'img',
						'src'=>self::$url.'images/visa.png',
						'class'=>'payment-method-img',
						'value'=>'Visa'
					),
			'paypal' => array(
						'type'=>'img',
						'src'=>self::$url.'images/paypal.png',
						'class'=>'payment-method-img',
						'value'=>'PayPal'
					)
		);
	}
}
?>