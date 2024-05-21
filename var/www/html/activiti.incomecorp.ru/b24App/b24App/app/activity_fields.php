<?php

$return['install'] = function($rest)
{
    $handlerBackUrl = "https://activiti.incomecorp.ru/b24-handler/";

    $delite =$rest->call(
        'bizproc.activity.delete',
        [
            'CODE' => 'A.ACTIVITY_FIELDS',
        ]
    );

    $delite =$rest->call(
        'bizproc.robot.delete',
        [
            'CODE' => 'R.ACTIVITY_FIELDS',
        ]
    );


    $fields = $rest->call(
        'crm.activity.fields',
        [
        ]
    );


    $fields = $fields['result'];
    $unset = [];

    unset($fields['ID']);
    foreach ($fields as $keyS => $valueS) {
        foreach ($valueS as $key => $value) {
            if ($key == 'type' && $value == 'enum') {
                foreach ($fields[$keyS]['values'] as $k => $v) {
                    $fields[$keyS]['Options'][$k] = $v;
                }
            }
            if ($key == 'title') {
                unset($fields[$keyS][$key]);
                if (empty($value)) {
                    $unset[] = $keyS;
                } else {
                    $fields[$keyS]['Name'] = $value;
                }
            }
            if ($key == 'type') {
                unset($fields[$keyS][$key]);
                if ($value != 'enum') {
                    $fields[$keyS]['Type'] = $value;
                } else {
                    $fields[$keyS]['Type'] = 'select';
                }
            }
            if ($key == 'default' || $key == 'values' || $key == 'required' || $key == 'title') {
                unset($fields[$keyS][$key]);
            }
        }
    }

    foreach ($unset as $uns) {
        unset($fields[$uns]);
    }

    $properties = [
        'id' => [
            'Name' => 'id дела',
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

//    $install = $rest->call(
//        'bizproc.activity.add',
//        [
//            'CODE' => 'A.ACTIVITY_FIELDS',
//            'USE_SUBSCRIPTION' => 'Y',
//            'HANDLER' =>  $handlerBackUrl,
//            'AUTH_USER_ID'=> 1,
//            'NAME' => 'Возвращает значения полей дела',
//            'PROPERTIES' => $properties,
//            'RETURN_PROPERTIES' => $fields,
//            'DOCUMENT_TYPE' => ['crm', 'CCrmDocumentCompany', 'COMPANY']
//        ]
//    );

    $install = $rest->call(
        'bizproc.robot.add',
        [
            'CODE' => 'R.ACTIVITY_FIELDS',
            'USE_SUBSCRIPTION' => 'Y',
            'HANDLER' =>  $handlerBackUrl,
            'AUTH_USER_ID'=> 1,
            'NAME' => 'Возвращает значения полей дела',
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

//    function search ($id) {
//        return CRest::call(
//            'crm.activity.get',
//            [
//                'id' => $id
//            ]
//        );
//    }


    $update =  $rest->call(
        'crm.activity.get',
        [
            'id' => $id
        ]
    )['result'];


   // $update = search($id)['result'];


    $endpoint = $_REQUEST['auth']['client_endpoint'];

    $params = array(
        "auth" => $_REQUEST['auth']["access_token"],
        "event_token" => $_REQUEST["event_token"],
        "log_message" => '',
        "return_values" => $update
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
        'activityDescription' =>  "Возвращает значения полей дела",
        'activityCode' =>  "ACTIVITY_FIELDS",
        'activityName' =>  "Поля дела *beta",
        'activityMulti' => true,
    ];


return $return;
