<?php

namespace Youngx\Component\Alipay;

class AlipayRequest extends AlipayBase
{
    /**
     * @var AlipayConfig
     */
    protected $config;

    /**
     *支付宝网关地址（新）
     */
    protected $gateway = 'https://mapi.alipay.com/gateway.do';

    public function __construct(AlipayConfig $config)
    {
        $this->config = $config;
    }

    /**
     * 建立请求，以表单HTML形式构造（默认）
     * @param $params 请求参数数组
     * @param $method 提交方式。两个值可选：post、get
     * @param $buttonLabel 确认按钮显示文字
     * @return string 提交表单HTML文本
     */
    public function buildForm(array $params, $method = 'get', $buttonLabel = '')
    {
        $params = $this->buildParams(array_merge(array(
                    "service" => "alipay.auth.authorize",
                    "partner" => $this->config->getPartner(),
                    "target_service"	=> 'user.auth.quick.login',
                    'return_url' => '',
                    'anti_phishing_key' => '',
                    'exter_invoke_ip' => '',
                    '_input_charset' => $this->config->getInputCharset()
                ), $params));

        $html = sprintf(
            '<form id="alipayForm" name="alipayForm" action="%s" method="%s">',
            $this->gateway,
            $method
        );
        foreach ($params as  $key => $val) {
            $html .= sprintf('<input type="hidden" name="%s" value="%s" />', $key, $val);
        }
        $html .= "<input type='submit' value='".$buttonLabel."'></form>";
        //$html .= '<script>document.forms["alipayForm"].submit();</script>';

        return $html;
    }


    /**
     * 生成要请求给支付宝的参数数组
     * @param array $params 请求前的参数数组
     * @return array 要请求的参数数组
     */
    protected function buildParams(array $params) {
        //除去待签名参数数组中的空值和签名参数
        $filtered = $this->filterParams($params);

        //对待签名参数数组排序
        $sorted = $this->sortParams($filtered);

        //生成签名结果
        $signedString = $this->buildSignedString($sorted);

        //签名结果与签名方式加入请求提交参数组中
        $sorted['sign'] = $signedString;
        $sorted['sign_type'] = $this->config->getSignType();

        return $sorted;
    }

    /**
     * 生成签名结果
     * @param $params 已排序要签名的数组
     * @return string 签名结果字符串
     */
    protected function buildSignedString(array $params)
    {
        $str = $this->createLinkstring($params);
        switch ($this->config->getSignType()) {
            case "MD5" :
                $signed = $this->md5Sign($str, $this->config->getKey());
                break;
            default :
                $signed = "";
        }
        return $signed;
    }
}