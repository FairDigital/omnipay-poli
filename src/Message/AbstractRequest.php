<?php

namespace Omnipay\Poli\Message;


abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
    public $endpoint;

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return $this->getParameter('apiUrl') . $this->endpoint;
    }

    /**
     * Get HTTP Method.
     *
     * This is nearly always POST but can be over-ridden in sub classes.
     *
     * @return string
     */
    public function getHttpMethod()
    {
        return 'POST';
    }

    public function getMerchantCode()
    {
        return $this->getParameter('merchantCode');
    }

    public function setMerchantCode($value)
    {
        return $this->setParameter('merchantCode', $value);
    }

    public function getAuthenticationCode()
    {
        return $this->getParameter('authenticationCode');
    }

    public function setAuthenticationCode($value)
    {
        return $this->setParameter('authenticationCode', $value);
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        $merchantCode = $this->getMerchantCode();
        $authenticationCode = $this->getAuthenticationCode();
        $auth = base64_encode($merchantCode . ":" . $authenticationCode);

        return array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . $auth,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function sendData($data)
    {
        $endpoint = ($this->getHttpMethod() == 'GET') ? $this->getEndpoint() . '?' . http_build_query($data) : $this->getEndpoint();
        $httpResponse = $this->httpClient->request($this->getHttpMethod(), $endpoint, $this->getHeaders(), json_encode($data));
        return $httpResponse->getBody()->getContents();
    }
}