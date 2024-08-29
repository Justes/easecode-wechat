<?php
/**
 * Wechat.php
 *
 * @copyright  Charles.
 * @author     Charles <charles.mz.lyn@gmail.com>
 * @created    2024/8/29 12:17
 */

namespace Easecode\Wechat;

class Wechat
{
    protected $host = 'https://api.weixin.qq.com';
    protected $appid;
    protected $appsecret;

    public function __construct($appid, $appsecret)
    {
        $this->appid = $appid;
        $this->appsecret = $appsecret;
    }

    public function getAccessToken($forceRefresh = false)
    {
        $url = $this->host . '/cgi-bin/token';
        $data = [
            'grant_type' => 'client_credential',
            'appid' => $this->appid,
            'secret' => $this->appsecret,
        ];
        $result = $this->curl($url, $data);
        return $result;
    }

    public function getUserInfo($code)
    {
        $url = $this->host . '/sns/jscode2session';
        $data = [
            'appid' => $this->appid,
            'secret' => $this->appsecret,
            'js_code' => $code,
            'grant_type' => 'authorization_code'
        ];
        $result = $this->curl($url, $data);
        return $result;
    }

    public function getUnlimitedQRCode($accessToken, $page, $wxappVersion, $scene = '', $checkPath=false)
    {
        $url = $this->host . '/wxa/getwxacodeunlimit?access_token=' . $accessToken;
        $data = [
            'page' => $page,
            'scene' => $scene,
            'check_path' => $checkPath,
            'env_version' => $wxappVersion,
        ];
        $result = $this->curl($url, json_encode($data), 'POST', [], false);
        return base64_encode($result);
    }

    public function checkMsg($accessToken, $openid, $scene, $message, $version=2)
    {
        $url = $this->host . '/wxa/msg_sec_check?access_token=' . $accessToken;
        $data = [
            'content' => $message,
            'version' => $version,
            'scene' => $scene,
            'openid' => $openid,
        ];

        $result = $this->curl($url, json_encode($data), 'POST');
        return $result;
    }

    public function getTicket()
    {
        $url = $this->host . '/cgi-bin/ticket/getticket';
        $data = [
            'access_token' => $this->getAccessToken(true),
            'type' => 'jsapi',
        ];
        $result = $this->curl($url, $data);
        $ticket = isset($result['ticket']) ? $result['ticket'] : '';
        return $ticket;
    }

    public function genTicketSign($url, $ticket)
    {
        $data = [
            'jsapi_ticket' => $ticket,
            'noncestr' => dechex(time()),
            'timestamp' => time(),
            'url' => $url
        ];
        $string = '';
        foreach ($data as $key => $item) {
            $string .= $key . '=' . $item . '&';
        }
        $string = rtrim($string, '&');
        $data['appid'] = $this->appid;
        $data['sign'] = sha1($string);
        return $data;
    }

    public function curl($url, $data = [], $method = 'GET', $header = [], $decode = true, $json = false)
    {
        if ($method == 'GET') {
            $url = $url . "?" . http_build_query($data);
        }

        $curl = curl_init(); // 初始化curl
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_URL, $url); // 抓取指定网页
        curl_setopt($curl, CURLOPT_TIMEOUT, 1000); // 设置超时时间1秒
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // curl不直接输出到屏幕
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
        curl_setopt($curl, CURLOPT_HEADER, 0); // 设置header

        if ($json) {
            $data = json_encode($data);
            $header[] = 'Content-Type:application/json;charset=utf-8';
            $header[] = 'Content-Length:' . strlen($data);
        }

        if ($method == 'POST') {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        if (!empty($header)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }

        $res = curl_exec($curl); // 运行curl

        if (curl_errno($curl)) {
            print("an error occured in function (): " . curl_error($curl) . "\n");
        }

        curl_close($curl);

        if ($decode) {
            return json_decode($res, true);
        }
        return $res;
    }

}