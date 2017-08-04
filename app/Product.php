<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table      = 'product';
    protected $primaryKey = 'PRODID';

    const CREATED_AT      = 'DATECREATE';
    const UPDATED_AT      = 'DATEUPDATE';
    
    public $incrementing  = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prices()
    {
    	return $this->hasMany('App\PriceList', 'prodid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stocks()
    {
        return $this->hasMany('App\Stock', 'PRODID');
    }

    /**
     * @return mixed
     */
    public function hasStock()
    {
        return $this->stocks()->where('balance2', '>', 0)->first();
    }

    /**
     * @param $whid
     * @param $quantity
     * @return
     */
    public function hasGrosirStock($whid, $quantity)
    {
        return $this->stocks()->where('balance2', '>=', $quantity)->where('WHID', '=', $whid)->first();
    }

    /**
     * @param $price
     * @return mixed
     */
    public function inPrice($price)
    {
    	return $this->prices()->where('levelprcid', $price)->first();
    }

    /**
     * @param $query
     * @param $group
     * @return mixed
     */
    public function scopeGroup($query, $group)
    {
    	return $query->where('PRSTATID1', $group);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
    	return $query->where('PRODSTAT', '<>', '1');
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeRetail($query)
    {
    	return $query->where('EV_DIAL', '777');
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeVisible($query)
    {
    	return $query->where('EV_EXCEL', '<>', '');
    }
}
