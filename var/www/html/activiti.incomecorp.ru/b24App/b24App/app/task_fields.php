<?php


$return['install'] = function($rest)
{
    $handlerBackUrl = "https://activiti.incomecorp.ru/b24-handler/";


    $delite = $rest->call(
        'bizproc.activity.delete',
        [
            'CODE' => 'A.TASK_FIELDS',
        ]
    );


    $delite = $rest->call(
        'bizproc.robot.delete',
        [
            'CODE' => 'R.TASK_FIELDS',
        ]
    );

    $fields = $rest->call(
        'tasks.task.getFields',
        [
        ]
    );


    $fields = $fields['result']['fields'];
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
            'Name' => 'id задачи',
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
//            'CODE' => 'A.TASK_FIELDS',
//            'USE_SUBSCRIPTION' => 'Y',
//            'HANDLER' => $handlerBackUrl,
//            'AUTH_USER_ID'=> 1,
//            'NAME' => 'Возвращает значения полей задачи',
//            'PROPERTIES' => $properties,
//            'RETURN_PROPERTIES' => $fields,
//            'DOCUMENT_TYPE' => ['crm', 'CCrmDocumentCompany', 'COMPANY']
//        ]
//    );

    $install = $rest->call(
        'bizproc.robot.add',
        [
            'CODE' => 'R.TASK_FIELDS',
            'USE_SUBSCRIPTION' => 'Y',
            'HANDLER' => $handlerBackUrl,
            'AUTH_USER_ID'=> 1,
            'NAME' => 'Возвращает значения полей задачи',
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


//    function filter ($id) {
//        return CRest::call(
//            'tasks.task.list',
//            [
//                'filter' => ['ID' => $id],
//                'select' => [
//                    '*',
//                ]
//            ]
//        );
//    }

//    function stringFormater($string) {
//        $string = strtolower($string);
//        $string = str_split($string);
//        $flug = false;
//        foreach ($string as $kstr => $str) {
//            if($flug) {
//                $string[$kstr] = strtoupper($str);
//                $flug = false;
//            }
//            if ($str == '_') {
//                $flug = true;
//            }
//        }
//        $string = implode('', $string);
//        return str_replace('_', '', $string);
//    }


    $info = $rest->call(
        'tasks.task.list',
        [
            'filter' => ['ID' => $id],
            'select' => [
                '*',
            ]
        ]
    );


    $fields = $rest->call(
        'tasks.task.getFields',
        [
        ]
    );


    $fields = $fields['result']['fields'];
    $unset = [];

    unset($fields['ID']);

    $values = [];
    foreach ($fields as $key => $value) {
        $string = $key;
                $string = strtolower($string);
                $string = str_split($string);
                $flug = false;
                foreach ($string as $kstr => $str) {
                    if($flug) {
                        $string[$kstr] = strtoupper($str);
                        $flug = false;
                    }
                    if ($str == '_') {
                        $flug = true;
                    }
                }
                $string = implode('', $string);
                $sf = str_replace('_', '', $string);


        $values[$sf] = $value;
    }

    $return = [];
    foreach ($info['result']['tasks'][0] as $key => $value){
        foreach ($values as $k => $v) {
            if ($key == $k && empty($value['title'])) {
                $return[$key] = $value;
            }
        }
    }
//$mass = ['$info' => $info['result']['tasks'][0], 'fields' => $values, 'return' => $return];
//file_put_contents('test.txt', print_r($mass, true));

    $endpoint = $_REQUEST['auth']['client_endpoint'];

    $params = array(
        "auth" => $_REQUEST['auth']["access_token"],
        "event_token" => $_REQUEST["event_token"],
        "log_message" => '',
        "return_values" => $return
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
        'activityDescription' =>  "Возвращает значения полей задачи",
        'activityCode' =>  "TASK_FIELDS",
        'activityName' =>  "Возвращает значения полей задачи *beta",
        'activityMulti' => true,
    ];


return $return;