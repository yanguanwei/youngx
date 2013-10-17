<?php

namespace Youngx\Component\Alipay;

class AlipayBase
{
    /**
     * 除去数组中的空值和签名参数
     * @param array $params 签名参数组
     * @return array 去掉空值与签名参数后的新签名参数组
     */
    protected function filterParams(array $params) {
        $filtered = array();
        while (list ($key, $val) = each ($params)) {
            if($key == "sign" || $key == "sign_type" || $val == "") {
                continue;
            } else {
                $filtered[$key] = $params[$key];
            }
        }
        return $filtered;
    }

    protected function sortParams(array $params) {
        ksort($params);
        reset($params);
        return $params;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     * @param array $params 需要拼接的数组
     * @return string 拼接完成以后的字符串
     */
    protected function createLinkString(array $params)
    {
        $arg  = "";
        while (list ($key, $val) = each ($params)) {
            $arg .= $key . "=" . $val . "&";
        }
        //去掉最后一个&字符
        $arg = substr($arg, 0, count($arg)-2);

        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }

        return $arg;
    }

    /**
     * 签名字符串
     * @param $str 需要签名的字符串
     * @param $key 私钥
     * @return string 签名结果
     */
    protected function md5Sign($str, $key)
    {
        return md5($str . $key);
    }

    /**
     * 验证签名
     * @param $str 需要签名的字符串
     * @param $sign 签名结果
     * @param $key 私钥
     * @return boolean 签名结果
     */
    protected function md5Verify($str, $sign, $key) {
        return md5($str . $key) == $sign;
    }

    /**
     * 远程获取数据，GET模式
     * 注意：
     * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
     * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
     * @param $url 指定URL完整路径地址
     * @param $cacertUrl 指定当前工作目录绝对路径
     * @return string 远程输出的数据
     */
    protected function getHttpResponse($url,$cacertUrl) {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);//SSL证书认证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
        curl_setopt($curl, CURLOPT_CAINFO, $cacertUrl);//证书地址
        $responseText = curl_exec($curl);
        curl_close($curl);
        return $responseText;
    }
}