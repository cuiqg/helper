<?php
/**
 * ATP.php for helper.
 *               _
 *   _______  __(_)___ _____ _
 *  / ___/ / / / / __ `/ __ `/
 * / /__/ /_/ / / /_/ / /_/ /
 * \___/\__,_/_/\__, /\__, /
 *                /_//____/
 */

namespace Cuiqg\Helper;

/**
 * Google Authenticator 二步验证
 * @package Cuiqg\Helper
 */
class ATP
{
    protected $_codeLength = 6;

    /**
     * @param int $length
     * @return $this
     */
    public function setCodeLength($length) {
        $this->_codeLength = $length;
        return $this;
    }

    protected function _base32Decode($secret) {
        if(empty($secret)) {
            return '';
        }

        $base32Chars = $this->_getBase32LookupTable();
        $base32CharsFlipped = array_flip($base32Chars);

        $paddingCharCount = substr_count($secret, $base32Chars[32]);
        $allowedValues = [6, 4, 3, 1, 0];

        if(!in_array($paddingCharCount, $allowedValues)) {
            return false;
        }

        for($i = 0; $i < 4; ++$i) {
            if($paddingCharCount == $allowedValues[$i] && substr($secret, -($allowedValues[$i])) != str_repeat($base32Chars[32], $allowedValues[$i])) {
                return false;
            }
        }

        $secret = str_replace('=', '', $secret);
        $secret = str_split($secret);
        $binaryString = '';

        for($i = 0; $i < count($secret); $i = $i + 8) {
            $x = '';
            if(!in_array($secret[$i], $base32Chars)) {
                return false;
            }

            for($j = 0; $j < 8; ++$j) {
                $x .= str_pad(base_convert(@$base32CharsFlipped[@$secret[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
            }

            $eightBits = str_split($x, 8);

            for($z = 0; $z < count($eightBits); ++$z) {
                $binaryString .= (($y = chr(base_convert($eightBits[$z], 2, 10))) || ord($y) == 48) ? $y : '';
            }
        }

        return $binaryString;
    }

    protected function _getBase32LookupTable() {
        return [
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', //  7
            'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', // 15
            'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', // 23
            'Y', 'Z', '2', '3', '4', '5', '6', '7', // 31
            '=',  // padding char
        ];
    }

    private function timingSafeEquals($safeString, $userString) {
        if(function_exists('hash_equals')) {
            return hash_equals($safeString, $userString);
        }

        $safeLen = strlen($safeString);
        $userLen = strlen($userString);

        if($userLen != $safeLen) {
            return false;
        }

        $result = 0;

        for($i = 0; $i < $userLen; ++$i) {
            $result |= (ord($safeString[$i]) ^ ord($userString[$i]));
        }

        return $result === 0;
    }
}