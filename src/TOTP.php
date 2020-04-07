<?php

/**
 * TOTP.php for helper.
 *               _
 *   _______  __(_)___ _____ _
 *  / ___/ / / / / __ `/ __ `/
 * / /__/ /_/ / / /_/ / /_/ /
 * \___/\__,_/_/\__, /\__, /
 *                /_//____/
 */

namespace Cuiqg\Helper;

use \DomainException;
use \Exception;

/**
 * Google Authenticator 二步验证
 *
 * @author Michael Kliewe
 * @copyright 2012 Michael Kliewe
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class TOTP
{
    protected static $_codeLength = 6;

    /**
     * 创建 Secret
     *
     * @param int $secretLength
     * @return string
     * @throws Exception
     */
    public static function createSecret($secretLength = 16)
    {
        $validChars = static::_getBase32LookupTable();
        if ($secretLength < 16 || $secretLength > 128) {
            throw new DomainException('无效的Secret 长度');
        }

        $secret = '';
        $rnd = false;
        if (function_exists('random_bytes')) {
            $rnd = random_bytes($secretLength);
        } elseif (function_exists(function_exists('openssl_random_pseudo_bytes'))) {
            $rnd = openssl_random_pseudo_bytes($secretLength, $cryptoStrong);
            if (!$cryptoStrong) {
                $rnd = false;
            }
        }

        if ($rnd !== false) {
            for ($i = 0; $i < $secretLength; ++$i) {
                $secret .= $validChars[ord($rnd[$i]) & 31];
            }
        } else {
            throw new Exception('No source of secure random');
        }
        return $secret;
    }

    /**
     * 获取CODE
     *
     * @param string $secret
     * @param int|null $timeSlice
     * @return string
     */
    public static function getCode($secret, $timeSlice = null)
    {
        if ($timeSlice === null) {
            $timeSlice = floor(time() / 30);
        }
        $secretkey = static::_base32Decode($secret);

        $time = chr(0) . chr(0) . chr(0) . chr(0) . pack('N*', $timeSlice);

        $hm = hash_hmac('SHA1', $time, $secretkey, true);

        $offset = ord(substr($hm, -1)) & 0x0F;

        $hashpart = substr($hm, $offset, 4);

        $value = unpack('N', $hashpart);
        $value = $value[1];

        $value = $value & 0x7FFFFFFF;
        $modulo = pow(10, static::$_codeLength);
        return str_pad($value % $modulo, static::$_codeLength, '0', STR_PAD_LEFT);
    }

    /**
     * 生成二维码
     *
     * @param string $name 用户名
     * @param string $secret secret
     * @param string|null $title
     * @param array $params
     * @return string
     */
    public static function getQRCodeUrl($name, $secret, $title = null, $params = [])
    {
        $width = !empty($params['width']) && (int) $params['width'] > 0 ? (int) $params['width'] : 200;
        $height = !empty($params['height']) && (int) $params['height'] > 0 ? (int) $params['height'] : 200;
        $level = !empty($params['level']) && array_search($params['level'], array('L', 'M', 'Q', 'H')) !== false ? $params['level'] : 'M';
        $urlencoded = urlencode('otpauth://totp/' . $name . '?secret=' . $secret . '');
        if (isset($title)) {
            $urlencoded .= urlencode('&issuer=' . urlencode($title));
        }
        return "https://api.qrserver.com/v1/create-qr-code/?data=$urlencoded&size=${width}x${height}&ecc=$level";
    }

    /**
     * 验证 CODE
     * @param string $secret
     * @param string $code
     * @param int $discrepancy
     * @param int|null $currentTimeSlice
     * @return bool
     */
    public static function verifyCode($secret, $code, $discrepancy = 1, $currentTimeSlice = null)
    {
        if ($currentTimeSlice === null) {
            $currentTimeSlice = floor(time() / 30);
        }

        if (strlen($code) != 6) {
            return false;
        }

        for ($i = -$discrepancy; $i <= $discrepancy; ++$i) {
            $calculatedCode = static::getCode($secret, $currentTimeSlice + $i);
            if (static::timingSafeEquals($calculatedCode, $code)) {
                return true;
            }
        }
        return false;
    }

    protected static function _base32Decode($secret)
    {
        if (empty($secret)) {
            return '';
        }

        $base32Chars = static::_getBase32LookupTable();
        $base32CharsFlipped = array_flip($base32Chars);

        $paddingCharCount = substr_count($secret, $base32Chars[32]);
        $allowedValues = [6, 4, 3, 1, 0];

        if (!in_array($paddingCharCount, $allowedValues)) {
            return false;
        }

        for ($i = 0; $i < 4; ++$i) {
            if ($paddingCharCount == $allowedValues[$i] && substr($secret, - ($allowedValues[$i])) != str_repeat($base32Chars[32], $allowedValues[$i])) {
                return false;
            }
        }

        $secret = str_replace('=', '', $secret);
        $secret = str_split($secret);
        $binaryString = '';

        for ($i = 0; $i < count($secret); $i = $i + 8) {
            $x = '';
            if (!in_array($secret[$i], $base32Chars)) {
                return false;
            }

            for ($j = 0; $j < 8; ++$j) {
                $x .= str_pad(base_convert(@$base32CharsFlipped[@$secret[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
            }

            $eightBits = str_split($x, 8);

            for ($z = 0; $z < count($eightBits); ++$z) {
                $binaryString .= (($y = chr(base_convert($eightBits[$z], 2, 10))) || ord($y) == 48) ? $y : '';
            }
        }

        return $binaryString;
    }

    protected static function _getBase32LookupTable()
    {
        return [
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', //  7
            'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', // 15
            'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', // 23
            'Y', 'Z', '2', '3', '4', '5', '6', '7', // 31
            '=',  // padding char
        ];
    }

    private static function timingSafeEquals($safeString, $userString)
    {
        if (function_exists('hash_equals')) {
            return hash_equals($safeString, $userString);
        }

        $safeLen = strlen($safeString);
        $userLen = strlen($userString);

        if ($userLen != $safeLen) {
            return false;
        }

        $result = 0;

        for ($i = 0; $i < $userLen; ++$i) {
            $result |= (ord($safeString[$i]) ^ ord($userString[$i]));
        }

        return $result === 0;
    }
}
