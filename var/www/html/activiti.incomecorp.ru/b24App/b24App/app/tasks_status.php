<?php


$return['install'] = function($rest)
{
    $handlerBackUrl = "https://activiti.incomecorp.ru/b24-handler/";
    $res = $rest->call(
        'bizproc.activity.delete',
        [
            'CODE' => 'A.TASKS_STATUS',
        ]
    );

    $res1 = $rest->call(
        'bizproc.activity.add',
        [
            'CODE' => 'A.TASKS_STATUS',
            'USE_SUBSCRIPTION' => 'Y',
            'HANDLER' => $handlerBackUrl,
            'NAME' => 'Изменение статуса и ответственного по задаче',
            'PROPERTIES' => [
                'taskIdFrom' => [
                    'Name' => "Идентификатор задачи",
                    'Type' => 'int',
                    'Required' => 'Y'
                ],
                'taskTitle' => [
                    'Name' => "Название задачи",
                    'Type' => 'string',
                    'Required' => 'Y'
                ],
                'taskResponsible' => [
                    'Name' => "ID ответственного",
                    'Type' => 'user',
                    'Required' => 'Y'
                ],
                'taskStatus' =>  [
                    'Name' => "Статус",
                    'Type' => 'select',
                    'Options' => [
                        '2' => 'Ждет выполнения',
                        '3' => 'Выполняется',
                        '4' => 'Ожидает контроля',
                        '5' => 'Завершена',
                        '6' => 'Отложена',
                    ],
                    'Required' => 'Y'
                ]
            ],
            'RETURN_PROPERTIES' => [
                'is_success' => [
                    'Name' => "Задача обновлена",
                    'Type' => 'int'
                ]
            ],
            'DOCUMENT_TYPE' => ['crm', 'CCrmDocumentDeal', 'DEAL']
        ]
    );

    $res = $rest->call(
        'bizproc.robot.delete',
        [
            'CODE' => 'R.TASKS_STATUS',
        ]
    );

    $res1 = $rest->call(
        'bizproc.robot.add',
        [
            'CODE' => 'R.TASKS_STATUS',
            'USE_SUBSCRIPTION' => 'Y',
            'HANDLER' => $handlerBackUrl,
            'NAME' => 'Изменение статуса и ответственного по задаче',
            'PROPERTIES' => [
                'taskIdFrom' => [
                    'Name' => "Идентификатор задачи",
                    'Type' => 'int',
                    'Required' => 'Y'
                ],
                'taskTitle' => [
                    'Name' => "Название задачи",
                    'Type' => 'string',
                    'Required' => 'Y'
                ],
                'taskResponsible' => [
                    'Name' => "ID ответственного",
                    'Type' => 'user',
                    'Required' => 'Y'
                ],
                'taskStatus' =>  [
                    'Name' => "Статус",
                    'Type' => 'select',
                    'Options' => [
                        '2' => 'Ждет выполнения',
                        '3' => 'Выполняется',
                        '4' => 'Ожидает контроля',
                        '5' => 'Завершена',
                        '6' => 'Отложена',
                    ],
                    'Required' => 'Y'
                ]
            ],
            'RETURN_PROPERTIES' => [
                'is_success' => [
                    'Name' => "Задача обновлена",
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


    $taskIdFrom = $_REQUEST['properties']['taskIdFrom'];
    $taskResponsible = str_replace('user_', '',$_REQUEST['properties']['taskResponsible']);
    $taskStatus = $_REQUEST['properties']['taskStatus'];
    $taskTitle = $_REQUEST['properties']['taskTitle'];
    $fields = [];
    if (strlen($taskResponsible) > 0)
        $fields['RESPONSIBLE_ID'] = $taskResponsible;
    if (strlen($taskStatus) > 0)
        $fields['STATUS'] = $taskStatus;
    if (strlen($taskTitle) > 0)
        $fields['TITLE'] = $taskTitle;

    if (sizeof($fields) > 0) {
        $res = $rest->call('tasks.task.update', ['taskId' => $taskIdFrom, 'fields' => $fields]);
        if ($res['result']['task']['id'] > 0)
            $res2 = $rest->call('bizproc.event.send', ['event_token' => $_REQUEST['event_token'], 'return_values' => ['is_success' => 1]]);
    } else {
        $res2 = $rest->call('bizproc.event.send', ['event_token' => $_REQUEST['event_token'], 'return_values' => ['is_success' => 1]]);
    }

    //file_put_contents('test1.txt', print_r($_REQUEST, true));
};

$return['data'] =
    [
        'activityDescription' =>  "Изменяет статус задачи по указанному ID.",
        'activityCode' =>  "TASKS_STATUS",
        'activityName' =>  "Задача: изменение статуса задачи",
        'activityMulti' => true,
    ];


return $return;





