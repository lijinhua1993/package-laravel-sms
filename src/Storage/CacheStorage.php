<?php

namespace LiJinHua\LaravelSms\Storage;

use Cache;

/**
 * 缓存
 *
 * @author  lijinhua
 * @package App\Services\Sms\Storage
 */
class CacheStorage implements StorageInterface
{
    /**
     * @var int
     */
    protected static int $lifetime = 120;

    /**
     * 设置
     *
     * @param $key
     * @param $value
     */
    public function set($key, $value): void
    {
        Cache::put($key, $value, self::$lifetime);
    }

    /**
     * 获取
     *
     * @param $key
     * @param $default
     * @return mixed
     */
    public function get($key, $default): mixed
    {
        return Cache::get($key, $default);
    }

    /**
     * 删除
     *
     * @param $key
     */
    public function forget($key): void
    {
        if (Cache::has($key)) {
            Cache::forget($key);
        }
    }
}
