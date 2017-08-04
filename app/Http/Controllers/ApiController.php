<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Response;

class ApiController extends Controller
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var int
     */
    protected $statusCode = 200;

    /**
     * ApiController constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param $code
     * @return $this
     */
    public function setStatusCode($code)
    {
        $this->statusCode = $code;

        return $this;
    }

    /**
     * @param string $message
     * @param null $status
     * @return mixed
     */
    public function respondCreated($message = "Created", $status = null)
    {
        return $this->setStatusCode(HttpResponse::HTTP_CREATED)->respond(['message' => $message, 'status_code' => $status, 'price' => 0, 'sn' => '', 'trxdate' => date('Y-m-d H:i:s')]);
    }

    /**
     * @param string $message
     * @param null $status
     * @return mixed
     */
    public function respondNotFound($message = 'Not found', $status = null)
    {
        return $this->setStatusCode(HttpResponse::HTTP_NOT_FOUND)->respondWithError($message, $status);
    }

    /**
     * @param string $message
     * @param null $status
     * @return mixed
     */
    public function respondBadRequest($message = 'Bad request', $status = null)
    {
        return $this->setStatusCode(HttpResponse::HTTP_BAD_REQUEST)->respondWithError($message, $status);   
    }

    /**
     * @param string $message
     * @param null $status
     * @return mixed
     */
    public function respondUnprocessableEntity($message = 'The given data failed to pass validation', $status = null)
    {
        return $this->setStatusCode(HttpResponse::HTTP_UNPROCESSABLE_ENTITY)->respondWithError($message, $status);   
    }

    /**
     * @param string $message
     * @param null $status
     * @return mixed
     */
    public function respondServiceUnavailable($message = 'Service unavailable', $status = null)
    {
        return $this->setStatusCode(HttpResponse::HTTP_SERVICE_UNAVAILABLE)->respondWithError($message, $status);
    }

    /**
     * @param string $message
     * @param null $status
     * @return mixed
     */
    public function respondPaymentRequired($message = 'Insufficient funds', $status = null)
    {
        return $this->setStatusCode(HttpResponse::HTTP_PAYMENT_REQUIRED)->respondWithError($message, $status);
    }

    /**
     * @param string $message
     * @param null $status
     * @return mixed
     */
    public function respondForbidden($message = 'Forbidden', $status = null)
    {
        return $this->setStatusCode(HttpResponse::HTTP_FORBIDDEN)->respondWithError($message, $status);
    }

    /**
     * @param string $message
     * @param null $status
     * @return mixed
     */
    public function respondConflict($message = 'Conflict', $status = null)
    {
        return $this->setStatusCode(HttpResponse::HTTP_CONFLICT)->respondWithError($message, $status);
    }

    /**
     * @param $message
     * @param null $status
     * @return mixed
     */
    public function respondWithError($message, $status = null)
    {
        return $this->respond([
            'message'     => $message,
            'status_code' => is_null($status) ? $this->getStatusCode() : $status,
            'price'       => 0,
            'sn'          => '',
            'trxdate'     => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * @param $data
     * @param array $headers
     * @return mixed
     */
    public function respond($data, $headers = [])
    {
        return Response::make(
            $data['status_code'].';'.
            $data['message'].';'.
            $this->request->requestid.';'.
            $data['price'].';'.
            $data['sn'].';'.
            $data['trxdate'],
            $this->getStatusCode(),
            $headers
        );
    }
}
