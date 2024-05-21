<?php
namespace b24App;
use systems;


define('C_REST_CLIENT_ID','app.632852a7247225.96485395');//Application ID
define('C_REST_CLIENT_SECRET','QicJwvhN9ACtO5ERGobR8WaQGiHzJ7AO2WaOhiDU3zqvUddp2f');//Application key

class b24AppSys
{
    const VERSION = '1.36';
    const BATCH_COUNT    = 50;//count batch 1 query
    const TYPE_TRANSPORT = 'json';// json or xml

    protected $domain;

    function __construct() {

        if(empty(Systems::$config['db']))
        {
            systems::$config = require($_SERVER['DOCUMENT_ROOT'] . CONFIG_ADDR);
        }
        $this->pdo = systems::pdoConnect(systems::$config['db']);
    }


    public function install()
    {
        $result = [
            'rest_only' => true,
            'install' => false
        ];
        $_REQUEST[ 'event' ] = isset($_REQUEST[ 'event' ]) ? $_REQUEST[ 'event' ] : "";
        if($_REQUEST[ 'event' ] == 'ONAPPINSTALL' && !empty($_REQUEST[ 'auth' ]))
        {
            $result['install'] = $this->setAppSettings($_REQUEST[ 'auth' ], true);
        }
        elseif($_REQUEST['PLACEMENT'] == 'DEFAULT')
        {
            $result['rest_only'] = false;
            $result['install'] = $this->setAppSettings(
                [
                    'access_token' => htmlspecialchars($_REQUEST['AUTH_ID']),
                    'expires_in' => htmlspecialchars($_REQUEST['AUTH_EXPIRES']),
                    'application_token' => htmlspecialchars($_REQUEST['APP_SID']),
                    'refresh_token' => htmlspecialchars($_REQUEST['REFRESH_ID']),
                    'domain' => htmlspecialchars($_REQUEST['DOMAIN']),
                    'client_endpoint' => 'https://' . htmlspecialchars($_REQUEST['DOMAIN']) . '/rest/',
                    'status' =>  htmlspecialchars($_REQUEST['status']),
                    'expires' => 0,
                    'server_endpoint' => "",
                    'member_id' => htmlspecialchars($_REQUEST['member_id']),
                    'user_id' => 0,
                ],
                true
            );
        }

        return $result;
    }

    public function b24Demoronization()
    {
        if(!isset($_REQUEST['access_token']))
            $_REQUEST['access_token'] = htmlspecialchars($_REQUEST['AUTH_ID']);
        if(!isset($_REQUEST['expires_in']))
            $_REQUEST['expires_in'] = htmlspecialchars($_REQUEST['AUTH_EXPIRES']);
        if(!isset($_REQUEST['application_token']))
            $_REQUEST['application_token'] = htmlspecialchars($_REQUEST['APP_SID']);
//        if(!isset($_REQUEST['expires']))
//            $_REQUEST['expires'] = 0;
        if(!isset($_REQUEST['domain']))
            $_REQUEST['domain'] = htmlspecialchars($_REQUEST['DOMAIN']);
//        if(!isset($_REQUEST['server_endpoint']))
//            $_REQUEST['server_endpoint'] = "";
        if(!isset($_REQUEST['client_endpoint']))
            $_REQUEST['client_endpoint'] = 'https://' . htmlspecialchars($_REQUEST['DOMAIN']) . '/rest/';
        if(!isset($_REQUEST['status']))
            $_REQUEST['status'] = htmlspecialchars($_REQUEST['status']);
    }


    private function setAppSettings($arSettings, $isInstall = false)
    {
        $tFields = ["access_token", "expires", "expires_in", "domain", "server_endpoint", "status", "client_endpoint", "member_id", "user_id", "refresh_token", "application_token"];
        $return = false;

        if(is_array($arSettings) && isset($arSettings['domain']) )
        {
           //TODO scopes
            foreach ($arSettings as $k=>$v)
            {
                if(!in_array($k, $tFields))
                    unset($arSettings[$k]);
            }

            if($this->getAppSettings($arSettings['member_id']))
            {
                $field = "";
                foreach ($arSettings as $k=>$v)
                {
                    if($k == 'member_id') continue;
                    $field .= $k . "=:".$k .", ";
                }
                $field = substr($field, 0,-2);
                $query = "UPDATE `oAuth` SET $field WHERE `member_id` LIKE :member_id";

                $stmt = $this->pdo->prepare($query);
                $stmt->execute($arSettings);

                //TODO if install fail
            }else{

                $query = "INSERT INTO `oAuth` (`access_token`, `expires`, `expires_in`, `domain`, `server_endpoint`, `status`, `client_endpoint`, `member_id`, `user_id`, `refresh_token`, `application_token`) 
                          VALUES (:access_token, :expires, :expires_in, :domain, :server_endpoint, :status, :client_endpoint, :member_id, :user_id, :refresh_token, :application_token)";
                $stmt = $this->pdo->prepare($query);
                $stmt->execute($arSettings);
            }
            //TODO check success
            $return = true;
        }
        return $return;
    }

    /**
     * @return mixed setting application for query
     */

    private function getAppSettings($member_id)
    {


        if(defined("C_REST_WEB_HOOK_URL") && !empty(C_REST_WEB_HOOK_URL))
        {
            $arData = [
                'client_endpoint' => C_REST_WEB_HOOK_URL,
                'is_web_hook'     => 'Y'
            ];
            $isCurrData = true;
        }
        else
        {
            $query = "SELECT * FROM `oAuth` 
                      LEFT JOIN `appScopes` ON `appScopes`.`oAuthId` = `oAuth`.`id`
                      LEFT JOIN `scope` ON `scope`.`scopeId` = `appScopes`.`scopeId`
                      WHERE `oAuth`.`member_id` LIKE :member_id 
                      ORDER BY `oAuth`.`id` desc
                      LIMIT 1 ";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['member_id' => $member_id]);

            $arData = $stmt->fetchAll()[0];

            systems::lvd($member_id);

            if(defined("C_REST_CLIENT_ID") && !empty(C_REST_CLIENT_ID))
            {
                $arData['C_REST_CLIENT_ID'] = C_REST_CLIENT_ID;
            }
            if(defined("C_REST_CLIENT_SECRET") && !empty(C_REST_CLIENT_SECRET))
            {
                $arData['C_REST_CLIENT_SECRET'] = C_REST_CLIENT_SECRET;
            }

            $isCurrData = false;
            if(
                !empty($arData[ 'access_token' ]) &&
                !empty($arData[ 'domain' ]) &&
                !empty($arData[ 'refresh_token' ]) &&
                !empty($arData[ 'application_token' ]) &&
                !empty($arData[ 'client_endpoint' ])
            )
            {
                $isCurrData = true;
            }
        }

        return ($isCurrData) ? $arData : false;
    }

    public function setPortalDomain($domain)
    {
        $this->domain = $domain;
    }
    public function getPortalDomain()
    {
        return $this->domain;
    }


    public function call($method, $params = [])
    {
        $arPost = [
            'method' => $method,
            'params' => $params
        ];

        $result = $this->callCurl($arPost);
        return $result;
    }

    protected function callCurl($arParams)
    {
        if(!function_exists('curl_init'))
        {
            return [
                'error'             => 'error_php_lib_curl',
                'error_information' => 'need install curl lib'
            ];
        }
        $arSettings =$this->getAppSettings($this->domain);
        if($arSettings !== false)
        {
            if(isset($arParams[ 'this_auth' ]) && $arParams[ 'this_auth' ] == 'Y')
            {
                $url = 'https://oauth.bitrix.info/oauth/token/';
            }
            else
            {
                $url = $arSettings[ "client_endpoint" ] . $arParams[ 'method' ] . '.' . static::TYPE_TRANSPORT;
                if(empty($arSettings[ 'is_web_hook' ]) || $arSettings[ 'is_web_hook' ] != 'Y')
                {
                    $arParams[ 'params' ][ 'auth' ] = $arSettings[ 'access_token' ];
                }
            }

            $sPostFields = http_build_query($arParams[ 'params' ]);

            try
            {
                $obCurl = curl_init();
                curl_setopt($obCurl, CURLOPT_URL, $url);
                curl_setopt($obCurl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($obCurl, CURLOPT_POSTREDIR, 10);
                curl_setopt($obCurl, CURLOPT_USERAGENT, 'Bitrix24 CRest PHP ' . static::VERSION);
                if($sPostFields)
                {
                    curl_setopt($obCurl, CURLOPT_POST, true);
                    curl_setopt($obCurl, CURLOPT_POSTFIELDS, $sPostFields);
                }
                curl_setopt(
                    $obCurl, CURLOPT_FOLLOWLOCATION, (isset($arParams[ 'followlocation' ]))
                    ? $arParams[ 'followlocation' ] : 1
                );
                if(defined("C_REST_IGNORE_SSL") && C_REST_IGNORE_SSL === true)
                {
                    curl_setopt($obCurl, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($obCurl, CURLOPT_SSL_VERIFYHOST, false);
                }
                $out = curl_exec($obCurl);
                $info = curl_getinfo($obCurl);
                if(curl_errno($obCurl))
                {
                    $info[ 'curl_error' ] = curl_error($obCurl);
                }
                if(static::TYPE_TRANSPORT == 'xml' && (!isset($arParams[ 'this_auth' ]) || $arParams[ 'this_auth' ] != 'Y'))//auth only json support
                {
                    $result = $out;
                }
                else
                {
                    $result = static::expandData($out);
                }
                curl_close($obCurl);

                if(!empty($result[ 'error' ]))
                {
                    if($result[ 'error' ] == 'expired_token' && empty($arParams[ 'this_auth' ]))
                    {
                        $result = $this->GetNewAuth($arParams);
                    }
                    else
                    {
                        $arErrorInform = [
                            'expired_token'          => 'expired token, cant get new auth? Check access oauth server.',
                            'invalid_token'          => 'invalid token, need reinstall application',
                            'invalid_grant'          => 'invalid grant, check out define C_REST_CLIENT_SECRET or C_REST_CLIENT_ID',
                            'invalid_client'         => 'invalid client, check out define C_REST_CLIENT_SECRET or C_REST_CLIENT_ID',
                            'QUERY_LIMIT_EXCEEDED'   => 'Too many requests, maximum 2 query by second',
                            'ERROR_METHOD_NOT_FOUND' => 'Method not found! You can see the permissions of the application: CRest::call(\'scope\')',
                            'NO_AUTH_FOUND'          => 'Some setup error b24, check in table "b_module_to_module" event "OnRestCheckAuth"',
                            'INTERNAL_SERVER_ERROR'  => 'Server down, try later'
                        ];
                        if(!empty($arErrorInform[ $result[ 'error' ] ]))
                        {
                            $result[ 'error_information' ] = $arErrorInform[ $result[ 'error' ] ];
                        }
                    }
                }
                if(!empty($info[ 'curl_error' ]))
                {
                    $result[ 'error' ] = 'curl_error';
                    $result[ 'error_information' ] = $info[ 'curl_error' ];
                }

//               systems::lvd(
//                    [
//                        'url'    => $url,
//                        'info'   => $info,
//                        'params' => $arParams,
//                        'result' => $result
//                    ],
//                    'callCurl'
//                );

                return $result;
            }
            catch(Exception $e)
            {
                systems::lvd(
                    [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'trace' => $e->getTrace(),
                        'params' => $arParams
                    ],
                    'exceptionCurl'
                );

                return [
                    'error' => 'exception',
                    'error_exception_code' => $e->getCode(),
                    'error_information' => $e->getMessage(),
                ];
            }
        }
        else
        {
            systems::lvd($this->domain);
            systems::lvd(
                [
                    'params' => $arParams
                ],
                'emptySetting'
            );
        }

       // systems::lvd($arSettings);

        return [
            'error'             => 'no_install_app',
            'error_information' => 'error install app, pls install local application '
        ];
    }

    private function GetNewAuth($arParams)
    {
        $result = [];
        $arSettings = $this->getAppSettings($this->domain);

        if($arSettings !== false)
        {
            $arParamsAuth = [
                'this_auth' => 'Y',
                'params'    =>
                    [
                        'client_id'     => $arSettings[ 'C_REST_CLIENT_ID' ],
                        'grant_type'    => 'refresh_token',
                        'client_secret' => $arSettings[ 'C_REST_CLIENT_SECRET' ],
                        'refresh_token' => $arSettings[ "refresh_token" ],
                    ]
            ];


            $newData = $this->callCurl($arParamsAuth);

            if(isset($newData[ 'C_REST_CLIENT_ID' ]))
            {
                unset($newData[ 'C_REST_CLIENT_ID' ]);
            }
            if(isset($newData[ 'C_REST_CLIENT_SECRET' ]))
            {
                unset($newData[ 'C_REST_CLIENT_SECRET' ]);
            }
            if(isset($newData[ 'error' ]))
            {
                unset($newData[ 'error' ]);
            }
            if($this->setAppSettings($newData))
            {
                $arParams[ 'this_auth' ] = 'N';
                $result = $this->callCurl($arParams);
            }
        }
        return $result;
    }


    protected static function expandData($data)
    {
        $return = json_decode($data, true);
        if(defined('C_REST_CURRENT_ENCODING'))
        {
            $return = static::changeEncoding($return, false);
        }
        return $return;
    }



}