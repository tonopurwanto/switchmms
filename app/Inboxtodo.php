<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Inboxtodo extends Model
{
    protected $table      = 'inboxtodo';
    protected $primaryKey = 'idx';

    const CREATED_AT      = 'DTR';
    const UPDATED_AT      = 'DTP';
    
    protected $fillable = [
		'DTR',
		'DTP',
		'FromHP',
		'FromName',
		'Message',
		'Mode',
		'BlockStatus',
		'UserID',
		'ComPort',
		'xStatus',
		'JobID',
		'TransmisiHP',
		'csstatid1',
		'csstatid4',
		'h2h'
	];

    /**
     * @return string
     */
    public static function jobId()
	{
		$row = static::whereRaw('length(jobid) = 20')->orderBy('jobid', 'desc')->first();
		
		if (! $row) return "00000000000000000001";

		$jobid = ((double) $row->JobID) + 1;
		
		return str_repeat("0", 20 - strlen($jobid)) . $jobid;
	}
}
