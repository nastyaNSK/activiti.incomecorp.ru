<?php

$return['install'] = function($rest) {

    $handlerBackUrl = "https://activiti.incomecorp.ru/b24-handler/";

    $delite = $rest->call(
        'bizproc.activity.delete',
        [
            'CODE' => 'A.COMPANY_SEARCH',
        ]
    );

    $delite = $rest->call(
        'bizproc.robot.delete',
        [
            'CODE' => 'R.COMPANY_SEARCH',
        ]
    );

    $fields = $rest->call(
        'crm.company.fields',
        [
        ]
    );

    $fields = $fields['result'];
    $copy = $fields['result'];

    unset($fields['PHOTO']);
    unset($fields['LOGO']);
    $unset = [];

    foreach ($fields as $keyS => $valueS) {
        foreach ($valueS as $key => $value) {
            if ($key == 'isReadOnly' && $value == 1 && $keyS != 'ID') {
                $unset[] = $keyS;
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

            } elseif ($key == 'items') {
                $ret = [];
                foreach ($fields[$keyS][$key] as $sub) {
                    $ret[$sub['ID']] = $sub['VALUE'];
                }
                $fields[$keyS][$key] = $ret;
            }
            elseif ($key != 'type') {
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
        else
        {
            if (strripos($v['type'], 'crm_') !== false)
                $fields[$k]['type'] = 'int';
            switch ($v['type'])
            {
                case 'integer':
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
                case 'enumeration':
                    $fields[$k]['type'] = 'select';
                    $fields[$k]['Options'] = $fields[$k]['items'];
                    unset($fields[$k]['items']);
                    break;
            }
        }
    }


//    $install = $rest->call(
//        'bizproc.activity.add',
//        [
//            'CODE' => 'A.COMPANY_SEARCH',
//            'USE_SUBSCRIPTION' => 'Y',
//            'HANDLER' => $handlerBackUrl,
//            'AUTH_USER_ID'=> 1,
//            'NAME' => 'Поиск компании',
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
//            'DOCUMENT_TYPE' => ['crm', 'CCrmDocumentCompany', 'COMPANY']
//        ]
//    );

    $install = $rest->call(
        'bizproc.robot.add',
        [
            'CODE' => 'R.COMPANY_SEARCH',
            'USE_SUBSCRIPTION' => 'Y',
            'HANDLER' => $handlerBackUrl,
            'AUTH_USER_ID'=> 1,
            'NAME' => 'Поиск компании',
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
            'DOCUMENT_TYPE' => ['crm', 'CCrmDocumentCompany', 'COMPANY']
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
                        'crm.company.list',
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


    if(!empty($data)) {
        $result = $rest->call(
            'crm.company.list',
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

    $endpoint = $_REQUEST['auth']['client_endpoint'];
    $params = array(
        "auth" => $_REQUEST['auth']["access_token"],
        "event_token" => $_REQUEST["event_token"],
        "log_message" => '',
        "return_values" => ['ids' => $output, 'ids_iter' => $mainRes, 'first' => current($mainRes), 'last' => end($mainRes), 'cnt' => count($mainRes)]
    );
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

  //  $response = callB24Method($endpoint,'bizproc.event.send', $params);

// Отправляю логи в случае ошибки
    if ($response['error_description'] && $error_log == 'Y') {
        $params = array(
            "auth" => $_REQUEST['auth']["access_token"],
            "event_token" => $_REQUEST["event_token"],
            "log_message" => $response['error_description'],
        );
       // callB24Method($endpoint,'bizproc.event.send', $params);
        $response = $callB24Method = $rest->call(
            'bizproc.event.send',
            $params
        );
    }

};

$return['data'] =
    [
        'activityDescription' =>  "Поиск компании по всем полям компаний. Получение ID компаний по результатам поиска.",
        'activityCode' =>  "COMPANY_SEARCH",
        'activityName' =>  "Компания: поиск",
        'activityMulti' => true,
    ];




return $return;