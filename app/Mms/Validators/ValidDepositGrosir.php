<?php

namespace App\Mms\Validators;

use App\Customer;
use App\Mms\Exceptions\InsufficientException;
use App\Mms\StatusCode;
use App\PriceList;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ValidDepositGrosir
{
    /**
     * Client balance should enough
     *
     * @param Request $request
     * @param array $params
     *
     * @return float|int
     * @throws ModelNotFoundException
     * @throws InsufficientException
     */
    public function check(Request $request, array $params = [])
    {
        $customer   = $params['customer'];
        $harga      = $customer->detail->levelprcid;
        $totalPrice = 0;
        $partOrder  = explode('*', $request->order);
        $msisdn     = $params['msisdn'];

        // Cek jenis harga
        $jenisHarga = $customer->region->jenisharga;
        if (! $customer->isInternal()) {
            $jenisHarga = 0;
        }

        // Penentuan levelprice
        if (($jenisHarga == 1 || $jenisHarga == 2) && $customer->isInternal()) {
            $harga = $msisdn->detail->levelprcid;
        }

        $priceList = PriceList::grosirInPrice($harga);
        if (! $priceList) throw new ModelNotFoundException("Produk tidak tersedia", StatusCode::PRODUCT_NOT_FOUND);

        $arrayPrice = json_decode(json_encode($priceList->toArray()), true);

        // Total harga
        for ($i = 4; $i < count($partOrder); $i += 2) {
            $qty        = (double) $partOrder[$i-1];
            $prodid     = 'S' . $partOrder[$i];
            $totalPrice += $this->sellPrice($arrayPrice, $prodid) * $qty;
        }

        if ($customer->DEPOSIT < $totalPrice) {
            throw new InsufficientException("Deposit tidak mencukupi", StatusCode::INSUFFICIENT_FUNDS);
        }

        return $totalPrice;
    }

    private function sellPrice($aryProduct, $key)
    {
        $prodKey = array_search($key, array_column($aryProduct, 'prodid'));
        if ($prodKey === FALSE)
            throw new ModelNotFoundException("Product $key tidak ditemukan", StatusCode::PRODUCT_NOT_FOUND);

        return (double) $aryProduct[$prodKey]['sellprice'];
    }
}