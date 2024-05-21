<?php

$return['install'] = function($rest)
{
    $handlerBackUrl = "https://activiti.incomecorp.ru/b24-handler/";

    $delite = $rest->call(
        'bizproc.activity.delete',
        [
            'CODE' => 'A.LEAD_SEARCH',
        ]
    );
    $delite = $rest->call(
        'bizproc.activity.delete',
        [
            'CODE' => 'R.LEAD_SEARCH',
        ]
    );

    $delite = $rest->call(
        'bizproc.robot.delete',
        [
            'CODE' => 'R.LEAD_SEARCH',
        ]
    );

    $fields = $rest->call(
        'crm.lead.fields',
        [
        ]
    );

    $fields = $fields['result'];
    $unset = [];

    foreach ($fields as $keyS => $valueS) {
        foreach ($valueS as $key => $value) {
            if ($key == 'isReadOnly' && $value == 1 && $keyS != 'ID') {
                $unset[] = $keyS;
            }
            if ($key == 'title' || $key == 'listLabel') {
                $fields[$keyS]['Name'] = $fields[$keyS][$key];
                unset($fields[$keyS][$key]);
            } elseif ($key == 'isMultiple') {
                $fields[$keyS]['Multiple'] = $fields[$keyS][$key];
                unset($fields[$keyS][$key]);
            }elseif ($key == 'statusType'){
                if ($value == 'HONORIFIC') {
                    $fields[$keyS]['type'] = 'select';
                    $fields[$keyS]['Options'] = [
                        'HNR_RU_1' => 'Г-дн',
                        'HNR_RU_2' => 'Г-жа'
                    ];
                } elseif ($value == 'STATUS') {
                    $fields[$keyS]['type'] = 'select';
                    $fields[$keyS]['Options'] = [
                        'NEW' => 'Не обработан',
                        'IN_PROCESS' => 'В работе',
                        'PROCESSED' => 'Обработан',
                        'JUNK' => 'Некачественный лид',
                        'CONVERTED' => 'Качественный лид'
                    ];
                }
                elseif ($value == 'SOURCE') {
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
            } elseif ($key != 'type') {
                unset($fields[$keyS][$key]);
            }
        }
    }
    foreach ($unset as $uns) {
        unset($fields[$uns]);
    }

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

    foreach ($fields as $k=>$v)
    {
        if(empty($v['Name']))
            $fields[$k]['Name'] = $k;

        if(!$v['type'])
        {
            unset($fields[$k]);
        }
        else
        {
            if (strripos($v['type'], 'crm_') !== false)
                $fields[$k]['type'] = 'int';
            switch ($v['type'])
            {
                case 'integer':
                case 'enumeration':
                case 'iblock_section':
                case 'employee':
                case 'crm':
                case 'resourcebooking':
                    $fields[$k]['type'] = 'int';
                    break;
                case 'char':
                case 'crm_multifield':
                case 'location':
                case 'url':
                case 'address':
                case 'file':
                    $fields[$k]['type'] = 'string';
                    break;
                case 'boolean':
                    $fields[$k]['type'] = 'bool';
                    break;
            }
        }
    }
//    $install = $rest->call(
//        'bizproc.activity.add',
//        [
//            'CODE' => 'A.LEAD_SEARCH',
//            'USE_SUBSCRIPTION' => 'Y',
//            'HANDLER' => $handlerBackUrl,
//            'AUTH_USER_ID'=> 1,
//            'NAME' => 'Поиск лида',
//            'PROPERTIES' => $fields,
//            'RETURN_PROPERTIES' => [
//                'ids' => [
//                    'Name' => 'id в строку',
//                    'Type' => 'String'
//                ],
//                'ids_iter' => [
//                    'Name' => 'id  для итератератора',
//                    'Type' => 'String',
//                    'Multiple' => 'Y'
//                ],
//                'first' => [
//                    'Name' => 'Первый элемент списка',
//                    'Type' => 'String',
//                ],
//                'last' => [
//                    'Name' => 'Последний элемент списка',
//                    'Type' => 'String'
//                ],
//                'cnt' => [
//                    'Name' => 'Количество найденых элементов',
//                    'Type' => 'String'
//                ]
//            ],
//            'DOCUMENT_TYPE' => ['crm', 'CCrmDocumentLead', 'LEAD']
//        ]
//    );

    $install = $rest->call(
        'bizproc.robot.add',
        [
            'CODE' => 'R.LEAD_SEARCH',
            'USE_SUBSCRIPTION' => 'Y',
            'HANDLER' => $handlerBackUrl,
            'AUTH_USER_ID'=> 1,
            'NAME' => 'Поиск лида',
            'PROPERTIES' => $fields,
            'RETURN_PROPERTIES' => [
                'ids' => [
                    'Name' => 'id в строку',
                    'Type' => 'String'
                ],
                'ids_iter' => [
                    'Name' => 'id  для итератератора',
                    'Type' => 'String',
                    'Multiple' => 'Y'
                ],
                'first' => [
                    'Name' => 'Первый элемент списка',
                    'Type' => 'String',
                ],
                'last' => [
                    'Name' => 'Последний элемент списка',
                    'Type' => 'String'
                ],
                'cnt' => [
                    'Name' => 'Количество найденых элементов',
                    'Type' => 'String'
                ]
            ],
            'DOCUMENT_TYPE' => ['crm', 'CCrmDocumentLead', 'LEAD']
        ]
    );

};

$return['handler'] = function($rest)
{
    $id = $_POST['properties']['ID'];
    $error_log = $_POST['properties']['error_log'];

    $data = [];
    $many = [];
    $mainRes = [];
    $manyFlag = false;

    foreach ($_POST['properties'] as $key => $value) {
        if ($value && $key != 'error_log' ) {
            if (is_array($value)) {
                foreach ($value as $key1 => $value1) {
                    if ($key == 'PHONE') {
                        $value1 = preg_replace("/[^+0-9]/", '', $value1);
                    }
                    $tmp = $rest->call(
                        'crm.lead.list',
                        [
                            'filter' =>
                                [$key => $value1],
                            'select' => [
                                'ID',
                            ]
                        ]
                    );
                    if (empty($many)) {
                        $manyFlag = true;
                        foreach ($tmp['result'] as $k => $v) {
                            $many[] = $v['ID'];
                        }
                    } else {
                        $manyFlag = true;
                        $compair = [];
                        foreach ($tmp['result'] as $k => $v) {
                            $compair[] = $v['ID'];
                        }
                        $many = array_intersect($many, $compair);
                    }
                }
            } else {
                $data[$key] = $value;
            }
        }
    }

    foreach ($data as $k => $v) {
        if ($k == 'ASSIGNED_BY_ID'){
            $str = explode('_', $v);
            $data[$k] = $str[1];
        }
    }

    if(!empty($data)) {
        $result = $rest->call(
            'crm.lead.list',
            [
                'filter' =>
                    $data,
                'select' => [
                    'ID',
                ]
            ]
        );

        foreach ($result['result'] as $k => $v) {
            $mainRes[] = $v['ID'];
        }

        if (!empty($many) && !empty($mainRes)) {
            $mainRes = array_intersect($many, $mainRes);
        } elseif (!empty($many)) {
            foreach ($many as $val) {
                $mainRes[] = $val;
            }
        }
    } elseif (!empty($many)) {
        foreach ($many as $val) {
            $mainRes[] = $val;
        }
    }

    if (empty($many) && $manyFlag) {
        $mainRes = [];
    }

    $output = '';
    foreach ($mainRes as $key => $value) {
        $output = $output . $value . ', ';
    }
    if ($output) {
        $output = substr($output, 0, -2);
    }


    $params = array(
        "auth" => $_REQUEST['auth']["access_token"],
        "event_token" => $_REQUEST["event_token"],
        "log_message" => '',
        "return_values" => ['ids' => $output, 'ids_iter' => $mainRes, 'first' => current($mainRes), 'last' => end($mainRes), 'cnt' => count($mainRes)]
    );

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
        'activityDescription' =>  "Поиск лида по всем полям лида. Получение ID лидов по результатам поиска.",
        'activityCode' =>  "LEAD_SEARCH",
        'activityName' =>  "Лид: поиск",
        'activityMulti' => true,
    ];




return $return;