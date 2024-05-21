<?php

$return['install'] = function($rest)
{
    $handlerBackUrl = "https://activiti.incomecorp.ru/b24-handler/";

    $delite = $rest->call(
        'bizproc.activity.delete',
        [
            'CODE' => 'A.DEAL_GET_DATA',
        ]
    );

    $delite = $rest->call(
        'bizproc.robot.delete',
        [
            'CODE' => 'R.DEAL_GET_DATA',
        ]
    );

    $fields = $rest->call(
        'crm.deal.fields',
        [
        ]
    );

    $fields = $fields['result'];

    foreach ($fields as $keyS => $valueS) {
        foreach ($valueS as $key => $value) {
            if ($key == 'title' || $key == 'listLabel') {
                $fields[$keyS]['Name'] = $fields[$keyS][$key];
                unset($fields[$keyS][$key]);
            } else {
                if ($key != 'type' && $key != 'isMultiple') {
                    unset($fields[$keyS][$key]);
                }
            }
        }
    }

//    $install = $rest->call(
//        'bizproc.activity.add',
//        [
//            'CODE' => 'DEAL_GET_DATA',
//            'USE_SUBSCRIPTION' => 'Y',
//            'HANDLER' => $handlerBackUrl,
//            'AUTH_USER_ID'=> 1,
//            'NAME' => 'Получения данных по сделке',
//            'PROPERTIES' => [
//
//                'id' => [
//                    'Name' => 'ID Сделки',
//                    'Type' => 'int',
//                    'Required' => 'Y'
//                ],
//
//
//                'error_log' => [
//                    'Name' => "Сообщение об ошибки",
//                    'Type' => 'bool',
//                    'Required' => 'Y'
//                ],
//
//            ],
//
//            'RETURN_PROPERTIES' => $fields,
//            'DOCUMENT_TYPE' => ['crm', 'CCrmDocumentDeal', 'DEAL']
//        ]
//    );

    $install = $rest->call(
        'bizproc.robot.add',
        [
            'CODE' => 'R.DEAL_GET_DATA',
            'USE_SUBSCRIPTION' => 'Y',
            'HANDLER' => $handlerBackUrl,
            'AUTH_USER_ID'=> 1,
            'NAME' => 'Получения данных по сделке',
            'PROPERTIES' => [

                'id' => [
                    'Name' => 'ID Сделки',
                    'Type' => 'int',
                    'Required' => 'Y'
                ],


                'error_log' => [
                    'Name' => "Сообщение об ошибки",
                    'Type' => 'bool',
                    'Required' => 'Y'
                ],

            ],

            'RETURN_PROPERTIES' => $fields,
            'DOCUMENT_TYPE' => ['crm', 'CCrmDocumentDeal', 'DEAL']
        ]
    );

};

$return['handler'] = function($rest)
{
    $my_company_id = intVal($_REQUEST['properties']['id']);
    $error_log = $_REQUEST['properties']['error_log'];

    $deal = $rest->call(
        'crm.deal.get',
        [
            'id' => $my_company_id,
        ]
    );


// Убираю незаполненые значения
    $available_deals = [];
    foreach ($deal['result'] as $key => $value) {
        if($value) {
            $available_deals[$key] = $value;
        }
    }


// Формирую данные для отправки в битрикс
    $data = [];
    foreach ($available_deals as $key => $value) {
        if (is_array($available_deals[$key])) {
            $str ='';
            foreach ($available_deals[$key] as $elem) {
                $str = $str . $elem['VALUE'] . ', ';
            }
            $str = substr($str,0,-2);
            $data[$key] = $str;
        } else {
            $data[$key] = $available_deals[$key];
        }
    }


    $params = array(
        "auth" => $_REQUEST['auth']["access_token"],
        "event_token" => $_REQUEST["event_token"],
        "log_message" => '',
        "return_values" => $data
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
        'activityDescription' =>  "Получение информации из полей сделки по ID сделки.",
        'activityCode' =>  "DEAL_GET_DATA",
        'activityName' =>  "Сделка: получение полей",
        'activityMulti' => true,
    ];




return $return;