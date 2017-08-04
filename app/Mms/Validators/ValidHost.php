<?php

namespace App\Mms\Validators;

use App\Mms\Exceptions\ForbiddenException;
use App\Mms\StatusCode;
use Illuminate\Http\Request;

class ValidHost
{
    /**
     * Client IP address must be registered
     *
     * @param Request $request
     * @param array $params
     *
     * @throws ForbiddenException
     */
    public function check(Request $request, array $params = [])
	{
		$customer = $params['customer'];
		$ip       = $customer->ip_public;
		$splitIP  = explode(':', $ip);
		
		if (count($splitIP) > 0) $ip = $splitIP[0];
		
		if ($ip !== $_SERVER['REMOTE_ADDR']) {
			throw new ForbiddenException("Host tidak terdaftar", StatusCode::UNKNOWN_HOST);
		}
	}
}