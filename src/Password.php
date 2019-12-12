<?php

namespace Cuiqg\Helper;

/**
 * 密码生成/解析
 *
 * @package  Password
 */
class Password
{

    public function make($value)
    {
        return password_hash($value, $this->algorithm());
    }

    public function check($value, $hashedValue) {
        return password_verify($value, $hashedValue);
    }

    public function needsRehash($hashedValue)
    {
        return password_needs_rehash($hashedValue, $this->algorithm());
    }

    public function info($hashedValue)
    {
        return password_get_info($hashedValue);
    }

    protected function algorithm()
    {
        return PASSWORD_DEFAULT;
    }
}
