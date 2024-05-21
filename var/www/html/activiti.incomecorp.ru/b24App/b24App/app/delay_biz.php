<?php

$return['install'] = function($rest)
{
    $handlerBackUrl = "https://activiti.incomecorp.ru/b24-handler/";

    $res = $rest->call(
        'bizproc.robot.delete',
        [
            'CODE' => 'R.DELAY_BIZ',
        ]
    );

    $res1 = $rest->call(
        'bizproc.robot.add',
        [
            'CODE' => 'R.DELAY_BIZ',
            'USE_SUBSCRIPTION' => 'Y',
            'HANDLER' => $handlerBackUrl,
            'NAME' => 'Остановка Бизнес-процесса',
            'PROPERTIES' => [
                'seconds' => [
                    'Name' => "Время в секундах",
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
            'CODE' => 'A.DELAY_BIZ',
        ]
    );

    $res1 = $rest->call(
        'bizproc.activity.add',
        [
            'CODE' => 'A.DELAY_BIZ',
            'USE_SUBSCRIPTION' => 'Y',
            'HANDLER' => $handlerBackUrl,
            'NAME' => 'Остановка Бизнес-процесса',
            'PROPERTIES' => [
                'seconds' => [
                    'Name' => "Время в секундах",
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


    $seconds = $_REQUEST['properties']['seconds'];
    sleep($seconds);
    $res2 = $rest->call('bizproc.event.send', ['event_token' => $_REQUEST['event_token'], 'return_values' => ['is_success' => 1]]);

};

$return['data'] =
    [
        'activityDescription' =>  "Добавляет возможность останавливать выполнение робота или бизнес-процесса, начиная с 1 секунды (штатно не менее 10 минут).",
        'activityCode' =>  "DELAY_BIZ",
        'activityName' =>  "Остановка Бизнес-процесса",
        'activityMulti' => true,
    ];




return $return;
