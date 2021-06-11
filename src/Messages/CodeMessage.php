<?php

namespace LiJinHua\LaravelSms\Messages;

use Overtrue\EasySms\Contracts\GatewayInterface;
use Overtrue\EasySms\Message;

/**
 * 验证码短信
 *
 * @author  lijinhua
 * @package App\Services\Sms\Messages
 */
class CodeMessage extends Message
{
    /**
     * @var
     */
    protected $code;

    /**
     * @var
     */
    protected $minutes;

    protected $data;

    /**
     * CodeMessage constructor.
     *
     * @param         $code
     * @param         $minutes
     * @param  array  $data
     */
    public function __construct($code, $minutes, array $data = [])
    {
        $this->code    = $code;
        $this->minutes = $minutes;
        $this->data    = $data;
    }

    /**
     * 定义直接使用内容发送平台的内容.
     *
     * @param  GatewayInterface|null  $gateway
     *
     * @return string
     */
    public function getContent(GatewayInterface $gateway = null)
    {
        if (!empty($this->data) && isset($this->data['content'])) {
            $content = $this->data['content'];
        } else {
            $content = config('lijinhua.sms.content');
        }

        return vsprintf($content, [$this->code, $this->minutes]);
    }

    /**
     * 定义使用模板发送方式平台所需要的模板 ID.
     *
     * @param  GatewayInterface|null  $gateway
     *
     * @return mixed
     */
    public function getTemplate(GatewayInterface $gateway = null)
    {
        $classname = get_class($gateway);

        if ($pos = strrpos($classname, '\\')) {
            $classname = substr($classname, $pos + 1);
        }

        if ($classname) {
            $classname = strtolower(str_replace('Gateway', '', $classname));
        }

        if (!empty($this->data) && isset($this->data['template'])) {
            return $this->data['template'];
        } else {
            return config('lijinhua.sms.easy_sms.gateways.' . $classname . '.code_template_id');
        }
    }

    /**
     * 模板变量
     *
     * @param  GatewayInterface|null  $gateway
     *
     * @return array
     */
    public function getData(GatewayInterface $gateway = null)
    {
        if (!empty($this->data) && isset($this->data['data']) && is_array($this->data['data'])) {
            $data = $this->data['data'];
        } else {
            $data = array_filter(config('lijinhua.sms.data'));
        }

        return array_merge($data, ['code' => $this->code]);
    }
}
