<?php

namespace App\Mms\Validators;

use App\Mms\StatusCode;
use App\Mms\Exceptions\MaintenanceException;
use App\Setpulsa;
use Illuminate\Http\Request;

class TransactionOpen
{
    /**
     * Time the closing occure
     *
     * @var string
     */
    private $begin = '23:45:00';


    /**
     * Time the closing finish
     *
     * @var string
     */
    private $end   = '00:00:01';

    /**
     * No transaction allowed while closing
     *
     * @param Request $request
     * @param array $params
     *
     * @throws MaintenanceException
     */
    public function check(Request $request, array $params = [])
	{
	    $setpulsa = Setpulsa::first();
	    if ($setpulsa->jp_aktif == '-' || $setpulsa->jp_aktif == '' || $setpulsa->jp_aktif == null) {
            throw new MaintenanceException("Sistem sedang closing harian", StatusCode::CLOSING_SERVER);
        }

		if (date('H:i:s') >= $this->begin || date('H:i:s') <= $this->end) {
			throw new MaintenanceException("Sistem sedang closing harian", StatusCode::CLOSING_SERVER);
		}
	}
}