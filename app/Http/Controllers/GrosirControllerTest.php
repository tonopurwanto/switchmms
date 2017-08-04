<?php

namespace App\Http\Controllers;

use App\Client;
use App\Customer;
use App\Inboxtodo;
use App\Mms\Exceptions\ForbiddenException;
use App\Mms\Exceptions\InsufficientException;
use App\Mms\Exceptions\MaintenanceException;
use App\Mms\Exceptions\ModelDuplicateException;
use App\Mms\StatusCode;
use App\Mms\Transformers\H2HRetailTransformer;
use App\Mms\Validators\Normalize;
use App\Mms\Validators\ValidDepositGrosir;
use App\Outboxtodo;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class GrosirControllerTest extends ApiController
{
    protected $retailTransformer;
    protected $requestid;
    protected $product;
    protected $master;
    protected $order;
    protected $sign;

    protected $messageToSend;

    /**
     * GrosirController constructor.
     *
     * @param Request $request
     * @param H2HRetailTransformer $transform
     */
    public function __construct(Request $request, H2HRetailTransformer $transform)
    {
        parent::__construct($request);

        $this->retailTransformer = $transform;
    }

    /**
     * New transaction
     *
     * @param Request $request
     * @return string
     */
    public function store(Request $request)
    {
        try {

            Normalize::check($request);

            $this->order     = $request->order;         // S5.085215870408
            $this->requestid = $request->requestid;     // 20160815120254000001
            $this->master    = $request->master;        // 085777557537
            $this->sign      = $request->sign;          // SHA1(CL003MKIOSORDERPIN)
            $this->product   = 'MKIOS';                 // $this->input->post('product');

            $customer = Customer::find(Normalize::phone($this->master));
            $client   = Client::find($_SERVER['REMOTE_ADDR']);

            //================INSERT INTO INBOXTODO===================
            $data = array(
                'DTR'         => date('Y-m-d H:i:s'),
                'DTP'         => date('Y-m-d H:i:s'),
                'FromHP'      => $customer->CUSTID,
                'FromName'    => $customer->CUSTNAME,
                'Message'     => $this->order . $customer->pin . '#',
                'Mode'        => 'XML',
                'BlockStatus' => 'False',
                'UserID'      => 'API',
                'ComPort'     => '12',
                'xStatus'     => 'vch',
                'JobID'       => Inboxtodo::jobId(),
                'TransmisiHP' => $client->ip_address . ':' . $this->requestid,
                'csstatid1'   => $customer->csstatid1,
                'csstatid4'   => $customer->csstatid4,
                'h2h'         => $client->ip_address
            );

            Inboxtodo::create($data);

        } catch (ValidationException $e) {
            return $this->respondUnprocessableEntity($e->getResponse(), StatusCode::VALIDATION_FAILED);
        } catch (ModelNotFoundException $e) {
            return $this->respondNotFound($e->getMessage(), $e->getCode());
        } catch (ModelDuplicateException $e) {
            return $this->respondConflict($e->getMessage(), $e->getCode());
        } catch (MaintenanceException $e) {
            return $this->respondServiceUnavailable($e->getMessage(), $e->getCode());
        } catch (InsufficientException $e) {
            if ($e->getCode() == StatusCode::INSUFFICIENT_FUNDS) {
                return $this->respondPaymentRequired($e->getMessage(), $e->getCode());
            } else if ($e->getCode() == StatusCode::INSUFFICIENT_STOCK) {
                return $this->respondServiceUnavailable($e->getMessage(), $e->getCode());
            }
        } catch (ForbiddenException $e) {
            return $this->respondForbidden($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            if ($e->getCode() == 0) {
                return $this->respondBadRequest($e->getMessage(), StatusCode::OTHERS);
            }

            return $this->respondBadRequest($e->getMessage(), $e->getCode() ?: StatusCode::VALIDATION_FAILED);
        }

        return $this->respondCreated('Pending', StatusCode::PENDING);
    }

    /**
     * Check transaction status
     *
     * @param Request $request
     * @return mixed
     */
    public function show(Request $request)
    {
        try {

            Normalize::status($request);

        } catch (ValidationException $e) {
            return $this->respondUnprocessableEntity($e->getResponse(), StatusCode::VALIDATION_FAILED);
        } catch (ModelNotFoundException $e) {
            return $this->respondNotFound($e->getMessage(), $e->getCode());
        } catch (ModelDuplicateException $e) {
            return $this->respondConflict($e->getMessage(), $e->getCode());
        } catch (MaintenanceException $e) {
            return $this->respondServiceUnavailable($e->getMessage(), $e->getCode());
        } catch (InsufficientException $e) {
            if ($e->getCode() == StatusCode::INSUFFICIENT_FUNDS) {
                return $this->respondPaymentRequired($e->getMessage(), $e->getCode());
            } else if ($e->getCode() == StatusCode::INSUFFICIENT_STOCK) {
                return $this->respondServiceUnavailable($e->getMessage(), $e->getCode());
            }
        } catch (ForbiddenException $e) {
            return $this->respondForbidden($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            if ($e->getCode() == 0) {
                return $this->respondBadRequest($e->getMessage(), StatusCode::OTHERS);
            }

            return $this->respondBadRequest($e->getMessage(), $e->getCode() ?: StatusCode::VALIDATION_FAILED);
        }

        return $this->respondNotFound(
            "Transaksi dengan requestid $request->requestid tidak ditemukan",
            StatusCode::FAIL
        );
    }

    public function testCase(Request $request)
    {
        try {

            Normalize::check($request);

            $this->order     = $request->order;         // S5.085215870408
            $this->requestid = $request->requestid;     // 20160815120254000001
            $this->master    = $request->master;        // 085777557537
            $this->sign      = $request->sign;          // SHA1(CL003MKIOSORDERPIN)
            $this->product   = 'MKIOS';                 // $this->input->post('product');

            $customer = Customer::find($this->master);
            $client = Client::find($_SERVER['REMOTE_ADDR']);
            $msisdn = explode('*', $request->order)[2];

            switch ($msisdn) {
                case '082187427866':
                    $this->successTest($request, $client, $customer);
                    break;
                case '082193111741':
                    $this->failedTest($request, $client, $customer);
                    break;
                case '082187414355':
                    $this->pendingTest();
                    break;
            }

            if (! $this->messageToSend) {
                return $this->respondBadRequest("Something wrong with the test", StatusCode::OTHERS);
            }

            $this->sendResponse();

        } catch (ValidationException $e) {
            return $this->respondUnprocessableEntity($e->getResponse(), StatusCode::VALIDATION_FAILED);
        } catch (ModelNotFoundException $e) {
            return $this->respondNotFound($e->getMessage(), $e->getCode());
        } catch (ModelDuplicateException $e) {
            return $this->respondConflict($e->getMessage(), $e->getCode());
        } catch (MaintenanceException $e) {
            return $this->respondServiceUnavailable($e->getMessage(), $e->getCode());
        } catch (InsufficientException $e) {
            if ($e->getCode() == StatusCode::INSUFFICIENT_FUNDS) {
                return $this->respondPaymentRequired($e->getMessage(), $e->getCode());
            } else if ($e->getCode() == StatusCode::INSUFFICIENT_STOCK) {
                return $this->respondServiceUnavailable($e->getMessage(), $e->getCode());
            }
        } catch (ForbiddenException $e) {
            return $this->respondForbidden($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            if ($e->getCode() == 0) {
                return $this->respondBadRequest($e->getMessage(), StatusCode::OTHERS);
            }

            return $this->respondBadRequest($e->getMessage(), $e->getCode() ?: StatusCode::VALIDATION_FAILED);
        }

        return $this->respondCreated("Pending", StatusCode::PENDING);
    }

    private function successTest(Request $request, $client, $customer)
    {
        $partOrder = explode("*", $this->order);
        $msisdn = Customer::find($partOrder[2]);
        $products = "";

        $params['customer'] = $customer;
        $params['msisdn'] = $msisdn;

        for ($i = 4; $i < count($partOrder); $i += 2) {
            $qty        = (double) $partOrder[$i-1];
            $prodid     = 'S' . $partOrder[$i];
            $products   .= $prodid . '=' . $qty . ', ';
        }

        $products = substr($products, 0, strlen($products)-2);
        $amount = (new ValidDepositGrosir())->check($request, $params);
        $message = $customer->CUSTNAME . ': Pengisian ' . $products .' ke nomor ' . $msisdn->CUSTID . ' berhasil. Total Harga Rp. ' . $amount .', Sisa Deposit Anda Rp.'.$customer->DEPOSIT;

        $this->messageToSend = [
            'DTR'       => date('Y-m-d H:i:s'),
            'DTP'       => date('Y-m-d H:i:s'),
            'ToHP'      => $client->code,
            'ToName'    => $client->client_name,
            'Message'   => $message,
            'Mode'      => 'API',
            'ComPort'   => '00',
            'Status'    => '-',
            'JobID'     => '-',
            'Voucher'   => '-',
            'csstatid1' => $customer->csstatid1,
            'csstatid4' => $customer->csstatid4,
            'Refrence'  => $this->requestid,
            'h2h'       => $client->ip_address
        ];
    }

    private function failedTest(Request $request, $client, $customer)
    {
        $partOrder = explode("*", $this->order);
        $msisdn = Customer::find($partOrder[2]);
        $products = "";

        $params['customer'] = $customer;
        $params['msisdn'] = $msisdn;

        for ($i = 4; $i < count($partOrder); $i += 2) {
            $qty        = (double) $partOrder[$i-1];
            $prodid     = 'S' . $partOrder[$i];
            $products   .= $prodid . '=' . $qty . ', ';
        }

        $products = substr($products, 0, strlen($products)-2);
        $amount = (new ValidDepositGrosir())->check($request, $params);
        $message = $customer->CUSTNAME . ': Pengisian ' . $products .' ke nomor ' . $msisdn->CUSTID . ' GAGAL/dibatalkan. Total Harga Rp. ' . $amount .', Sisa Deposit Anda Rp.'.$customer->DEPOSIT;

        $this->messageToSend = [
            'DTR'       => date('Y-m-d H:i:s'),
            'DTP'       => date('Y-m-d H:i:s'),
            'ToHP'      => $client->code,
            'ToName'    => $client->client_name,
            'Message'   => $message,
            'Mode'      => 'API',
            'ComPort'   => '00',
            'Status'    => '-',
            'JobID'     => '-',
            'Voucher'   => '-',
            'csstatid1' => $customer->csstatid1,
            'csstatid4' => $customer->csstatid4,
            'Refrence'  => $this->requestid,
            'h2h'       => $client->ip_address
        ];
    }

    private function pendingTest()
    {
    }

    private function sendResponse()
    {
        Outboxtodo::create($this->messageToSend);
    }

    public function callback(Request $request)
    {
        $data = array(
            'DTR'         => date('Y-m-d H:i:s'),
            'DTP'         => date('Y-m-d H:i:s'),
            'FromHP'      => '-',
            'FromName'    => '-',
            'Message'     => file_get_contents("php://input"),
            'Mode'        => 'XML',
            'BlockStatus' => 'False',
            'UserID'      => 'API',
            'ComPort'     => '12',
            'xStatus'     => 'Receiving',
            'JobID'       => Inboxtodo::jobId(),
            'TransmisiHP' => '-',
            'csstatid1'   => '-',
            'csstatid4'   => '-',
            'h2h'         => $_SERVER['REMOTE_ADDR']
        );

        Inboxtodo::create($data);

        return 'Received';
    }
}