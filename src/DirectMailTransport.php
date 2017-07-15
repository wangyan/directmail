<?php

namespace WangYan\DirectMail;

use GuzzleHttp\Client;
use Illuminate\Mail\Transport\Transport;
use Psr\Http\Message\ResponseInterface;
use Swift_Mime_Message;
use GuzzleHttp\Exception\ClientException;

class DirectMailTransport extends Transport
{
    const API_URL = 'https://dm.aliyuncs.com/';

    private $AccessKeyId;
    private $AccessSecret;
    private $CommonParameters;
    private $SingleParameters;

    /**
     * DirectMailTransport 构造函数
     * 初始化 AccessKeyId、AccessSecret、公共请求参数
     *
     * @param $AccessKeyId
     * @param $AccessSecret
     */
    public function __construct($AccessKeyId, $AccessSecret, $ReplyToAddress, $AddressType)
    {
        $this->AccessKeyId    =  $AccessKeyId;
        $this->AccessSecret   =  $AccessSecret;
        $this->ReplyToAddress =  $ReplyToAddress;
        $this->AddressType    =  $AddressType;

        $this->CommonParameters['Format'] = 'JSON';
        $this->CommonParameters['Version'] = '2015-11-23';
        $this->CommonParameters['AccessKeyId'] = $AccessKeyId;
        $this->CommonParameters['SignatureMethod'] = 'HMAC-SHA1';
        $this->CommonParameters['Timestamp'] = date('Y-m-d\TH:i:s\Z');
        $this->CommonParameters['SignatureVersion'] = '1.0';
        $this->CommonParameters['SignatureNonce'] = uniqid();
    }

    /**
     * 调用单一发信接口或者批量发信接口发信
     *
     * @param Swift_Mime_Message $message
     * @param null $failedRecipients
     * @return mixed
     */
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $body = $message->getBody();

        if ($body instanceof BatchSendMail) {
            // $result = $this->BatchSendMail($message);
        } else {
            $result = $this->SingleSendMail($message);
        }

        return $result;
    }

    /**
     * 使用单一发信接口发信
     *
     * @param Swift_Mime_Message $message
     * @return ResponseInterface
     */
    private function SingleSendMail(Swift_Mime_Message $message)
    {
        // 单一发信接口参数
        $this->SingleParameters['Action'] = 'SingleSendMail';
        $this->SingleParameters['AccountName'] = $this->getAddress($message->getFrom());
        $this->SingleParameters['ReplyToAddress'] = (string) $this->ReplyToAddress;
        $this->SingleParameters['AddressType'] = (string) $this->AddressType;
        $this->SingleParameters['ToAddress'] = $this->getAddress($message->getTo());
        $this->SingleParameters['FromAlias'] = $this->getFromName($message);
        $this->SingleParameters['Subject'] = $message->getSubject();
        $this->SingleParameters['HtmlBody'] = $message->getBody();

        $Parameters = array_merge($this->CommonParameters,$this->SingleParameters);
        $Parameters['Signature'] = $this->makeSign($Parameters);

        $Http = new Client();
        try {
            $Response = $Http->post(self::API_URL, [
                'form_params' => $Parameters,
            ]);
        } catch (ClientException $e) {
            return $this->response($e->getResponse());
        }

        return $this->response($Response);
    }

    /**
     * 获取发信地址或目标地址
     * 该地址需要先在阿里云管理控制台中配置。
     *
     * @param $data
     * @return null|string
     */
    private function getAddress($data)
    {
        if (!$data) {
            return;
        }
        return array_get(array_keys($data), 0, null);
    }

    /**
     * 获取发信人昵称
     * 发信人昵称长度小于15个字符
     *
     * @param Swift_Mime_Message $message
     * @return mixed
     */
    private function getFromName(Swift_Mime_Message $message)
    {
        return array_get(array_values($message->getFrom()), 0);
    }

    /**
     * 生成请求签名（Signature）信息
     *
     * @param $Parameters
     * @return string
     */
    private function makeSign($Parameters)
    {
        date_default_timezone_set("GMT");
        ksort($Parameters);
        $CanonicalizedQueryString = '';
        foreach ($Parameters as $key => $value) {
            $CanonicalizedQueryString .= '&' . $this->percentEncode($key) . '=' . $this->percentEncode($value);
        }
        $StringToSign = 'POST&%2F&' . $this->percentEncode(substr($CanonicalizedQueryString, 1));
        $Signature = hash_hmac('sha1', $StringToSign, $this->AccessSecret . "&", true);

        $Signature = base64_encode($Signature);
        return $Signature;
    }

    /**
     * 构造规范化字符串用于计算签名（Signature）
     *
     * @param $str
     * @return mixed|string
     */
    private function percentEncode($str)
    {
        $res = urlencode($str);
        $res = preg_replace('/\+/', '%20', $res);
        $res = preg_replace('/\*/', '%2A', $res);
        $res = preg_replace('/%7E/', '~', $res);
        return $res;
    }

    /**
     * 解析 DirectMail 返回值，提示异常原因
     *
     * @param ResponseInterface $response
     * @return bool
     */
    protected function response(ResponseInterface $response)
    {
        $Body = json_decode($response->getBody());

        if (isset($Body->Code)) {
            $Code = $Body->Code;
            switch ($Code):
                case 'InvalidMailAddress.NotFound':
                    echo "发信地址不存在";
                    break;
                case 'InvalidMailAddressStatus.Malformed':
                    echo "发信地址状态不正确";
                    break;
                case 'InvalidToAddress':
                    echo "目标地址不正确";
                    break;
                case 'InvalidBody':
                    echo "邮件正文不正确。textBody 或 htmlBody不能同时为空";
                    break;
                case 'InvalidSendMail.Spam':
                    echo "本次发送操作被反垃圾系统检测为垃圾邮件，禁止发送。请仔细检查邮件内容和域名状态等";
                    break;
                case 'InvalidSubject.Malformed':
                    echo "邮件主题限制在100个字符以内";
                    break;
                case 'InvalidMailAddressDomain.Malformed':
                    echo "发信地址的域名状态不正确，请检查MX、SPF配置是否正确";
                    break;
                case 'InvalidFromALias.Malformed':
                    echo "发信人昵称不正确，请检查发信人昵称是否正确，长度小于15个字符。";
                    break;
                default:
                    return true;
            endswitch;
        }
    }
}