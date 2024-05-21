<?php


$return['install'] = function($rest)
{
    $handlerBackUrl = "https://activiti.incomecorp.ru/b24-handler/";

    $delite = $rest->call(
        'bizproc.activity.delete',
        [
            'CODE' => 'A.SMART_PROCESS_STAGE_UPDATE',
        ]
    );

    $delite = $rest->call(
        'bizproc.robot.delete',
        [
            'CODE' => 'A.SMART_PROCESS_STAGE_UPDATE',
        ]
    );


    $properties = [
        'id' => [
            'Name' => 'ID стадии',
            'Type' => 'int',
            'Required' => 'Y'
        ],
        'name' =>
            [
                'Name' => 'Название стадии',
                'Type' => 'string',
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
//            'CODE' => 'A.SMART_PROCESS_STAGE_UPDATE',
//            'USE_SUBSCRIPTION' => 'Y',
//            'HANDLER' => $handlerBackUrl,
//            'AUTH_USER_ID'=> 1,
//            'NAME' => 'Изменяет стадию смарт процесса',
//            'PROPERTIES' => $properties,
//            'RETURN_PROPERTIES' => $fields,
//            'DOCUMENT_TYPE' => ['crm', 'CCrmDocumentCompany', 'COMPANY']
//        ]
//    );

    $install = $rest->call(
        'bizproc.robot.add',
        [
            'CODE' => 'R.SMART_PROCESS_STAGE_UPDATE',
            'USE_SUBSCRIPTION' => 'Y',
            'HANDLER' => $handlerBackUrl,
            'AUTH_USER_ID'=> 1,
            'NAME' => 'Изменяет стадию смарт процесса',
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


//$smartProcess = CRest::call(
//  'crm.status.list',
//  [
//      'order' =>
//        [
//            'SORT' => 'ASC'
//        ]
//  ]
//);

    $stage = $rest->call(
        'crm.status.update',
        [
            'id' => $id,
            'fields' =>
                [
                    "NAME" => $name,
                ]
        ]
    );

//Logger::deleteLog();
//Logger::updateLog($smartProcess);
//Logger::updateLog($stage);


    $endpoint = $_REQUEST['auth']['client_endpoint'];

    $params = array(
        "auth" => $_REQUEST['auth']["access_token"],
        "event_token" => $_REQUEST["event_token"],
        "log_message" => '',
        "return_values" =>
            [
                'return' => 'true'
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
        $response =$rest->call(
            'bizproc.event.send',
            $params
        );
    }


};


$return['data'] =
    [
        'activityDescription' =>  "Изменяет стадию смарт процесса",
        'activityCode' =>  "SMART_PROCESS_STAGE_UPDATE",
        'activityName' =>  "Изменяет стадию смарт процесса *beta",
        'activityMulti' => true,
    ];


return $return;