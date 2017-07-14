<?php
/**
 * Class that renders the tutorial pages.
 * @author Clayton Rogers
 *
 */
class WC_Admin_Tutorials{
	
	/**
	 * Display the tutorials page.
	 */
	public static function showTutorialsView(){
		WorldpayUS_Admin::get_admin_header();
		self::tutorialsHeader();
		self::setupInstructions();
		self::woocommerceTutorial();
		self::woocommerceSubscriptions();
		self::worldpaySubscriptions();
	}
	
	public static function tutorialsHeader(){
		?>
			<div class="div--tutorialHeader">
			  <ul>
			    <li><a tutorial-container="setup_instructions" href="#"><?php echo __('Setup Instructions', 'worldpayus')?></a></li>
			  	<li><a tutorial-container="woocommerce_settings" href="#"><?php echo __('WooCoomerce Settings', 'worldpayus')?></a></li>
			  	<li><a tutorial-container="woocommerce_subscriptions" href="#"><?php echo __('WooCommerce Subscriptions', 'worldpayus')?></a></li>
			  	<li><a tutorial-container="worldpay_subscriptions" href="#"><?php echo __('Worldpay Subscriptions', 'worldpayus')?></a></li>
			  </ul>
			</div>
			<?php
	}
	
	public static function setupInstructions(){
		?>
			<div id="setup_instructions" class="worldpay-explanation-container display">
			  <div class="div--title">
			    <h2><?php echo __('Step 1: Create Payment Plugins Account', 'worldpayus')?></h2>
			  </div>
			  <div class="explanation">
			    <div><strong><?php echo __('Payment Plugins Account: ', 'worldpayus')?></strong>
			      <?php echo __('In order to use this plugin you must create a <a target="_blank" href="https://paymentplugins.com/signup">Payment Plugins</a> account. By registering for a Payment Plugins account,
			      		you will gain access to the SAQ A compliant hosted payment form. In order to test your integration, you can create a <a target="_blank" href="https://sandbox.paymentplugins.com/signup">Payment Plugins Sandbox</a> account.', 'worldpayus')?>
			    </div>
			  </div>
			  <div class="div--title">
			    <h2><?php echo __('Step 2: Create A Worldpay Account', 'worldpayus')?></h2>
			  </div>
			  <div class="explanation">
			    <div><strong><?php echo __('Worldpay Account: ', 'worldpayus')?></strong>
			      <?php echo __('In order to start processing payments on your site, you will need to signup for a merchant account with <a target="_blank" href="https://www.worldpay.us/partner/Bradstreet-Payment-Plugins">Worldpay</a>. Once
			      		you are approved, you will be provided with logon details to your <a target="_blank" href="https://terminal.securenet.com/login.aspx">Worldpay Terminal</a> which is where you can access your API keys. While you are waiting for Worldpay to approve your account,
			      		you can sign up for a <a target="_blank" href="http://www.securenet.com/get-sandbox">Worldpay Sandbox</a> account which will allow you to start testing your integration.', 'worldpayus')?>
			    </div>
			  </div>
			  
			  <div class="div--title">
			    <h2><?php echo __('Step 3: Obtain API Keys', 'worldpayus')?></h2>
			  </div>
			  <div class="explanation">
			    <div><strong><?php echo __('API Keys: ', 'worldpayus')?></strong>
			      <?php echo __('In order for the payment form to appear and transactions to be processed, you will need to configure your API Keys. Your API keys are used to communicate securely with Worldpay\'s system in addition to Payment Plugins.
			      		<p>To access your API keys, login to <a target="_blank" href="https://terminal.securenet.com/login.aspx">Worldpay Account</a> or <a target="_blank" href="https://terminal.demo.securenet.com/login.aspx">Worldpay Sandbox Account</a></p>', 'worldpayus')?>
			    </div>
			    <div class="setting-explanation">
				    <div><strong><?php echo __('SecureNet ID', 'worldpayus')?></strong></div>
				    <div><p><?php echo __('To locate your SecureNet ID, login to your Worldpay Account. The ID will be located on the bottom left and upper right hand corner of the Worldpay Terminal.', 'worldpayus')?></div>
				  	<div><img src="https://tutorials.paymentplugins.com/payment-worldpay-us/images/worldpay-settings-securenet-id.png"/></div>
				</div>
			  	<div class="setting-explanation">
				    <div><strong>Secure Key</strong></div>
				    <div><p><?php echo __('To obtain your Secure Key, login to your Worldpay Account. Click on the Settings tab and click the link that says Obtain Secure Key.', 'worldpayus')?></div>
				  	<div><img src="https://tutorials.paymentplugins.com/payment-worldpay-us/images/worldpay-settings-obtain-secure-key.png"/></div>
					<div><img src="https://tutorials.paymentplugins.com/payment-worldpay-us/images/worldpay-settings-secure-key.png"/></div>
				</div>
				
				<div class="setting-explanation">
				  <div><strong><?php echo __('Public Key', 'worldpayus')?></strong></div>
				  <div><p><?php echo __('To locate your Public Key, navigate to the settings page and click the link that says Obtain Public Key. ', 'worldpayus')?></p></div>
				  <div><img src="https://tutorials.paymentplugins.com/payment-worldpay-us/images/worldpay-settings-obtain-public-key.png"/></div>
				   <div><img src="https://tutorials.paymentplugins.com/payment-worldpay-us/images/worldpay-settings-public-key.png"/></div>
				</div>
			  </div>
			  
			   <div class="div--title">
			    <h2><?php echo __('Step 4: Configure API Keys', 'worldpayus')?></h2>
			  </div>
			  <div class="explanation">
			    <div><strong><?php echo __('Configure API Keys: ', 'worldpayus')?></strong>
			      <?php echo __('Now that you have your API Keys, you will need to save them in your <a target="_blank" href="https://paymentplugins.com/login">Payment Plugins Production</a> and 
			      		<a target="_blank" href="https://sandbox.paymentplugins.com/login">Payment Plugins Sandbox</a> account. The API Keys from your Worldpay Production account are to be saved in teh Payment Plugins production account and the API Keys from 
			      		your Worldpay Sandbox account should be saved in your Payment Plugins Sandbox account.', 'worldpayus')?>
			    </div>
			    
			    <div class="setting-explanation">
			      <div><p><?php echo __('Login to your Payment Plugins Production and/or Sandbox account.', 'worldpayus')?></p></div>
			      <div><img src="https://tutorials.paymentplugins.com/payment-worldpay-us/images/payment-plugins-login.png"/></div>
			    </div>
			    <div class="setting-explanation">
			      <div><p><?php echo __('Navigate to the Profile page and scroll to the bottom. From there you can click the link View Keys.', 'worldpayus')?></p></div>
			      <div><img src="https://tutorials.paymentplugins.com/payment-worldpay-us/images/payment-plugins-view-api-keys.png"/></div>
			    </div>
			    <div class="setting-explanation">
			      <div><p><?php echo __('On the API Keys page, you should enter your Worldpay SecureNet ID, Secure Key, and Public Key in the input fields and save.', 'worldpayus')?></p></div>
			      <div><img src="https://tutorials.paymentplugins.com/payment-worldpay-us/images/payment-plugins-save-api-keys.png"/></div>
			    </div>
			    <div class="setting-explanation">
			      <div><p><?php echo __('Navigate to the <a target="_blank" href="'.admin_url().'admin.php?page=worldpayus-payment-settings">Plugin Settings Page</a> and enter your API keys. 
			      		You can enable Sandbox mode to test and Production mode when you are ready to Go-Live.', 'worldpayus')?></p></div>
			      <div><img src="https://tutorials.paymentplugins.com/payment-worldpay-us/images/worldpay-plugin-settings.png"/></div>
			    </div>
			  </div>
			</div>
			<?php 
	}
		
	public static function woocommerceTutorial(){
		?>
		<div id="woocommerce_settings" class="worldpay-explanation-container">
		  <div class="div--title">
		    <h2><?php echo __('WooCommerce Settings', 'worldpayus')?></h2>
		  </div>
		  <div class="explanation">
		    <div><strong><?php echo __('WooCommerce Settings: ', 'worldpayus')?></strong>
		      <p><?php echo __('By integrating with the <a target="_blank" href="https://wordpress.org/plugins/woocommerce/">WooCommerce Plugin</a> you can create and sell products on your Wordpress site.', 'worldpayus')?></p>
		    </div>
		  </div>
		  <div>
		    <img src="https://tutorials.paymentplugins.com/payment-worldpay-us/images/worldpay-plugin-woocommerce.png"/>
		  </div>
		</div>
		<?php
	}
	
	public static function woocommerceSubscriptions(){
		?>
		<div id="woocommerce_subscriptions" class="worldpay-explanation-container">
		  <div class="div--title">
		    <h2><?php echo __('WooCommerce Subscriptions', 'worldpayus')?></h2>
		  </div>
		  <div class="explanation">
		    <div><strong><?php echo __('WooCommerce Subscriptions: ', 'worldpayus')?></strong>
		      <p><?php echo __('If you have the <a target="_blank" href="https://www.woothemes.com/products/woocommerce-subscriptions/">WooCommerce Subscriptions</a> plugin, you can sell your subscriptions products using this plugin. When a customer purchases a subscription, a subscription object is created
		      		within the Worldpay Terminal. This subscription will automatically bill the customer on the schedule that you have configured.', 'worldpayus')?></p>
		    </div>
		  </div>
		  <div>
		    <img src="https://tutorials.paymentplugins.com/payment-worldpay-us/images/worldpay-plugin-woocommerce-subscriptions.png"/>
		  </div>
		</div>
				<?php
	}
	
	public static function worldpaySubscriptions(){
		?>
		<div id="worldpay_subscriptions" class="worldpay-explanation-container">
		  <div class="div--title">
		    <h2><?php echo __('Worldpay Subscriptions', 'worldpayus')?></h2>
		  </div>
		  <div class="explanation">
		    <div>
		      <p><?php echo __('If you want to sell subscription products, this plugin allows you to convert a standard WooCommerce product into a subscription. If you ever decide to use the <a target="_blank" href="https://www.woothemes.com/products/woocommerce-subscriptions/">WooCommerce Subscriptions</a> plugin
		      		all of your subscriptions will be compatible. This is a great option for anyone wanting to save $$ by not having to purchasing a subscription plugin. When a customer purchases a subscription, a subscription object will be 
		      		created in the Worldpay Terminal.', 'worldpayus')?></p>
		    </div>
		    <div class="setting-explanation">
		    	<div><p><?php echo __('If you have enabled Worldpay subscriptions, you will be able to convert any product that you want into a subscription product. Once enabled, navigate to the product that you want to sell as 
		    			a subsription. ', 'worldpayus')?></p></div>
		    	<div><p><strong><?php echo __('Subscription Price: ', 'worldpayus')?></strong><?php echo __('The subscription price is the amount that will be billed to the customer based on the configured period and interval.', 'worldpayus')?></p></div>
			    <div><p><strong><?php echo __('Subscription Interval: ', 'worldpayus')?></strong><?php echo __('The subscription interval determines the frequency that the subscription is billed at.', 'worldpayus')?></p></div>
			    <div><p><strong><?php echo __('Subscription Period: ', 'worldpayus')?></strong><?php echo __('The subscription period determines how the subscription is billed. You can configure daily, weekly, monthly, or yearly periods.', 'worldpayus')?></p></div>
			    <div><p><strong><?php echo __('Subscription Length: ', 'worldpayus')?></strong><?php echo __('The length of time that the subscription will remain active.', 'worldpayus')?></p></div>
			    <div><img src="https://tutorials.paymentplugins.com/payment-worldpay-us/images/worldpay-plugin-worldpay-subscription-product.png"/></div>
			    
			</div>    
		  </div>
		</div>
		<?php
	}
}
?>