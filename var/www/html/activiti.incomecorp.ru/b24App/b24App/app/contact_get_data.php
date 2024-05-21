<?php


$return['install'] = function($rest)
{
    $handlerBackUrl = "https://activiti.incomecorp.ru/b24-handler/";

    $delite = $rest->call(
        'bizproc.activity.delete',
        [
            'CODE' => 'A.CONTACT_GET_DATA',
        ]
    );

    $delite = $rest->call(
        'bizproc.robot.delete',
        [
            'CODE' => 'R.CONTACT_GET_DATA',
        ]
    );

    $fields = $rest->call(
        'crm.contact.fields',
        [
        ]
    );


    $fields = $fields['result'];

    foreach ($fields as $keyS => $valueS) {
        foreach ($valueS as $key => $value) {
            if ($key == 'title' || $key == 'listLabel') {
                $fields[$keyS]['Name'] = $fields[$keyS][$key];
                unset($fields[$keyS][$key]);
            } else {
                if ($key != 'type' && $key != 'isMultiple') {
                    unset($fields[$keyS][$key]);
                }
            }
        }
    }


    foreach ($fields as $k=>$v)
    {
        if(empty($v['Name']))
            $fields[$k]['Name'] = $k;
    }
//    $install = $rest->call(
//        'bizproc.activity.add',
//        [
//            'CODE' => 'A.CONTACT_GET_DATA',
//            'USE_SUBSCRIPTION' => 'Y',
//            'HANDLER' => $handlerBackUrl,
//            'AUTH_USER_ID'=> 1,
//            'NAME' => 'Получения данных по контакту',
//            'PROPERTIES' => [
//
//                'id' => [
//                    'Name' => 'ID Контакта',
//                    'Type' => 'int',
//                    'Required' => 'Y'
//                ],
//
//
//                'error_log' => [
//                    'Name' => "Сообщение об ошибки",
//                    'Type' => 'bool',
//                    'Required' => 'Y'
//                ],
//
//            ],
//
//            'RETURN_PROPERTIES' => $fields,
//            'DOCUMENT_TYPE' => ['crm', 'CCrmDocumentDeal', 'DEAL']
//        ]
//    );

    systems::lvd($install);

    $install = $rest->call(
        'bizproc.robot.add',
        [
            'CODE' => 'R.CONTACT_GET_DATA',
            'USE_SUBSCRIPTION' => 'Y',
            'HANDLER' => $handlerBackUrl,
            'AUTH_USER_ID'=> 1,
            'NAME' => 'Получения данных по контакту',
            'PROPERTIES' => [

                'id' => [
                    'Name' => 'ID Контакта',
                    'Type' => 'int',
                    'Required' => 'Y'
                ],


                'error_log' => [
                    'Name' => "Сообщение об ошибки",
                    'Type' => 'bool',
                    'Required' => 'Y'
                ],

            ],

            'RETURN_PROPERTIES' => $fields,
            'DOCUMENT_TYPE' => ['crm', 'CCrmDocumentContact', 'Contact']
        ]
    );

};

$return['handler'] = function($rest)
{
    $my_company_id = intVal($_REQUEST['properties']['id']);
    $error_log = $_REQUEST['properties']['error_log'];

    $contact = $rest->call(
        'crm.contact.get',
        [
            'id' => $my_company_id,
        ]
    )['result'];

    $stat = $rest->call(
        'crm.status.list',
        []
    )['result'];

    $fiel = $rest->call(
        'crm.contact.fields',
        [
        ]
    )['result'];

    $lists = [];
    $lists_enum = [];
    foreach ($fiel as $k => $v) {
        if (array_key_exists('statusType', $v))
            $lists[$k] = $v['statusType'];
        if ($v['type'] == 'enumeration')
            $lists_enum[$k] = $v['items'];
    }

// delete empty fields and change value of enum
    $available_contacts = [];
    foreach ($contact as $key => $value)
    {
        if($value)
        {
            if (array_key_exists($key, $lists))
            {
                foreach ($stat as $v)
                {
                    if ($v['ENTITY_ID'] == $lists[$key] && $v['STATUS_ID'] == $value)
                    {
                        $available_contacts[$key] = $v['NAME'];
                        continue;
                    }
                }
            }
            elseif (array_key_exists($key, $lists_enum))
            {
                foreach ($lists_enum[$key] as $enam)
                {
                    if ($enam['ID'] == $value)
                    {
                        $available_contacts[$key] = $enam['VALUE'];
                        continue;
                    }
                }
            }
            else
            {
                $available_contacts[$key] = $value;
            }
        }
    }


// form data to sent in Bitrix
    $data = [];
    foreach ($available_contacts as $key => $value) {
        if (is_array($available_contacts[$key])) {
            $str ='';
            foreach ($available_contacts[$key] as $elem) {
                $str = $str . $elem['VALUE'] . ', ';
            }
            $str = substr($str,0,-2);
            $data[$key] = $str;
        } else {
            $data[$key] = $available_contacts[$key];
        }
    }



    $params = array(
        "auth" => $_REQUEST['auth']["access_token"],
        "event_token" => $_REQUEST["event_token"],
        "log_message" => '',
        "return_values" => $data
    );

    $response = $callB24Method = $rest->call(
        'bizproc.event.send',
        $params
    );

// Send logs if raise error
    if ($response['error_description'] && $error_log == 'Y') {
        $params = array(
            "auth" => $_REQUEST['auth']["access_token"],
            "event_token" => $_REQUEST["event_token"],
            "log_message" => $response['error_description'],
        );
        $response = $callB24Method = $rest->call(
            'bizproc.event.send',
            $params
        );
    }

};


$return['data'] =
    [
        'activityDescription' =>  "Получение информации из полей контакта по ID контакта.",
        'activityCode' =>  "CONTACT_GET_DATA",
        'activityName' =>  "Контакт: получение полей",
        'activityMulti' => true,
    ];


return $return;