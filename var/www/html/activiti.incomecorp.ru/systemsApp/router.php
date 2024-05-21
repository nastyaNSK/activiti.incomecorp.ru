<?php
define('ADDR_CHARS_ALLOW', '/[^a-zA-Z0-9?&%=._\-\/\s]/');

class router
{
    public $error;
    private $routes;

    function __construct()
    {
        switch (systems::getSubDomain())
        {
            case "":
                $this->rootRoutes();
                break;
            case "lk":
                $this->lkRoutes();
                break;
            default:
                //TODO move header respone
                $this->rootRoutes();
                break;
        }
    }

    // Site Page plan
    //================================================================================================
    private function rootRoutes()
    {
        $this->routes['/'] = 'b24App\b24App@Page_main';
        $this->routes['/b24-handler/'] = 'b24App\b24App@handler';
        $this->routes['/b24-installer/'] = 'b24App\b24App@installApp';
        $this->routes['/placement/'] = 'b24App\b24App@getPlacement';
        
        $this->routes['/b24-webhook'] = 'b24App\b24App@webhook';
        $this->routes['/b24-activity-switch/'] = 'b24App\b24App@activitySwitch';
        $this->routes['/b24-support-query/'] = 'b24App\b24App@supportQuery';

        //temp test routes
        $this->routes['/b24-test'] = 'b24App\b24App@b24test';
        $this->routes['/adminer'] = 'b24App\b24App@adminer';

    }
    private function lkRoutes()
    {


    }

    //================================================================================================

    public function getRouteToClass($r, $param=NULL)
    {

        if(gettype($r) != 'string')
        {
            $str = 'uri is not string |';
            $str .=$_SERVER['REMOTE_ADDR']." | ".$_SERVER['HTTP_USER_AGENT'];
            systems::writeLog($str, LOG__WARNING);

            $this->error = "404";
            return false;
        }

        if(preg_match(ADDR_CHARS_ALLOW, $r))
        {
            $str = 'uri includes invalid characters |';
            $str .=$_SERVER['REMOTE_ADDR']." | ".$_SERVER['HTTP_USER_AGENT'] ." | ". $r;
            systems::writeLog($str, LOG__WARNING);
            $this->error = "403";
            return false;
        }

        //отрезаем GET запрос
        $r = explode("?", $r)[0];

        if(isset($this->routes[$r]))
        {
            $f = explode("@", $this->routes[$r]);


            if(!isset($f[0]) || !class_exists($f[0]))
            {
                $str = 'class '.$f[0].' not found | ';
                $str .=$_SERVER['REMOTE_ADDR']." | ".$_SERVER['HTTP_USER_AGENT'];
                systems::writeLog($str, LOG__ERROR);
                $this->error = "404";
                return false;
            }

            $c = new $f[0]();

            if(!isset($f[1]) || !method_exists($c, $f[1]))
            {
                $str = 'function '.$f[1].' not found | ';
                $str .=$_SERVER['REMOTE_ADDR']." | ".$_SERVER['HTTP_USER_AGENT'];
                systems::writeLog($str, LOG__ERROR);
                header( "HTTP/1.1 404 Not Found" );
                return false;
            }
            $fn=$f[1];
            $c->$fn();
            //$c->page_main();
        }else{

            $str = 'page '.$r.' not found | ';
            $str .=$_SERVER['REMOTE_ADDR']." | ".$_SERVER['HTTP_USER_AGENT'];
            systems::writeLog($str, LOG__WARNING);
            $this->error = "404";
            return false;
        }


        return true;

    }

}
