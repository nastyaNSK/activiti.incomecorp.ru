<?php

$return['install'] = function($rest)
{


    $handlerBackUrl = "https://activiti.incomecorp.ru/b24-handler/";

    $delite = $rest->call(
        'bizproc.activity.delete',
        [
            'CODE' => 'A.COMPANY_CHANGE',
        ]
    );

    $delite = $rest->call(
        'bizproc.robot.delete',
        [
            'CODE' => 'R.COMPANY_CHANGE',
        ]
    );



    $fields = $rest->call(
        'crm.company.fields',
        [
        ]
    );


    $fields = $fields['result'];

   // systems::lvd($fields);

    foreach ($fields as $keyS => $valueS) {
        foreach ($valueS as $key => $value) {
            if ($key == 'isReadOnly' && $value == 1 && $keyS != 'ID') {
                unset($fields[$keyS]);
            }
            if($keyS == 'LOGO') {
                unset($fields[$keyS]);
            }
            if ( $keyS == 'PHOTO') {
                unset($fields[$keyS]);
            }
            if ($keyS == 'OPENED') {
                $fields[$keyS]['type'] = 'select';
                $fields[$keyS]['Options'] = [
                    'N' => 'Нет',
                    'Y' => 'Да'
                ];
            }
            if ($keyS == 'IS_MY_COMPANY') {
                $fields[$keyS]['type'] = 'select';
                $fields[$keyS]['Options'] = [
                    'N' => 'Нет',
                    'Y' => 'Да'
                ];
            }
            if ($key == 'title' || $key == 'listLabel' || $key == 'formLabel' || $key == 'filterLabel') {
                if (isset($fields[$keyS][$key])) {
                    $fields[$keyS]['Name'] = $fields[$keyS][$key];
                }
                if (!isset($fields[$keyS]['Name'])) {
                    $fields[$keyS]['Name'] = $keyS;
                }
                unset($fields[$keyS][$key]);
            }  elseif ($key == 'isMultiple') {
                $fields[$keyS]['Multiple'] = $fields[$keyS][$key];
                unset($fields[$keyS][$key]);
            }elseif ($key == 'statusType'){
                if ($value == 'COMPANY_TYPE') {
                    $fields[$keyS]['type'] = 'select';
                    $fields[$keyS]['Options'] = [
                        'CUSTOMER' => 'Клиент',
                        'SUPPLIER' => 'Постовщик',
                        'COMPETITOR' => 'Конкурент',
                        'PARTNER' => 'Партнёр',
                        'OTHER' => 'Другое',
                    ];
                }
                if ($value == 'INDUSTRY') {
                    $fields[$keyS]['type'] = 'select';
                    $fields[$keyS]['Options'] = [
                        'IT' => 'Информационные технологии',
                        'TELECOM' => 'Телекоммуникации и связь',
                        'MANUFACTURING' => 'Производство',
                        'BANKING' => 'Банковские услуги',
                        'CONSULTING' => 'Консалтинг',
                        'FINANCE' => 'Финансы',
                        'GOVERNMENT' => 'Правительство',
                        'DELIVERY' => 'Доставка',
                        'ENTERTAINMENT' => 'Развлечения',
                        'NOTPROFIT' => 'Не для получения прибыли',
                        'OTHER' => 'Другое',
                    ];
                }

                if($value == 'EMPLOYEES') {
                    $fields[$keyS]['type'] = 'select';
                    $fields[$keyS]['Options'] = [
                        'EMPLOYEES_1' => 'Менее 50',
                        'EMPLOYEES_2' => '50-250',
                        'EMPLOYEES_3' => '250-500',
                        'EMPLOYEES_4' => 'Более 500'
                    ];
                }

            } elseif ($key != 'type') {
                unset($fields[$keyS][$key]);
            }
        }
    }

    $fields['clear_mail_and_phons'] =
        [
            'NAME' => 'Очистить почты и(или) телефоны:',
            'Type' => 'select',
            'Options' => [
                'no' => 'Не очищать',
                'mail' => 'Очистить почту',
                'phone' => 'Очистить телефоны',
                'all' => 'Очистить всё'
            ]
        ];

    $fields['error_log'] =
        [
            'Name' => "Сообщение об ошибки",
            'Type' => 'bool',
            'Required' => 'Y'
        ];


    foreach ($fields as $k=>$v)
    {
        if(empty($v['Name']))
            $fields[$k]['Name'] = $k;
    }


//    $install = $rest->call(
//        'bizproc.activity.add',
//        [
//            'CODE' => 'A.COMPANY_CHANGE',
//            'USE_SUBSCRIPTION' => 'Y',
//            'HANDLER' => $handlerBackUrl,
//            'AUTH_USER_ID'=> 1,
//            'NAME' => 'Изменение компании',
//            'PROPERTIES' => $fields,
//            'RETURN_PROPERTIES' => [
//                'success' => [
//                    'Name' => 'Успешно',
//                    'Type' => 'String'
//                ],
//            ],
//            'DOCUMENT_TYPE' => ['crm', 'CCrmDocumentCompany', 'COMPANY']
//        ]
//    );


    $install = $rest->call(
        'bizproc.robot.add',
        [
            'CODE' => 'R.COMPANY_CHANGE',
            'USE_SUBSCRIPTION' => 'Y',
            'HANDLER' => $handlerBackUrl,
            'AUTH_USER_ID'=> 1,
            'NAME' => 'Изменение компании',
            'PROPERTIES' => $fields,
            'RETURN_PROPERTIES' => [
                'success' => [
                    'Name' => 'Успешно',
                    'Type' => 'String'
                ],
            ],
            'DOCUMENT_TYPE' => ['crm', 'CCrmDocumentCompany', 'COMPANY']
        ]
    );


};

$return['handler'] = function($rest)
{
    $id = $_POST['properties']['ID'];
    $clear_mail_and_phons = $_POST['properties']['clear_mail_and_phons'];
    $error_log = $_POST['properties']['error_log'];
    $data = [];
    foreach ($_POST['properties'] as $key => $value) {
        if ($value && ($key != 'ID' && $key != 'clear_mail_and_phons' && $key != 'error_log')) {
            if (is_array($value)) {
                $arr = [];
                foreach ($value as $key1 => $value1) {
                    $arr[] = ['VALUE' => $value1, "VALUE_TYPE" => ""];
                }
                $data[$key] = $arr;
            } else {
                $data[$key] = $value;
            }
        }
    }

    $lead = $rest->call(
        'crm.company.get',
        [
            'id' => $id,
        ]
    );

    if (is_array($lead['result']['PHONE']) && ($clear_mail_and_phons == 'phone' || $clear_mail_and_phons == 'all')){
        $arDeletePhone = [];
        foreach ($lead['result']['PHONE'] as $phone) {
            $arDeletePhone[] = array("ID" => $phone['ID'], 'VALUE' => '');
        }
        $resultContactChange = $rest->call(
            'crm.company.update',
            [
                'id' => $id,
                'fields' => [
                    'PHONE' => $arDeletePhone
                ]
            ]
        );
    }

    if (is_array($lead['result']['EMAIL']) && ($clear_mail_and_phons == 'mail' || $clear_mail_and_phons == 'all')){
        $arDeleteMail = [];
        foreach ($lead['result']['EMAIL'] as $mail) {
            $arDeleteMail[] = array("ID" => $mail['ID'], 'VALUE' => '');
        }
        $resultContactChange = $rest->call(
            'crm.company.update',
            [
                'id' => $id,
                'fields' => [
                    'EMAIL' => $arDeleteMail
                ]
            ]
        );
    }

    $contact = $rest->call(
        'crm.company.update',
        [
            'id' => $id,
            'fields' => $data,
            'params' => [
                'REGISTER_SONET_EVENT' => 'Y'
            ]
        ]
    );


    $params = array(
        "auth" => $_REQUEST['auth']["access_token"],
        "event_token" => $_REQUEST["event_token"],
        "log_message" => '',
        "return_values" => ['success' => 'true']
    );

    $endpoint = $_REQUEST['auth']['client_endpoint'];

    $response = $callB24Method = $rest->call(
        'bizproc.event.send',
        $params
    );


// вебхук для отправки ответа
//    function callB24Method($bitrix, $method, $params)
//    {
//        $curl = curl_init($bitrix . $method . '.json');
//
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($curl, CURLOPT_POST, true);
//        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
//
//        $response = curl_exec($curl);
//        $response = json_decode($response, true);
//
//        return $response;
//    }

   // $response = callB24Method($endpoint,'bizproc.event.send', $params);

// Отправляю логи в случае ошибки
    if ($response['error_description'] && $error_log == 'Y') {
        $params = array(
            "auth" => $_REQUEST['auth']["access_token"],
            "event_token" => $_REQUEST["event_token"],
            "log_message" => $response['error_description'],
        );

        $response = $callB24Method = $rest->call(
            'bizproc.event.send',
            $params
        );
        //callB24Method($endpoint,'bizproc.event.send', $params);
    }

};




$return['data'] =
    [
        'activityDescription' =>  "Изменение полей компании по ID компании.",
        'activityCode' =>  "COMPANY_CHANGE",
        'activityName' =>  "Компания: изменение полей",
        'activityMulti' => true,
    ];




return $return;