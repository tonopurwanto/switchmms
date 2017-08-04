<?php

namespace App\Mms\Validators;

use App\Customer;
use App\Mms\Operators\OperatorsContract;
use App\Mms\StatusCode;
use Exception;
use Illuminate\Http\Request;

class ValidMsisdn
{
    /**
     * @var OperatorsContract
     */
    protected $operator;

    /**
     * ValidMsisdn constructor.
     * @param OperatorsContract $operator
     */
    public function __construct(OperatorsContract $operator)
    {
        $this->operator = $operator;
    }

    /**
     * @param Request $request
     * @param array $params
     * @throws Exception
     */
    public function check(Request $request, array $params = [])
    {
        $msisdn = $params['msisdn'];

        if ($msisdn instanceof Customer) {
            $msisdn = $msisdn->CUSTID;
        }

        if (! array_search(substr($msisdn, 0, 4), $this->operator->prefix())) {
            throw new Exception(
                "Msisdn $msisdn->CUSTID tidak sesuai dengan operator " . $this->operator->getName(),
                StatusCode::PROVIDER_MISMATCH
            );
        }
    }
}