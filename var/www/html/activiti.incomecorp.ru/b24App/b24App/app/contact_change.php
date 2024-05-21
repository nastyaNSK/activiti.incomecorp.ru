<?php
$return['install'] = function($rest)
{
    $handlerBackUrl = "https://activiti.incomecorp.ru/b24-handler/";


    $delite = $rest->call(
        'bizproc.activity.delete',
        [
            'CODE' => 'A.CONTACT_CHANGE',
        ]
    );

    $delite = $rest->call(
        'bizproc.robot.delete',
        [
            'CODE' => 'R.CONTACT_CHANGE',
        ]
    );



    $fields = $rest->call(
        'crm.contact.fields',
        [
        ]
    );

    $fields = $fields['result'];


    foreach ($fields as $keyS => $valueS) {
        foreach ($valueS as $key => $value) {
            if ($key == 'isReadOnly' && $value == 1 && $keyS != 'ID') {
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
                if ($value == 'HONORIFIC') {
                    $fields[$keyS]['type'] = 'select';
                    $fields[$keyS]['Options'] = [
                        'HNR_RU_1' => 'Г-дн',
                        'HNR_RU_2' => 'Г-жа'
                    ];
                }
                if ($value == 'SOURCE') {
                    $fields[$keyS]['type'] = 'select';
                    $fields[$keyS]['Options'] = [
                        'CALL' => 'Звонок',
                        'EMAIL' => 'Электронная почта',
                        'WEB' => 'Веб-сайт',
                        'ADVERTISING' => 'Реклама',
                        'PARTNER' => 'Существующий клиент',
                        'RECOMMENDATION' => 'По рекомендации',
                        'TRADE_SHOW' => 'Выставка',
                        'WEBFORM' => 'CRM-форма',
                        'CALLBACK' => 'Обратный звонок',
                        'RC_GENERATOR' => 'Генератор продаж',
                        'STORE' => 'Интернет магазин',
                        'OTHER' => 'Другое'
                    ];
                }

                if($value == 'CONTACT_TYPE') {
                    $fields[$keyS]['type'] = 'select';
                    $fields[$keyS]['Options'] = [
                        'CLIENT' => 'Клиенты',
                        'SUPPLIER' => 'Поставщики',
                        'PARTNER' => 'Партнеры',
                        'OTHER' => 'Другое'
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
//            'CODE' => 'A.CONTACT_CHANGE',
//            'USE_SUBSCRIPTION' => 'Y',
//            'HANDLER' => $handlerBackUrl,
//            'AUTH_USER_ID'=> 1,
//            'NAME' => 'Изменение контакта',
//            'PROPERTIES' => $fields,
//            'RETURN_PROPERTIES' => [
//                'success' => [
//                    'Name' => 'Успешно',
//                    'Type' => 'String'
//                ],
//            ],
//            'DOCUMENT_TYPE' => ['crm', 'CCrmDocumentLead', 'LEAD']
//        ]
//    );

    $install = $rest->call(
        'bizproc.robot.add',
        [
            'CODE' => 'R.CONTACT_CHANGE',
            'USE_SUBSCRIPTION' => 'Y',
            'HANDLER' => $handlerBackUrl,
            'AUTH_USER_ID'=> 1,
            'NAME' => 'Изменение контакта',
            'PROPERTIES' => $fields,
            'RETURN_PROPERTIES' => [
                'success' => [
                    'Name' => 'Успешно',
                    'Type' => 'String'
                ],
            ],
            'DOCUMENT_TYPE' => ['crm', 'CCrmDocumentLead', 'LEAD']
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
        'crm.contact.get',
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
            'crm.contact.update',
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
            'crm.contact.update',
            [
                'id' => $id,
                'fields' => [
                    'EMAIL' => $arDeleteMail
                ]
            ]
        );
    }

    $contact = $rest->call(
        'crm.contact.update',
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

// вебхук для отправки ответа

    $response = $callB24Method = $rest->call(
        'bizproc.event.send',
        $params
    );

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
    }



};


$return['data'] =
    [
        'activityDescription' =>  "Изменение полей контакта по ID контакта.",
        'activityCode' =>  "CONTACT_CHANGE",
        'activityName' =>  "Контакт: изменение полей",
        'activityMulti' => true,
    ];




return $return;