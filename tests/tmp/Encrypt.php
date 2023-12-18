<?php


$str = 'abc789321';
$salt = 'd291e0f5-4d71-11eb-bfbc-d017c287c1a5';
$str_enc =  vigenere_encrypt($str, $salt);
$str_dec = vigenere_decrypt($str_enc, $salt);

var_dump($str_enc);
var_dump($str_dec);
exit;

function genBase62()
{
    return array(
        0 => 0, 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 'A' => 10, 'B' => 11, 'C' => 12, 'D' => 13, 'E' => 14, 'F' => 15, 'G' => 16, 'H' => 17, 'I' => 18, 'J' => 19, 'K' => 20, 'L' => 21, 'M' => 22, 'N' => 23, 'O' => 24, 'P' => 25, 'Q' => 26, 'R' => 27, 'S' => 28, 'T' => 29, 'U' => 30, 'V' => 31, 'W' => 32, 'X' => 33, 'Y' => 34, 'Z' => 35, 'a' => 36, 'b' => 37, 'c' => 38, 'd' => 39, 'e' => 40, 'f' => 41, 'g' => 42, 'h' => 43, 'i' => 44, 'j' => 45, 'k' => 46, 'l' => 47, 'm' => 48, 'n' => 49, 'o' => 50, 'p' => 51, 'q' => 52, 'r' => 53, 's' => 54, 't' => 55, 'u' => 56, 'v' => 57, 'w' => 58, 'x' => 59, 'y' => 60, 'z' => 61, );
    
    $ret = array();
    for ($i = 0;$i<=9;$i++) {
        $ret[] = ''.$i;
    }
    for ($i=ord('A'),$last=ord('Z');$i<=$last;$i++) {
        $ret[] = chr($i);
    }
    for ($i=ord('a'),$last=ord('z');$i<=$last;$i++) {
        $ret[] = chr($i);
    }
    return array_flip($ret);
}


// function to encrypt the text given
function vigenere_encrypt($text = '', $salt = '')
{
    $ret = "";

    $arr_base_62 = genBase62();
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
    

    if ($length ==0) {
        return '';
    }
    if ($salt_len ==0) {
        throw new Exception('param error!');
    }

    if ($salt_len<$length) {
        while ($salt_len<$length) {
            $salt .= md5($salt);
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
            $ret .= ''.array_search($enc_key, $arr_base_62);
        } else {
            $ret .=  ''.$arr_text[$i];
        }
        
        var_dump($arr_text[$i]);
        var_dump($arr_base_62[$arr_text[$i]]);
        var_dump($enc_key);
        //var_dump($arr_base_62);
        var_dump(array_search($enc_key, $arr_base_62));
        var_dump($ret);
    }
    
    // return the encrypted code
    return $ret;
}

// function to decrypt the text given
function vigenere_decrypt($text = '', $salt = '')
{
    // intialize variables
    $ret = "";

    $arr_base_62 = genBase62();
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
    

    if ($length ==0) {
        return '';
    }
    if ($salt_len ==0) {
        throw new Exception('param error!');
    }

    if ($salt_len<$length) {
        while ($salt_len<$length) {
            $salt .= md5($salt);
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




//coder:Jiangbin
//date:2011-11-25
/*****************************************
    凯撒密码表
        ABCDEFGHIJKLMNOPQRSTUVWXYZ
        BCDEFGHIJKLMNOPQRSTUVWXYZA
        CDEFGHIJKLMNOPQRSTUVWXYZAB
        DEFGHIJKLMNOPQRSTUVWXYZABC
        EFGHIJKLMNOPQRSTUVWXYZABCD
        FGHIJKLMNOPQRSTUVWXYZABCDE
        GHIJKLMNOPQRSTUVWXYZABCDEF
        HIJKLMNOPQRSTUVWXYZABCDEFG
        IJKLMNOPQRSTUVWXYZABCDEFGH
        JKLMNOPQRSTUVWXYZABCDEFGHI
        KLMNOPQRSTUVWXYZABCDEFGHIJ
        LMNOPQRSTUVWXYZABCDEFGHIJK
        MNOPQRSTUVWXYZABCDEFGHIJKL
        NOPQRSTUVWXYZABCDEFGHIJKLM
        OPQRSTUVWXYZABCDEFGHIJKLMN
        PQRSTUVWXYZABCDEFGHIJKLMNO
        QRSTUVWXYZABCDEFGHIJKLMNOP
        RSTUVWXYZABCDEFGHIJKLMNOPQ
        STUVWXYZABCDEFGHIJKLMNOPQR
        TUVWXYZABCDEFGHIJKLMNOPQRS
        UVWXYZABCDEFGHIJKLMNOPQRST
        VWXYZABCDEFGHIJKLMNOPQRSTU
        WXYZABCDEFGHIJKLMNOPQRSTUV
        XYZABCDEFGHIJKLMNOPQRSTUVW
        YZABCDEFGHIJKLMNOPQRSTUVWX
        ZABCDEFGHIJKLMNOPQRSTUVWXY
******************************************/
echo '<pre>';
$mkey = 'Jiangbin';     //密钥
$mstr = 'I known what love is because of you';	//原文
$nkey = strtoupper($mkey);			//转成大写
$nstr = strtoupper($mstr);				//转成大写（明文）
$nstr = str_replace(chr(32), '', $nstr);  	//去空格
$key = str_split($nkey);        //密钥转换成数组
$str = str_split($nstr);        //明文转换成数组
$keylen = count($key);          //获取密钥长度
$strlen = count($str);          //获取去空格后的明文长度
$arr = array();
for ($i = 0; $i < $strlen; $i++) {
    $arr[$i] = chr(((ord($str[$i])-65) + (ord($key[$i%$keylen])-65))%26+65);
}
echo '密钥:'.$mkey."<br />";
echo '原文:'.$mstr."<br />";
echo '明文:'.$nstr."<br />";
echo '密文:'.implode("", $arr);
