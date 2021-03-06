<?php

/*
 * InterKassa driver for the Omnipay PHP payment processing library
 *
 * @link      https://github.com/hiqdev/omnipay-interkassa
 * @package   omnipay-interkassa
 * @license   MIT
 * @copyright Copyright (c) 2015-2016, HiQDev (http://hiqdev.com/)
 */

namespace Omnipay\InterKassa\Tests\Message;

use Omnipay\InterKassa\Message\CompletePurchaseRequest;
use Omnipay\InterKassa\Message\CompletePurchaseResponse;
use Omnipay\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class CompletePurchaseResponseTest extends TestCase
{
    protected $purse = '887ac1234c1eeee1488b156b';
    protected $signAlgorithm = 'sha256';
    protected $signKey = 'Zp2zfdSJzbS61L32';
    protected $testKey = 'W0b98idvHeKY2h3w';
    protected $payment_no = '1235151';
    protected $description = 'Test Transaction long description';
    protected $payway = 'visa_liqpay_merchant_usd';
    protected $invoiceId = '5632156';
    protected $transactionId = 'ID_123456';
    protected $amount = '5.12';
    protected $currency = 'USD';
    protected $state = 'success';
    protected $sign = 'CwbLEwwevJc/5TyOTfIPDXMfIfXP5tPjWkUDX98bAug=';
    protected $time = '2015-12-17 17:36:13';
    protected $timestamp = 1450362973;

    /**
     * @param array $options
     * @return CompletePurchaseRequest
     */
    public function createRequest($options = [])
    {
        $httpRequest = new HttpRequest([], array_merge([
            'ik_co_id' => $this->purse,
            'ik_pm_no' => $this->payment_no,
            'ik_desc' => $this->description,
            'ik_pw_via' => $this->payway,
            'ik_am' => $this->amount,
            'ik_cur' => $this->currency,
            'ik_inv_id' => $this->transactionId,
            'ik_inv_st' => $this->state,
            'ik_inv_prc' => $this->time,
            'ik_sign' => $this->sign,
        ], $options));

        $request = new CompletePurchaseRequest($this->getHttpClient(), $httpRequest);
        $request->initialize([
            'signAlgorithm' => $this->signAlgorithm,
            'signKey' => $this->signKey,
        ]);

        return $request;
    }

    public function testSignException()
    {
        $this->setExpectedException('Omnipay\Common\Exception\InvalidResponseException', 'Failed to validate signature');
        $this->createRequest(['ik_wtf' => ':)'])->send();
    }

    public function testStateException()
    {
        $this->setExpectedException('Omnipay\Common\Exception\InvalidResponseException', 'The payment was not success');
        $this->createRequest(['ik_inv_st' => 'fail', 'ik_sign' => 'ElWhUp/CjjSXF0ZjNIKbOk+WjpQ9/KIeowD0TjTshw0='])->send();
    }

    public function testSuccess()
    {
        /** @var CompletePurchaseResponse $response */
        $response = $this->createRequest()->send();
        $this->assertTrue($response->isSuccessful());
        $this->assertSame($this->purse, $response->getCheckoutId());
        $this->assertSame($this->payment_no, $response->getTransactionId());
        $this->assertSame($this->transactionId, $response->getTransactionReference());
        $this->assertSame($this->amount, $response->getAmount());
        $this->assertSame($this->currency, $response->getCurrency());
        $this->assertSame($this->timestamp, $response->getTime());
        $this->assertSame($this->payway, $response->getPayer());
        $this->assertSame($this->state, $response->getState());
        $this->assertSame($this->sign, $response->getSign());
    }
}
