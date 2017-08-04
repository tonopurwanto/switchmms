<?php

namespace App\Mms\Validators;

use App\Customer;
use App\Mms\Exceptions\InsufficientException;
use App\Mms\StatusCode;
use App\Product;
use App\Setting;
use Illuminate\Http\Request;

class ValidStockGrosir
{
    /**
     * @param Request $request
     * @param array $params
     */
    public function check(Request $request, array $params = [])
    {
        $partOrder  = explode('*', $request->order);
        $msisdn     = $params['msisdn'];
        $whid       = $msisdn->detail->warehouse()->first()->ID_HP;

        $setting    = Setting::first();
        $chipSD     = Customer::find($msisdn->detail()->first()->hpupline);
        $whidAD     = $chipSD->detail->warehouse()->first()->ID_HP;

        for ($i = 4; $i < count($partOrder); $i += 2) {
            $quantity   = (double) $partOrder[$i-1];
            $product    = Product::find('S' . $partOrder[$i]);

            if (strtoupper($setting->tembakdariad) == 'TRUE' || $product->FORECAST == 1) {
                if (! $product->hasGrosirStock($whidAD, $quantity)) {
                    throw new InsufficientException("Stock tidak mencukupi", StatusCode::INSUFFICIENT_STOCK);
                }
            } else if ($product->FORECAST == 0) {
                if (! $product->hasGrosirStock($whid, $quantity)) {
                    throw new InsufficientException("Stock tidak mencukupi", StatusCode::INSUFFICIENT_STOCK);
                }
            }
        }
    }
}

//            if (! $product->hasGrosirStock($whid, $quantity) && $product->FORECAST == 0) {
//                throw new InsufficientException("Stock tidak mencukupi", StatusCode::INSUFFICIENT_STOCK);
//            }