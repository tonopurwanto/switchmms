<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PriceList extends Model
{
    protected $table      = 'levelprcid';
    protected $primaryKey = null;

    public $timestamps    = false;
    public $incrementing  = false;

    /**
     * @param $price
     * @return mixed
     */
    public static function grosirInPrice($price)
    {
        return DB::table('levelprcid')
            ->join('product', 'levelprcid.prodid', '=', 'product.prodid')
            ->select('levelprcid.prodid', 'levelprcid.sellprice', 'product.prodname1', 'product.prstatid1')
            ->where('levelprcid.levelprcid', $price)
            ->where('product.prstatid1', 'MKIOS')
            ->where('product.ev_dial', '773')
            ->where('product.prodstat', '<>', '1')
            ->where('product.ev_excel', '<>', '')
            ->orderBy('product.ev_excel')
            ->get();
    }
}
