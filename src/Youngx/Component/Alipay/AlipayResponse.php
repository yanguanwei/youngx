<?php

namespace Youngx\Component\Alipay;

class AlipayResponse extends AlipayBase
{
    /**
     * HTTPS形式消息验证地址
     * @var string
     */
    protected $httpsVerifyUrl = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';
    /**
     * HTTP形式消息验证地址
     * @var string
     */
    protected $httpVerifyUrl = 'http://notify.alipay.com/trade/notify_query.do?';
    /**
     * @var AlipayConfig
     */
    protected $config;

    public function __construct(AlipayConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        if (empty($_GET) || !$this->getSignVerify($_GET, $_GET["sign"])) {
            return false;
        }

        $responseTxt = 'true';
        if (!empty($_GET["notify_id"])) {
            $responseTxt = $this->getResponse($_GET["notify_id"]);
        }

        return (Boolean) preg_match("/true$/i", $responseTxt);
    }

    public function getUserId()
    {
        return isset($_GET['user_id']) ? $_GET['user_id'] : 0;
    }

    public function getToken()
    {
        return isset($_GET['token']) ? $_GET['token'] : '';
    }

    /**
     * 获取返回时的签名验证结果
     * @param $params 通知返回来的参数数组
     * @param $sign 返回的签名结果
     * @return boolean 签名验证结果
     */
    protected function getSignVerify(array $params, $sign) {
        $params = $this->filterParams($params);
        $params = $this->sortParams($params);
        $params = $this->createLinkString($params);

        switch ($this->config->getSignType()) {
            case "MD5" :
                $isSign = $this->md5Verify($params, $sign, $this->alipay_config['key']);
                break;
            default :
                $isSign = false;
        }

        return $isSign;
    }

    /**
     * 获取远程服务器ATN结果,验证返回URL
     * @param $notify_id 通知校验ID
     * @return string 服务器ATN结果
     * 验证结果集：
     * invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空
     * true 返回正确信息
     * false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
     */
    protected function getResponse($notify_id)
    {
        $verifyUrl = $this->config->getTransport() == 'https' ? $this->httpsVerifyUrl : $this->httpVerifyUrl;
        $verifyUrl .= "partner=" . $this->config->getPartner() . "&notify_id=" . $notify_id;
        return $this->getHttpResponse($verifyUrl, $this->config->getCacert());
    }
}