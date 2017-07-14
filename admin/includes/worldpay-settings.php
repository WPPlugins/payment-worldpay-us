<?php
return array (
		'woocommerce_title' => array (
				'type' => 'title',
				'title' => __ ( 'WooCommerce Settings', 'worldpayus' ),
				'class' => array (),
				'value' => '',
				'description' => __ ( 'In order to begin accepting payments via your worldpay account, 
						you must have entered your SecureNet ID, Secure Key, and Public Key in the API
						settings page of this plugin. To enable integration with WooCommerce, you must 
						click the checkbox Enable Woopayments. This indicates to WooCommerce that you 
						want to begin accepting payments via Worldpay.', 'worldpayus' ) 
		),
		'enabled' => array (
				'type' => 'checkbox',
				'value' => 'yes',
				'default' => 'yes',
				'title' => __ ( 'Enable WooPayments', 'worldpayus' ),
				'class' => array (),
				'tool_tip' => true,
				'description' => __ ( 'If Woopayments is enabled, you can process credit cards using your Worldpay account.', 'worldpayus' ) 
		),
		'api_settings' => array (
				'type' => 'title',
				'title' => __ ( 'Worldpay API Settings', 'worldpayus' ),
				'class' => array (),
				'value' => '',
				'tool_tip' => true,
				'description' => __ ( 'On this page you maintain the different API keys related to your account. These keys are used
						to communicate securely with Worlday and Payment Plugins. In order to obtain your API keys, you must sign up for a <a target="_blank" href="https://www.worldpay.us/partner/Bradstreet-Payment-Plugins">Worldpay Production</a> &/or 
						<a target="_blank" href="http://www.securenet.com/developers">Sandbox Account</a>. Once you have signed up for Worldpay, you can create a <a target="_blank" href="https://paymentplugins.com/signup">Payment Plugins Production Account</a> & a 
						<a a target="_blank" href="https://sandbox.paymentplugins.com/signup">Payment Plugins Sandbox Account</a>. You will enter your Worldpay API keys in the Payment Plugins environment which will give you access to the hosted payment form.', 'worldpayus' ) 
		),
		'production_environment' => array (
				'type' => 'checkbox',
				'value' => 'yes',
				'default' => '',
				'title' => __ ( 'Enable Production Mode', 'worldpayus' ),
				'class' => array (),
				'tool_tip' => true,
				'description' => __ ( 'This setting will enable the prodution mode. You must purchase a license and
						activate your key to maintain this setting. You can purchase a license at https://paymentplugins.com.', 'worldpayus' ) 
		),
		'sandbox_environment' => array (
				'type' => 'checkbox',
				'value' => 'yes',
				'default' => '',
				'title' => __ ( 'Enable Sandbox Mode', 'worldpayus' ),
				'class' => array (),
				'tool_tip' => true,
				'description' => __ ( 'This setting will enable sandbox mode. In sandbox mode you can test all of 
						the functionality offered by this plugin. To sign up for a sandbox account go to 
						http://www.securenet.com/developers', 'worldpayus' ) 
		),
		'sandbox_securenet_id' => array (
				'type' => 'text',
				'title' => __ ( 'Sandbox SecureNet ID', 'worldpayus' ),
				'value' => '',
				'default' => '',
				'class' => array (),
				'tool_tip' => true,
				'description' => __ ( 'The SecureNet ID is used to identify your merchant account. It is a required field.', 'worldpayus' ) 
		),
		'sandbox_secure_key' => array (
				'type' => 'password',
				'title' => __ ( 'Sandbox Secure Key', 'worldpayus' ),
				'value' => '',
				'default' => '',
				'class' => array (),
				'tool_tip' => true,
				'description' => __ ( 'The secure key is used to validate all request made to Worldpay. It is a required field.', 'worldpayus' ) 
		),
		'sandbox_public_key' => array (
				'type' => 'text',
				'title' => __ ( 'Sandbox Public Key', 'worldpayus' ),
				'value' => '',
				'default' => '',
				'class' => array (
						'worldpay-public-key' 
				),
				'tool_tip' => true,
				'description' => __ ( 'The public key is used for all credit card tokenization requests. It is a required field.', 'worldpayus' ) 
		),
		'production_securenet_id' => array (
				'type' => 'text',
				'title' => __ ( 'Production SecureNet ID', 'worldpayus' ),
				'value' => '',
				'default' => '',
				'class' => array (),
				'tool_tip' => true,
				'description' => __ ( 'The SecureNet ID is used to identify your merchant account. It is a required field.', 'worldpayus' ) 
		),
		'production_secure_key' => array (
				'type' => 'password',
				'title' => __ ( 'Production Secure Key', 'worldpayus' ),
				'value' => '',
				'default' => '',
				'class' => array (),
				'tool_tip' => true,
				'description' => __ ( 'The secure key is used to validate all request made to Worldpay. It is a required field.', 'worldpayus' ) 
		),
		'production_public_key' => array (
				'type' => 'text',
				'title' => __ ( 'Production Public Key', 'worldpayus' ),
				'value' => '',
				'default' => '',
				'class' => array (
						'worldpay-public-key' 
				),
				'tool_tip' => true,
				'description' => __ ( 'The public key is used for all credit card tokenization requests. It is a required field.', 'worldpayus' ) 
		),
		'title_text' => array (
				'type' => 'text',
				'title' => __ ( 'Title Text', 'worldpayus' ),
				'value' => '',
				'default' => __ ( 'Worldpay US', 'worldpayus' ),
				'class' => array (),
				'tool_tip' => true,
				'description' => __ ( 'This text is what displays on the checkout page. An example of what you might put is "Credit Card".', 'worldpayus' ) 
		),
		'invoice_prefix' => array (
				'type' => 'text',
				'title' => __ ( 'Invoice Prefix', 'worldpayus' ),
				'value' => '',
				'class' => array (),
				'tool_tip' => true,
				'description' => __ ( 'The invoice prefix can be used to create a more customized invoice. The standard invoice will consist of the WooCommerce Order ID. 
						Adding a prefix can help distinguish orders better for reporting purposes.', 'worldpayus' ) 
		),
		'order_status' => array (
				'type' => 'select',
				'options' => WP_Manager::getWooCommerceStatuses (),
				'title' => __ ( 'Order Status', 'worldpayus' ),
				'default' => __ ( 'wc-completed' ),
				'class' => array (),
				'tool_tip' => true,
				'description' => __ ( 'When a payment is processed succesfully, the plugin sets the status for the order. This setting allows you to
						 control what status your orders will receive upon a successful payment.', 'worldpayus' ) 
		),
		'order_notes'=>array(
				'type'=>'textarea',
				'title'=>__('Order Notes', 'worldpayus'),
				'class'=>array('ordernotes'),
				'default'=>'',
				'tool_tip'=>true,
				'description'=>__('If you would like to add notes to the order you can enter them here. If you leave this field blank, the notes added will consists of the product id\'s, descriptions, and quantities.', 'worldpayus')
		),
		'debug_title' => array (
				'type' => 'title',
				'title' => __ ( 'Debug Settings', 'worldpayus' ),
				'class' => array (),
				'value' => '',
				'description' => __ ( '', 'worldpayus' ) 
		),
		'enable_debug' => array (
				'type' => 'checkbox',
				'value' => 'yes',
				'default' => '',
				'title' => __ ( 'Enable Debugging', 'worldpayus' ),
				'tool_tip' => true,
				'description' => __ ( 'By enabling debug mode, you can view a description of all the transactions
						that are taking place within the system. Debugging is very useful for troubleshooting any issues 
						with payments or to verify that your configuration is correct.', 'worldpayus' ) 
		)
		,
		'payment_methods' => array (
				'type' => 'checkbox',
				'value' => WorldpayUS_PaymentMethods::payment_methods (),
				'default' => array (),
				'title' => __ ( 'Display Payment Methods', 'worldpayus' ),
				'class' => array (),
				'tool_tip' => true,
				'description' => __ ( 'If you want to display an image of the payment methods on the 
						checkout page that your Worldpay merchant account accepts, select the checkboxes.', 'worldpayus' ) 
		),
		'license_key' => array (
				'type' => 'text',
				'value' => '',
				'title' => __ ( 'Worldpay US License Key', 'worldpayus' ),
				'class' => array (),
				'tool_tip' => true,
				'description' => __ ( 'After purchasing your copy of Worldpay US, you will receive a license key. Enter the license key here
						to activate your copy for use in the production environment.', 'worldpayus' ) 
		),
		'subscription_title' => array (
				'type' => 'title',
				'title' => __ ( 'WooCommerce Subscriptions', 'worldpayus' ),
				'class' => array (),
				'value' => '',
				'tool_tip' => true,
				'description' => __ ( 'Worldpay US integrates with WooCommerce Subscriptions. If you have the plugin WooCommerce Subscriptions, you can capture recurring revenue for your business via your Subscription
						products. When a subscription is created, it will be viewable and editable from within your Worldpay terminal console.', 'worldpayus' ) 
		),
		'subscription_worldpay_title' => array (
				'type' => 'title',
				'title' => __ ( 'Worldpay Subscriptions', 'worldpayus' ),
				'class' => array (),
				'value' => '',
				'tool_tip' => true,
				'description' => __ ( 'Using the <a target="_blank" href="https://wordpress.org/plugins/woocommerce/">WooCommerce Plugin</a> you can sell your product as a subscription. If enabled, you will can 
						configure your products as subscriptions from within the product page. When a subscription is created, it will be viewable and editable from within your Worldpay terminal console.', 'worldpayus' ) 
		)
		,
		'subsciptions_allow_new' => array (
				'type' => 'checkbox',
				'value' => '',
				'title' => __ ( 'Create New Subscription - Payment Change', 'worldpayus' ),
				'class' => array (),
				'default' => '',
				'tool_tip' => true,
				'description' => __ ( 'If enabled, a new subscription will be created in the Worldpay system during a payment method change if the subscription was not created using the Worldpay US gateway.', 'worldpayus' ) 
		),
		'max_retries' => array (
				'type' => 'text',
				'value' => '',
				'title' => __ ( 'Max Retries', 'worldpayus' ),
				'class' => array (),
				'default' => 2,
				'tool_tip' => true,
				'description' => __ ( 'This setting will determine how many times Worldpay will try and process a subscription
						after a failed transaction attempt.', 'worldpayus' ) 
		)
		,
		'subscription_notes' => array (
				'type' => 'text',
				'value' => '',
				'title' => __ ( 'Subscription Notes', 'worldpayus' ),
				'class' => array (
						'subscription-notes-input' 
				),
				'tool_tip' => true,
				'description' => __ ( 'The notes that you place here will be inserted on the customer\'s subscription record within Worldpay. If left blank, the notes will be populated with 
						product information specific to the subscription. Example: Product ID: 3096. Product Description: This is a description of the product. Shipping Cost: $5.
						Notes can help you for reporting purposes, etc.', 'worldpayus' ) 
		),
		'subscription_status' => array (
				'type' => 'select',
				'options' => WP_Manager::getSubscriptionStatuses (),
				'title' => __ ( 'Subscription Status', 'worldpayus' ),
				'class' => array (
						'' 
				),
				'default' => 'wc-active',
				'tool_tip' => true,
				'description' => __ ( 'This status will be set for the subscription when checkout is successful.', 'worldpayus' ) 
		),
		'enable_payment_change' => array (
				'type' => 'checkbox',
				'value' => 'yes',
				'default' => '',
				'title' => __ ( 'Payment Change Enabled', 'worldpayus' ),
				'class' => array (),
				'tool_tip' => true,
				'description' => __ ( 'If set, customer\'s can change the payment method on their subscription. When a payment 
						method is changed, a new subscription is created as a copy of the old subscription. 
						If you do not want a new subscription to be created then do not allow payment changes.', 'worldpayus' ) 
		)
		,
		'license_activation' => array (
				'type' => 'title',
				'title' => __ ( 'License Activation', 'worldpayus' ),
				'class' => array (),
				'value' => '',
				'tool_tip' => true,
				'description' => __ ( 'Once you purchase a license key from https://paymentplugins.com you can enter the license here. This action will
						activate your license and allow you to start taking payments in the production environment.', 'worldpayus' ) 
		),
		'license_key' => array (
				'type' => 'text',
				'value' => '',
				'title' => __ ( 'License Key', 'worldpayus' ),
				'class' => array (),
				'tool_tip' => true,
				'description' => __ ( 'Enter your license key here and click Save. This will activate your production license. , etc.', 'worldpayus' ) 
		),
		'account_status' => array (
				'type' => 'custom',
				'default' => 'Inactive',
				'value' => '',
				'title' => __ ( 'Production Acount Status', 'worldpayus' ),
				'class' => array (),
				'tool_tip' => true,
				'function' => 'WorldpayUS_Admin::getAccountStatus',
				'description' => __ ( 'This field displays the status of your Production Payment Plugins account. To activate your production account, 
						sign up for an account and provide your payment information. <a target="_blank" href="https://paymentplugins.com/login">PaymentPlugins.com</a>.', 'worldpayus' ) 
		),
		'enable_woosubscriptions' => array (
				'type' => 'checkbox',
				'default' => '',
				'title' => __ ( 'Enable WooCommerce Subscriptions', 'worldpayus' ),
				'value' => 'yes',
				'class' => array (),
				'tool_tip' => true,
				'description' => __ ( 'By enabling WooCommerce Subscriptions integration, you can sell your subscription products via WooCommerce using
						WooCommerce Subscriptions. When a customer purchases a subscription, the subscription will be created within Worldpay and will be viewable within the 
						Worldpay Terminal.' ) 
		),
		'worldpayus_subscriptions' => array (
				'type' => 'checkbox',
				'default' => '',
				'title' => __ ( 'Enable Worldpay Subscriptions', 'worldpayus' ),
				'value' => 'yes',
				'class' => array (),
				'tool_tip' => true,
				'description' => __ ( 'Enabling Worldpay Subscriptions will allow you to sell your WooCommerce products as subscriptions. Enabling this
						setting will make a screen appear on each of your WooCommerce products where you can configure the type of subscription
						that you want to sell. If you already have the plugin WooCommerce Subscriptions, then you can integrate with that plugin
						by maintaining the other settings.', 'worldpayus' ) 
		),
		'subscription_prefix' => array (
				'type' => 'text',
				'value' => '',
				'default' => '',
				'title' => __ ( 'Subscription Prefix', 'worldpayus' ),
				'class' => array (),
				'tool_tip' => true,
				'description' => __ ( 'Adding a prefix will help to distinguish the different order types within your Worldpay terminal.
						The prefix will be added to the order id and saved in a user defined field.', 'worldpayus' ) 
		) 
);