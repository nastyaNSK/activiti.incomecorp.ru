<?php

$return['install'] = function($rest)
{
    $handlerBackUrl = "https://activiti.incomecorp.ru/b24-handler/";

    $delite = $rest->call(
        'bizproc.activity.delete',
        [
            'CODE' => 'A.ACTIVITY_CHANGE',
        ]
    );



    $delite = $rest->call(
        'bizproc.robot.delete',
        [
            'CODE' => 'R.ACTIVITY_CHANGE',
        ]
    );


    $fields = $rest->call(
        'crm.activity.fields',
        [
        ]
    );

    $owner = $rest->call(
        "crm.enum.ownertype",
        []
    );

    $owner_map = [];
    foreach ($owner['result'] as $ow) {
        $owner_map[$ow['ID']] = $ow['NAME'];
    }


    $fields = $fields['result'];
    $unset = [];

    foreach ($fields as $keyS => $valueS) {
        foreach ($valueS as $key => $value) {

            if ($key == 'title' && !($value == 'ID' || $value == 'ID владельца' || $value == 'Тип владельца' || $value == 'Тема' || $value == 'Начало'
                    || $value == 'Срок' || $value == 'Срок исполнения' || $value == 'Ответственный' || $value == 'Параметр уведомления' || $value == 'Описание'
                    || $value == 'Место' || $value == 'Внешний код' || $value == 'Внешний источник' || $value == 'Автозаполнение'
                ))
            {
                $unset[] = $keyS;
            }
            if ($key == 'isReadOnly' && $value == 1 && $keyS != 'ID') {
                $unset[] = $keyS;
            }
            if ($value == 'Тип владельца' && $key == 'title')
            {
                $fields[$keyS]['type'] = 'select';
                $fields[$keyS]['Options'] = $owner_map;

            }
            if ($key == 'title')
            {
                if (isset($fields[$keyS][$key])) {
                    $fields[$keyS]['Name'] = $fields[$keyS][$key];
                }
                if (!isset($fields[$keyS]['Name'])) {
                    $fields[$keyS]['Name'] = $keyS;
                }
                unset($fields[$keyS][$key]);
            }
            elseif ($key == 'isMultiple')
            {
                $fields[$keyS]['Multiple'] = $fields[$keyS][$key];
                unset($fields[$keyS][$key]);
            }
            elseif ($key == 'isRequired' && $keyS == 'ID') {
                $fields[$keyS]['Required'] = 'Y';
                unset($fields[$keyS][$key]);
            }
            elseif ($key != 'type')
            {
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

    $fields['ID']['Name'] = "ID дела";

//    $install = $rest->call(
//        'bizproc.activity.add',
//        [
//            'CODE' => 'A.ACTIVITY_CHANGE',
//            'USE_SUBSCRIPTION' => 'Y',
//            'HANDLER' => $handlerBackUrl,
//            'AUTH_USER_ID'=> 1,
//            'NAME' => 'Изменение дела',
//            'PROPERTIES' => $fields,
//            'RETURN_PROPERTIES' => [
//                'Ok' => [
//                    'Name' => 'Ответ',
//                    'Type' => 'String'
//                ]
//            ],
//            'DOCUMENT_TYPE' => ['crm', 'CCrmDocumentCompany', 'COMPANY']
//        ]
//    );



    $install = $rest->call(
        'bizproc.robot.add',
        [
            'CODE' => 'R.ACTIVITY_CHANGE',
            'USE_SUBSCRIPTION' => 'Y',
            'HANDLER' => $handlerBackUrl,
            'AUTH_USER_ID'=> 1,
            'NAME' => 'Изменение дела',
            'PROPERTIES' => $fields,
            'RETURN_PROPERTIES' => [
                'Ok' => [
                    'Name' => 'Ответ',
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
    unset($_POST['properties']['error_log']);
    unset($_POST['properties']['ID']);

//    function update ($data, $id) {
//        return CRest::call(
//            'crm.activity.update',
//            [
//                'id' => $id,
//                'fields' =>
//                    $data,
//            ]
//        );
//    }

    $data = [];
    foreach ($_POST['properties'] as $key => $value) {
        if ($value) {
            $data[$key] = $value;
        }
    }


 //   $info = update($data, $id);
    $info = $rest->call(
        'crm.activity.update',
        [
            'id' => $id,
            'fields' =>
                $data,
        ]
    );

    $endpoint = $_REQUEST['auth']['client_endpoint'];

    $params = array(
        "auth" => $_REQUEST['auth']["access_token"],
        "event_token" => $_REQUEST["event_token"],
        "log_message" => '',
        "return_values" => ['Ok' => 'Поля дела были успешно изменены']
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
        'activityDescription' =>  "Изменение дела",
        'activityCode' =>  "ACTIVITY_CHANGE",
        'activityName' =>  "Изменение дела *beta",
        'activityMulti' => true,
    ];


return $return;