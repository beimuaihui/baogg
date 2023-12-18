<?php

/**
 * beimuaihui System
 * Copyright(c) 2011-2020 beimuaihui.
 * @license    http://www.gnu.org/licenses/gpl.html     This software Under GPL V3 License
 * beimuaihui@gmail.com
 * https://github.com/beimuaihui
 * $Id: File.php 240 2011-06-13 13:02:06Z beimuaihui $
 */

namespace Baogg\File;

class Html extends \Baogg\File
{

    const CODE_ERR_LACK_FIELD = -541;
    const CODE_ERR_EXISTS_DUPLICATE = -542;
    const CODE_ERR_DATA = -540;
    const CODE_ERR_NO_DATA = -543;
    const CODE_UNAUTHORIZED_USER = -401;

    const CODE_OK = 200;
    const CODE_EMPTY = 0;
    const CODE_ERR_SQL = -543;

    const CODE_CREATED = 201;
    const CODE_UPDATED = 231;
    const CODE_DELETED = 204;
    const CODE_SAVE_LIST = 241;
    const CODE_NOT_FOUND = -404;
    const CODE_UNPROCESSABLE = -422;
    const CODE_SESSION_TIMEOUT = -419;



    static function genFile($dir, $filename, $content = '')
    {
        self::mkdir(\BAOGG_UPLOAD_DIR . $dir);
        $filename = self::fixFileName($filename);

        file_put_contents(\BAOGG_UPLOAD_DIR . $dir . $filename, $content);

        return \BAOGG_FILE_URL . $dir . $filename;
    }
    static function genFiles($dir, $filenames = array(), $urls = array())
    {
        self::mkdir(\BAOGG_UPLOAD_DIR . $dir);
        $contents = self::multiGet($urls);
        foreach ($filenames as $k => $filename) {
            self::genFile($dir, $filename, $contents[$k]);
        }

        return $filenames;
    }
    static function multiGet($urls = array(), $fixUrlName = true, $cookie = '')
    {
        $res = array();

        $urls = is_array($urls) ? $urls : array($urls);
        /*
		foreach($urls as $i => $url)
		{
			$res[$i] = file_get_contents($url);			
		}*/



        // Create get requests for each URL
        $mh = curl_multi_init();
        $ch = array();
        foreach ($urls as $i => $url) {
            $ch[$i] = curl_init();
            curl_setopt($ch[$i], CURLOPT_URL, $url);
            curl_setopt($ch[$i], CURLOPT_HEADER, 1);
            curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch[$i], CURLOPT_COOKIE, $cookie); //added cookie
            curl_setopt($ch[$i], CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; MSIE 6.0; Windows NT 5.0)");
            curl_setopt($ch[$i], CURLOPT_REFERER, "http://www.baidu.com");

            curl_setopt($ch[$i], CURLOPT_POST, true);
            curl_setopt($ch[$i], CURLOPT_POSTFIELDS, $posts);
            $ip = long2ip(mt_rand(0, 65537) * mt_rand(0, 65535));
            $ip2 = long2ip(mt_rand(0, 65537) * mt_rand(0, 65535));
            curl_setopt($ch[$i], CURLOPT_HTTPHEADER, array("REMOTE_ADDR: $ip", "HTTP_X_FORWARDED_FOR: $ip", 'X-FORWARDED-FOR:' . $ip, 'CLIENT-IP:' . $ip));
            //curl_setopt($ch[$i], CURLOPT_INTERFACE, $ip2);
            //curl_setopt($ch[$i], CURLOPT_HTTPPROXYTUNNEL, 0);
            //curl_setopt($ch[$i], CURLOPT_PROXY , $ip2.":80"); 

            curl_multi_add_handle($mh, $ch[$i]);
        }

        // Start performing the request
        $active = null;
        do {
            $execReturnValue = curl_multi_exec($mh, $active);
        } while ($execReturnValue == CURLM_CALL_MULTI_PERFORM);
        // Loop and continue processing the request
        while ($active && $execReturnValue == CURLM_OK) {
            // Wait forever for network
            if (curl_multi_select($mh) == -1) {
                usleep(100);
            }
            /*$numberReady = curl_multi_select($mh);
			if ($numberReady != -1) {*/
            // Pull in any new data, or at least handle timeouts
            do {
                $execReturnValue = curl_multi_exec($mh, $active);
            } while ($execReturnValue == CURLM_CALL_MULTI_PERFORM);
            //}
        }

        // Check for any errors
        if ($execReturnValue != CURLM_OK) {
            throw new \Exception("Curl multi read error $execReturnValue\n");
            //trigger_error("Curl multi read error $execReturnValue\n", E_USER_WARNING);
        }

        // Extract the content
        foreach ($urls as $i => $url) {
            // Check for errors
            $curlError = curl_error($ch[$i]);
            if ($curlError == "") {
                $res[$i] = curl_multi_getcontent($ch[$i]);
            } else {
                $res[$i] = ""; //
                //print "Curl error on handle $i: $curlError\n";
            }
            // Remove and close the handle
            curl_multi_remove_handle($mh, $ch[$i]);
            curl_close($ch[$i]);
        }
        // Clean up the curl_multi handle
        curl_multi_close($mh);


        //replace url like href="../test.css" and so on
        if ($fixUrlName) {
            foreach ((array)$res as $k => $v) {
                $baseUrl = $urls[$k];
                $res[$k] = preg_replace_callback("/(\shref=[\"'])([\w\.\/]+)([\"'][\s\/>])/mi", create_function('$matches', 'return $matches[1].Baogg_File::fixUrlName($matches[2], "' . $baseUrl . '").$matches[3];'), $res[$k]);
                $res[$k] = preg_replace_callback("/(\ssrc=[\"'])([\w\.\/]+)([\"'][\s\/>])/mi", create_function('$matches', 'return $matches[1].Baogg_File::fixUrlName($matches[2], "' . $baseUrl . '").$matches[3];'), $res[$k]);
            }
        }
        // Print the response data
        return $res;
    }

    public static function random_user_agent()
    {
        $browser_freq = array(
            "Internet Explorer" => 11.8,
            "Firefox" => 28.2,
            "Chrome" => 52.9,
            "Safari" => 3.9,
            "Opera" => 1.8
        );

        $browser_strings = array(
            "Internet Explorer" => array(
                "Mozilla/5.0 (compatible; MSIE 10.6; Windows NT 6.1; Trident/5.0; InfoPath.2; SLCC1; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; .NET CLR 2.0.50727) 3gpp-gba UNTRUSTED/1.0",
                "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)",
                "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)",
                "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/5.0)",
                "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/4.0; InfoPath.2; SV1; .NET CLR 2.0.50727; WOW64)",
                "Mozilla/5.0 (compatible; MSIE 10.0; Macintosh; Intel Mac OS X 10_7_3; Trident/6.0)",

            ),
            "Firefox" => array(
                "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:25.0) Gecko/20100101 Firefox/25.0",
                "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:25.0) Gecko/20100101 Firefox/25.0",
                "Mozilla/5.0 (Windows NT 6.0; WOW64; rv:24.0) Gecko/20100101 Firefox/24.0",
                "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:24.0) Gecko/20100101 Firefox/24.0",
                "Mozilla/5.0 (Windows NT 6.2; rv:22.0) Gecko/20130405 Firefox/23.0",
                "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:23.0) Gecko/20130406 Firefox/23.0",

            ),
            "Chrome" => array(
                "Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/30.0.1599.17 Safari/537.36",
                "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.62 Safari/537.36",
                "Mozilla/5.0 (X11; CrOS i686 4319.74.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.57 Safari/537.36",
                "Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.2 Safari/537.36",
                "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1468.0 Safari/537.36",
                "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1467.0 Safari/537.36",
            ),
            "Safari" => array(
                "Mozilla/5.0 (iPad; CPU OS 6_0 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10A5355d Safari/8536.25",
                "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/537.13+ (KHTML, like Gecko) Version/5.1.7 Safari/534.57.2",
                "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_3) AppleWebKit/534.55.3 (KHTML, like Gecko) Version/5.1.3 Safari/534.53.10",
                "Mozilla/5.0 (iPad; CPU OS 5_1 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko ) Version/5.1 Mobile/9B176 Safari/7534.48.3",
                "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_8; de-at) AppleWebKit/533.21.1 (KHTML, like Gecko) Version/5.0.5 Safari/533.21.1",
                "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_7; da-dk) AppleWebKit/533.21.1 (KHTML, like Gecko) Version/5.0.5 Safari/533.21.1",
            ),
            "Opera" => array(
                "Opera/9.80 (Windows NT 6.0) Presto/2.12.388 Version/12.14",
                "Mozilla/5.0 (Windows NT 6.0; rv:2.0) Gecko/20100101 Firefox/4.0 Opera 12.14",
                "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.0) Opera 12.14",
                "Opera/12.80 (Windows NT 5.1; U; en) Presto/2.10.289 Version/12.02",
                "Opera/9.80 (Windows NT 6.1; U; es-ES) Presto/2.9.181 Version/12.00",
                "Opera/9.80 (Windows NT 5.1; U; zh-sg) Presto/2.9.181 Version/12.00",
            )
        );

        $max = 0;
        $rcount = 0;
        $browser_type = '';

        foreach ($browser_freq as $k => $v) $max += $v;
        $roll = rand(0, $max);
        foreach ($browser_freq as $k => $v) if (($roll <= ($rcount += $v)) and (!$browser_type)) $browser_type = $k;
        $user_agent_array = $browser_strings[$browser_type];
        shuffle($user_agent_array);
        $user_agent = $user_agent_array[0];
        return $user_agent;
    }


    public static function MakeActionLink($text, $href, $aAttribute = array(), $img = '', $aImgAttribute = array())
    {
        if (!$text) {
            $img = addslashes($img);
            $sImgAttribute = '';
            foreach ((array) $aImgAttribute as $k => $v) {
                $v = addcslashes($v, "'\\");
                $sImgAttribute .= " $k='{$v}' ";
            }
            $text = "<img src='{BAOGG_THEME}/images/{$img}'  {$sImgAttribute}>";
        }
        $sAttribute = '';
        foreach ((array) $aAttribute as $k => $v) {
            $v = addcslashes($v, "'\\");
            $sAttribute .= " $k='{$v}' ";
        }
        return "<a href='{$href}' {$sAttribute}>$text</a>";
    }
    public static function MakeEditLink($href = '#')
    {
        global $multi;
        return self::MakeActionLink($multi->g_edit, $href, '', 'edit.gif');
    }

    public static function Tidy($html, $tidy_config = '')
    {
        $config = array(
            'clean' => false,
            'output-html' => false,
            'show-body-only'    => true
        );
        /*$config = array(
			'show-body-only' => false,
			'clean' => true,
			'char-encoding' => 'utf8',
			'add-xml-decl' => true,
			'add-xml-space' => true,
			'output-html' => false,
			'output-xml' => false,
			'output-xhtml' => true,
			'numeric-entities' => false,
			'ascii-chars' => false,
			'doctype' => 'strict',
			'bare' => true,
			'fix-uri' => true,
			'indent' => true,
			'indent-spaces' => 4,
			'tab-size' => 4,
			'wrap-attributes' => true,
			'wrap' => 0,
			'indent-attributes' => true,
			'join-classes' => false,
			'join-styles' => false,
			'enclose-block-text' => true,
			'fix-bad-comments' => true,
			'fix-backslash' => true,
			'replace-color' => false,
			'wrap-asp' => false,
			'wrap-jste' => false,
			'wrap-php' => false,
			'write-back' => true,
			'drop-proprietary-attributes' => false,
			'hide-comments' => false,
			'hide-endtags' => false,
			'literal-attributes' => false,
			'drop-empty-paras' => true,
			'enclose-text' => true,
			'quote-ampersand' => true,
			'quote-marks' => false,
			'quote-nbsp' => true,
			'vertical-space' => true,
			'wrap-script-literals' => false,
			'tidy-mark' => true,
			'merge-divs' => false,
			'repeated-attributes' => 'keep-last',
			'break-before-br' => true,
		);*/

        if ($tidy_config == '') {
            $tidy_config = $config;
        }

        $tidy = new \tidy;
        $tidy->parseString($html, $tidy_config, 'utf8');
        $tidy->cleanRepair();
        //remove body tag
        return trim($tidy->value);
    }

    static function getJsonByCurl($url = '')
    {
        $headerArray = array("Content-type:application/json;", "Accept:application/json");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
        $output = curl_exec($ch);
        //关闭URL请求
        if ($output === FALSE) {
            echo "CURL Error:" . curl_error($ch);
        }
        curl_close($ch);
        $output = json_decode($output, true);
        return $output;
    }

    static function getByPostJsonCurl($url = '', $param = [])
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($param),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        //echo $response;
        //var_dump($response);
        // $output = json_decode($response, true);
        return $response;
    }
}
