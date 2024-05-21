<?php


$return['install'] = function($rest) {
    $handlerBackUrl = "https://activiti.incomecorp.ru/b24-handler/";
    $res = $rest->call(
        'bizproc.activity.delete',
        [
            'CODE' => 'A.TASKS_AUDITORS',
        ]
    );


    $res1 = $rest->call(
        'bizproc.activity.add',
        [
            'CODE' => 'A.TASKS_AUDITORS',
            'USE_SUBSCRIPTION' => 'Y',
            'HANDLER' => $handlerBackUrl,
            'NAME' => 'Удаление наблюдателя из задачи',
            'PROPERTIES' => [
                'taskId' => [
                    'Name' => "Идентификатор задачи",
                    'Type' => 'int',
                    'Required' => 'Y'
                ],
                'auditors' => [
                    'Name' => "Наблюдатели, через запятую если несколько",
                    'Type' => 'string',
                    'Required' => 'Y'
                ]
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
        'bizproc.robot.delete',
        [
            'CODE' => 'R.TASKS_AUDITORS',
        ]
    );


    $res1 = $rest->call(
        'bizproc.robot.add',
        [
            'CODE' => 'R.TASKS_AUDITORS',
            'USE_SUBSCRIPTION' => 'Y',
            'HANDLER' => $handlerBackUrl,
            'NAME' => 'Удаление наблюдателя из задачи',
            'PROPERTIES' => [
                'taskId' => [
                    'Name' => "Идентификатор задачи",
                    'Type' => 'int',
                    'Required' => 'Y'
                ],
                'auditors' => [
                    'Name' => "Наблюдатели, через запятую если несколько",
                    'Type' => 'string',
                    'Required' => 'Y'
                ]
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

    $users = explode(';', $_REQUEST['properties']['auditors']);

    //file_put_contents('test1.txt', print_r($users, true));
    $auditors = [];
    foreach ($users as $user) {
        if (strlen(trim($user)) == 0)
            continue;
        $userId = str_replace('user_', '', $user);
        $auditors[] = trim($userId);
    }

    $taskIdFrom = $_REQUEST['properties']['taskId'];

    $res = $rest->call('tasks.task.get', ['taskId' => $taskIdFrom])['result'];
    $auditor = array_diff($res['task']['auditors'], $auditors);

    if (sizeof($auditor) == 0)
        $auditor[] = 0;

    $res = $rest->call('tasks.task.update', ['taskId' => $taskIdFrom, 'fields' => ['AUDITORS' => $auditor]]);


    $res2 = $rest->call('bizproc.event.send', ['event_token' => $_REQUEST['event_token'], 'return_values' => ['is_success' => 1]]);
};

$return['data'] =
    [
        'activityDescription' =>  "Находит задачу по ID и удаляет указанного наблюдателя.",
        'activityCode' =>  "TASKS_AUDITORS",
        'activityName' =>  "Задача: удаление наблюдателя из задачи",
        'activityMulti' => true,
    ];

return $return;





