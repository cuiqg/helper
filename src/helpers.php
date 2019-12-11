<?php

if(! function_exists('value')) {
    /**
     * 返回参数
     *
     * @param mixed $value
     * @return mixed
     */
    function value($value) {
        return $value instanceof Closure ? $value() : $value;
    }
}

if(! function_exists('windows_os')) {

    function windows_os(){
        return PHP_OS_FAMILY === 'Windows';
    }
}