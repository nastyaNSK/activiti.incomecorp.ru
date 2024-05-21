<?php

class systems
{
    public static $config;

    public static function pdoConnect($db)
    {

        $dsn = "mysql:host=" . $db['host'];
        $dsn .= ";dbname=" . $db['dbname'];
        $dsn .= ";charset=" . $db['charset'];


        $opt = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($dsn, $db['user'], $db['pwd'], $opt);
        return $pdo;
    }

    public static function encrypt_aes_256_cbc($data, $encryption_key)
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(AES_256_CBC));

        $encrypted = openssl_encrypt($data, AES_256_CBC, $encryption_key, 0, $iv);

        $encrypted = $encrypted . ':' . base64_encode($iv);

        return $encrypted;

    }

    public static function decrypt_aes_256_cbc($encrypted, $encryption_key)
    {
        $parts = explode(':', $encrypted);

        $decrypted = openssl_decrypt($parts[0], AES_256_CBC, $encryption_key, 0, base64_decode($parts[1]));

        return $decrypted;
    }


    public static function getRandomString($max=64)
    {
        $chars="qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
        $size=StrLen($chars)-1;

        $password=null;
        while($max--)
            $password.=$chars[rand(0,$size)];
        return $password;
    }

    public static function getHTTPAddress()
    {
        $isHttps = !empty($_SERVER['HTTPS']) && 'off' !== strtolower($_SERVER['HTTPS']);

        //$host = explode('.', $_SERVER['HTTP_HOST']);

        //$host = implode('.', array_slice($host, count($host) - 2, 2));

        $host = $_SERVER['HTTP_HOST'];

        $host = ($isHttps) ? 'https://'. $host :  'http://'. $host;
        return $host;
    }

    public static function getMainHost()
    {

        $host = explode('.', $_SERVER['HTTP_HOST']);

        $host = implode('.', array_slice($host, count($host)- 2, 2 ));

        return $host;
    }

    public static function getSubDomain()
    {
        $host = explode('.', $_SERVER['HTTP_HOST']);
        $subdomain = implode('.', array_slice($host, 0, count($host) - 2 ));
        return $subdomain;
    }


    // Dev and Maintenance functions
    //==========================================================================================

    public static function DevLock($s=false, $log=false)
    {
        if($s) setcookie("devlock", "unlock", time()+COOKIE_TIME, '/', ".".self::getMainHost());
        $l = isset($_COOKIE['devlock']) ? $_COOKIE['devlock'] : "";
        if($l == "unlock" || $s)
        {
            return false;
        }
        if($log)
        {
            $str = "DevLock ";
            $str .=$_SERVER['REQUEST_URI']." | ".$_SERVER['REMOTE_ADDR']." | ".$_SERVER['HTTP_USER_AGENT'];
            Systems::writeLog($str, LOG__ACTION);
        }

        return true;
    }

    // Logging functions
    //==========================================================================================

    public static function writeLog($str, $flag)
    {
        $cnf = require($_SERVER['DOCUMENT_ROOT'] . CONFIG_ADDR);

        switch ($flag) {
            case LOG__ERROR:
                $str = PHP_EOL . "ERROR: " . date("m.d.y H:i:s") . " " . $str;
                break;
            case LOG__WARNING:
                $str = PHP_EOL . "WARNING: " . date("m.d.y H:i:s") . " " . $str;
                break;
            case LOG__ACTION:
                $str = PHP_EOL . "ACTION: " . date("m.d.y H:i:s") . " " . $str;
                break;
        }
        if ($cnf['log']['mode'] & LOG__ECHO) {
            echo $str . "<br>";
        }
        if ($cnf['log']['mode'] & $flag) {
            if ($fp = fopen($cnf['log']['path'], "a")) {

                fwrite($fp, $str);
                fclose($fp);
            } else {
                //TODO check before

                return false;
            }
        }
        return true;
    }


    public static function vd($v)
    {
        echo "<pre>";
        var_dump($v);
        echo "</pre>";
    }
    public static function vdd($v)
    {
        echo "<pre>";
        var_dump($v);
        echo "</pre>";
        die;
    }

    public static function lvd($v)
    {
        ob_start();
        var_dump($v);
        $str = ob_get_clean();
        self::writeLog($str, LOG__ACTION);
    }
}