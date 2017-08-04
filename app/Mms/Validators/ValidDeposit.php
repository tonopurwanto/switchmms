<?php

namespace App\Mms\Validators;

use App\Mms\Exceptions\InsufficientException;
use App\Mms\StatusCode;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ValidDeposit
{
    /**
     * Client balance should enough
     *
     * @param Request $request
     * @param array $params
     *
     * @throws ModelNotFoundException
     * @throws InsufficientException
     */
    public function check(Request $request, array $params = [])
	{
		$customer     = $params['customer'];
		$product      = $params['product'];
		$harga        = $customer->detail->levelprcid;
		$priceList    = $product->inPrice($harga);

		if (! $priceList) throw new ModelNotFoundException("Produk tidak tersedia", StatusCode::PRODUCT_NOT_FOUND);

		if ($customer->DEPOSIT < $priceList->sellprice) {
			throw new InsufficientException("Deposit tidak mencukupi", StatusCode::INSUFFICIENT_FUNDS);
		}
	}
}