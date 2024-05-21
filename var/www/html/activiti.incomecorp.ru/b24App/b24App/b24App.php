<?php
namespace b24App;
use systems;
use PHPMailer\PHPMailer\PHPMailer;

define('EXT_APP', __DIR__."/app/");//Application key
define('EXT_PLACEMENT', __DIR__."/placement/");//Application key


class b24App extends b24AppSys
{

    function __construct() {
        parent::__construct();
//        systems::lvd($_POST);
//        systems::lvd($_GET);

    }
    public function activitySwitch()
    {
        if(isset($_REQUEST['member_id'])) {
            $this->setPortalDomain($_REQUEST['member_id']);
            if(isset($_REQUEST['code']))
            {
                $e = explode(".", $_REQUEST['code']);
                $file = isset($e[1]) ? $e[1] :  $_REQUEST['code'];
                systems::lvd("++".$file."++");
                if(is_file(EXT_APP . strtolower($file.".php")))
                {
                    $app = require(EXT_APP . strtolower($file.".php"));
                    $app['install']($this);
                    echo json_encode(["result" => "ok"]);
                    return;
                }else{
                    systems::lvd($_REQUEST);
                    systems::lvd(is_file(EXT_APP . strtolower($_REQUEST['code'].".php")));
                }
            }
        }
    }
    public function supportQuery()
    {
        if(isset($_REQUEST['member_id'])) {

            $toAddress = $toName = "sale@income-media.ru";
            $subject = "Запрос из приложения";

            $message = ' 
                        Адрес пользователя:' .$_REQUEST['DOMAIN'] .' <br><br>
                        Email '.$_REQUEST['email'].'<br>
                        PHONE '.$_REQUEST['phone'].'<br><hr>
                        '.$_REQUEST['supportrequest'].'';


            $mail = new PHPMailer;
            $mail->isSMTP();
            $mail->SMTPDebug = 0;
            $mail->Host = systems::$config['email']['host'];
            $mail->Port = 465;
            $mail->SMTPSecure = "ssl";
            $mail->SMTPAuth = true;
            $mail->Username = systems::$config['email']['userName'];
            $mail->Password = systems::$config['email']['password'];
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->setFrom(systems::$config['email']['from'], 'info');
            $mail->addReplyTo(systems::$config['email'], 'info');
            $mail->addAddress($toAddress, $toName);
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body = $message;

            if (!$mail->send()) {
                echo json_encode(["result" => "false"]);
            } else {
                echo json_encode(["result" => "ok"]);
            }
        }

    }


    public function handler()
    {
//        systems::lvd("=========== HANDLER ==========");
////
  //      systems::lvd($_REQUEST);

        if(isset($_REQUEST["PLACEMENT"]) && $_REQUEST["PLACEMENT"] == "DEFAULT" ){

            $activity = self::getActivityDataByFiles();
            $sections = self::getSection();

            $handlerBackUrl = "https://activiti.incomecorp.ru/b24-activity-switch/";
            $supportBackUrl = "https://activiti.incomecorp.ru/b24-support-query/";
            $BackUrl = "https://activiti.incomecorp.ru/";

            require_once (__DIR__."/forms/default.php");
        }


        if(isset($_REQUEST["auth"]['member_id'])){
            $this->setPortalDomain($_REQUEST["auth"]['member_id']);

            $this->UpdateCounter();

            if(isset($_REQUEST['event']))
            {
                $f = $_REQUEST['event'];
                if(function_exists($f))
                    $f();
                return;
            }

            if(isset($_REQUEST['code']))
            {
                $e = explode(".", $_REQUEST['code']);
                $file = isset($e[1]) ? $e[1] :  $_REQUEST['code'];
                //systems::lvd($file);
                if(is_file(EXT_APP . strtolower($file.".php")))
                {
                    $app = require(EXT_APP . strtolower($file.".php"));
                    $app['handler']($this);
                    return;
                }
            }
        }
    }

    public function Page_main()
    {
//        echo "ok";
   //     $this->setPortalDomain('ce881ffc5c0021f8e0dfc5f2040fafd2');
//        $this->UpdateCounter('ce881ffc5c0021f8e0dfc5f2040fafd2');
     //   var_dump($this->getAppSettings('ce881ffc5c0021f8e0dfc5f2040fafd2'));


    }

    public function log($str)
    {
        //systems::lvd($str);
    }

    public function webhook()
    {

    }

    public function getPlacement()
    {

        if(!isset($_GET['placement']) || !isset($_REQUEST['member_id']))
        {
           return;
        }
        $file = preg_replace('/[^a-z_]/', '', $_GET['placement']);

        $fname = EXT_PLACEMENT."/".$file."/".$file .".php";

        if(is_file($fname))
        {
            $this->b24Demoronization();
            $this->setPortalDomain($_REQUEST['member_id']);
            include_once($fname);
        }



    }

    public function b24test()
    {
        ini_set('error_reporting', E_ALL);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        echo "OK";
    }
    public function adminer()
    {
        require_once  __DIR__."/../adminer/adminer.php";
    }

    public function UpdateCounter()
    {
        $member_id = $_REQUEST['auth']['member_id'];
        $activiti_code = $_REQUEST['code'];

        $query = "SELECT `id` FROM `QueryCounter` WHERE `member_id` LIKE :member_id AND `activiti_code` LIKE :activiti_code";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['member_id' => $member_id, 'activiti_code' => $activiti_code]);
        if($stmt->fetch())
        {
            $query = "UPDATE `QueryCounter` SET `run_count`  =  `run_count`+1, `updated` = NOW()
                      WHERE `member_id` LIKE :member_id AND `activiti_code` LIKE :activiti_code  ";
        }else{

            $query = "INSERT INTO `QueryCounter` (`member_id`, `activiti_code`, `run_count`, `updated`)
                      VALUES(:member_id, :activiti_code, 1, NOW())";
        }
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['member_id' => $member_id, 'activiti_code' => $activiti_code]);
    }


    public function installApp()
    {


        $resultInstall = $this->install();


        /**  if || true instsall all activitis */
        if($resultInstall['install'] == true && false) {
            $this->b24Demoronization();
            $this->setPortalDomain($_REQUEST['member_id']);
            $dir = scandir(EXT_APP);
            foreach ($dir as $item) {
                if (is_file(EXT_APP . $item)) {
                    $app = require(EXT_APP . $item);
                    $app['install']($this);
                }
            }
        }

        if($resultInstall['rest_only'] === false):?>
        <head>
            <script src="//api.bitrix24.com/api/v1/"></script>
            <?php if($resultInstall['install'] == true):?>
                <script>
                    BX24.init(function(){
                        BX24.installFinish();
                    });
                </script>
            <?php endif;?>
        </head>
        <body>
        <?php if($resultInstall['install'] == true):?>
            Установка прошла успешно
        <?php else:?>
            Ошибка установки
        <?php endif;?>
        </body>
        <?php endif;



    }

    public static function getSection()
    {
        return
        [
           [ 'name'=>'Все', 'img' => '', 'activityCode' => ['*'], 'active'=>'Y'],
           [ 'name'=>'Сделки', 'img' => '', 'activityCode' => ['DEAL_CHANGE', 'DEAL_GET_DATA', 'DEAL_SEARCH', 'DEAL_BACK_STAGE']],
           [ 'name'=>'Лиды', 'img' => '', 'activityCode' => ['LEAD_CHANGE', 'LEAD_GET_DATA', 'LEAD_SEARCH', 'LEAD_BACK_STAGE']],
           [ 'name'=>'Компании', 'img' => '', 'activityCode' => ['COMPANY_CHANGE', 'COMPANY_GET_DATA', 'COMPANY_SEARCH']],
           [ 'name'=>'Контакты', 'img' => '', 'activityCode' => ['CONTACT_CHANGE', 'CONTACT_GET_DATA', 'CONTACT_SEARCH']],
           [ 'name'=>'Задачи', 'img' => '', 'activityCode' => ['TASKS_STATUS', 'TASKS_RENEW', 'TASKS_COMPLETE', 'TASKS_CHANGE_DATE', 'TASKS_AUDITORS']],
           [ 'name'=>'Прочее', 'img' => '', 'activityCode' => ['DELAY_BIZ']],
        ];
    }

    public static function getActivityDataByFiles()
    {
        $dir = scandir(EXT_APP);
        $arResult =[];
        foreach ($dir as $item) {
            if (is_file(EXT_APP . $item)) {

                $app = require(EXT_APP . $item);
                if(isset($app['data']))
                {
                    $arResult[] =  $app['data'];
                }else{
                    $r['activityDescription'] =  $r['activityCode'] =  $r['activityName'] = strtoupper(explode(".", $item)[0]);
                    $arResult[] = $r;

                }

            }
        }

        return $arResult;
    }


}
