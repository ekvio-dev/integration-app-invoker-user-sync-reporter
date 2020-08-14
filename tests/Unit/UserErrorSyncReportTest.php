<?php
declare(strict_types=1);

namespace Unit;

use Ekvio\Integration\Invoker\Report\ReportError;
use Ekvio\Integration\Invoker\Report\ReportHeader;
use Ekvio\Integration\Invoker\Report\UserErrorSyncReport;
use PHPUnit\Framework\TestCase;
use RuntimeException;

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

    private function defaultHeader(): array
    {
        return [
            'USR_LOGIN',
            'USR_FIRST_NAME',
            'USR_LAST_NAME',
            'USR_EMAIL',
            'USR_MOBILE',
            'MANAGER_EMAIL',
            'USR_UDF_USER_FIRED',
            'REGION_NAME',
            'CITY_NAME',
            'ROLE',
            'POSITION_NAME',
            'TEAM_NAME',
            'DEPARTAMENT_NAME',
            'ASSIGNMENT_NAME',
            'LOGIN_NVALID',
            'EMAIL_NUNIQ',
            'PHONE_NUNIQ',
            'EMAIL_NVALID',
            'CHIEF_EMAIL_NVALID',
            'PHONE_NVALID',
            'TABNUMBER_EMPT',
            'HOLDING_EMPT',
            'BE_EMPT',
            'DIVISION_EMPT',
            'TERRITORY_EMPT',
            'TEAM_EMPT',
            'POSITION_EMPT',
            'FIRST_NAME_EMPT',
            'LAST_NAME_EMPT',
            'FIRST_NAME_NVALID',
            'LAST_NAME_NVALID',
            'UNKWN_ERR'
        ];
    }

    private function defaultUsers()
    {
        return [
            [
                'USR_LOGIN' => 'test1',
                'USR_FIRST_NAME' => 'Петр',
                'USR_LAST_NAME' => 'Иванов',
                'USR_EMAIL' => 'ivanov.p@dev.test',
                'USR_MOBILE' => '89275000000',
                'MANAGER_EMAIL' => null,
                'USR_UDF_USER_FIRED' => '0',
                'REGION_NAME' => null,
                'CITY_NAME' => 'Moscow',
                'ROLE' => 'HD',
                'POSITION_NAME' => 'Director',
                'TEAM_NAME' => 'Head team',
                'DEPARTAMENT_NAME' => 'Demo',
                'ASSIGNMENT_NAME' => 'Demo',
            ],
            [
                'USR_LOGIN' => 'test2',
                'USR_FIRST_NAME' => 'Иван',
                'USR_LAST_NAME' => 'Семенов',
                'USR_EMAIL' => 'semenov.i@dev.test',
                'USR_MOBILE' => '89275000001',
                'MANAGER_EMAIL' => null,
                'USR_UDF_USER_FIRED' => '0',
                'REGION_NAME' => 'Moscow region',
                'CITY_NAME' => null,
                'ROLE' => 'HD',
                'POSITION_NAME' => 'Director',
                'TEAM_NAME' => 'Head team',
                'DEPARTAMENT_NAME' => 'Demo',
                'ASSIGNMENT_NAME' => 'Demo',
            ],
            [
                'USR_LOGIN' => null,
                'USR_FIRST_NAME' => 'Иван',
                'USR_LAST_NAME' => 'Семенов',
                'USR_EMAIL' => 'semenov.i@dev.test',
                'USR_MOBILE' => '89275000001',
                'MANAGER_EMAIL' => null,
                'USR_UDF_USER_FIRED' => '0',
                'REGION_NAME' => 'Moscow region',
                'CITY_NAME' => null,
                'ROLE' => 'HD',
                'POSITION_NAME' => 'Director',
                'TEAM_NAME' => 'Head team',
                'DEPARTAMENT_NAME' => 'Demo',
                'ASSIGNMENT_NAME' => 'Demo',
            ],
        ];
    }

    public function testRaiseExceptionIfRawUsersNotSet()
    {
        $this->expectException(RuntimeException::class);
        $this->report->build(['syncLog' => []]);
    }

    public function testRaiseExceptionIfSyncLogNotSet()
    {
        $this->expectException(RuntimeException::class);
        $this->report->build(['users' => [1, 2, 3]]);
    }

    public function testReportIfUsersAndSyncLogEmpty()
    {
        $report = $this->report->build([
            'users' => [],
            'syncLog' => []
        ]);

        $this->assertEquals($this->defaultHeader(), $report->header());
        $this->assertEquals([], $report->content());
    }

    public function testReportWithEmptyUsers()
    {
        $report = $this->report->build([
            'users' => [],
            'syncLog' => $this->defaultSyncLog()
        ]);

        $this->assertEquals($this->defaultHeader(), $report->header());
        $this->assertEquals([], $report->content());
    }

    public function testReportWithEmptySyncLog()
    {
        $report = $this->report->build([
            'users' => [],
            'syncLog' => $this->defaultSyncLog()
        ]);

        $this->assertEquals($this->defaultHeader(), $report->header());
        $this->assertEquals([], $report->content());
    }

    public function testDefaultUserErrorReport()
    {
        $report = $this->report->build([
            'users' => $this->defaultUsers(),
            'syncLog' => $this->defaultSyncLog()
        ]);

        $this->assertEquals($this->defaultHeader(), $report->header());
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
                0, 1, 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0
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
                0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1
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
                1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0
            ]
        ], $report->content());
    }
}