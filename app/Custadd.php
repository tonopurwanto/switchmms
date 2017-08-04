<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Custadd extends Model
{
    protected $table      = 'custadd';
    protected $primaryKey = 'CUSTID';

    const CREATED_AT      = 'datecreate';
    const UPDATED_AT      = 'dateupdate';
    
    public $incrementing  = false;

    public function warehouse()
    {
        return $this->hasOne('App\Warehouse', 'Nomor_Hp', 'hpupline');
    }
}
