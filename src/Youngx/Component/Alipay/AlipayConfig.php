<?php

namespace Youngx\Component\Alipay;

class AlipayConfig
{
    protected $cacert;
    protected $partner;
    protected $key;
    protected $signType;
    protected $inputCharset;
    protected $transport;

    public function __construct($partner, $key, $signType = 'MD5', $inputCharset = 'uft-8', $transport = 'http')
    {
        $this->setCacert(__DIR__ . '/cacert.pem');
        $this->setPartner($partner);
        $this->setKey($key);
        $this->setSignType($signType);
        $this->setInputCharset($inputCharset);
        $this->setTransport($transport);
    }

    public function getCacert()
    {
        return $this->cacert;
    }

    public function setCacert($realpath)
    {
        $this->cacert = $realpath;

        return $this;
    }

    /**
     * @param string $inputCharset
     */
    public function setInputCharset($inputCharset)
    {
        $this->inputCharset = strtolower($inputCharset);

        return $this;
    }

    /**
     * @return string
     */
    public function getInputCharset()
    {
        return $this->inputCharset;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $partner
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPartner()
    {
        return $this->partner;
    }

    /**
     * @param string $signType
     */
    public function setSignType($signType)
    {
        $this->signType = strtoupper($signType);

        return $this;
    }

    /**
     * @return string
     */
    public function getSignType()
    {
        return $this->signType;
    }

    /**
     * @param string $transport
     */
    public function setTransport($transport)
    {
        $this->transport = strtolower($transport);

        return $this;
    }

    /**
     * @return string
     */
    public function getTransport()
    {
        return $this->transport;
    }


}