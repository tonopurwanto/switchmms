<?php

namespace App\Mms\Validators;

use App\Mms\Exceptions\InsufficientException;
use App\Mms\StatusCode;
use Illuminate\Http\Request;

class ValidStock
{
    /**
     * @param Request $request
     * @param array $params
     */
    public function check(Request $request, array $params = [])
    {
        $product = $params['product'];

        if (! $product->hasStock()) {
            throw new InsufficientException('Stock tidak mencukupi', StatusCode::INSUFFICIENT_STOCK);
        }
    }
}