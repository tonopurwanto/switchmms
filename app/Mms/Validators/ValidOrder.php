<?php

namespace App\Mms\Validators;

use App\Mms\StatusCode;
use Exception;
use Illuminate\Http\Request;

class ValidOrder
{
    /**
     * The client sign field must be same with server sign
     *
     * @param Request $request
     * @param array $params
     *
     * @throws Exception
     */
    public function check(Request $request, array $params = [])
	{
		$customer     = $params['customer'];
		$productGroup = $params['productGroup'];
        $client       = $params['client'];

		$signToCheck = strtoupper(
			sha1($client->code.$request->requestid.$request->master.$productGroup.$client->password.$request->order.'.'.$customer->pin)
		);
		
		if ($signToCheck !== $request->sign) {
			throw new Exception("Pesan tidak valid", StatusCode::SIGN_MISMATCH);
		}
	}
}