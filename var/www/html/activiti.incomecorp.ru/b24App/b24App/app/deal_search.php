<?


$return['install'] = function($rest)
{
    $handlerBackUrl = "https://activiti.incomecorp.ru/b24-handler/";
    $delite = $rest->call(
        'bizproc.activity.delete',
        [
            'CODE' => 'A.SEARCH_DEAL',
        ]
    );
    $delite = $rest->call(
        'bizproc.robot.delete',
        [
            'CODE' => 'R.SEARCH_DEAL',
        ]
    );

    $fields = $rest->call(
        'crm.deal.fields',
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
            if ($keyS == 'TYPE_ID' || $keyS == 'CATEGORY_ID' || $keyS == 'IS_RECURRING' || $keyS == 'IS_RETURN_CUSTOMER' || $keyS == 'IS_REPEATED_APPROACH' || $keyS == 'IS_MANUAL_OPPORTUNITY' || $keyS == 'OPENED' || $keyS == 'CLOSED' ) {
                unset($fields[$keyS]);
            }
            if ($key == 'title' || $key == 'listLabel') {
                $fields[$keyS]['Name'] = $fields[$keyS][$key];
                unset($fields[$keyS][$key]);
            } elseif ($key == 'isMultiple') {
                $fields[$keyS]['Multiple'] = $fields[$keyS][$key];
                unset($fields[$keyS][$key]);
            }elseif ($key == 'statusType'){
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
                if ($value == 'DEAL_STAGE') {
                    $fields[$keyS]['type'] = 'select';
                    $fields[$keyS]['Options'] = [
                        'NEW' => 'Общее..Новая',
                        'PREPARATION' => 'Общее..Подготовка документов',
                        'PREPAYMENT_INVOICE' => 'Общее..Счёт на предоплату',
                        'EXECUTING' => 'Общее..В работе',
                        'FINAL_INVOICE' => 'Общее..Финальный счёт',
                        'WON' => 'Общее..Сделка успешна',
                        'LOSE' => 'Общее..Сделка провалена',
                        'APOLOGY' => 'Общее..Анализ причин провала',
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
//            'CODE' => 'A.DEAL_SEARCH',
//            'USE_SUBSCRIPTION' => 'Y',
//            'HANDLER' => $handlerBackUrl,
//            'AUTH_USER_ID'=> 1,
//            'NAME' => 'Поиск сделки',
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
//            'DOCUMENT_TYPE' => ['crm', 'CCrmDocumentDeal', 'DEAL']
//        ]
//    );

    $install = $rest->call(
        'bizproc.robot.add',
        [
            'CODE' => 'R.DEAL_SEARCH',
            'USE_SUBSCRIPTION' => 'Y',
            'HANDLER' => $handlerBackUrl,
            'AUTH_USER_ID'=> 1,
            'NAME' => 'Поиск сделки',
            'PROPERTIES' => $fields,
            'RETURN_PROPERTIES' => [
                'ids' => [
                    'Name' => 'id в строку',
                    'Type' => 'String'
                ],
                'ids_iter' => [
                    'Name' => 'id  для итератора',
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
                    'Name' => 'Количество найденных элементов',
                    'Type' => 'String'
                ]
            ],
            'DOCUMENT_TYPE' => ['crm', 'CCrmDocumentDeal', 'DEAL']
        ]
    );
};

$return['handler'] = function($rest)
{
    systems::lvd($_POST);
    $id = $_POST['properties']['ID'];
    $error_log = $_POST['properties']['error_log'];


    $data = [];
    $many = [];
    $mainRes = [];
    $manyFlag = false;


    foreach ($_POST['properties'] as $key => $value)
    {
        if ($value && $key != 'error_log' )
        {
            if (is_array($value))
            {
                foreach ($value as $key1 => $value1)
                {
                    if ($key == 'PHONE')
                    {
                        $value1 = preg_replace("/[^+0-9]/", '', $value1);
                    }
                    $tmp = $rest->call(
                        'crm.deal.list',
                        [
                            'filter' =>
                                [$key => $value1],
                            'select' => [
                                'ID',
                            ]
                        ]
                    );
                    if (empty($many))
                    {
                        $manyFlag = true;
                        foreach ($tmp['result'] as $k => $v)
                        {
                            $many[] = $v['ID'];
                        }
                    } else {
                        $manyFlag = true;
                        $compair = [];
                        foreach ($tmp['result'] as $k => $v)
                        {
                            $compair[] = $v['ID'];
                        }
                        $many = array_intersect($many, $compair);
                    }
                }
            }
            else
            {
                if ($key = 'ASSIGNED_BY_ID')
                {
                    $data[$key] = explode('_', $value)[1];
                }
                else
                {
                    $data[$key] = $value;
                }
            }
        }
    }


    if(!empty($data))
    {
        $result = $rest->call(
            'crm.deal.list',
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
        'activityDescription' =>  "Поиск сделки по всем полям сделки. Получение ID сделок по результатам поиска.",
        'activityCode' =>  "DEAL_SEARCH",
        'activityName' =>  "Сделка: поиск",
        'activityMulti' => true,
    ];




return $return;

