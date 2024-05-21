<?php


$return['install'] = function($rest)
{
    $handlerBackUrl = "https://activiti.incomecorp.ru/b24-handler/";


    $delite = $rest->call(
        'bizproc.activity.delete',
        [
            'CODE' => 'A.SMART_PROCESS_VECTOR',
        ]
    );

    $delite = $rest->call(
        'bizproc.robot.delete',
        [
            'CODE' => 'R.SMART_PROCESS_VECTOR',
        ]
    );


    $properties = [
        'id' => [
            'Name' => 'ID процесса',
            'Type' => 'int',
            'Required' => 'Y'
        ],
        'name' =>
            [
                'Name' => "Имя направления",
                'Type' => 'string',
                'Required' => 'Y'
            ],
        'default' =>
            [
                'Name' => 'По умолчанию',
                'Type' => 'bool',
                'Required' => 'Y'
            ],
        'error_log' =>
            [
                'Name' => "Сообщение об ошибки",
                'Type' => 'bool',
                'Required' => 'Y'
            ]
    ];

    $fields =
        [
            'return' => [
                'Name' => 'Результат операции',
                'Type' => 'string',
            ]
        ];

//    $install = $rest->call(
//        'bizproc.activity.add',
//        [
//            'CODE' => 'A.SMART_PROCESS_VECTOR',
//            'USE_SUBSCRIPTION' => 'Y',
//            'HANDLER' => $handlerBackUrl,
//            'AUTH_USER_ID'=> 1,
//            'NAME' => 'Создает новое направление смарт пророцесса',
//            'PROPERTIES' => $properties,
//            'RETURN_PROPERTIES' => $fields,
//            'DOCUMENT_TYPE' => ['crm', 'CCrmDocumentCompany', 'COMPANY']
//        ]
//    );

    $install = $rest->call(
        'bizproc.robot.add',
        [
            'CODE' => 'R.SMART_PROCESS_VECTOR',
            'USE_SUBSCRIPTION' => 'Y',
            'HANDLER' => $handlerBackUrl,
            'AUTH_USER_ID'=> 1,
            'NAME' => 'Создает новое направление смарт пророцесса',
            'PROPERTIES' => $properties,
            'RETURN_PROPERTIES' => $fields,
            'DOCUMENT_TYPE' => ['crm', 'CCrmDocumentCompany', 'COMPANY']
        ]
    );

};

$return['handler'] = function($rest)
{

    $error_log = $_POST['properties']['error_log'];
    (int)$id = $_POST['properties']['id'];
    $name = $_POST['properties']['name'];
    $default = $_POST['properties']['default'];

    $smartProcess = $rest->call(
        'crm.type.get',
        [
            'id' => $id
        ]
    );

    $vector = $rest->call(
        'crm.category.add',
        [
            'entityTypeId' => $smartProcess['result']['type']['entityTypeId'],
            'fields' =>
                [
                    'id' => $id,
                    'name' => $name,
                    'isDefault' => $default,
                ]
        ]
    );

//Logger::deleteLog();
//Logger::updateLog($smartProcess);
//Logger::updateLog($vector);

    if($vector['result']['category']['id'])
        $return = $vector['result']['category']['id'];
    else
        $return = 'error';

    $endpoint = $_REQUEST['auth']['client_endpoint'];

    $params = array(
        "auth" => $_REQUEST['auth']["access_token"],
        "event_token" => $_REQUEST["event_token"],
        "log_message" => '',
        "return_values" =>
            [
                'return' => $return
            ]
    );


    $response = $rest->call(
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
        $response = $rest->call(
            'bizproc.event.send',
            $params
        );
    }


};


$return['data'] =
    [
        'activityDescription' =>  "Создает новое направление смарт процесса",
        'activityCode' =>  "SMART_PROCESS_VECTOR",
        'activityName' =>  "Создает новое направление смарт процесса *beta",
        'activityMulti' => true,
    ];


return $return;