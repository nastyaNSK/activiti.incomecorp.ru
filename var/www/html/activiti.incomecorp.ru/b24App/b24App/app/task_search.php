<?php


$return['install'] = function($rest)
{
    $handlerBackUrl = "https://activiti.incomecorp.ru/b24-handler/";


    $delite = $rest->call(
        'bizproc.activity.delete',
        [
            'CODE' => 'A.TASK_SEARCH',
        ]
    );

    $delite = $rest->call(
        'bizproc.robot.delete',
        [
            'CODE' => 'R.TASK_SEARCH',
        ]
    );

    $fields = $rest->call(
        'tasks.task.getFields',
        [
        ]
    );


    $fields = $fields['result']['fields'];
    $unset = [];
    if(isset($fields['ID']))
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

    $fields['error_log'] =
        [
            'Name' => "Сообщение об ошибки",
            'Type' => 'bool',
            'Required' => 'Y'
        ];

   // file_put_contents('test.txt', print_r($fields, true));

//    $install = $rest->call(
//        'bizproc.activity.add',
//        [
//            'CODE' => 'A.TASK_SEARCH',
//            'USE_SUBSCRIPTION' => 'Y',
//            'HANDLER' => $handlerBackUrl,
//            'AUTH_USER_ID'=> 1,
//            'NAME' => 'Поиск задач',
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
            'CODE' => 'R.TASK_SEARCH',
            'USE_SUBSCRIPTION' => 'Y',
            'HANDLER' => $handlerBackUrl,
            'AUTH_USER_ID'=> 1,
            'NAME' => 'Поиск задач',
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


    $error_log = $_POST['properties']['error_log'];
    unset($_POST['properties']['error_log']);

//    function filter ($data) {
//        return CRest::call(
//            'tasks.task.list',
//            [
//                'filter' =>
//                    $data,
//                'select' => [
//                    'ID',
//                ]
//            ]
//        );
//    }

    $data = [];

    foreach ($_POST['properties'] as $key => $value) {
        if (!empty($value)) {
            $data[$key] = $value;
        }
    }

    //$info = filter($data);
    $info = $rest->call(
        'tasks.task.list',
        [
            'filter' =>
                $data,
            'select' => [
                'ID',
            ]
        ]
    );

    $mainRes = $info['result']['tasks'];

    $output = '';
    foreach ($mainRes as $key => $value) {
        $output = $output . $value['id'] . ', ';
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
        'activityDescription' =>  "Поиск задач",
        'activityCode' =>  "TASK_SEARCH",
        'activityName' =>  "Поиск задач *beta",
        'activityMulti' => true,
    ];


return $return;