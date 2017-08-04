<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
	protected $table      = 'customer';
	protected $primaryKey = 'CUSTID';

	const CREATED_AT      = 'DATECREATE';
	const UPDATED_AT      = 'DATEUPDATE';

	public $incrementing  = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function detail()
    {
    	return $this->hasOne('App\Custadd', 'CUSTID');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function region()
    {
        return $this->hasOne('App\Region', 'CSSTATID', 'csstatid4');
    }

    /**
     * @return bool
     */
    public function isInternal()
    {
    	return strtoupper($this->CSSTATID3) == 'INTERNAL';
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders()
    {
    	return $this->hasMany('App\Doitem', 'custid');
    }

    /**
     * @param $id
     * @return mixed
     */
    public function hasRequestId($id)
    {
    	return $this->orders()->where('requestid', $id)
			->whereDate('datecreate', date('Y-m-d'))
			->first();
    }

    /**
     * @param $order
     * @return mixed
     */
    public function hasOrder($order)
    {
    	return $this->orders()->where('pesanmasuk', $order)
	    	->whereDate('datecreate', date('Y-m-d'))
			->first();
    }

    /**
     * @param $data
     * @return array
     */
    public function newTransaction($data)
	{
		self::insert('inboxtodo', $data);

		return ['status' => 2, 'message' => 'Pending'];
	}
}
