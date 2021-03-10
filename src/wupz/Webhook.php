<?php
namespace wupz;

class Webhook
{
    private $access_token;

    private $secret;

    public function __construct($config)
    {
        $this->access_token = $config['access_token'];

        $this->secret = $config['secret'];
    }

    public function send(string $content, array $mobiles)
    {
        list($s1, $s2) = explode(' ', microtime());
        $timestamp = (float) sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
        $signStr = base64_encode(hash_hmac('sha256', $timestamp . "\n" . $this->secret, $this->secret, true));
        $signStr = utf8_encode(urlencode($signStr));
        $url = "https://oapi.dingtalk.com/robot/send?access_token=" . $this->access_token . '&timestamp=' . $timestamp . '&sign=' . $signStr;
        $text = [
            'msgtype' => 'text',
            'text' => [
                'content' => $content,
            ],
            'at' => [
                'atMobiles' => $mobiles,
                'isAtAll' => false,
            ],
        ];
        $this->request($url, json_encode($text));
    }

    private function request($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=utf-8'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
