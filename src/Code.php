<?php

namespace LiJinHua\LaravelSms;

use Illuminate\Support\Collection;

/**
 * 验证码
 *
 * @author  lijinhua
 * @package App\Services\Sms
 */
class Code extends Collection
{
    /**
     * Code constructor.
     *
     * @param $phoneNumber
     * @param $phoneArea
     * @param $code
     * @param $sent
     * @param $attempts
     * @param $expireAt
     */
    public function __construct($phoneNumber, $phoneArea, $code, $sent, $attempts, $expireAt)
    {
        $items = compact('phoneNumber', 'phoneArea', 'code', 'sent', 'attempts', 'expireAt');
        parent::__construct($items);
    }

    /**
     *
     *
     * @param  string  $property
     * @return \Illuminate\Support\HigherOrderCollectionProxy|mixed
     */
    public function __get($property)
    {
        if ($this->has($property)) {
            return $this->get($property);
        }
    }
}
