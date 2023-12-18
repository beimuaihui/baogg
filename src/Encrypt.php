<?php

namespace Baogg;

class Encrypt
{
    const ENC_SALT = 'd291e0f54d7111ebbfbcd017c287c1a5';

    public static $key = '4da8f388-6dff-11ed-a132-0242ac030004'; // 'zf1-ext/library/Baogg/Encrypt.php';
    const SESS_CIPHER = 'AES-128-CBC';

    public static function encrypt($data, $key, $iv = '')
    {
        if (!$iv) {
            $iv = $key;
        }

        $final_key = self::_getSalt($key);
        $final_iv = self::_getIv($iv);

        var_dump($data, $final_key, $final_iv);


        return base64_encode(openssl_encrypt($data, 'AES-128-CBC', $final_key, 1, $final_iv));
    }
    public static function decrypt($data, $key, $iv = '')
    {
        if (!$iv) {
            $iv = $key;
        }
        return openssl_decrypt(base64_decode($data), 'AES-128-CBC',  self::_getSalt($key), 1, self::_getIv($iv));
    }

    public static function _getIv($iv)
    {
        $ivlen = openssl_cipher_iv_length(self::SESS_CIPHER);
        return substr(md5(self::_getSalt($iv)), 0, $ivlen);
    }

    public static function _getSalt($key)
    {
        if (!$key) {
            $key = self::$key;
        }
        $key = md5($key);
        return $key;
    }


    public static function encrypt_deprecated($plaintext, $key = '')
    {
        if (!$key) {
            $key = self::$key;
        }
        $key = hash("SHA256", $key, true);
        # create a random IV to use with CBC encoding
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

        # creates a cipher text compatible with AES (Rijndael block size = 128)
        # to keep the text confidential
        # only suitable for encoded input that never ends with value 00h
        # (because of default zero padding)
        $ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $plaintext, MCRYPT_MODE_CBC, $iv);

        # prepend the IV for it to be available for decryption
        $ciphertext = $iv . $ciphertext;

        # encode the resulting cipher text so it can be represented by a string
        $ciphertext_base64 = base64_encode($ciphertext);
        //echo  $ciphertext_base64 . "\n";
        return $ciphertext_base64;
    }

    /*public static function encrypt($plaintext,$key = ''){
        if(!$key){
            $key = self::$key;
        }
        $key = hash("SHA256", $key, true);
        $cipher = "aes-128-gcm";
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);

        return openssl_encrypt($plaintext,$cipher, $key, null, $iv);
    }*/

    # === WARNING ===

    # Resulting cipher text has no integrity or authenticity added
    # and is not protected against padding oracle attacks.

    # --- DECRYPTION ---
    public static function decrypt_deprecated($ciphertext_base64, $key = '')
    {
        if (!$key) {
            $key = self::$key;
        }
        $key = hash("SHA256", $key, true);

        # create a random IV to use with CBC encoding
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        //$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

        $ciphertext_dec = base64_decode($ciphertext_base64);

        # retrieves the IV, iv_size should be created using mcrypt_get_iv_size()
        $iv_dec = substr($ciphertext_dec, 0, $iv_size);

        # retrieves the cipher text (everything except the $iv_size in the front)
        $ciphertext_dec = substr($ciphertext_dec, $iv_size);

        # may remove 00h valued characters from end of plain text
        $plaintext_dec = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);

        //echo  $plaintext_dec . "\n";
        return $plaintext_dec;
    }


    public static function password($password)
    {
        $options = [
            'cost' => 11
        ];
        return password_hash($password, PASSWORD_BCRYPT, $options);
    }

    public static function checkPassword($password, $stored_value)
    {
        if (!$stored_value) {
            return false;
        }
        // error_log(__FILE__ . __LINE__ . " \n stored_value=$stored_value;password={$password};");
        if (password_verify($password, $stored_value)) {
            return true;
        } else {
            return false;
        }
    }

    public static function genFormSecret($fields = '', $key = '')
    {
        return md5(uniqid(rand(), true));
        //must post in 1 day
        $key = $key ? $key : date('Y-m-d', strtotime("+1 week 2 days"));
        $md5 = md5(md5($fields) . $date);
        return $md5;
    }

    public static function checkFormSecret($fields, $secret, $key = '')
    {
        //must post in 1 day
        $arr_key = $key ? array($key) : array(date('Y-m-d', strtotime("+1 week 2 days")), date('Y-m-d:H', strtotime("+1 week 3 days")));
        foreach ($arr_key as $key) {
            if ($secret === self::genFormSecret($fields, $key)) {
                return true;
            }
        }
        return false;
    }

    public static function randomToken($length = 16)
    {
        if (!isset($length) || intval($length) <= 8) {
            $length = 16;
        }
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes($length));
        }
        if (function_exists('mcrypt_create_iv')) {
            return bin2hex(mcrypt_create_iv($length, MCRYPT_DEV_URANDOM));
        }
        if (function_exists('openssl_random_pseudo_bytes')) {
            return bin2hex(openssl_random_pseudo_bytes($length));
        }
    }

    /**
     * 生成rsa公钥及密钥
     *
     * @return array 公钥及密钥
     */
    public static function genRSAPublicPrivateKey($gen_rsa_id = 0)
    {
        //just gen keys ahead for performance
        if (class_exists('App\\Model\\GenRsa')) {
            $gen_rsa_id = $gen_rsa_id ? $gen_rsa_id : mt_rand(1, \App\Model\GenRsa::MAX_ID);
            $GenRsa = new \App\Model\GenRsa();
            $row_gen_rsa = $GenRsa->getByID($gen_rsa_id);

            //\Baogg\App::getLogger()->debug(__FILE__ . __LINE__ . " random id = {$gen_rsa_id}");
            if ($row_gen_rsa && $row_gen_rsa['private_key']) {
                return array('pub' => $row_gen_rsa['public_key'], 'priv' => $row_gen_rsa['private_key']);
            }
        }

        $config = array(
            "digest_alg" => "sha512",
            "private_key_bits" => 4096,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );

        // Create the private and public key
        $res = openssl_pkey_new($config);

        // Extract the private key from $res to $privKey
        openssl_pkey_export($res, $privKey);

        // Extract the public key from $res to $pubKey
        $pubKey = openssl_pkey_get_details($res);
        $pubKey = $pubKey["key"];

        return array('pub' => $pubKey, 'priv' => $privKey);
    }

    /**
     * 十进制数转换成其它进制
     * 可以转换成2-62任何进制
     *
     * @param integer $num
     * @param integer $to
     * @return string
     */
    public static function decTo($num, $to = 62)
    {
        if ($to == 10 || $to > 62 || $to < 2) {
            return $num;
        }
        $dict = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $ret = '';
        do {
            $ret = $dict[bcmod($num, $to)] . $ret;
            $num = bcdiv($num, $to);
        } while ($num > 0);
        return $ret;
    }

    /**
     * 其它进制数转换成十进制数
     * 适用2-62的任何进制
     *
     * @param string $num
     * @param integer $from
     * @return number
     */
    public static function decFrom($num, $from = 62)
    {
        if ($from == 10 || $from > 62 || $from < 2) {
            return $num;
        }
        $num = strval($num);
        $dict = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $len = strlen($num);
        $dec = 0;
        for ($i = 0; $i < $len; $i++) {
            $pos = strpos($dict, $num[$i]);
            if ($pos >= $from) {
                continue; // 如果出现非法字符，会忽略掉。比如16进制中出现w、x、y、z等
            }
            $dec = bcadd(bcmul(bcpow($from, $len - $i - 1), $pos), $dec);
        }
        return $dec;
    }


    public static function genBase62()
    {
        return array(
            0 => 0, 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 'A' => 10, 'B' => 11, 'C' => 12, 'D' => 13, 'E' => 14, 'F' => 15, 'G' => 16, 'H' => 17, 'I' => 18, 'J' => 19, 'K' => 20, 'L' => 21, 'M' => 22, 'N' => 23, 'O' => 24, 'P' => 25, 'Q' => 26, 'R' => 27, 'S' => 28, 'T' => 29, 'U' => 30, 'V' => 31, 'W' => 32, 'X' => 33, 'Y' => 34, 'Z' => 35, 'a' => 36, 'b' => 37, 'c' => 38, 'd' => 39, 'e' => 40, 'f' => 41, 'g' => 42, 'h' => 43, 'i' => 44, 'j' => 45, 'k' => 46, 'l' => 47, 'm' => 48, 'n' => 49, 'o' => 50, 'p' => 51, 'q' => 52, 'r' => 53, 's' => 54, 't' => 55, 'u' => 56, 'v' => 57, 'w' => 58, 'x' => 59, 'y' => 60, 'z' => 61,
        );

        /* $ret = array();
        for ($i = 0;$i<=9;$i++) {
            $ret[] = ''.$i;
        }
        for ($i=ord('A'),$last=ord('Z');$i<=$last;$i++) {
            $ret[] = chr($i);
        }
        for ($i=ord('a'),$last=ord('z');$i<=$last;$i++) {
            $ret[] = chr($i);
        }
        return array_flip($ret); */
    }


    /**
     * 模拟维吉尼亚密码加密
     *
     * @param string $text 需要加密的字符串
     * @param string $salt 加密盐
     *
     * @return string
     */
    public static function vigenereEncrypt($text = '', $salt = '')
    {
        $ret = "";

        $arr_base_62 = self::genBase62();
        $arr_text = str_split($text);
        $arr_salt = str_split($salt);


        //修正salt
        $arr_salt = array_filter($arr_salt, function ($value, $key) use ($arr_base_62) {
            return isset($arr_base_62[$value]);
        }, ARRAY_FILTER_USE_BOTH);
        $arr_salt = array_values($arr_salt);
        $salt = implode('', $arr_salt);



        $salt_len = count($arr_salt);
        $length = count($arr_text);


        if ($length == 0) {
            return '';
        }
        if ($salt_len == 0) {
            throw new \Exception('param error!');
        }

        if ($salt_len < $length) {
            while ($salt_len < $length) {
                $salt .= md5($salt);
                $salt_len = \strlen($salt);
            }
            $arr_salt = str_split($salt);
            $salt_len = count($arr_salt);
        }
        /*
            var_dump($arr_text);

        var_dump($arr_base_62);
         var_dump($arr_salt);

             */



        // iterate over each line in text
        for ($i = 0; $i < $length; $i++) {
            if (isset($arr_base_62[$arr_text[$i]])) {
                $enc_key = ($arr_base_62[$arr_text[$i]] + $arr_base_62[$arr_salt[$i]]) % 62;
                $ret .= '' . array_search($enc_key, $arr_base_62);
            } else {
                $ret .=  '' . $arr_text[$i];
            }
            /*
                var_dump($arr_text[$i]);
                var_dump($arr_base_62[$arr_text[$i]]);
                var_dump($enc_key);
                //var_dump($arr_base_62);
                var_dump(array_search($enc_key, $arr_base_62));
                var_dump($ret); */
        }

        // return the encrypted code
        return $ret;
    }

    /**
     * 模拟维吉尼亚密码解密
     *
     * @param string $text 需要解密的字符串
     * @param string $salt 加密盐
     *
     * @return string
     */
    public static function vigenereDecrypt($text = '', $salt = '')
    {
        // intialize variables
        $ret = "";

        $arr_base_62 = self::genBase62();
        $arr_text = str_split($text);
        $arr_salt = str_split($salt);


        //修正salt
        $arr_salt = array_filter($arr_salt, function ($value, $key) use ($arr_base_62) {
            return isset($arr_base_62[$value]);
        }, ARRAY_FILTER_USE_BOTH);
        $arr_salt = array_values($arr_salt);

        $salt = implode('', $arr_salt);



        $salt_len = count($arr_salt);
        $length = count($arr_text);


        if ($length == 0) {
            return '';
        }
        if ($salt_len == 0) {
            throw new \Exception('param error!');
        }

        if ($salt_len < $length) {
            while ($salt_len < $length) {
                $salt .= md5($salt);
                $salt_len = \strlen($salt);
            }
            $arr_salt = str_split($salt);
            $salt_len = count($arr_salt);
        }



        // iterate over each line in text
        for ($i = 0; $i < $length; $i++) {
            if (isset($arr_base_62[$text[$i]])) {
                $enc_key = ($arr_base_62[$text[$i]] + 62 - $arr_base_62[$salt[$i]]) % 62;
                $ret .= array_search($enc_key, $arr_base_62);
            } else {
                $ret .=  $text[$i];
            }
        }

        // return the decrypted text
        return $ret;
    }

    /**
     * Convert bigint to english word string
     *
     * @param string $bigint_id input bigint id
     *
     * @return void
     */
    public static function convertBigintToString($bigint_id = '')
    {
        if (!is_numeric($bigint_id)) {
            return $bigint_id;
        }

        //\Baogg\App::getLogger()->debug("big int id = {$bigint}");
        $arr_bigint_id = \str_split('' . $bigint_id);
        $bigint_id_sign = 9 - (array_sum($arr_bigint_id) % 10);

        $str_log_id = \Baogg\Encrypt::decTo('' . $bigint_id . $bigint_id_sign, 62);
        return \Baogg\Encrypt::vigenereEncrypt($str_log_id, self::ENC_SALT);
    }

    /**
     * convert english word string to big int
     *
     * @param string $bigint_id
     * @return void
     */
    public static function convertStringToBigint($str_bigint_id = '')
    {
        $dec_code = \Baogg\Encrypt::vigenereDecrypt($str_bigint_id, self::ENC_SALT);

        $id_full = \Baogg\Encrypt::decFrom($dec_code, 62);
        if (array_sum(str_split($id_full)) % 10 != 9) {
            return '-1';
        }
        $login_id = substr('' . $id_full, 0, -1);
    }

    public static function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function base64url_decode($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), 4 - ((strlen($data) % 4) ?: 4), '=', STR_PAD_RIGHT));
    }
}
