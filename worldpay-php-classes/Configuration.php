<?php
namespace PaymentPlugins;
/**
 * 
 * @author Clayton Rogers
 * @copyright 2015 PaymentPlugins
 */
class Configuration
{

	public static $config;
	
	private $publicKey;
	
	private $privateKey;
	
	private $environment;
	
	private $merchantId;
	
	public function __get($key)
	{
		
	}
	
	public static function publicKey($publicKey)
	{
		self::$config->setPublicKey($publicKey);
	}
	
	public static function privateKey($privateKey)
	{
		self::$config->setPrivateKey($privateKey);
	}
	
	public static function environment($environment)
	{
		self::$config->setEnvironment($environment);
	}
	
	public static function worldpayPublicKey($worldpayPublicKey)
	{
		self::$config->setWorldpayPublicKey($worldpayPublicKey);
	}
	
	public function setPublicKey($publicKey)
	{
		$this->publicKey = $publicKey;
	}
	
	public function setPrivateKey($privateKey)
	{
		$this->privateKey = $privateKey;
	}
	
	public function setEnvironment($environment)
	{
		$this->environment = $environment;
	}
	
	public function setWorldpayPublicKey($worldpayPublicKey)
	{
		$this->worldpayPublicKey = $worldpayPublicKey;
	}
	public static function configuration()
	{
		self::$config = new self();
	}
	
	public function getPublicKey()
	{
		return $this->publicKey;
	}
	
	public function getPrivateKey()
	{
		return $this->privateKey;
	}
	
	public function getEnvironment()
	{
		return $this->environment;
	}
	
	public function getWorldpayPublicKey()
	{
		return $this->worldpayPublicKey;
	}
}
Configuration::configuration();