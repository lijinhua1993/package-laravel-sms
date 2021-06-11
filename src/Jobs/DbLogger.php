<?php

namespace LiJinHua\LaravelSms\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use LiJinHua\LaravelSms\Models\SmsLog;

class DbLogger implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 验证码
     *
     * @var
     */
    private $code;

    /**
     * 请求结果
     *
     * @var
     */
    private $result;

    /**
     * 发送状态
     *
     * @var
     */
    private $flag;

    /**
     * Create a new job instance.
     */
    public function __construct($code, $result, $flag)
    {
        $this->code   = $code;
        $this->result = $result;
        $this->flag   = $flag;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        if (!config('lijinhua.sms.dblog')) {
            return;
        }
        if ($this->code->phoneArea) {
            $mobile = '+' . $this->code->phoneArea . $this->code->phoneNumber;
        } else {
            $mobile = $this->code->phoneNumber;
        }

        SmsLog::create([
            'mobile'     => $mobile,
            'data'       => json_encode($this->code),
            'is_sent'    => $this->flag,
            'result'     => $this->result,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
