<?php

namespace Omnipay\Poli\Message;

use Omnipay\Common\Message\AbstractRequest;

/**
 * Poli Purchase Request
 * 
 * @link http://www.polipaymentdeveloper.com/doku.php?id=initiate
 */
class PurchaseRequest extends AbstractRequest
{

    protected $endpoint = 'https://merchantapi.apac.paywithpoli.com/MerchantAPIService.svc/Xml/transaction/initiate';

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

    public function getData()
    {
        $this->validate(
            'merchantCode',
            'authenticationCode',
            'transactionId',
            'currency',
            'amount',
            'returnUrl',
            'cancelUrl'
        );

        $data = array();
        $data['AuthenticationCode'] = $this->getAuthenticationCode();
        $data['CurrencyAmount'] = $this->getAmount();
        $data['CurrencyCode'] = $this->getCurrency();
        $data['MerchantCheckoutURL'] = $this->getCancelUrl();
        $data['MerchantCode'] = $this->getMerchantCode();
        $data['MerchantDateTime'] = date('Y-m-d\TH:i:s');
        $data['MerchantHomePageURL'] = $this->getCancelUrl();
        $data['MerchantRef'] = $this->getTransactionId();
        $data['NotificationURL'] = $this->getNotifyUrl();
        $data['SuccessfulURL'] = $this->getReturnUrl();
        $data['Timeout'] = 0;
        $data['UnsuccessfulURL'] = $this->getReturnUrl();
        $data['MerchantData'] = $this->getTransactionReference();
        $data['MerchantReferenceFormat'] = 0;
        $data['UserIPAddress'] = $this->getClientIp();
        
        return $data;
    }

    public function send()
    {
        $postdata = $this->packageData($this->getData());
        $httpRequest = $this->httpClient->post(
            $this->endpoint,
            array('Content-Type'=>'text/xml'),
            $postdata
        );
        $httpResponse = $httpRequest->send();

        return $this->response = new PurchaseResponse($this, $httpResponse->getBody());
    }

    protected function packageData($data)
    {
        $authenticationcode = $data['AuthenticationCode'];
        unset($data['AuthenticationCode']);
        $fields = "";
        foreach ($data as $field => $value) {
            $fields .= str_repeat(" ", 24)."<dco:$field>$value</dco:$field>\n";
        }
        $namespace = "http://schemas.datacontract.org/2004/07/Centricom.POLi.Services.MerchantAPI.Contracts";
        $i_namespace = "http://www.w3.org/2001/XMLSchema-instance";
        $dco_namespace = "http://schemas.datacontract.org/2004/07/Centricom.POLi.Services.MerchantAPI.DCO";

        return '<?xml version="1.0" encoding="utf-8" ?>
                <InitiateTransactionRequest xmlns="" xmlns:i="'.$i_namespace.'">
                    <AuthenticationCode>'. $authenticationcode.'</AuthenticationCode>
                    <Transaction xmlns:dco="'.$dco_namespace.'">'
                        .$fields.
                    '</Transaction>
                </InitiateTransactionRequest>';
    }
}
