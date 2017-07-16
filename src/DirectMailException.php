<?php

namespace WangYan\DirectMail;

use Symfony\Component\HttpKernel\Exception\HttpException;

class DirectMailException extends HttpException
{
    protected $message;

    /**
     * DirectMailException constructor.
     * @param string $StatusCode
     * @param null $message
     */
    public function __construct($StatusCode, $message)
    {
        parent::__construct($StatusCode,$this->setMessage($message));
    }

    /**
     * 翻译错误提示代码
     * @param $message
     * @return string
     */
    private function setMessage($message)
    {
        switch ($message):
            case 'InvalidMailAddress.NotFound':
                $this->message =  "发信地址不存在";
                break;
            case 'InvalidMailAddressStatus.Malformed':
                $this->message = "发信地址状态不正确";
                break;
            case 'InvalidToAddress':
                $this->message = "目标地址不正确";
                break;
            case 'InvalidBody':
                $this->message = "邮件正文不正确。textBody 或 htmlBody不能同时为空";
                break;
            case 'InvalidSendMail.Spam':
                $this->message = "本次发送操作被反垃圾系统检测为垃圾邮件，禁止发送。请仔细检查邮件内容和域名状态等";
                break;
            case 'InvalidSubject.Malformed':
                $this->message = "邮件主题限制在100个字符以内";
                break;
            case 'InvalidMailAddressDomain.Malformed':
                $this->message = "发信地址的域名状态不正确，请检查MX、SPF配置是否正确";
                break;
            case 'InvalidFromALias.Malformed':
                $this->message = "发信人昵称不正确，请检查发信人昵称是否正确，长度小于15个字符。";
                break;
            case 'InvalidTimeStamp.Expired':
                $this->message = "时间戳错误，请检查 timezone 是否为 UTC";
                break;
            default:
                $this->message = $message;
        endswitch;

        return $this->message;
    }
}