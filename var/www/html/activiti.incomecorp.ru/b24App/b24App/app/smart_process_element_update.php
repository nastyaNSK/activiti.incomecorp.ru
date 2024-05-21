<?php

$return['install'] = function($rest)
{
    $handlerBackUrl = "https://activiti.incomecorp.ru/b24-handler/";
    $placementUrl = "https://activiti.incomecorp.ru/placement/?placement=smart_process_element_update";

    $delite = $rest->call(
        'bizproc.activity.delete',
        [
            'CODE' => 'A.SMART_PROCESS_ELEMENT_UPDATE',
        ]
    );

    $delite = $rest->call(
        'bizproc.robot.delete',
        [
            'CODE' => 'R.SMART_PROCESS_ELEMENT_UPDATE',
        ]
    );

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
//            'CODE' => 'A.SMART_PROCESS_ELEMENT_UPDATE',
//            'USE_SUBSCRIPTION' => 'Y',
//            'HANDLER' => $handlerBackUrl,
//            'AUTH_USER_ID'=> 1,
//            'NAME' => 'Обновляет элемент смарт процесса',
//            'PROPERTIES' => [],
//            'RETURN_PROPERTIES' => $fields,
//            'DOCUMENT_TYPE' => ['crm', 'CCrmDocumentCompany', 'COMPANY'],
//            'USE_PLACEMENT' => 'Y',
//            'PLACEMENT_HANDLER' => $placementUrl
//        ]
//    );

    $install = $rest->call(
        'bizproc.robot.add',
        [
            'CODE' => 'R.SMART_PROCESS_ELEMENT_UPDATE',
            'USE_SUBSCRIPTION' => 'Y',
            'HANDLER' => $handlerBackUrl,
            'AUTH_USER_ID'=> 1,
            'NAME' => 'Обновляет элемент смарт процесса',
            'PROPERTIES' => [],
            'RETURN_PROPERTIES' => $fields,
            'DOCUMENT_TYPE' => ['crm', 'CCrmDocumentCompany', 'COMPANY'],
            'USE_PLACEMENT' => 'Y',
            'PLACEMENT_HANDLER' => $placementUrl . "&robot=Y"
        ]
    );


};

$return['handler'] = function($rest)
{

    $domain = $_REQUEST['auth']['domain'];
    $event_token = $_REQUEST['event_token'];
    $separator = '|';
    $activity_name = explode($separator, $event_token)[1];
    $file = '/home/bitrix/ext_www/activiti.incomecorp.ru/b24App/b24App/placement/smart_process_element_update/data/' . $activity_name . $domain;

    $return = 'Activity not exist';

    if (file_exists($file))
    {
        $data = file_get_contents($file);
        $data = json_decode($data, true);

        $entityTypeId = $data['entity_type_id'];
        $id = $data['id'];
        unset($data['entity_type_id']);
        unset($data['id']);

        foreach ($data as $key => $value)
        {
            if (empty($value))
                unset($data[$key]);
        }

        $answer = $rest->call(
            'crm.item.update',
            [
                'entityTypeId' => $entityTypeId,
                'id' => $id,
                'fields' => $data
            ]
        );
        if ($answer['result']['item']['id'])
            $return = 'Element ID: ' . $answer['result']['item']['id'];
        else
            $return = $answer['error_description'];

    }

    $endpoint = $_REQUEST['auth']['client_endpoint'];

    $params = array(
        "auth" => $_REQUEST['auth']["access_token"],
        "event_token" => $_REQUEST["event_token"],
        "log_message" => '',
        "return_values" =>
            [
                'return' => (string)$return
            ]
    );

    $response = $rest->call(
        'bizproc.event.send',
        $params
    );



};


$return['data'] =
    [
        'activityDescription' =>  "Обновляет элемент смарт процесса",
        'activityCode' =>  "SMART_PROCESS_ELEMENT_UPDATE",
        'activityName' =>  "Обновляет элемент смарт процесса *beta",
        'activityMulti' => true,
    ];


return $return;