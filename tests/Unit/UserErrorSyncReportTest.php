<?php
declare(strict_types=1);

namespace Unit;

use Ekvio\Integration\Invoker\Report\ReportError;
use Ekvio\Integration\Invoker\Report\ReportHeader;
use Ekvio\Integration\Invoker\Report\UserErrorSyncReport;
use PHPUnit\Framework\TestCase;

/**
 * Class UserErrorSyncReportTest
 * @package Unit
 */
class UserErrorSyncReportTest extends TestCase
{
    /**
     * @var UserErrorSyncReport
     */
    private $report;

    protected function setUp(): void
    {
        $this->report = new UserErrorSyncReport(new ReportHeader(), new ReportError());
        parent::setUp();
    }

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

    public function testReportWithEmptySyncLog()
    {
        $this->assertEquals([], []);
    }

    public function testDefaultUserErrorReport()
    {
        $report = $this->report->build();

        $this->assertEquals([
            [
                'test1',
                'Петр',
                'Иванов',
                'ivanov.p@dev.test',
                '89275000000',
                null,
                '0',
                null,
                'Moscow',
                'HD',
                'Director',
                'Head team',
                'Demo',
                'Demo',
                0, 1, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0
            ],
            [
                'test2',
                'Иван',
                'Семенов',
                'semenov.i@dev.test',
                '89275000001',
                null,
                '0',
                'Moscow region',
                null,
                'HD',
                'Director',
                'Head team',
                'Demo',
                'Demo',
                0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1
            ],
            [
                null,
                'Иван',
                'Семенов',
                'semenov.i@dev.test',
                '89275000001',
                null,
                '0',
                'Moscow region',
                null,
                'HD',
                'Director',
                'Head team',
                'Demo',
                'Demo',
                1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0
            ]
        ], $report->content());
    }
}