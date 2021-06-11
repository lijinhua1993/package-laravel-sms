<?php

namespace LiJinHua\LaravelSms;

use Carbon\Carbon;
use Exception;
use LiJinHua\LaravelSms\Jobs\DbLogger;
use LiJinHua\LaravelSms\Messages\CodeMessage;
use LiJinHua\LaravelSms\Storage\StorageInterface;
use Overtrue\EasySms\Contracts\MessageInterface;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;
use Overtrue\EasySms\PhoneNumber;

/**
 * 短信服务
 *
 * @author  lijinhua
 * @package App\Services\Sms
 */
class Sms
{

    /**
     * 第三方短信服务
     *
     * @var EasySms
     */
    protected EasySms $easySms;

    /**
     * 存储器
     *
     * @var StorageInterface
     */
    protected StorageInterface $storage;

    /**
     * 缓存key
     *
     * @var string
     */
    protected string $key;

    /**
     * Sms constructor.
     *
     * @param  EasySms  $easySms
     * @param  StorageInterface  $storage
     */
    public function __construct(EasySms $easySms, StorageInterface $storage)
    {
        $this->easySms = $easySms;
        $this->storage = $storage;
    }

    /**
     * 发送短信
     *
     * @param  string  $phoneNumber
     * @param  string|null  $phoneArea
     * @param  array|null  $data
     * @param  array  $gateways
     * @return bool
     */
    public function send(string $phoneNumber, string $phoneArea = null, ?array $data = [], array $gateways = []): bool
    {
        $flag = false;

        $this->setKey($phoneArea . $phoneNumber);

        $code = $this->getCodeFromStorage();
        if ($this->needNewCode($code)) {
            $code = $this->getNewCode($phoneNumber, $phoneArea);
        }

        $validMinutes = (int) config('lijinhua.sms.code.validMinutes', 5);

        if (!($data instanceof MessageInterface)) {
            $message = new CodeMessage($code->code, $validMinutes, $data);
        } else {
            $message = $data;
        }

        if (config('lijinhua.sms.debug')) {
            $gateways = ['errorlog'];
        }

        try {
            $results = $this->easySms->send(new PhoneNumber($phoneNumber, $phoneArea), $message, $gateways);

            foreach ($results as $value) {
                if ('success' == $value['status']) {
                    $code->put('sent', true);
                    $code->put('sentAt', Carbon::now());
                    $this->storage->set($this->key, $code);
                    $flag = true;
                }
            }
        } catch (NoGatewayAvailableException $noGatewayAvailableException) {
            $results = $noGatewayAvailableException->getResults();
            $flag    = false;
        } catch (Exception $exception) {
            $results = $exception->getMessage();
            $flag    = false;
        }

        DbLogger::dispatch($code, json_encode($results), $flag);

        return $flag;
    }

    /**
     * 设置缓存
     *
     * @param  string  $key
     */
    public function setKey(string $key)
    {
        $key       = 'sms.' . $key;
        $this->key = md5($key);
    }

    /**
     * 从存储器中获取短信验证码
     *
     * @return mixed
     */
    public function getCodeFromStorage(): mixed
    {
        return $this->storage->get($this->key, '');
    }

    /**
     * 获取新验证码
     *
     * @param  string  $phoneNumber
     * @param  string|null  $phoneArea
     * @return Code
     */
    public function getNewCode(string $phoneNumber, string $phoneArea = null): Code
    {
        $code = $this->generateCode($phoneNumber, $phoneArea);

        $this->storage->set($this->key, $code);

        return $code;
    }

    /**
     * 生成验证码
     *
     * @param  string  $phoneNumber
     * @param  string|null  $phoneArea
     * @return Code
     */
    public function generateCode(string $phoneNumber, string $phoneArea = null): Code
    {
        $length       = (int) config('lijinhua.sms.code.length', 5);
        $characters   = '0123456789';
        $charLength   = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; ++$i) {
            $randomString .= $characters[mt_rand(0, $charLength - 1)];
        }

        $validMinutes = (int) config('lijinhua.sms.code.validMinutes', 5);

        return new Code($phoneNumber, $phoneArea, $randomString, false, 0, Carbon::now()->addMinutes($validMinutes));
    }

    /**
     * 检查验证码
     *
     * @param  string  $phoneNumber
     * @param  string|null  $phoneArea
     * @param  string  $inputCode
     * @return bool
     */
    public function checkCode(string $phoneNumber, string $phoneArea = null, string $inputCode = ''): bool
    {
        $this->setKey($phoneArea . $phoneNumber);

        $code = $this->storage->get($this->key, '');

        if (empty($code)) {
            return false;
        }

        if ($code && $code->code == $inputCode) {
            return true;
        }

        $code->put('attempts', $code->attempts + 1);

        $this->storage->set($this->key, $code);

        return false;
    }

    /**
     * 清除短信验证
     *
     * @param  string  $phoneNumber
     * @param  string|null  $phoneArea
     */
    public function clearCode(string $phoneNumber, string $phoneArea = null)
    {
        $this->setKey($phoneArea . $phoneNumber);

        $this->storage->forget($this->key);
    }

    /**
     * 获取存储器
     *
     * @return StorageInterface
     */
    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    /**
     * 设置存储器
     *
     * @param  StorageInterface  $storage
     */
    public function setStorage(StorageInterface $storage): void
    {
        $this->storage = $storage;
    }

    /**
     * 是否可以发送
     *
     * @param  string  $phoneNumber
     * @param  string|null  $phoneArea
     * @return bool
     */
    public function canSend(string $phoneNumber, string $phoneArea = null): bool
    {
        $this->setKey($phoneArea . $phoneNumber);

        $code = $this->storage->get($this->key, '');

        if (empty($code) || $code->sentAt < Carbon::now()->addMinutes(-1)) {
            return true;
        }

        return false;
    }

    /**
     * 是否需要生成新的短信验证码
     *
     * @param $code
     * @return bool
     */
    protected function needNewCode($code): bool
    {
        if (empty($code)) {
            return true;
        }

        return $this->checkAttempts($code);
    }

    /**
     * 检查尝试次数
     *
     * @param $code
     * @return bool
     */
    private function checkAttempts($code): bool
    {
        $maxAttempts = config('lijinhua.sms.code.maxAttempts');

        if ($code->expireAt > Carbon::now() && $code->attempts < $maxAttempts) {
            return false;
        }

        return true;
    }

}