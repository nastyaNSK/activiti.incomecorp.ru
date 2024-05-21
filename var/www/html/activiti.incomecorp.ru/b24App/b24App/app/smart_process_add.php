<?php


$return['install'] = function($rest)
{
    $handlerBackUrl = "https://activiti.incomecorp.ru/b24-handler/";


    $delite = $rest->call(
        'bizproc.activity.delete',
        [
            'CODE' => 'A.SMART_PROCESS_ADD',
        ]
    );

    $delite = $rest->call(
        'bizproc.robot.delete',
        [
            'CODE' => 'R.SMART_PROCESS_ADD',
        ]
    );

    $fields = $rest->call(
        'crm.type.fields',
        [
        ]
    );
    $fields = $fields['result']['fields'];

    $unset = [];
    foreach ($fields as $keyS => $valueS) {
        foreach ($valueS as $key => $value) {

            if ($key == 'isReadOnly' && $value == 1 && $keyS != 'ID') {
                $unset[] = $keyS;
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
                $fields[$keyS]['Multiple'] = $fields[$keyS][$key] ?: 'N';
                unset($fields[$keyS][$key]);
            }
            elseif ($key == 'isRequired') {
                if ($keyS != 'entityTypeId')
                    $fields[$keyS]['Required'] = $value ? 'Y' : 'N';
                unset($fields[$keyS][$key]);
            }
            elseif ($key == 'type')
            {
                if ($value == 'boolean')
                {
                    $value = 'bool';
                }
                elseif ($value == 'integer')
                {
                    $value = 'int';
                }
                $fields[$keyS]['Type'] = $value;
                unset($fields[$keyS][$key]);
            }
            else
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
//            'CODE' => 'A.SMART_PROCESS_ADD',
//            'USE_SUBSCRIPTION' => 'Y',
//            'HANDLER' =>  $handlerBackUrl,
//            'AUTH_USER_ID'=> 1,
//            'NAME' => 'Создание смарт процесса',
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
            'CODE' => 'R.SMART_PROCESS_ADD',
            'USE_SUBSCRIPTION' => 'Y',
            'HANDLER' =>  $handlerBackUrl,
            'AUTH_USER_ID'=> 1,
            'NAME' => 'Создание смарт процесса',
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
    $error_log = $_POST['properties']['error_log'];
    unset($_POST['properties']['error_log']);
    if (empty($_POST['properties']['entityTypeId'])) {
        unset($_POST['properties']['entityTypeId']);
    }

    $fields = [];
    foreach ($_POST['properties'] as $key => $value)
    {
//    if ($value)
        $fields[$key] = $value;
    }

    $fields['createdBy'] = $_REQUEST['auth']['user_id'];

    $add = $rest->call(
        'crm.type.add',
        [
            'fields' => $fields
        ]
    );

    $endpoint = $_REQUEST['auth']['client_endpoint'];

    $params = array(
        "auth" => $_REQUEST['auth']["access_token"],
        "event_token" => $_REQUEST["event_token"],
        "log_message" => '',
        "return_values" => ['Ok' => 'Ok']
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
        'activityDescription' =>  "Создание смарт процесса",
        'activityCode' =>  "SMART_PROCESS_ADD",
        'activityName' =>  "Создание смарт процесса *beta",
        'activityMulti' => true,
    ];


return $return;