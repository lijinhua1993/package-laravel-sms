<?php

use LiJinHua\LaravelSms\Storage\CacheStorage;
use Overtrue\EasySms\Strategies\OrderStrategy;

return [

    /**
     * 调试模式
     *
     * 调试模式下不会调用第三方网关发送短信
     * 可以通过api接口获取当前验证码信息
     */
    'debug'    => true,

    /**
     * 是否写入日志
     */
    'dblog'    => true,

    /**
     * 验证码
     */
    'code'     => [
        'length'       => 5, // 长度
        'validMinutes' => 5, // 有效期(分钟)
        'maxAttempts'  => 0, // 最大尝试输错次数,超过将重新生成验证码
    ],

    /**
     * 短信内容
     */
    'content'  => '【your app signature】您的验证码是%s。有效期为%s分钟，请尽快验证。',

    /**
     * 短信模板变量
     */
    'data'     => [
        // 'product' => '',
    ],

    /**
     * 存储器
     */
    'storage'  => CacheStorage::class,

    /**
     * 第三方扩展
     */
    'easy_sms' => [
        // HTTP 请求的超时时间（秒）
        'timeout'  => 5.0,

        // 默认发送配置
        'default'  => [
            // 网关调用策略，默认：顺序调用
            'strategy' => OrderStrategy::class,

            // 默认可用的发送网关
            'gateways' => [
                'yunpian',
            ],
        ],
        // 可用的网关配置
        'gateways' => [
            'errorlog' => [
                'file' => storage_path('logs/laravel-sms.log'),
            ],
            'yunpian'  => [
                'api_key'          => 'bbaba2625b16bcc0cc90253fb9d423d2',
                // 验证码模板id
                'code_template_id' => '4239136',
            ],
            //...
        ],
    ],

];