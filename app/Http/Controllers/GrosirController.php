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
use App\Mms\Validators\TransactionExistsGrosir;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class GrosirController extends ApiController
{
    protected $retailTransformer;
    protected $requestid;
    protected $product;
    protected $master;
    protected $order;
    protected $sign;

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
     * For testing purpose
     * Transform column in db to array key
     *
     * @return mixed
     */
    public function index()
    {
        $customers = Customer::find('0811333653');

        if (!$customers) {
            return $this->respondNotFound('Customer does not exists');
        }

        return $this->respond([
            'data' => $this->retailTransformer->transform($customers->toArray())
        ]);
    }

    /**
     * For testing purpose
     *
     * @return string
     */
    public function status()
    {
        $input = file_get_contents('php://input');

        return 'Received: ' . $input;
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

            $client   = Client::find($_SERVER['REMOTE_ADDR']);
            $customer = Customer::find(Normalize::phone($client->hp));

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
}