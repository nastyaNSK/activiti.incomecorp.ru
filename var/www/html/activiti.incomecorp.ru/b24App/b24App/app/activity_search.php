<?php

$return['install'] = function($rest)
{
    $handlerBackUrl = "https://activiti.incomecorp.ru/b24-handler/";

    $delite = $rest->call(
        'bizproc.activity.delete',
        [
            'CODE' => 'A.ACTIVITY_SEARCH',
        ]
    );

    $delite = $rest->call(
        'bizproc.robot.delete',
        [
            'CODE' => 'R.ACTIVITY_SEARCH',
        ]
    );

    $fields = $rest->call(
        'crm.activity.fields',
        [
        ]
    );

    $type = $rest->call(
        "crm.enum.activitytype",
        []
    );

    $type_map = [];
    foreach ($type['result'] as $tp) {
        if ($tp['ID'] != 0)
            $type_map[$tp['ID']] = $tp['NAME'];
    }

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

            if ($key == 'title' && !( $value == 'ID владельца' || $value == 'Тип владельца' || $value == 'Тема' || $value == 'Начало'
                    || $value == 'Срок' || $value == 'Срок исполнения' || $value == 'Ответственный' || $value == 'Параметр уведомления' || $value == 'Описание'
                    || $value == 'Место' || $value == 'Внешний код' || $value == 'Внешний источник' || $value == 'Автозаполнение' || $value == 'Тип'
                    || $value == 'Выполнено'
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
            if ($value == 'Тип' && $key == 'title')
            {
                $fields[$keyS]['type'] = 'select';
                $fields[$keyS]['Options'] = $type_map;

            }
            if ($value == 'Выполнено' && $key == 'title')
            {
                $fields[$keyS]['type'] = 'select';
                $fields[$keyS]['Options'] = [
                    'N' => 'Нет',
                    'Y' => 'Да'
                ];

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



//    $install = $rest->call(
//        'bizproc.activity.add',
//        [
//            'CODE' => 'A.ACTIVITY_SEARCH',
//            'USE_SUBSCRIPTION' => 'Y',
//            'HANDLER' => $handlerBackUrl,
//            'AUTH_USER_ID'=> 1,
//            'NAME' => 'Поиск дел',
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
            'CODE' => 'R.ACTIVITY_SEARCH',
            'USE_SUBSCRIPTION' => 'Y',
            'HANDLER' => $handlerBackUrl,
            'AUTH_USER_ID'=> 1,
            'NAME' => 'Поиск дел',
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
    unset($_POST['properties']['error_log']);
    unset($_POST['properties']['ID']);


    $params = [];
    foreach ($_POST['properties'] as $key => $value) {
        if ($value) {
            $params[$key] = $value;
        }
    }

//    function search ($params) {
//        return CRest::call(
//            'crm.activity.list',
//            [
//                'filter' => $params,
//                'select' =>
//                    ["ID"],
//            ]
//        );
//    }



   // $update = search($params)['result'];
    $update = $rest->call(
    'crm.activity.list',
            [
                'filter' => $params,
                'select' =>
                    ["ID"],
            ]
        )['result'];

    $return = [];
    foreach ($update as $value) {
        $return[] = $value['ID'];
    }

    $mark = false;
    foreach ($_POST['properties'] as $propK => $propV) {
        if (!empty($propV)) {
            $mark = true;
        }
    }

    if (!$mark)
        $return = [];


    $output = '';
    foreach ($return as $key => $value) {
        $output = $output . $value . ', ';
    }
    if ($output) {
        $output = substr($output, 0, -2);
    }

    $endpoint = $_REQUEST['auth']['client_endpoint'];
    $mass = [$output, $return];
    $params = array(
        "auth" => $_REQUEST['auth']["access_token"],
        "event_token" => $_REQUEST["event_token"],
        "log_message" => '',
        "return_values" => ['ids' => $output, 'ids_iter' => $return, 'first' => current($return), 'last' => end($return), 'cnt' => count($return)]
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
        'activityDescription' =>  "Поиск дел",
        'activityCode' =>  "ACTIVITY_SEARCH",
        'activityName' =>  "Поиск дел *beta",
        'activityMulti' => true,
    ];


return $return;