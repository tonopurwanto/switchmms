<?php

namespace App\Mms\Validators;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ValidRequest
{
    /**
     * List validation failed message
     *
     * @return array
     */
    private function messages()
	{
		return [
		    'requestid.required'    => 'Field requestid harus diisi',
		    'requestid.alpha_num'   => 'Requestid hanya boleh huruf dan angka',
		    'requestid.max'         => 'Requestid tidak boleh lebih dari 20 karakter',
		    'order.required'        => 'Field Order harus diisi',
		    'order.max'             => 'Order tidak boleh lebih dari 100 karakter',
		    'master.required'       => 'Field master harus diisi',
		    'master.alpha_num'      => 'Master hanya boleh huruf dan angka',
		    'master.digits_between' => 'Master harus antara 10 dan 12 digit',
		    'sign.required'         => 'Field sign harus diisi',
		    'sign.alpha_num'        => 'Field sign hanya boleh huruf dan angka',
		];
	}

    /**
     * Validate incoming request
     *
     * @param Request $request
     * @param array $params
     *
     * @throws ValidationException
     */
    public function check(Request $request, array $params = [])
	{
		$validator = Validator::make($request->all(), [
			'requestid' => 'required|alpha_num|max:20',
			'order' 	=> 'required|max:100',
			'master' 	=> 'required|digits_between:10,12',
			'sign' 		=> 'required|alpha_num'
		], $this->messages());

		if ($validator->fails()) {
			$errors = $validator->errors();

			foreach ($errors->all() as $error) {
				throw new ValidationException($validator, $error);
			}
		}
	}
}