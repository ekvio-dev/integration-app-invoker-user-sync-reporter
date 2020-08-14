<?php
declare(strict_types=1);

namespace Ekvio\Integration\Invoker\Tests\Unit;

use Ekvio\Integration\Invoker\Report\ReportCollector;
use Ekvio\Integration\Invoker\Report\ReportHeader;
use Ekvio\Integration\Invoker\Report\UserSuccessSyncReport;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Class UserSuccessSyncReportTest
 * @package Ekvio\Integration\Invoker\Tests\Unit
 */
class UserSuccessSyncReportTest extends TestCase
{
    private function getTestUsers(): array
    {
        return [
            [
                'USR_LOGIN' => 'test',
                'USR_FIRST_NAME' => 'Петр',
                'USR_LAST_NAME' => 'Иванов',
                'USR_EMAIL' => 'ivanov.p@dev.test',
                'USR_MOBILE' => '89275000000',
                'MANAGER_EMAIL' => null,
                'USR_UDF_USER_FIRED' => '0',
                'REGION_NAME' => 'Moscow region',
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
                'MANAGER_EMAIL' => 'null',
                'USR_UDF_USER_FIRED' => '0',
                'REGION_NAME' => 'Moscow region',
                'CITY_NAME' => 'Moscow',
                'ROLE' => 'HD',
                'POSITION_NAME' => 'Director',
                'TEAM_NAME' => 'Head team',
                'DEPARTAMENT_NAME' => 'Demo',
                'ASSIGNMENT_NAME' => 'Demo',
            ],
        ];
    }

    public function testExceptionIfUserDataNotFound()
    {
        $this->expectException(RuntimeException::class);
        (new UserSuccessSyncReport(new ReportHeader()))->build(['syncLog' => []]);
    }

    public function testExceptionIfSyncLogNotFound()
    {
        $this->expectException(RuntimeException::class);
        (new UserSuccessSyncReport(new ReportHeader()))->build(['users' => [['login' => 'test']]]);
    }

    public function testEmptyUsersAndSyncLogData()
    {
        $header = new ReportHeader();
        $report = (new UserSuccessSyncReport($header))->build([
            'users' => [],
            'syncLog' => []
        ]);
        $this->assertInstanceOf(ReportCollector::class, $report);
        $this->assertEquals($header->headers(), $report->header());
        $this->assertEquals([], $report->content());
    }

    public function testIfUserNotExistInSyncLog()
    {
        $header = new ReportHeader();
        $report = (new UserSuccessSyncReport($header))->build([
            'users' => $this->getTestUsers(),
            'syncLog' => []
        ]);

        $this->assertInstanceOf(ReportCollector::class, $report);
        $this->assertEquals($header->headers(), $report->header());
        $this->assertEquals([], $report->content());
    }

    public function testSuccessUserReportWithOneUserInSyncLog()
    {
        $syncLog = [
            [
                'index' => 0,
                'login' => 'test',
                'status' => 'created',
                'data' => [
                    'status' => 'blocked'
                ]
            ]
        ];

        $header = new ReportHeader();
        $report = (new UserSuccessSyncReport($header))->build([
            'users' => $this->getTestUsers(),
            'syncLog' => $syncLog
        ]);

        $this->assertInstanceOf(ReportCollector::class, $report);
        $this->assertEquals($header->headers(), $report->header());
        $this->assertEquals([
                [
                    'test',
                    'Петр',
                    'Иванов',
                    'ivanov.p@dev.test',
                    '89275000000',
                    null,
                    'blocked',
                    'Moscow region',
                    'Moscow',
                    'HD',
                    'Director',
                    'Head team',
                    'Demo',
                    'Demo',
                ]
            ], $report->content());
    }

    public function testSuccessUserReportWithForms()
    {
        $syncLog = [
            [
                'index' => 0,
                'login' => 'test',
                'status' => 'created',
                'data' => [
                    'status' => 'active'
                ]
            ],
            [
                'index' => 0,
                'login' => 'test2',
                'status' => 'updated',
                'data' => [
                    'status' => 'blocked'
                ]
            ],
        ];
        $users = $this->getTestUsers();
        $users[0]['OTCH'] = 'Андреевич';
        $users[0]['HIRE_DATE'] = '20.09.1985';
        $users[1]['OTCH'] = 'Антонович';
        $users[1]['HIRE_DATE'] = '01.01.2000';

        $header = (new ReportHeader())->addForms([
            '1' => 'OTCH',
            '2' => 'HIRE_DATE'
        ]);

        $report = (new UserSuccessSyncReport($header))->build([
            'users' => $users,
            'syncLog' => $syncLog
        ]);

        $this->assertInstanceOf(ReportCollector::class, $report);
        $this->assertEquals([
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
            'OTCH',
            'HIRE_DATE'
        ], $report->header());
        $this->assertEquals([
            [
                'test',
                'Петр',
                'Иванов',
                'ivanov.p@dev.test',
                '89275000000',
                null,
                'active',
                'Moscow region',
                'Moscow',
                'HD',
                'Director',
                'Head team',
                'Demo',
                'Demo',
                'Андреевич',
                '20.09.1985'
            ],
            [
                'test2',
                'Иван',
                'Семенов',
                'semenov.i@dev.test',
                '89275000001',
                'null',
                'blocked',
                'Moscow region',
                'Moscow',
                'HD',
                'Director',
                'Head team',
                'Demo',
                'Demo',
                'Антонович',
                '01.01.2000'
            ]
        ], $report->content());
    }

    public function testSuccessReportWithMutateHeaderMap()
    {
        $users = [
            [
                'LOGIN' => 'test',
                'FIRST_NAME' => 'Петр',
                'LAST_NAME' => 'Иванов',
                'USR_EMAIL' => 'ivanov.p@dev.test',
                'USR_MOBILE' => '89275000000',
                'MANAGER_EMAIL' => null,
                'USR_UDF_USER_FIRED' => '0',
                'REGION_NAME' => 'Moscow region',
                'CITY_NAME' => 'Moscow',
                'ROLE' => 'HD',
                'POSITION_NAME' => 'Director',
                'TEAM_NAME' => 'Head team',
                'DEPARTAMENT_NAME' => 'Demo',
                'ASSIGNMENT_NAME' => 'Demo',
            ],
            [
                'LOGIN' => 'test2',
                'FIRST_NAME' => 'Иван',
                'LAST_NAME' => 'Семенов',
                'USR_EMAIL' => 'semenov.i@dev.test',
                'USR_MOBILE' => '89275000001',
                'MANAGER_EMAIL' => 'null',
                'USR_UDF_USER_FIRED' => '0',
                'REGION_NAME' => 'Moscow region',
                'CITY_NAME' => 'Moscow',
                'ROLE' => 'HD',
                'POSITION_NAME' => 'Director',
                'TEAM_NAME' => 'Head team',
                'DEPARTAMENT_NAME' => 'Demo',
                'ASSIGNMENT_NAME' => 'Demo',
            ],
        ];

        $syncLog = [
            [
                'index' => 0,
                'login' => 'test',
                'status' => 'created',
                'data' => [
                    'status' => 'active'
                ]
            ],
            [
                'index' => 0,
                'login' => 'test2',
                'status' => 'updated',
                'data' => [
                    'status' => 'blocked'
                ]
            ],
        ];

        $header = new ReportHeader([
            'login' => 'LOGIN',
            'first_name' => 'FIRST_NAME',
            'last_name' => 'LAST_NAME'
        ]);

        $report = (new UserSuccessSyncReport($header))->build([
            'users' => $users,
            'syncLog' => $syncLog
        ]);

        $this->assertInstanceOf(ReportCollector::class, $report);
        $this->assertEquals([
            'LOGIN',
            'FIRST_NAME',
            'LAST_NAME',
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
        ], $report->header());
        $this->assertEquals([
            [
                'test',
                'Петр',
                'Иванов',
                'ivanov.p@dev.test',
                '89275000000',
                 null,
                'active',
                'Moscow region',
                'Moscow',
                'HD',
                'Director',
                'Head team',
                'Demo',
                'Demo',
            ],
            [
                'test2',
                'Иван',
                'Семенов',
                'semenov.i@dev.test',
                '89275000001',
                'null',
                'blocked',
                'Moscow region',
                'Moscow',
                'HD',
                'Director',
                'Head team',
                'Demo',
                'Demo',
            ]
        ], $report->content());
    }
}