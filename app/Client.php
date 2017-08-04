<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table      = 'mobile_client';
    protected $primaryKey = 'ip_address';

    const CREATED_AT      = 'datecreate';
    const UPDATED_AT      = 'dateupdate';

    public $incrementing  = false;
}
