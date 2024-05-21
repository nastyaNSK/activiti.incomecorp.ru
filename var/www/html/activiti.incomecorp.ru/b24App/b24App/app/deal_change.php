<?

$return['install'] = function($rest)
{
    $handlerBackUrl = "https://activiti.incomecorp.ru/b24-handler/";

    $delite = $rest->call(
        'bizproc.activity.delete',
        [
            'CODE' => 'A.DEAL_CHANGE',
        ]
    );

    $delite = $rest->call(
        'bizproc.robot.delete',
        [
            'CODE' => 'R.DEAL_CHANGE',
        ]
    );

    $fields = $rest->call(
        'crm.deal.fields',
        [
        ]
    );

    $fields = $fields['result'];
//    file_put_contents(__DIR__ . '/deal.txt', print_r($fields, true));

    foreach ($fields as $keyS => $valueS) {
        foreach ($valueS as $key => $value) {
            if ($key == 'isReadOnly' && $value == 1 && $keyS != 'ID') {
                unset($fields[$keyS]);
            }
            if ($keyS == 'TYPE_ID' || $keyS == 'CATEGORY_ID' || $keyS == 'IS_RECURRING' || $keyS == 'IS_RETURN_CUSTOMER' || $keyS == 'IS_REPEATED_APPROACH' || $keyS == 'IS_MANUAL_OPPORTUNITY' || $keyS == 'OPENED' || $keyS == 'CLOSED' ) {
                unset($fields[$keyS]);
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

        if(!$v['type'])
        {
            unset($fields[$k]);
        }
        else
        {
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

        if (strripos($v['type'], 'crm_') !== false)
            $fields[$k]['type'] = 'int';
    }
//    file_put_contents(__DIR__ . '/dealcl.txt', print_r($fields, true));


//    $install = $rest->call(
//        'bizproc.activity.add',
//        [
//            'CODE' => 'A.DEAL_CHANGE',
//            'USE_SUBSCRIPTION' => 'Y',
//            'HANDLER' => $handlerBackUrl,
//            'AUTH_USER_ID'=> 1,
//            'NAME' => 'Изменение сделки',
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
            'CODE' => 'R.DEAL_CHANGE',
            'USE_SUBSCRIPTION' => 'Y',
            'HANDLER' => $handlerBackUrl,
            'AUTH_USER_ID'=> 1,
            'NAME' => 'Изменение сделки',
            'PROPERTIES' => $fields,
            'RETURN_PROPERTIES' => [
                'success' => [
                    'Name' => 'Успешно',
                    'Type' => 'String'
                ],
            ],
            'DOCUMENT_TYPE' => ['crm', 'CCrmDocumentDeal', 'DEAL']
        ]
    );


};

$return['handler'] = function($rest)
{
    $id = $_POST['properties']['ID'];
    $error_log = $_POST['properties']['error_log'];
    $data = [];
    foreach ($_POST['properties'] as $key => $value) {
        if ($value && ($key != 'ID' && $key != 'error_log')) {
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


    $contact = $rest->call(
        'crm.deal.update',
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
        'activityDescription' =>  "Изменение полей сделки по ID сделки.",
        'activityCode' =>  "DEAL_CHANGE",
        'activityName' =>  "Сделка: изменение полей",
        'activityMulti' => true,
    ];




return $return;