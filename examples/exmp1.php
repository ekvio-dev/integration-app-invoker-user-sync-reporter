<?php
declare(strict_types=1);

use Ekvio\Integration\Contracts\Profiler;
use Ekvio\Integration\Contracts\User\UserPipelineData;
use Ekvio\Integration\Invoker\Report\CsvReportConverter;
use Ekvio\Integration\Invoker\Report\ReportError;
use Ekvio\Integration\Invoker\Report\ReportHeader;
use Ekvio\Integration\Invoker\Report\UserErrorSyncReport;
use Ekvio\Integration\Invoker\Report\UserSuccessSyncReport;
use Ekvio\Integration\Invoker\TypicalUserSyncReport;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

require_once __DIR__ . '/../vendor/autoload.php';

class ConsoleProfiler implements Profiler
{
    public function profile(string $message): void
    {
        fwrite(STDOUT, $message . PHP_EOL);
    }
}

class UserSyncPipelineData implements UserPipelineData
{
    private $keyDelimiter = '___';
    private $sources = [
        '0_aaa' => [
            'name' => 'hr1.csv',
            'data' => [
                [
                    'USR_LOGIN' => 'test1',
                    'USR_FIRST_NAME' => 'Петр',
                    'USR_LAST_NAME' => 'Петров',
                    'USR_EMAIL' => null,
                    'USR_MOBILE' => '79276222400',
                    'MANAGER_EMAIL' => null,
                    'USR_UDF_USER_FIRED' => '0',
                    'REGION_NAME' => 'Московская область',
                    'CITY_NAME' => 'Москва',
                    'ROLE' => 'Дирекция',
                    'POSITION_NAME' => 'Генеральный директор',
                    'TEAM_NAME' => 'Дирекция',
                    'DEPARTAMENT_NAME' => 'ИТР',
                    'ASSIGNMENT_NAME' => 'Default',
                ],
                [
                    'USR_LOGIN' => 'test2',
                    'USR_FIRST_NAME' => 'Иван',
                    'USR_LAST_NAME' => 'Иванов',
                    'USR_EMAIL' => 'ivanov@first.ru',
                    'USR_MOBILE' => '79276222410',
                    'MANAGER_EMAIL' => 'petrov@first.ru',
                    'USR_UDF_USER_FIRED' => '0',
                    'REGION_NAME' => 'Московская область',
                    'CITY_NAME' => 'Москва',
                    'ROLE' => 'Дирекция',
                    'POSITION_NAME' => 'Помощник директора',
                    'TEAM_NAME' => 'Дирекция',
                    'DEPARTAMENT_NAME' => 'ИТР',
                    'ASSIGNMENT_NAME' => 'Default',
                ],
                [
                    'USR_LOGIN' => 'test1',
                    'USR_FIRST_NAME' => null,
                    'USR_LAST_NAME' => null,
                    'USR_EMAIL' => 'andreev@first.ru',
                    'USR_MOBILE' => '79276222420',
                    'MANAGER_EMAIL' => 'petrov@first.ru',
                    'USR_UDF_USER_FIRED' => '0',
                    'REGION_NAME' => 'Московская область',
                    'CITY_NAME' => 'Москва',
                    'ROLE' => 'Дирекция',
                    'POSITION_NAME' => 'Помощник директора',
                    'TEAM_NAME' => 'Дирекция',
                    'DEPARTAMENT_NAME' => 'ИТР',
                    'ASSIGNMENT_NAME' => 'Default',
                ],
            ]
        ],
        '1_bbb' => [
            'name' => 'hr2.csv',
            'data' => [
                [
                    'USR_LOGIN' => 'test2_1',
                    'USR_FIRST_NAME' => 'Сидр',
                    'USR_LAST_NAME' => 'Сидоров',
                    'USR_EMAIL' => null,
                    'USR_MOBILE' => '79276222430',
                    'MANAGER_EMAIL' => 'petrov@first.ru',
                    'USR_UDF_USER_FIRED' => '0',
                    'REGION_NAME' => 'Московская область',
                    'CITY_NAME' => 'Москва',
                    'ROLE' => 'Дирекция',
                    'POSITION_NAME' => 'Генеральный директор',
                    'TEAM_NAME' => 'Дирекция',
                    'DEPARTAMENT_NAME' => 'ИТР',
                    'ASSIGNMENT_NAME' => 'Default',
                ],
                [
                    'USR_LOGIN' => 'test2_2',
                    'USR_FIRST_NAME' => 'Кузьмин',
                    'USR_LAST_NAME' => 'Кузьма',
                    'USR_EMAIL' => 'kuzmin@first.ru',
                    'USR_MOBILE' => '79276222440',
                    'MANAGER_EMAIL' => 'petrov@first.ru',
                    'USR_UDF_USER_FIRED' => '1',
                    'REGION_NAME' => 'Московская область',
                    'CITY_NAME' => 'Москва',
                    'ROLE' => 'Дирекция',
                    'POSITION_NAME' => 'Помощник директора',
                    'TEAM_NAME' => 'Дирекция',
                    'DEPARTAMENT_NAME' => 'ИТР',
                    'ASSIGNMENT_NAME' => 'Default',
                ],
                [
                    'USR_LOGIN' => 'test3_3',
                    'USR_FIRST_NAME' => null,
                    'USR_LAST_NAME' => null,
                    'USR_EMAIL' => 'andreev@first.ru',
                    'USR_MOBILE' => null,
                    'MANAGER_EMAIL' => 'petrov@first.ru',
                    'USR_UDF_USER_FIRED' => '0',
                    'REGION_NAME' => 'Московская область',
                    'CITY_NAME' => 'Москва',
                    'ROLE' => 'Дирекция',
                    'POSITION_NAME' => 'Помощник директора',
                    'TEAM_NAME' => 'Дирекция',
                    'DEPARTAMENT_NAME' => 'ИТР',
                    'ASSIGNMENT_NAME' => 'Default',
                ],
            ]
        ]
    ];
    private $log = [
        [
            'index' => '0_aaa___2',
            'login' => 'test1',
            'status' => 'error',
            'errors' => [
                [
                    'code' => 1007,
                    'field' => 'groups',
                    'message' => 'Group path invalid format. Group index: region'
                ],
                [
                    'code' => 1007,
                    'field' => 'groups',
                    'message' => 'Group path greater then. Group index: region'
                ],
                [
                    'code' => 1007,
                    'field' => 'groups',
                    'message' => 'Group path greater then. Group index: role'
                ],
                [
                    'code' => 1007,
                    'field' => 'groups',
                    'message' => 'Group path greater then. Group index: unknown'
                ],
                [
                    'code' => 1007,
                    'field' => 'first_name',
                    'message' => 'First name required'
                ],
                [
                    'code' => 1007,
                    'field' => 'last_name',
                    'message' => 'Last name required'
                ],
            ]
        ],
        [
            'index' => '1_bbb___0',
            'login' => 'test2_1',
            'status' => 'created',
            'data' => [
                'status' => 'active'
            ]
        ],
        [
            'index' => '0_aaa___0',
            'login' => 'test1',
            'status' => 'created',
            'data' => [
                'status' => 'active'
            ]
        ],
        [
            'index' => '0_aaa___1',
            'login' => 'test2',
            'status' => 'updated',
            'data' => [
                'status' => 'active'
            ]
        ],
        [
            'index' => '1_bbb___1',
            'login' => 'test2_2',
            'status' => 'unchanged',
            'data' => [
                'status' => 'active'
            ]
        ],
        [
            'index' => '1_bbb___2',
            'login' => 'test3',
            'status' => 'error',
            'errors' => [
                [
                    "code" => 1007,
                    "field" => "login",
                    "message" => "Login already exists",
                    "extra" => "hr1.csv"
                ],
                [
                    "code" => 1007,
                    "field" => "first_name",
                    "message" => "First name required"
                ],
                [
                    "code" => 1007,
                    "field" => "last_name",
                    "message" => "Last name required"
                ],
                [
                    "code" => 1007,
                    "field" => "phone",
                    "message" => "Phone required"
                ],
            ]
        ],
        [
            'index' => null,
            'login' => 'Amitin',
            'status' => 'unchanged',
            'data' => [
                'status' => 'active'
            ]
        ],
    ];
    private $data = [];

    public function change(array $usersData): UserPipelineData
    {
        return $this;
    }

    public function addSource(string $key, array $data): void
    {
    }

    public function addLog(array $log): void
    {
    }

    public function data(): array
    {
        return $this->data;
    }

    public function logs(): array
    {
        return $this->log;
    }

    public function sources(): array
    {
        return $this->sources;
    }

    public function dataFromSource(string $key): array
    {
        if(strpos($key, $this->keyDelimiter) === false) {
            throw new RuntimeException(sprintf('Invalid data key %s', $key));
        }

        [$sourceKey, $dataKey] = explode($this->keyDelimiter, $key);

        if(!isset($this->sources[$sourceKey]['data'][$dataKey])) {
            throw new RuntimeException(sprintf('Source data with key %s not found', $key));
        }

        return $this->sources[$sourceKey]['data'][$dataKey];
    }

    public function sourceName(string $key): string
    {
        if(strpos($key, $this->keyDelimiter) !== false) {
            [$key, ] = explode($this->keyDelimiter, $key);
        }

        if(!isset($this->sources[$key])) {
            throw new RuntimeException(sprintf('Source with key %s not found', $key));
        }

        return $this->sources[$key]['name'];
    }
}

$fs = new Filesystem(new Local(__DIR__ . '/tmp' ));

$reportHeader = new ReportHeader();
$reportErrors = new ReportError([
    'errorGroup' => ['region' => 'ERROR_OS'],
    'logUnknownMessage' => true
]);

$successReport = new UserSuccessSyncReport($reportHeader);
$errorReport = new UserErrorSyncReport($reportHeader, $reportErrors);

$typicalReportBuilder = new TypicalUserSyncReport(
    $fs,
    $successReport,
    $errorReport,
    new CsvReportConverter(),
    new ConsoleProfiler()
);

$pipeline = new UserSyncPipelineData();

$typicalReportBuilder([
    'prev' => $pipeline,
    'parameters' => [
        'successReportFilename' => 'done.csv',
        'errorReportFilename' => 'error.csv'
    ]
]);