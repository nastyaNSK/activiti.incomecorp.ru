<?php


$return['install'] = function($rest)
{
    $handlerBackUrl = "https://activiti.incomecorp.ru/b24-handler/";


$delite = $rest->call(
    'bizproc.activity.delete',
    [
        'CODE' => 'A.SMART_PROCESS_STAGE',
    ]
);

    $delite = $rest->call(
        'bizproc.robot.delete',
        [
            'CODE' => 'R.SMART_PROCESS_STAGE',
        ]
    );


$properties = [
    'id' => [
            'Name' => 'ID процесса',
            'Type' => 'int',
            'Required' => 'Y'
    ],
    'vector_id' =>
    [
            'Name' => "ID направления",
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

//$install = $rest->call(
//    'bizproc.activity.add',
//    [
//        'CODE' => 'A.SMART_PROCESS_STAGE',
//        'USE_SUBSCRIPTION' => 'Y',
//        'HANDLER' => $handlerBackUrl,
//        'AUTH_USER_ID'=> 1,
//        'NAME' => 'Создает новую стадию смарт процесса',
//        'PROPERTIES' => $properties,
//        'RETURN_PROPERTIES' => $fields,
//        'DOCUMENT_TYPE' => ['crm', 'CCrmDocumentCompany', 'COMPANY']
//    ]
//);

    $install = $rest->call(
        'bizproc.robot.add',
        [
            'CODE' => 'R.SMART_PROCESS_STAGE',
            'USE_SUBSCRIPTION' => 'Y',
            'HANDLER' => $handlerBackUrl,
            'AUTH_USER_ID'=> 1,
            'NAME' => 'Создает новую стадию смарт процесса',
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
    (int)$vector_id = $_POST['properties']['vector_id'];

    $smartProcess = $rest->call(
        'crm.type.get',
        [
            'id' => $id
        ]
    );

    $status_list = $rest->call('crm.status.list',
            [
                'order' => ['SORT' => 'ASC'],
                'filter' => ['ENTITY_ID' => "DYNAMIC_" . $smartProcess['result']['type']['entityTypeId'] . "_STAGE_" . $vector_id]
            ]);

    $list = $status_list['result'];

    $status_id_prefixs = array();
    foreach ($list as $key => $value) {
        foreach ($value as $k => $v) {
            if ($k == 'STATUS_ID') {
                $status_id_prefix = explode(':' ,$v)[1];
                $status_id_prefixs[] = (int)$status_id_prefix;
            }
        }
    }
    $status_id_prefix_new = max($status_id_prefixs) + 1;

    $stage = $rest->call(
        'crm.status.add',
        [
            'fields' =>
                [
                    "COLOR" => "#1111AA",
                    "NAME" => $name,
                    "SORT" => 250,
                    "ENTITY_ID" => "DYNAMIC_" . $smartProcess['result']['type']['entityTypeId'] . "_STAGE_" . $vector_id,
                    "STATUS_ID" => "DT" . $smartProcess['result']['type']['entityTypeId'] . "_" . $vector_id . ":" . $status_id_prefix_new,
                ]
        ]
    );
    if($stage['error_description'])
        $return = $stage['error_description'];
    else
        $return = $stage['result'];




//    Logger::deleteLog();
//    Logger::updateLog($smartProcess);
//    Logger::updateLog($stage);


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
        'activityDescription' =>  "Создает новую стадию смарт процесса",
        'activityCode' =>  "SMART_PROCESS_STAGE",
        'activityName' =>  "Создает новую стадию смарт процесса *beta",
        'activityMulti' => true,
    ];


return $return;