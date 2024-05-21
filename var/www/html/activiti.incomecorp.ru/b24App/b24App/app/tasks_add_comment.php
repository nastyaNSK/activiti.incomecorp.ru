<?php


$return['install'] = function($rest)
{
    $handlerBackUrl = "https://activiti.incomecorp.ru/b24-handler/";
    $res = $rest->call(
        'bizproc.activity.delete',
        [
            'CODE' => 'A.TASKS_ADD_COMMENT',
        ]
    );

    $res1 = $rest->call(
        'bizproc.activity.add',
        [
            'CODE' => 'A.TASKS_ADD_COMMENT',
            'USE_SUBSCRIPTION' => 'Y',
            'HANDLER' => $handlerBackUrl,
            'NAME' => 'Создание комментария к задаче',
            'PROPERTIES' => [
                'taskIdFrom' => [
                    'Name' => "Идентификатор задачи",
                    'Type' => 'int',
                    'Required' => 'Y'
                ],
                'authorId' => [
                    'Name' => "ID автора комментария",
                    'Type' => 'user',
                    'Required' => 'Y'
                ],
                'postMessage' => [
                    'Name' => "Сообщение",
                    'Type' => 'string',
                    'Required' => 'Y'
                ],
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
            'CODE' => 'R.TASKS_ADD_COMMENT',
        ]
    );

    $res1 = $rest->call(
        'bizproc.robot.add',
        [
            'CODE' => 'R.TASKS_ADD_COMMENT',
            'USE_SUBSCRIPTION' => 'Y',
            'HANDLER' => $handlerBackUrl,
            'NAME' => 'Создание комментария к задаче',
            'PROPERTIES' => [
                'taskIdFrom' => [
                    'Name' => "Идентификатор задачи",
                    'Type' => 'int',
                    'Required' => 'Y'
                ],
                'authorId' => [
                    'Name' => "ID автора комментария",
                    'Type' => 'user',
                    'Required' => 'Y'
                ],
                'postMessage' => [
                    'Name' => "Сообщение",
                    'Type' => 'string',
                    'Required' => 'Y'
                ],
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

    systems::lvd($res1);

};

$return['handler'] = function($rest)
{
    $placement = $_REQUEST['PLACEMENT'];
    $placementOptions = isset($_REQUEST['PLACEMENT_OPTIONS']) ? json_decode($_REQUEST['PLACEMENT_OPTIONS'], true) : array();
    $handler = ($_SERVER['SERVER_PORT'] === '443' ? 'https' : 'http') . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'];




    $taskIdFrom = $_REQUEST['properties']['taskIdFrom'];
    $authorId = str_replace('user_', '',$_REQUEST['properties']['authorId']);
    $message = $_REQUEST['properties']['postMessage'];
    $taskTitle = $_REQUEST['properties']['taskTitle'];
    $fields = [];
    if (strlen($authorId) > 0)
        $fields['AUTHOR_ID'] = $authorId;
    if (strlen($message) > 0)
        $fields['POST_MESSAGE'] = $message;
    if (sizeof($fields) > 0) {
        $res = $rest->call('task.commentitem.add', ['taskId' => $taskIdFrom, 'fields' => $fields]);
        //file_put_contents('res.txt', print_r($res, true));
        if ($res['result'] > 0)
            $res2 = $rest->call('bizproc.event.send', ['event_token' => $_REQUEST['event_token'], 'return_values' => ['is_success' => 1]]);

        //file_put_contents('res2.txt', print_r($res2, true));
    } else {
        $res2 = $rest->call('bizproc.event.send', ['event_token' => $_REQUEST['event_token'], 'return_values' => ['is_success' => 1]]);
    }

};


$return['data'] =
    [
        'activityDescription' =>  "Находит задачу по ID и указывает комментарий от лица выбранного пользователя.",
        'activityCode' =>  "TASKS_ADD_COMMENT",
        'activityName' =>  "Задача: добавить комментарий в задачу",
        'activityMulti' => true,
    ];

return $return;





