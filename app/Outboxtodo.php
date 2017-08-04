<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Outboxtodo extends Model
{
    protected $table      = 'outboxtodo';
    protected $primaryKey = 'idx';

    const CREATED_AT      = 'DTR';
    const UPDATED_AT      = 'DTP';

    protected $fillable = [
        'DTR',
        'DTP',
        'ToHP',
        'ToName',
        'Message',
        'Mode',
        'ComPort',
        'Status',
        'JobID',
        'csstatid1',
        'csstatid4',
        'Refrence',
        'h2h'
    ];
}
