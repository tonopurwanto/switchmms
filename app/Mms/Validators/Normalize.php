<?php

namespace App\Mms\Validators;

use App\Client;
use App\Customer;
use App\Mms\Exceptions\ForbiddenException;
use App\Mms\Operators\OperatorsContract;
use App\Mms\Operators\Telkomsel;
use App\Mms\StatusCode;
use App\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class Normalize
{
    /**
     * hardcoded for now
     *
     * @var string
     */
    protected $productGroup = 'MKIOS';

    /**
     * @var
     */
    protected $client;

    /**
     * @var
     */
    protected $customer;

    /**
     * @var
     */
    protected $msisdn;

    /**
     * For retail transaction only
     *
     * @var
     */
    protected $product;

    /**
     * Normalize constructor.
     * @param Request $request
     */
    private function __construct(Request $request)
	{
		(new ValidRequest)->check($request);

		$this->prepareGrosir($request);
	}

    /**
     * List of validation
     * Register here if there is a new one
     *
     * @var array
     */
    protected $checks = [
        ValidOrderGrosir::class,
        ValidMsisdn::class,
        ValidStockGrosir::class,
        ValidDepositGrosir::class,
        TransactionOpen::class,
        TransactionExistsGrosir::class
    ];

    /**
     * All data from the database required for this checks should be here
     * to reduce queries
     *
     * @param Request $request
     *
     * @throws \Exception
     */
    private function prepare(Request $request)
	{
        $this->client = Client::find($_SERVER['REMOTE_ADDR']);
        if (! $this->client) {
            throw new ForbiddenException("Host tidak terdaftar", StatusCode::UNKNOWN_HOST);
        }

		$this->customer = Customer::find(static::phone($request->master));
		if (! $this->customer) {
			throw new ModelNotFoundException("No master $request->master tidak terdaftar", StatusCode::USERID_NOT_FOUND);
		}

		$partOrder    = explode('.', $request->order);
		$orderProduct = $partOrder[0];

		$this->product = Product::where('PRODID', $orderProduct)
			->group($this->productGroup)
			->active()->retail()->visible()->first();
		if (! $this->product || strtoupper($this->productGroup) !== 'MKIOS') {
			throw new ModelNotFoundException("Produk tidak tersedia", StatusCode::PRODUCT_NOT_FOUND);
		}

        $this->msisdn = Normalize::phone($partOrder[1]);

		$this->dependencies();
	}

    /**
     * All data from the database required for this checks should be here
     * to reduce queries
     *
     * @param Request $request
     *
     * @throws \Exception
     */
    private function prepareGrosir(Request $request)
    {
        $partOrder    = explode('*', $request->order);

        $this->client = Client::find($_SERVER['REMOTE_ADDR']);
        if (! $this->client) {
            throw new ForbiddenException("Host tidak terdaftar", StatusCode::UNKNOWN_HOST);
        }

        $this->customer = Customer::find(static::phone($this->client->hp));
        if (! $this->customer) {
            throw new ModelNotFoundException("No master " . $this->client->hp . " tidak terdaftar", StatusCode::USERID_NOT_FOUND);
        }

        $this->msisdn = Customer::find(static::phone($partOrder[2]));
        if (! $this->msisdn) {
            throw new ModelNotFoundException("Nomor tujuan : $partOrder[2] belum terdaftar", StatusCode::USERID_NOT_FOUND);
        }

        $this->dependencies();
    }

    /**
     * Provide dependencies for requested class
     */
    private function dependencies()
    {
        app()->when(ValidMsisdn::class)
            ->needs(OperatorsContract::class)
            ->give(function () {
                return new Telkomsel();
            });
    }

    /**
     * Normalize phone number to local format
     *
     * @param $phone
     *
     * @return string
     */
    public static function phone($phone)
	{
		if (substr($phone, 0, 3) == '+62') {
			return '0' . substr($phone, 3);
		} else if (substr($phone, 0, 2) == '62') {
			return '0'. substr($phone, 2);
		} else if (substr($phone, 0, 1) == '8') {
			return '0'. $phone;
		}

		return $phone;
	}

    /**
     * Run all defined validation
     * each class thrown an Exception
     *
     * @param Request $request
     */
    public static function check(Request $request)
	{
		$normalize = new static($request);

		$params['customer']     = $normalize->customer;
		$params['product']      = $normalize->product;
		$params['productGroup'] = $normalize->productGroup;
		$params['msisdn']       = $normalize->msisdn;
		$params['client']       = $normalize->client;

		foreach ($normalize->checks as $class) {
			app($class)->check($request, $params);
		}
	}

    /**
     * Run all defined validation
     * each class thrown an Exception
     *
     * @param Request $request
     */
    public static function status(Request $request)
    {
        $normalize = new static($request);

        $params['customer']     = $normalize->customer;
        $params['product']      = $normalize->product;
        $params['productGroup'] = $normalize->productGroup;
        $params['msisdn']       = $normalize->msisdn;
        $params['client']       = $normalize->client;

        $checks = [
            ValidOrderGrosir::class,
            ValidMsisdn::class,
            TransactionExistsGrosir::class
        ];

        foreach ($checks as $class) {
            app($class)->check($request, $params);
        }
    }
}