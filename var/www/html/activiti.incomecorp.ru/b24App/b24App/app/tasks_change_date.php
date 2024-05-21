<?php


$return['install'] = function($rest)
{
    $handlerBackUrl = "https://activiti.incomecorp.ru/b24-handler/";
    $res = $rest->call(
        'bizproc.activity.delete',
        [
            'CODE' => 'A.TASKS_CHANGE_DATE',
        ]
    );


    $res1 = $rest->call(
        'bizproc.activity.add',
        [
            'CODE' => 'A.TASKS_CHANGE_DATE',
            'USE_SUBSCRIPTION' => 'Y',
            'HANDLER' => $handlerBackUrl,
            'NAME' => 'Изменение времени задач',
            'PROPERTIES' => [
                'taskId' => [
                    'Name' => "Идентификатор задачи",
                    'Type' => 'int',
                    'Required' => 'Y'
                ],
                'dateFrom' => [
                    'Name' => "Дата и время начала",
                    'Type' => 'datetime',
                    'Required' => 'N'
                ],
                'dateTo' =>  [
                    'Name' => "Дата и время окончания",
                    'Type' => 'datetime',
                    'Required' => 'N'
                ]
            ],
            'RETURN_PROPERTIES' => [
                'is_success' => [
                    'Name' => "Задача изменена",
                    'Type' => 'int'
                ]
            ],
            'DOCUMENT_TYPE' => ['crm', 'CCrmDocumentDeal', 'DEAL']
        ]
    );

    $res = $rest->call(
        'bizproc.robot.delete',
        [
            'CODE' => 'R.TASKS_CHANGE_DATE',
        ]
    );


    $res1 = $rest->call(
        'bizproc.robot.add',
        [
            'CODE' => 'R.TASKS_CHANGE_DATE',
            'USE_SUBSCRIPTION' => 'Y',
            'HANDLER' => $handlerBackUrl,
            'NAME' => 'Изменение времени задач',
            'PROPERTIES' => [
                'taskId' => [
                    'Name' => "Идентификатор задачи",
                    'Type' => 'int',
                    'Required' => 'Y'
                ],
                'dateFrom' => [
                    'Name' => "Дата и время начала",
                    'Type' => 'datetime',
                    'Required' => 'N'
                ],
                'dateTo' =>  [
                    'Name' => "Дата и время окончания",
                    'Type' => 'datetime',
                    'Required' => 'N'
                ]
            ],
            'RETURN_PROPERTIES' => [
                'is_success' => [
                    'Name' => "Задача изменена",
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


    $taskFrom = $_REQUEST['properties']['taskId'];
    $dateFrom = $_REQUEST['properties']['dateFrom'];
    $dateTo = $_REQUEST['properties']['dateTo'];


    $fields = [];

    if (strlen($dateFrom) > 0)
        $fields['START_DATE_PLAN'] = $dateFrom;
    if (strlen($dateTo) > 0)
        $fields['END_DATE_PLAN'] = $dateTo;

    if (sizeof($fields) > 0) {
        $res = $rest->call('tasks.task.update', ['taskId' => $taskFrom, 'fields' => $fields]);

        if ($res['result']['task']['id'] > 0)
            $res2 = $rest->call('bizproc.event.send', ['event_token' => $_REQUEST['event_token'], 'return_values' => ['is_success' => 1]]);
    } else {
        $res2 = $rest->call('bizproc.event.send', ['event_token' => $_REQUEST['event_token'], 'return_values' => ['is_success' => 1]]);
    }

   // file_put_contents('test1.txt', print_r($res2, true));
};

$return['data'] =
    [
        'activityDescription' =>  "Меняет дату начала и завершения задачи по указанному ID.",
        'activityCode' =>  "TASKS_CHANGE_DATE",
        'activityName' =>  "Задача: сменить дату начала и завершения задачи",
        'activityMulti' => true,
    ];


return $return;





