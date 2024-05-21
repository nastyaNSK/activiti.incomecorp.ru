<?php

$return['install'] = function($rest)
{
    $handlerBackUrl = "https://activiti.incomecorp.ru/b24-handler/";

    $res = $rest->call(
        'bizproc.robot.delete',
        [
            'CODE' => 'R.DEAL_BACK_STAGE',
        ]
    );

    $res1 = $rest->call(
        'bizproc.robot.add',
        [
            'CODE' => 'R.DEAL_BACK_STAGE',
            'USE_SUBSCRIPTION' => 'Y',
            'HANDLER' => $handlerBackUrl,
            'NAME' => 'Возврат на предыдущую стадию',
            'PROPERTIES' => [
                'dealIdFrom' => [
                    'Name' => "Идентификатор сделки",
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
            'DOCUMENT_TYPE' => ['crm', 'CCrmDocumentDeal', 'DEAL']
        ]
    );

    $res = $rest->call(
        'bizproc.activity.delete',
        [
            'CODE' => 'A.DEAL_BACK_STAGE',
        ]
    );

    $res1 = $rest->call(
        'bizproc.activity.add',
        [
            'CODE' => 'A.DEAL_BACK_STAGE',
            'USE_SUBSCRIPTION' => 'Y',
            'HANDLER' => $handlerBackUrl,
            'NAME' => 'Возврат на предыдущую стадию',
            'PROPERTIES' => [
                'dealIdFrom' => [
                    'Name' => "Идентификатор сделки",
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
            'DOCUMENT_TYPE' => ['crm', 'CCrmDocumentDeal', 'DEAL']
        ]
    );



};

$return['handler'] = function($rest)
{
    $placement = $_REQUEST['PLACEMENT'];
    $placementOptions = isset($_REQUEST['PLACEMENT_OPTIONS']) ? json_decode($_REQUEST['PLACEMENT_OPTIONS'], true) : array();
    $handler = ($_SERVER['SERVER_PORT'] === '443' ? 'https' : 'http') . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'];


    $dealIdFrom = $_REQUEST['properties']['dealIdFrom'];

    $res = $rest->call('crm.stagehistory.list', ['entityTypeId' => 2, 'order' => ['ID' => 'DESC'], 'filter'=> ['OWNER_ID' => $dealIdFrom], 'select'=> ['STAGE_ID']]);

    if(isset($res["result"]["items"][1]["STAGE_ID"]))
    {
        $last_stage = $res["result"]["items"][1]["STAGE_ID"];
        $rest->call('crm.deal.update', ['id' => $dealIdFrom, 'fields' => ['STAGE_ID' => $last_stage]]);
    }

    $res2 = $rest->call('bizproc.event.send', ['event_token' => $_REQUEST['event_token'], 'return_values' => ['is_success' => 1]]);

};

$return['data'] =
    [
        'activityDescription' =>  "Возвращает сделку на предыдущую (в которой находилась до перемещения) стадию.",
        'activityCode' =>  "DEAL_BACK_STAGE",
        'activityName' =>  "Сделка: возвращение сделки на предыдущую стадию",
        'activityMulti' => true,
    ];




return $return;
