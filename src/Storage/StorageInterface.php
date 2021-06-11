<?php

namespace LiJinHua\LaravelSms\Storage;

/**
 * Interface StorageInterface
 *
 * @package App\Services\Sms\Storage
 */
interface StorageInterface
{
    /**
     * 设置
     *
     * @param $key
     * @param $value
     */
    public function set($key, $value): void;

    /**
     * 获取
     *
     * @param $key
     * @param $default
     * @return mixed
     */
    public function get($key, $default): mixed;

    /**
     * 删除
     *
     * @param $key
     */
    public function forget($key): void;
}
