<?php

namespace Omnipay\Poli\Message;

/**
 * Poli Purchase Request
 * 
 * @link http://www.polipaymentdeveloper.com/doku.php?id=initiate
 */
class PurchaseRequest extends AbstractRequest
{
    public $endpoint = '/api/v2/Transaction/Initiate';

    /**
     * @return array|mixed
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
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
        $data['Amount'] = $this->getAmount();
        $data['CurrencyCode'] = $this->getCurrency();
        $data['CancellationURL'] = $this->getCancelUrl();
        $data['MerchantData'] = $this->getTransactionId();
        $data['MerchantDateTime'] = date('Y-m-d\TH:i:s');
        $data['MerchantHomePageURL'] = $this->getCancelUrl();
        $data['MerchantReference'] = $this->getCombinedMerchantRef();
        $data['MerchantReferenceFormat'] = 1;
        $data['NotificationURL'] = $this->getNotifyUrl();
        $data['SuccessURL'] = $this->getReturnUrl();
        $data['Timeout'] = 0;
        $data['FailureURL'] = $this->getReturnUrl();
        $data['UserIPAddress'] = $this->getClientIp();

        return $data;
    }

    /**
     * Generate reference data
     * @link http://www.polipaymentdeveloper.com/doku.php?id=nzreconciliation
     */
    public function getCombinedMerchantRef()
    {
        $card = $this->getCard();
        $id = $this->cleanField($this->getTransactionId());
        if ($card && $card->getName()) {
            $data = array($this->cleanField($card->getName()), "", $id);
            return implode("|", $data);
        }

        return $id;
    }

    /**
     * Data in reference field must not contain illegal characters
     */
    protected function cleanField($field)
    {
        return substr($field, 0, 12);
    }

    /**
     * @return \Omnipay\Common\Message\ResponseInterface|PurchaseResponse
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     * @throws \Omnipay\Common\Exception\InvalidResponseException
     */
    public function send()
    {
        return new PurchaseResponse($this, $this->sendData($this->getData()));
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
                <InitiateTransactionRequest xmlns="'.$namespace.'" xmlns:i="'.$i_namespace.'">
                    <AuthenticationCode>'. $authenticationcode.'</AuthenticationCode>
                    <Transaction xmlns:dco="'.$dco_namespace.'">'
                        .$fields.
                    '</Transaction>
                </InitiateTransactionRequest>';
    }
}
