<?php
namespace Worldpay;

/**
 * Constants used throught the SDK.
 * @author Clayton Rogers
 *
 */
class Constants {
	
	const REFUND = 'refund';
	const TRANSACTION = 'transaction';
	const PAYMENTMETHOD = 'paymentMethod';
	const CREATE_PAYMENTMETHOD = 'createPaymentMethod';
	const GET_CUSTOMER = 'getCustomer';
	const GET_PAYMENTACCOUNT = "getPaymentAccount";
	const CREATE_CUSTOMER = 'createCustomer';
	const DELETE_CUSTOMER = 'deleteCustomer';
	const DELETE_PMTMTHD = 'deletePaymentMethod';
	const PAYMENTPLAN = 'paymentPlan';
	const DELETE_PLAN = 'deletePaymentPlan';
	const UPDATE_CUSTOMER = 'updateCustomer';
	const INSTALLMENT = 'installmentPlan';
}