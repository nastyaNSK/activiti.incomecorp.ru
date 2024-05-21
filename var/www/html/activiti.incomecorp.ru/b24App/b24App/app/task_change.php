<?php


$return['install'] = function($rest)
{
    $handlerBackUrl = "https://activiti.incomecorp.ru/b24-handler/";


    $delite = $rest->call(
        'bizproc.activity.delete',
        [
            'CODE' => 'A.TASK_CHANGE',
        ]
    );

    $delite = $rest->call(
        'bizproc.robot.delete',
        [
            'CODE' => 'R.TASK_CHANGE',
        ]
    );

    $fields = $rest->call(
        'tasks.task.getFields',
        [
        ]
    );

    $fields = $fields['result']['fields'];
    $unset = [];


    foreach ($fields as $keyS => $valueS) {
        foreach ($valueS as $key => $value) {
            if ($key == 'title' && $value == 'ID')
            {
                $fields[$keyS]['required'] = 'Y';
            }
            if ($key == 'type' && $value == 'enum') {
                foreach ($fields[$keyS]['values'] as $k => $v) {
                    $fields[$keyS]['Options'][$k] = $v;
                }
            }
            if ($key == 'title') {
                unset($fields[$keyS][$key]);
                if (empty($value)) {
                    $fields[$keyS]['Name'] = $keyS;
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
            if ($keyS == 'PARENT_ID' || $keyS == 'CREATED_DATE' || $keyS == 'CHANGED_BY'
                || $keyS == 'CHANGED_DATE' || $keyS == 'STATUS_CHANGED_BY' || $keyS == 'STATUS_CHANGED_DATE' ||
                $keyS == 'CLOSED_BY' || $keyS == 'CLOSED_DATE' || $keyS == 'ACTIVITY_DATE' ||
                $keyS == 'DATE_START' || $keyS == 'GUID' || $keyS == 'XML_ID' ||
                $keyS == 'COMMENTS_COUNT' || $keyS == 'SERVICE_COMMENTS_COUNT' || $keyS == 'NEW_COMMENTS_COUNT' ||
                $keyS == 'FORKED_BY_TEMPLATE_ID' || $keyS == 'TIME_ESTIMATE' || $keyS == 'TIME_SPENT_IN_LOGS' ||
                $keyS == 'MATCH_WORK_TIME' || $keyS == 'FORUM_TOPIC_ID' || $keyS == 'FORUM_ID' ||
                $keyS == 'SITE_ID' || $keyS == 'SUBORDINATE' || $keyS == 'EXCHANGE_MODIFIED' ||
                $keyS == 'EXCHANGE_ID' || $keyS == 'OUTLOOK_VERSION' || $keyS == 'VIEWED_DATE' ||
                $keyS == 'SORTING' || $keyS == 'CHECKLIST' || $keyS == 'UF_CRM_TASK' ||
                $keyS == 'UF_TASK_WEBDAV_FILES' || $keyS == 'UF_MAIL_MESSAGE'
            ) {
                $unset[] = $keyS;
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
//            'CODE' => 'A.TASK_CHANGE',
//            'USE_SUBSCRIPTION' => 'Y',
//            'HANDLER' => $handlerBackUrl,
//            'AUTH_USER_ID'=> 1,
//            'NAME' => 'Изменение задачи',
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
            'CODE' => 'R.TASK_CHANGE',
            'USE_SUBSCRIPTION' => 'Y',
            'HANDLER' => $handlerBackUrl,
            'AUTH_USER_ID'=> 1,
            'NAME' => 'Изменение задачи',
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
//            'tasks.task.update',
//            [
//                'taskId' => $id,
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


    $info = $rest->call(
        'tasks.task.update',
        [
            'taskId' => $id,
            'fields' =>
                $data,
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
        'activityDescription' =>  "Изменение задачи",
        'activityCode' =>  "TASK_CHANGE",
        'activityName' =>  "Изменение задачи *beta",
        'activityMulti' => true,
    ];


return $return;