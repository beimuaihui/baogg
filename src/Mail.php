<?php

namespace Baogg;
class Mail {

    /**
     * decode the subject of chinese
     *
     * @param string $subject            
     * @return sting
     */
    public static function subjectDecode ($subject, $des_encode='utf-8')
    {
        $init_subject = $subject;

        if (! is_string($subject)) {
            return $subject;
        }
        
        
        $encodings       = self::mb_list_lowerencodings();
        $des_encode      = strtolower($des_encode);
        
        $decodedStr      = '';
        $subjects        = imap_mime_header_decode($subject);
        for ($n=sizeOf($subjects), $i=0; $i<$n; $i++) {
            $subject=$subjects[$i];
            $subject->charset=strtolower($subject->charset);
            if ($subject->charset == 'default' || !$subject->charset || $subject->charset == $des_encode){
                $decodedStr.=$subject->text;
            }else {
              if(in_array($subject->charset, $encodings)){
                $decodedStr.=mb_convert_encoding($subject->text, $des_encode, $subject->charset );
              }else{
                $decodedStr.=iconv($subject->charset, $des_encode, $subject->text);
              }
              
            }
        } 
        if($decodedStr == $init_subject){
            $decodedStr = Zend_Mime_Decode::decodeQuotedPrintable($decodedStr);
        }
        //echo __FILE__.__LINE__.'<pre>';print_r($decodedStr);exit;
        return $decodedStr;



        // $rs = Zend_Mime_Decode::decodeQuotedPrintable($subject);

        //echo __FILE__.__LINE__.'<pre>';print_r($rs);exit;
        //$subject = trim($subject);

       /* $matches = array();
        preg_match('/^\=\?[\w\-]+/', $subject, $matches);
        if (! $matches) {
            $separator = '=?GB2312';
            $toEncoding = 'GB2312';
        } else {
            $separator = $matches[0];
            $toEncoding = substr($separator, 2);
        }

       
        
       
        $encode = strstr($subject, $separator); 

        echo __FILE__.__LINE__.'<pre>';var_dump($separator);var_dump($toEncoding);var_dump($encode);exit;

        $subSubjectArr =array();
        if ($encode) {
            $explodeArr = explode($separator, $subject);
            $length = count($explodeArr);
            $subjectArr = array();
            for ($i = 0; $i < $length / 2; $i ++) {
                $subjectArr[$i][] = $explodeArr[$i * 2];
                if (@$explodeArr[$i * 2 + 1]) {
                    $subjectArr[$i][] = $explodeArr[$i * 2 + 1];
                }
            }
            foreach ($subjectArr as $arr) {
                $subSubject = implode($separator, $arr);
                if (count($arr) == 1) {
                    $subSubject = $separator . $subSubject;
                }
                //such as =?gbk?B?zfjS19PKz+TTw7un?=
                $begin = strpos($subSubject, "=?");
                $end = strpos($subSubject, "?=");
                $beginStr = '';
                $endStr = '';
                if ($end > 0) {
                    if ($begin > 0) {
                        $beginStr = substr($subSubject, 0, $begin);
                    }
                    if ((strlen($subSubject) - $end) > 2) {
                        $endStr = substr($subSubject, $end + 2, 
                                strlen($subSubject) - $end - 2);
                    }

                    //remove last ?=, the end param is minus
                    $str = substr($subSubject, 0, $end - strlen($subSubject));
                    $pos = strrpos($str, "?");
                    $str = substr($str, $pos + 1, strlen($str) - $pos);
                    $subSubject = $beginStr . imap_base64($str) . $endStr;
                    $subSubjectArr[] = iconv($toEncoding, 'utf-8', $subSubject);
                    // mb_convert_encoding($subSubject, 'utf-8'
                // ,'gb2312,ISO-2022-JP');
                }
            }
            $subject = implode('', $subSubjectArr);
        }else if(preg_match('/^(\w\w=)+\w\w.*$/', $subject)) {//such as E8=B6=85=E7=BA=A7=E7=AE=A1=E7=90=86=E5=91=982
            echo __FILE__.__LINE__.'<pre>';var_dump($subject);exit;
            $subject = quoted_printable_decode('='.$subject);
        }
        return $subject;
        */
    }



    public static function mb_list_lowerencodings() { 
        $r=mb_list_encodings();
        //echo __FILE__.__LINE__.'<pre>';print_r($r);print_r($r);exit;
        for ($n=sizeOf($r); $n--; ) { 
            $r[$n]=strtolower($r[$n]); 
        } 

       
        return $r;
    }


    public static function parseEmailMessage (Zend_Mail_Message $msg)
    {
        $charset                   = '';
        $content_transfer_encoding = null;
        $matches                   = array();
        $file_name                 = '';
        $is_html                   = 1;

        //echo __FILE__.__LINE__.'<pre>';var_dump($msg->isMultiPart());exit;
        if ($msg->isMultiPart()) {
            $arrAttachments = array();
            $body = '';

            // Multipart Mime Message
            foreach (new RecursiveIteratorIterator($msg) as $part) {
                try {
                    
                                      
                   //get charset 
                   /*if( preg_match('/charset="([a-zA-Z0-9\-_]+)"/is', $part->contentType, $matches)){
                        $charset = $matches[1];
                    }*/
                    try{
                        if(preg_match('/charset=([^;]*)/i', $part->contentType, $matches)) {
                            $charset = strtoupper(trim($matches[1], '"')); 
                        }
                    }catch(Exception $e){
                        $charset = null;
                    }

                    

                    //get content transfer encoding 
                    try{
                        if ($content_transfer_encoding == null || $part->getHeader('content-transfer-encoding')) {
                            $content_transfer_encoding = strtolower($part->getHeader('content-transfer-encoding'));
                        }
                    }catch(Exception $e){
                        $content_transfer_encoding = null;
                    }

                    $decode_fn = '';
                    if($content_transfer_encoding == 'quoted-printable'){
                        $decode_fn = 'quoted_printable_decode'; 
                    }else if($content_transfer_encoding == 'base64'){
                        $decode_fn = 'imap_base64';
                    }/*else if($content_transfer_encoding == 'binary'){
                        $decode_fn = 'imap_binary';
                    }else{
                        $decode_fn = 'imap_8bit';
                    }   */                 
                    //$decode_fn = $content_transfer_encoding == 'quoted-printable'?'quoted_printable_decode':'base64_decode';

                    //get mime type
                    try{
                        $mimeType = strtok($part->contentType, ';');
                    }catch(Exception $e){
                        $mimeType = null;
                    }

                    // Parse file name                    
                    $file_charset = $charset;
                    try{
                        $content_disposition = self::subjectDecode($part->getHeader('content-disposition'));
                        if(preg_match('/filename="([^"]+)?"/is',$content_disposition, $matches)){
                            //echo __FILE__.__LINE__.'<pre>';var_export($part->contentType);exit;
                            $file_name = $matches[1];
                        }
                    }catch(Exception $e){
                        $file_name = uniqid();
                    }
                    

                    
                    /*
                     * echo __FILE__.__LINE__.'<pre>';var_export($part->contentType);var_export($part->getContent());var_dump($mimeType);
                     * var_dump($part->getHeaders()); var_dump($charset);exit;
                     */
                    
                    // Append plaintext results to $body
                    // All other content parts will be treated as attachments
                    switch (strtolower($mimeType)) {
                        case 'text/plain':
                        // $body .= trim($part->getContent()) . "\n";
                        // break;
                            $is_html = 0 ;
                        case 'text/html':
                            $is_html = 1 ;
                            $tmp_body = trim($part->getContent());
                            if($decode_fn){
                                $tmp_body = $decode_fn($tmp_body);
                            }
                           
                            
                            if ($charset && strtolower($charset) != 'utf-8') {
                                $tmp_body = iconv($charset, 'UTF-8', $tmp_body);
                            }
                            // echo __FILE__.__LINE__.'<pre>'.$charset;echo '<br
                            // />'.$tmp_body;
                            $body .= $tmp_body;
                            break;
                        default:
                            /*echo __FILE__.__LINE__.'<pre>';
                            var_dump($content_disposition);
                            var_dump($file_name);
                            var_export($part->contentType);
                            var_export($part->getContent());
                            var_dump($mimeType);
                              var_dump($part->getHeaders());
                              var_dump($content_transfer_encoding);
                              var_dump($decode_fn);
                              var_dump($charset);exit; */
                            //$file_name = Zend_Mime_Decode::decodeQuotedPrintable(@$attachmentName['filename']);
                            /*if ($charset && strtolower($charset) != 'utf-8') {
                                $file_name = iconv($charset, 'UTF-8', $file_name);
                            }*/
                            $is_html = 1 ;
                            $arrAttachments[] = array(
                                    'attachment_mime' => $mimeType,
                                    'charset'         => $file_charset,
                                    'attachment_name' => $file_name,                                    
                                    'base64data'      => base64_decode(trim($part->getContent()))
                            );
                    }
                } catch (Zend_Mail_Exception $e) {
                    // ignore
                }
            }
            
            return array(
                    'content'    => $body,
                    'attachment' => $arrAttachments,
                    'charset'    => $charset,
                    'is_html'    => $is_html
            );
        } else {            

            //get content transfer encoding 
            try {
                if ($content_transfer_encoding == null || $msg->getHeader('content-transfer-encoding')) {
                    $content_transfer_encoding = strtolower($msg->getHeader('content-transfer-encoding'));
                }
            }catch(Exception $e){
                $content_transfer_encoding = null;
            }
            

            try{
                //get mime type
                $mimeType = strtok($msg->contentType, ';');
            }catch(Exception $e){
                $mimeType = 'text/plain';
            }


            $tmp_body = trim($msg->getContent());


            //$decode_fn = $content_transfer_encoding == 'quoted-printable'?'quoted_printable_decode':'base64_decode';
            $decode_fn = '';
            if($content_transfer_encoding == 'quoted-printable'){                
                $decode_fn = 'quoted_printable_decode';          

            }else if($content_transfer_encoding == 'base64'){
                $decode_fn = 'imap_base64';
                
            }/*else if($content_transfer_encoding == 'binary'){
                $decode_fn = 'imap_binary';
            }else if($content_transfer_encoding == '8bit'){
                $decode_fn = 'imap_8bit';
            }*/

            try{
                if(preg_match('/charset=([^;]*)/i', $msg->contentType, $matches)) {
                    $charset = strtolower(trim($matches[1], '"')); 
                }
            }catch(Exception $e){
                $charset = null;
            }
            



            if($decode_fn){
                $tmp_body = $decode_fn($tmp_body);          
            }
            
            if ($charset && $charset != 'utf-8') {
                $tmp_body = iconv($charset, 'UTF-8', $tmp_body);
            }

            /*echo __FILE__.__LINE__.'<pre>';print_r($msg);var_dump($msg->getContent());
            var_dump($msg->getHeader('content-transfer-encoding'));
            var_dump($content_transfer_encoding);
            var_dump($mimeType);var_dump($decode_fn);var_dump($charset);exit;*/
            // Plain text message 
            return array(
                    'content' => $tmp_body,
                    'attachment' => array(),
                    'charset' => $charset,
                    'is_html' => ($mimeType == 'text/plain'?0:1)
            );
        }
    }
}