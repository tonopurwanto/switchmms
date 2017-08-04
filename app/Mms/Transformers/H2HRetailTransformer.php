<?php

namespace App\Mms\Transformers;

class H2HRetailTransformer extends Transformer
{
    /**
     * @param $item
     * @return array
     */
    public function transform($item) {
		return [
    		'id'    => $item['CUSTID'],
    		'name'  => $item['CUSTNAME'],
    		'saldo' => $item['DEPOSIT']
    	];
	}
}