<?php
declare(strict_types=1);

namespace Unit;

use Ekvio\Integration\Invoker\Report\UserErrorSyncReport;
use PHPUnit\Framework\TestCase;

/**
 * Class UserErrorSyncReportTest
 * @package Unit
 */
class UserErrorSyncReportTest extends TestCase
{
    private function defaultSyncLog(): array
    {
        return [
            [
                'index' => 10,
                'login' => 'test10',
                'status' => 'created',
                'data' => [
                    'status' => 'active'
                ]
            ],
            [
                'index' => 11,
                'login' => 'test11',
                'status' => 'updated',
                'data' => [
                    'status' => 'blocked'
                ]
            ],
            [
                "index" => 1,
                "login" => "test1",
                "status" => "error",
                "errors" => [
                    [
                        "code" => 1007,
                        "field" => "email",
                        "message" => "Value is not unique"
                    ],
                    [
                        "code" => 1007,
                        "field" => "phone",
                        "message" => "Value is not unique"
                    ],
                    [
                        "code" => 1007,
                        "field" => "region",
                        "message" => "Group is empty"
                    ],
                ]
            ],
            [
                "index" => 1,
                "login" => "test2",
                "status" => "error",
                "errors" => [
                    [
                        "code" => 1007,
                        "field" => "city",
                        "message" => "Group is empty"
                    ],
                    [
                        "code" => 1007,
                        "field" => "login",
                        "message" => "Неизвестная ошибка"
                    ],
                ]
            ],
            [
                "index" => 2,
                "login" => null,
                "status" => "error",
                "errors" => [
                    [
                        "code" => 1007,
                        "field" => "login",
                        "message" => "Login required"
                    ]
                ]
            ],
            [
                "index" => null,
                "login" => null,
                "status" => "error",
                "errors" => [
                    [
                        "code" => 1007,
                        "field" => "login",
                        "message" => "Login required"
                    ]
                ]
            ],
        ];
    }
}