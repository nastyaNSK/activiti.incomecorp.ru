<?php

$return['install'] = function($rest)
{
    $handlerBackUrl = "https://activiti.incomecorp.ru/b24-handler/";

    $res = $rest->call(
        'bizproc.robot.delete',
        [
            'CODE' => 'R.LEAD_BACK_STAGE',
        ]
    );

    $res1 = $rest->call(
        'bizproc.robot.add',
        [
            'CODE' => 'R.LEAD_BACK_STAGE',
            'USE_SUBSCRIPTION' => 'Y',
            'HANDLER' => $handlerBackUrl,
            'NAME' => 'Лид: возвращение лида на предыдущую стадию',
            'PROPERTIES' => [
                'dealIdFrom' => [
                    'Name' => "Идентификатор",
                    'Type' => 'int',
                    'Required' => 'Y'
                ],
            ],
            'RETURN_PROPERTIES' => [
                'is_success' => [
                    'Name' => "Связь установлена",
                    'Type' => 'int'
                ]
            ],
            'DOCUMENT_TYPE' => ['crm', 'CCrmDocumentLead', 'LEAD']
        ]
    );

    $res = $rest->call(
        'bizproc.activity.delete',
        [
            'CODE' => 'A.LEAD_BACK_STAGE',
        ]
    );

    $res1 = $rest->call(
        'bizproc.activity.add',
        [
            'CODE' => 'A.LEAD_BACK_STAGE',
            'USE_SUBSCRIPTION' => 'Y',
            'HANDLER' => $handlerBackUrl,
            'NAME' => 'Лид: возвращение лида на предыдущую стадию',
            'PROPERTIES' => [
                'dealIdFrom' => [
                    'Name' => "Идентификатор",
                    'Type' => 'int',
                    'Required' => 'Y'
                ],
            ],
            'RETURN_PROPERTIES' => [
                'is_success' => [
                    'Name' => "Связь установлена",
                    'Type' => 'int'
                ]
            ],
            'DOCUMENT_TYPE' => ['crm', 'CCrmDocumentLead', 'LEAD']
        ]
    );



};

$return['handler'] = function($rest)
{
    $placement = $_REQUEST['PLACEMENT'];
    $placementOptions = isset($_REQUEST['PLACEMENT_OPTIONS']) ? json_decode($_REQUEST['PLACEMENT_OPTIONS'], true) : array();
    $handler = ($_SERVER['SERVER_PORT'] === '443' ? 'https' : 'http') . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'];


    $dealIdFrom = $_REQUEST['properties']['dealIdFrom'];

    $res = $rest->call('crm.stagehistory.list', ['entityTypeId' => 1, 'order' => ['ID' => 'DESC'], 'filter'=> ['OWNER_ID' => $dealIdFrom]]);



    if(isset($res["result"]["items"][1]["STATUS_ID"]))
    {
        $last_stage = $res["result"]["items"][1]["STATUS_ID"];
        $ret = $rest->call('crm.lead.update', ['id' => $dealIdFrom, 'fields' => ['STATUS_ID' => $last_stage]]);

    }

    $res2 = $rest->call('bizproc.event.send', ['event_token' => $_REQUEST['event_token'], 'return_values' => ['is_success' => 1]]);

};

$return['data'] =
    [
        'activityDescription' =>  "Возвращает лид на предыдущую (в которой находилась до перемещения) стадию.",
        'activityCode' =>  "LEAD_BACK_STAGE",
        'activityName' =>  "Лид: возвращение лида на предыдущую стадию",
        'activityMulti' => true,
    ];




return $return;
