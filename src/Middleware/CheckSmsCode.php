<?php

namespace App\Http\Middleware;

use Closure;
use LiJinHua\LaravelSms\Exceptions\SmsException;
use LiJinHua\LaravelSms\Facade as Sms;

/**
 * 检查手机验证码
 *
 * @author lijinhua
 * @package App\Http\Middleware
 */
class CheckSmsCode
{

    /**
     *
     *
     * @param $request
     * @param  \Closure  $next
     * @return mixed
     * @throws \LiJinHua\LaravelSms\Exceptions\SmsException
     */
    public function handle($request, Closure $next)
    {
        $request->validate([
            'mobile'      => ['required'],
            'mobile_area' => ['nullable'],
            'sms_code'    => ['required'],
        ]);

        $phoneNumber = $request->input('mobile');
        $phoneArea   = $request->input('mobile_area') ?? null;
        $smsCode     = $request->input('sms_code');

        if (!Sms::checkCode($phoneNumber, $phoneArea, $smsCode)) {
            throw new SmsException(__('sms.coder_check_error'));
        }

        $response = $next($request);

        // api响应状态码为0时 清除短信验证码缓存,防止重复利用
        $responseOriginalContent = $response->getOriginalContent();
        if (isset($responseOriginalContent['code']) && $responseOriginalContent['code'] === 0) {
            Sms::clearCode($phoneNumber, $phoneArea);
        }

        return $response;
    }
}