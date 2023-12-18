<?php
/**
 * beimuaihui System
 * Copyright(c) 2011-2020 beimuaihui.
 * @license    http://www.gnu.org/licenses/gpl.html     This software Under GPL V3 License
 * beimuaihui@gmail.com
 * https://github.com/beimuaihui
 * $Id: Date.php 239 2011-06-13 04:08:13Z beimuaihui $
 */

namespace Baogg;


/**
 * App
 *
 * This is the primary class with which you instantiate,
 * configure, and run a Slim Framework application.
 * The \Baogg\App class also accepts Slim Framework middleware.
 */
class Waf
{

    public static function  run(){
        /*
        检测请求方式，除了get和post之外拦截下来并写日志。
        */
        /*if($_SERVER['REQUEST_METHOD'] != 'POST' && $_SERVER['REQUEST_METHOD'] != 'GET' && $_SERVER['REQUEST_METHOD'] != 'OPTIONS'){
            self::write_attack_log("method");
        }*/

        $url = isset($_SERVER['REQUEST_URI'])?$_SERVER['REQUEST_URI']:''; //获取uri来进行检测

        $data = file_get_contents('php://input'); //获取post的data，无论是否是mutipart

        $headers = self::get_all_headers(); //获取header

        self::filter_attack_keyword(self::filter_invisible(urldecode(self::filter_0x25($url)))); //对URL进行检测，出现问题则拦截并记录
        self::filter_attack_keyword(self::filter_invisible(urldecode(self::filter_0x25($data)))); //对POST的内容进行检测，出现问题拦截并记录

        /*
        检测过了则对输入进行简单过滤
        */
        foreach ($_GET as $key => $value) {
            $_GET[$key] = self::filter_dangerous_words($value);
        }
        foreach ($_POST as $key => $value) {
            $_POST[$key] = self::filter_dangerous_words($value);
        }
        foreach ($headers as $key => $value) {
            self::filter_attack_keyword(self::filter_invisible(urldecode(self::filter_0x25($value)))); //对http请求头进行检测，出现问题拦截并记录
            $_SERVER[$key] = self::filter_dangerous_words($value); //简单过滤
        }

        $Webscan = new Webscan();
        $Webscan->check();
    }
    /*
    获取http请求头并写入数组
    */
    public static function  get_all_headers() {
        $headers = array();

        foreach($_SERVER as $key => $value) {
            if(substr($key, 0, 5) === 'HTTP_') {
                $headers[$key] = $value;
            }
        }

        return $headers;
    }


    /*
    检测不可见字符造成的截断和绕过效果，注意网站请求带中文需要简单修改
    */
    public static function  filter_invisible($str){
        for($i=0;$i<strlen($str);$i++){
            $ascii = ord($str[$i]);
            if($ascii>126 || $ascii < 32){ //有中文这里要修改
                if(false && !in_array($ascii, array(9,10,13))){  //baogg 中文搜索"四级警长" 报错
                    self::write_attack_log("interrupt");
                }else{
                    $str = str_replace($ascii, " ", $str);
                }
            }
        }
        $str = str_replace(array("`","|",";",","), " ", $str);
        return $str;
    }

    /*
    检测网站程序存在二次编码绕过漏洞造成的%25绕过，此处是循环将%25替换成%，直至不存在%25
    */
    public static function  filter_0x25($str){
        if(strpos($str,"%25") !== false){
            $str = str_replace("%25", "%", $str);
            return self::filter_0x25($str);
        }else{
            return $str;
        }
    }

    /*
    攻击关键字检测，此处由于之前将特殊字符替换成空格，即使存在绕过特性也绕不过正则的\b
    */
    public static function  filter_attack_keyword($str){
        if(preg_match("/select\b|insert\b|update\b|drop\b|delete\b|dumpfile\b|outfile\b|load_file|rename\b|floor\(|extractvalue|updatexml|name_const|multipoint\(|union\b|table\b|from\b|ascii\b|hex\b|unhex\b/i", $str)){
            // \error_log(__FILE__.__LINE__." \n str = ". $str);
            // self::write_attack_log("sqli");
        }

        //此处文件包含的检测我真的不会写了，求高人指点。。。
        if(substr_count($str,$_SERVER['PHP_SELF']) < 2){
            $tmp = str_replace($_SERVER['PHP_SELF'], "", $str);
            if(preg_match("/\.\.|.*\.php[35]{0,1}/i", $tmp)){
                self::write_attack_log("LFI/LFR");;
            }
        }else{
            self::write_attack_log("LFI/LFR");
        }
        if(preg_match("/base64_decode|eval\(|assert\(/i", $str)){
            self::write_attack_log("EXEC");
        }
        if(preg_match("/flag/i", $str)){
            self::write_attack_log("GETFLAG");
        }

    }

    /*
    简单将易出现问题的字符替换成中文
    */
    public static function  filter_dangerous_words($str){

        if($_POST && isset($_POST['allow_html']) && $_POST['allow_html']){
            $config = \HTMLPurifier_Config::createDefault();
            $purifier = new \HTMLPurifier($config);
            $str = $purifier->purify($str);
            //    echo __FILE__.__LINE__.'<pre>';var_dump($str);
            return $str;
        }

        $str = str_replace("'", "‘", $str);
        $str = str_replace("\"", "“", $str);
        $str = str_replace("<", "《", $str);
        $str = str_replace(">", "》", $str);
        return $str;
    }

    /*
    获取http的请求包，意义在于获取别人的攻击payload
    */
    public static function  get_http_raw() {
        $raw = '';

        $raw .= $_SERVER['REQUEST_METHOD'].' '.$_SERVER['REQUEST_URI'].' '.$_SERVER['SERVER_PROTOCOL']."\r\n";

        foreach($_SERVER as $key => $value) {
            if(substr($key, 0, 5) === 'HTTP_') {
                $key = substr($key, 5);
                $key = str_replace('_', '-', $key);
                $raw .= $key.': '.$value."\r\n";
            }
        }
        $raw .= "\r\n";
        $raw .= file_get_contents('php://input');
        return $raw;
    }

    /*
    这里拦截并记录攻击payload
    */
    public static function  write_attack_log($alert){
        $data = date("Y/m/d H:i:s")." -- [".$alert."]"."\r\n".self::get_http_raw()."\r\n\r\n";
        error_log($data);
        if($alert == 'GETFLAG'){
            echo "HCTF{aaaa}"; //如果请求带有flag关键字，显示假的flag。（2333333）
        }else{
            sleep(15); //拦截前延时15秒
        }
        exit(0);
    }
}


class Webscan {
    private $webscan_switch; //拦截开关(1为开启，0关闭)
    private $webscan_white_directory; //后台白名单
    private $webscan_white_url; //url白名单
    private $webscan_get;
    private $webscan_post;
    private $webscan_cookie;
    private $webscan_referer;

    public function __construct($webscan_switch=1, $webscan_white_directory='', $webscan_white_url=array(), $webscan_get=1, $webscan_post=1, $webscan_cookie=1, $webscan_referer=1) {
        $this->webscan_switch = $webscan_switch;
        $this->webscan_white_directory = $webscan_white_directory;
        $this->webscan_white_url = $webscan_white_url;
        $this->webscan_get = $webscan_get;
        $this->webscan_post = $webscan_post;
        $this->webscan_cookie = $webscan_cookie;
        $this->webscan_referer = $webscan_referer;
    }

    // 参数拆分
    private function webscan_arr_foreach($arr) {
        static $str;
        static $keystr;
        if (!is_array($arr)) {
            return $arr;
        }
        foreach ($arr as $key => $val ) {
            $keystr=$keystr.$key;
            if (is_array($val)) {
                $this->webscan_arr_foreach($val);
            } else {
                $str[] = $val.$keystr;
            }
        }
        return implode($str);
    }

    // 攻击检查拦截
    private function webscan_StopAttack($StrFiltKey, $StrFiltValue, $ArrFiltReq, $method) {
        $StrFiltValue = $this->webscan_arr_foreach($StrFiltValue);
        if (preg_match("/".$ArrFiltReq."/is", $StrFiltValue) == 1 || preg_match("/".$ArrFiltReq."/is", $StrFiltKey) == 1){
            return true;
        } else {
            return false;
        }

    }

    // 拦截目录白名单
    private function webscan_white($webscan_white_name, $webscan_white_url=array()) {
        $url_path=$_SERVER['SCRIPT_NAME'];
        $url_var= isset($_SERVER['QUERY_STRING'])?$_SERVER['QUERY_STRING']:'';
        if (preg_match("/".$webscan_white_name."/is",$url_path) == 1 && !empty($webscan_white_name)) {
            return false;
        }
        foreach ($webscan_white_url as $key => $value) {
            if(!empty($url_var) && !empty($value)){
                if (stristr($url_path, $key) && stristr($url_var, $value)) {
                    return false;
                }
            }
            elseif (empty($url_var) && empty($value)) {
                if (stristr($url_path,$key)) {
                    return false;
                }
            }

        }
        return true;
    }

    // 检测
    public function check() {
        // get拦截规则
        $getfilter = "\\<.+javascript:window\\[.{1}\\\\x|<.*=(&#\\d+?;?)+?>|<.*(data|src)=data:text\\/html.*>|\\b(alert\\(|confirm\\(|expression\\(|prompt\\(|benchmark\s*?\(.*\)|sleep\s*?\(.*\)|\\b(group_)?concat[\\s\\/\\*]*?\\([^\\)]+?\\)|\bcase[\s\/\*]*?when[\s\/\*]*?\([^\)]+?\)|load_file\s*?\\()|<[a-z]+?\\b[^>]*?\\bon([a-z]{4,})\s*?=|^\\+\\/v(8|9)|\\b(and|or)\\b\\s*?([\\(\\)'\"\\d]+?=[\\(\\)'\"\\d]+?|[\\(\\)'\"a-zA-Z]+?=[\\(\\)'\"a-zA-Z]+?|>|<|\s+?[\\w]+?\\s+?\\bin\\b\\s*?\(|\\blike\\b\\s+?[\"'])|\\/\\*.*\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT\s*(\(.+\)\s*|@{1,2}.+?\s*|\s+?.+?|(`|'|\").*?(`|'|\")\s*)|UPDATE\s*(\(.+\)\s*|@{1,2}.+?\s*|\s+?.+?|(`|'|\").*?(`|'|\")\s*)SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE)@{0,2}(\\(.+\\)|\\s+?.+?\\s+?|(`|'|\").*?(`|'|\"))FROM(\\(.+\\)|\\s+?.+?|(`|'|\").*?(`|'|\"))|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
        // post拦截规则
        $postfilter = "<.*=(&#\\d+?;?)+?>|<.*data=data:text\\/html.*>|\\b(alert\\(|confirm\\(|expression\\(|prompt\\(|benchmark\s*?\(.*\)|sleep\s*?\(.*\)|\\b(group_)?concat[\\s\\/\\*]*?\\([^\\)]+?\\)|\bcase[\s\/\*]*?when[\s\/\*]*?\([^\)]+?\)|load_file\s*?\\()|<[^>]*?\\b(onerror|onmousemove|onload|onclick|onmouseover)\\b|\\b(and|or)\\b\\s*?([\\(\\)'\"\\d]+?=[\\(\\)'\"\\d]+?|[\\(\\)'\"a-zA-Z]+?=[\\(\\)'\"a-zA-Z]+?|>|<|\s+?[\\w]+?\\s+?\\bin\\b\\s*?\(|\\blike\\b\\s+?[\"'])|\\/\\*.*\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT\s*(\(.+\)\s*|@{1,2}.+?\s*|\s+?.+?|(`|'|\").*?(`|'|\")\s*)|UPDATE\s*(\(.+\)\s*|@{1,2}.+?\s*|\s+?.+?|(`|'|\").*?(`|'|\")\s*)SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE)(\\(.+\\)|\\s+?.+?\\s+?|(`|'|\").*?(`|'|\"))FROM(\\(.+\\)|\\s+?.+?|(`|'|\").*?(`|'|\"))|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
        // cookie拦截规则
        $cookiefilter = "benchmark\s*?\(.*\)|sleep\s*?\(.*\)|load_file\s*?\\(|\\b(and|or)\\b\\s*?([\\(\\)'\"\\d]+?=[\\(\\)'\"\\d]+?|[\\(\\)'\"a-zA-Z]+?=[\\(\\)'\"a-zA-Z]+?|>|<|\s+?[\\w]+?\\s+?\\bin\\b\\s*?\(|\\blike\\b\\s+?[\"'])|\\/\\*.*\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT\s*(\(.+\)\s*|@{1,2}.+?\s*|\s+?.+?|(`|'|\").*?(`|'|\")\s*)|UPDATE\s*(\(.+\)\s*|@{1,2}.+?\s*|\s+?.+?|(`|'|\").*?(`|'|\")\s*)SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE)@{0,2}(\\(.+\\)|\\s+?.+?\\s+?|(`|'|\").*?(`|'|\"))FROM(\\(.+\\)|\\s+?.+?|(`|'|\").*?(`|'|\"))|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
        // referer获取
        $referer = empty($_SERVER['HTTP_REFERER']) ? array() : array('HTTP_REFERER' => $_SERVER['HTTP_REFERER']);

        if ($this->webscan_switch && $this->webscan_white($this->webscan_white_directory, $this->webscan_white_url)) {
            if ($this->webscan_get) {
                foreach($_GET as $key=>$value) {
                    if ($this->webscan_StopAttack($key, $value, $getfilter, "GET")) return true;
                }
            }
            if ($this->webscan_post) {
                foreach($_POST as $key=>$value) {
                    if ($this->webscan_StopAttack($key, $value, $postfilter, "POST")) return true;
                }
            }
            if ($this->webscan_cookie) {
                foreach($_COOKIE as $key=>$value) {
                    if ($this->webscan_StopAttack($key, $value, $cookiefilter, "COOKIE")) return true;
                }
            }
            if ($this->webscan_referer) {
                foreach($referer as $key=>$value) {
                    if ($this->webscan_StopAttack($key, $value, $postfilter, "REFERRER")) return true;
                }
            }
            return false;
        }
    }
}


/*云体检通用漏洞防护补丁v1.1
更新时间：2013-05-25
功能说明：防护XSS,SQL,代码执行，文件包含等多种高危漏洞
*/

$url_arr=array(
    'xss'=>"\\=\\+\\/v(?:8|9|\\+|\\/)|\\%0acontent\\-(?:id|location|type|transfer\\-encoding)",
);

$args_arr=array(
    'xss'=>"[\\'\\\"\\;\\*\\<\\>].*\\bon[a-zA-Z]{3,15}[\\s\\r\\n\\v\\f]*\\=|\\b(?:expression)\\(|\\<script[\\s\\\\\\/]|\\<\\!\\[cdata\\[|\\b(?:eval|alert|prompt|msgbox)\\s*\\(|url\\((?:\\#|data|javascript)",

    'sql'=>"[^\\{\\s]{1}(\\s|\\b)+(?:select\\b|update\\b|insert(?:(\\/\\*.*?\\*\\/)|(\\s)|(\\+))+into\\b).+?(?:from\\b|set\\b)|[^\\{\\s]{1}(\\s|\\b)+(?:create|delete|drop|truncate|rename|desc)(?:(\\/\\*.*?\\*\\/)|(\\s)|(\\+))+(?:table\\b|from\\b|database\\b)|into(?:(\\/\\*.*?\\*\\/)|\\s|\\+)+(?:dump|out)file\\b|\\bsleep\\([\\s]*[\\d]+[\\s]*\\)|benchmark\\(([^\\,]*)\\,([^\\,]*)\\)|(?:declare|set|select)\\b.*@|union\\b.*(?:select|all)\\b|(?:select|update|insert|create|delete|drop|grant|truncate|rename|exec|desc|from|table|database|set|where)\\b.*(charset|ascii|bin|char|uncompress|concat|concat_ws|conv|export_set|hex|instr|left|load_file|locate|mid|sub|substring|oct|reverse|right|unhex)\\(|(?:master\\.\\.sysdatabases|msysaccessobjects|msysqueries|sysmodules|mysql\\.db|sys\\.database_name|information_schema\\.|sysobjects|sp_makewebtask|xp_cmdshell|sp_oamethod|sp_addextendedproc|sp_oacreate|xp_regread|sys\\.dbms_export_extension)",

    'other'=>"\\.\\.[\\\\\\/].*\\%00([^0-9a-fA-F]|$)|%00[\\'\\\"\\.]");

$referer=empty($_SERVER['HTTP_REFERER']) ? array() : array($_SERVER['HTTP_REFERER']);
$query_string=empty($_SERVER["QUERY_STRING"]) ? array() : array($_SERVER["QUERY_STRING"]);

check_data($query_string,$url_arr);
check_data($_GET,$args_arr);
check_data($_POST,$args_arr);
check_data($_COOKIE,$args_arr);
check_data($referer,$args_arr);
function W_log($log)
{
    /*$logpath=$_SERVER["DOCUMENT_ROOT"]."/log.txt";
    $log_f=fopen($logpath,"a+");
    fputs($log_f,$log."\r\n");
    fclose($log_f);*/
    Waf::write_attack_log($log);
}
function check_data($arr,$v) {
    foreach($arr as $key=>$value)
    {
        if(!is_array($key))
        { check($key,$v);}
        else
        { check_data($key,$v);}

        if(!is_array($value))
        { check($value,$v);}
        else
        { check_data($value,$v);}
    }
}
function check($str,$v)
{
    foreach($v as $key=>$value)
    {
        if (preg_match("/".$value."/is",$str)==1||preg_match("/".$value."/is",urlencode($str))==1)
        {
            Waf::write_attack_log("<br>IP: ".$_SERVER["REMOTE_ADDR"]."<br>时间: ".strftime("%Y-%m-%d %H:%M:%S")."<br>页面:".$_SERVER["PHP_SELF"]."<br>提交方式: ".$_SERVER["REQUEST_METHOD"]."<br>提交数据: ".$str);
            print "您的提交带有不合法参数,谢谢合作";
            exit();
        }
    }
}