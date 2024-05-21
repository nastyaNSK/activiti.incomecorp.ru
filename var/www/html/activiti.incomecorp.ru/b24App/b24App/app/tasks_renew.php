<?php


$return['install'] = function($rest)
{
    $handlerBackUrl = "https://activiti.incomecorp.ru/b24-handler/";
    $res = $rest->call(
        'bizproc.activity.delete',
        [
            'CODE' => 'A.TASKS_RENEW',
        ]
    );

    $res1 = $rest->call(
        'bizproc.activity.add',
        [
            'CODE' => 'A.TASKS_RENEW',
            'USE_SUBSCRIPTION' => 'Y',
            'HANDLER' => $handlerBackUrl,
            'NAME' => 'Возобновление задачи',
            'PROPERTIES' => [
                'taskId' => [
                    'Name' => "Идентификатор задачи",
                    'Type' => 'int',
                    'Required' => 'Y'
                ],
            ],
            'RETURN_PROPERTIES' => [
                'is_success' => [
                    'Name' => "Задача возобновлена",
                    'Type' => 'int'
                ]
            ],
            'DOCUMENT_TYPE' => ['crm', 'CCrmDocumentDeal', 'DEAL']
        ]
    );

    $res = $rest->call(
        'bizproc.robot.delete',
        [
            'CODE' => 'R.TASKS_RENEW',
        ]
    );

    $res1 = $rest->call(
        'bizproc.robot.add',
        [
            'CODE' => 'R.TASKS_RENEW',
            'USE_SUBSCRIPTION' => 'Y',
            'HANDLER' => $handlerBackUrl,
            'NAME' => 'Возобновление задачи',
            'PROPERTIES' => [
                'taskId' => [
                    'Name' => "Идентификатор задачи",
                    'Type' => 'int',
                    'Required' => 'Y'
                ],
            ],
            'RETURN_PROPERTIES' => [
                'is_success' => [
                    'Name' => "Задача возобновлена",
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

    $taskIdFrom = $_REQUEST['properties']['taskId'];

    $res = $rest->call('tasks.task.renew', ['taskId' => $taskIdFrom]);

    $res2 = $rest->call('bizproc.event.send', ['event_token' => $_REQUEST['event_token'], 'return_values' => ['is_success' => 1]]);
    //file_put_contents('test1.txt', print_r($res, true));
};



$return['data'] =
    [
        'activityDescription' =>  "Возобновляет задачу по указанному ID.",
        'activityCode' =>  "TASKS_RENEW",
        'activityName' =>  "Задача: возобновление задачи",
        'activityMulti' => true,
    ];

return $return;





