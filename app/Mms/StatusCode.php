<?php

namespace App\Mms;

class StatusCode
{
	const FAIL               = "0";
	const SUCCESS            = "1";
	const PENDING            = "2";
	const SIGN_MISMATCH      = "3";
	const PRODUCT_NOT_FOUND  = "4";
	const INSUFFICIENT_FUNDS = "5";
	const VALIDATION_FAILED  = "6";
	const UNKNOWN_HOST       = "7";
	const CLOSING_SERVER     = "8";
	const OTHERS             = "9";
	const DOUBLE_TRANSACTION = "10";
	const INSUFFICIENT_STOCK = "11";
	const USERID_NOT_FOUND   = "12";
	const PROVIDER_MISMATCH  = "13";
}