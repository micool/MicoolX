<?php

/**
 * @author micool
 * @email micool@micool.cn
 * @package micool strtoxx
 * @version 1.0
 */
class MicoolX {

    static $key = 'wenmicool';

    static private function ekey() {
        return substr(md5(self::$key) . sha1(self::$key), 19, 8);
    }

//en
    static function encode($value) {
        $td = mcrypt_module_open(MCRYPT_DES, '', 'ecb', ''); //使用MCRYPT_DES算法,ecb模式   
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init($td, self::ekey(), $iv); //初始处理   
        $encrypted = mcrypt_generic($td, $value);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return $encrypted;
    }

//de
    static function decode($value) {
        $td = mcrypt_module_open(MCRYPT_DES, '', 'ecb', ''); //使用MCRYPT_DES算法,ecb模式   
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init($td, self::ekey(), $iv); //初始处理   
        $decrypted = mdecrypt_generic($td, $value);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return $decrypted;
    }

    
    static function xcode_en($value,$nb){
        if($nb%2){
            $vi=intval($nb%2);
        }else{
            $vi=intval($nb%3);
        }
        for($i=0;$i<$vi;$i++){
            $value= base64_encode($value);
        }
        return $value;
    }
    
    static function xcode_de($value,$nb) {
        if($nb%2){
            $vi=intval($nb%2);
        }else{
            $vi=intval($nb%3);
        }
        for($i=0;$i<$vi;$i++){
            $value= base64_decode($value);
        }
        return $value;
    }

    /**
     * 
     * @param type $ConMail 邮件配置
     * @param type $Emailtitle 邮件标题
     * @param type $Emailbody 邮件内容
     * @return boolean 
     */
    function email($ConMail, $Emailtitle, $Emailbody) {
        $mail = new PHPMailer ( ); //
        $mail->IsSMTP();
        $mail->Host = $ConMail ['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $ConMail ['user'];
        $mail->Password = $ConMail ['pass'];
        $mail->From = $ConMail ['from'];

        $mail->FromName = $ConMail ['fromname'];
        $mail->AddAddress($ConMail ['clientmail'], $ConMail ['clientname']);

        //编码的判断
        $mail->CharSet = $ConMail ['charset'];

        $mail->WordWrap = 100; // 设置每行最大数超过改数后自动换行
        //附件部分
//        if (!empty($Emailachment)) {
//            $mail->AddAttachment($Emailachment); // add attachments
//        }
        //$mail->AddAttachment("/tmp/image.jpg", "new.jpg"); // optional name
        $mail->IsHTML(true); // set email format to HTML

        $mail->Subject = $Emailtitle;
        $boxinfo = $Emailbody;
        $mail->Body = $boxinfo;
        if (!$mail->Send()) {
            $thetemp = '邮件内容' . $Emailbody . '[' . $mail->ErrorInfo . ']';
            $this->write('pss/log/email.err.log', $thetemp);
            return false; //发送错误返回相关错误
        } else {
            return true; //成功返回代码
        }
    }

    function read($path) {
        
    }
    
    static function log($info){
        $path='pss/log/access.log';
        $word='Time:'.date('Y-m-d H:i:s');
        $word.=' C-IP:'.self::Get_Real_Ip();
        $word.=' S-IP:'.self::Real_Server_Ip();
        $word.=' info:'.$info;
        self::write($path,$word,'log');
    }

    static function write($path, $word, $type = 'log') {
        switch ($type) {
            case 'log':
                @$fp = fopen($path, "a");
                @flock($fp, LOCK_EX);
                @fwrite($fp, $word . "\r\n");
                @flock($fp, LOCK_UN);
                @fclose($fp);
                break;
            case 'indata':
                @$fp = fopen($path, "a");
                @flock($fp, LOCK_EX);
                @fwrite($fp, $word);
                @flock($fp, LOCK_UN);
                @fclose($fp);
                break;
            case 'edit':
                break;
        }
    }

    /**
     * 获取或更新总流水单
     * @param type $type 
     */
    static function Counts($type = 'r') {
        $filepath='conf/lib.conf';
        if (!file_exists($filepath))
            return 0;
        switch ($type) {
            default :
            case 'r':
                $readhandle = fopen($filepath, "r");
                $value = fread($readhandle, filesize($filepath));
                fclose($readhandle);
                return $value;
                break;
            case '+':
                $readhandle = fopen($filepath, "r");
                $value = fread($readhandle, filesize($filepath));
                fclose($readhandle);
                $updatehandle = fopen($filepath, "wb");
                flock($updatehandle, 2);
                fputs($updatehandle, intval($value) + 1);
                fclose($updatehandle);
                break;
        }
    }

    //callback json 
    static function backPostjosn($array) {
        exit(json_encode($array));
    }

    /**
     * 处理JSON 传过来是数据转化为数组
     *
     * @param unknown_type $array
     * @return unknown
     */
    function object_array($array) {
        if (is_object($array)) {
            $array = (array) $array;
        }
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $array [$key] = object_array($value);
            }
        }
        return $array;
    }

    /**
     *  获取真实IP
     * 函数名：get_real_ip() 
     * 作 用：判断当前访问页面下用户的IP地址
     * 参 数：
     * 返回值：ip地址
     */
    static function Get_Real_Ip() {
        $ip = false;
        if (!empty($_SERVER ["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER ["HTTP_CLIENT_IP"];
        }
        if (!empty($_SERVER ['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(", ", $_SERVER ['HTTP_X_FORWARDED_FOR']);
            if ($ip) {
                array_unshift($ips, $ip);
                $ip = FALSE;
            }
            for ($i = 0; $i < count($ips); $i++) {
                if (!eregi("^(10|172\.16|192\.168)\.", $ips [$i])) {
                    $ip = $ips [$i];
                    break;
                }
            }
        }
        return ($ip ? $ip : $_SERVER ['REMOTE_ADDR']);
    }

    /**
     * 获取服务器的ip
     * @access      public
     * @return string
     * */
    static function Real_Server_Ip() {
        static $serverip = NULL;

        if ($serverip !== NULL) {
            return $serverip;
        }

        if (isset($_SERVER)) {
            if (isset($_SERVER['SERVER_ADDR'])) {
                $serverip = $_SERVER['SERVER_ADDR'];
            } else {
                $serverip = '0.0.0.0';
            }
        } else {
            $serverip = getenv('SERVER_ADDR');
        }

        return $serverip;
    }

}

?>
