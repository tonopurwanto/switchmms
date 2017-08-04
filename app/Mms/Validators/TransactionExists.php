<?php

namespace App\Mms\Validators;

use App\Mms\Exceptions\ModelDuplicateException;
use App\Mms\StatusCode;
use Illuminate\Http\Request;

class TransactionExists
{
    /**
     * Check if requestid exists
     * Check if same order exists
     *
     * @param Request $request
     * @param array $params
     *
     * @return bool
     *
     * @throws ModelDuplicateException
     */
    public function check(Request $request, array $params = [])
	{
		$customer = $params['customer'];
		$message = ''; $status = 0;

		$doitem = $customer->hasRequestId($request->requestid);

		if (! $doitem) {
			// Cek transaksi beda requestid tapi pesanmasuknya sama, 
			// in case trx bisa masuk dari beberapa jalur (POS, SMS, API)
			$doitem = $customer->hasOrder($request->order . '.' . $customer->pin);

			if ($doitem) {
				throw new ModelDuplicateException(
					'Transaksi ' . $request->order .' Double', 
					StatusCode::DOUBLE_TRANSACTION
				);
			}

			return true;
		}

		// Transaksi dengan requestid yang sama, return status keberhasilan
		if (! $doitem->usrupdate) {
			$status  = StatusCode::PENDING;
			$message = 'Transaksi telah terjadi, status pending';
		} else if (strtoupper(substr($doitem->usrupdate, 0, 8)) == 'BERHASIL') {
			$status  = StatusCode::SUCCESS;
			$message = 'Pengisian ' . $request->order . ' telah terjadi, Status : BERHASIL';
		} else if (strtoupper(substr($doitem->usrupdate, 0, 5)) == 'GAGAL') {
		    // GAGAL boleh tembak lagi
//			$status  = StatusCode::FAIL;
//			$message = 'Pengisian ' . $request->order . ' telah terjadi, Status : GAGAL';
		}

		throw new ModelDuplicateException($message, $status);
	}
}