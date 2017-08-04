<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    protected $table      = 'levelprice';
    protected $primaryKey = 'levelprcid';

    public $timestamps    = false;
    public $incrementing  = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function detail()
    {
    	return $this->hasMany('App\PriceList', 'levelprcid');
    }
}
