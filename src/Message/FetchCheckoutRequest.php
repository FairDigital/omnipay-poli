<?php

namespace Omnipay\Poli\Message;

class FetchCheckoutRequest extends AbstractRequest
{
    public $endpoint = '/api/v2/Transaction/GetTransaction';

    /**
     * @return array|mixed
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getData()
    {
        $this->validate(
            'merchantCode',
            'authenticationCode'
        );
        $data = array();
        return $data;
    }

    /**
     * @return \Omnipay\Common\Message\ResponseInterface|FetchCheckoutResponse
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     * @throws \Omnipay\Common\Exception\InvalidResponseException
     */
    public function send()
    {
        return $this->response = new FetchCheckoutResponse($this, $this->sendData($this->getData()));
    }

    /**
     * @return string
     */
    public function getHttpMethod()
    {
        return 'GET';
    }
}
