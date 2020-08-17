<?php
declare(strict_types=1);

use Ekvio\Integration\Contracts\Profiler;
use Ekvio\Integration\Extractor\DataFromCsv;
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

$fs = new Filesystem(new Local(__DIR__ . '/tmp' ));

$reportHeader = (new ReportHeader([
    'status' => 'USER_ACTIVE',
    'groups.region' => 'HOLDING_NAME',
    'groups.city' => 'BE_NAME',
    'groups.role' => 'DIVISION_NAME',
    'groups.position' => 'USR_UDF_POSITIONNAME',
    'groups.team' => 'TERRITORY_NAME',
    'groups.department' => 'TEAM_NAME'
], [
    'withoutFields' => ['groups.assignment']]
))->addForms([
    '1' => 'USR_PATR',
    '2' => 'COMP_DATE',
    '3' => 'POS_DATE',
    '4' => 'TABNUMBER'
]);
$reportErrors = new ReportError();

$successReport = new UserSuccessSyncReport($reportHeader);
$errorReport = new UserErrorSyncReport($reportHeader, $reportErrors);

$userExtractor = DataFromCsv::fromFile('tmp/users.csv');

$syncLog = [
    [
        'index' => 1,
        'login' => 'AMitin',
        'status' => 'created',
        'data' => [
            'status' => 'active'
        ]
    ],
    [
        'index' => 2,
        'login' => 'EMikhalev',
        'status' => 'updated',
        'data' => [
            'status' => 'blocked'
        ]
    ],
    [
        'index' => 4,
        'login' => 'SMelnikov',
        'status' => 'created',
        'data' => [
            'status' => 'active'
        ]
    ],
    [
        'index' => 8,
        'login' => 'MTroitskiy',
        'status' => 'created',
        'data' => [
            'status' => 'blocked'
        ]
    ],
    [
        'index' => 8,
        'login' => 'APetrov',
        'status' => 'updated',
        'data' => [
            'status' => 'active'
        ]
    ],
    [

        "index" => 3,
        "login" => "KKolentionok",
        "status" => "error",
        "errors" => [
            [
                "code" => 1007,
                "field" => "first_name",
                "message" => "First name required"
            ],
        ]
    ],
    [

        "index" => 5,
        "login" => "DKoroteev",
        "status" => "error",
        "errors" => [
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
        ]
    ],
    [

        "index" => 6,
        "login" => "Artamonov.ma",
        "status" => "error",
        "errors" => [
            [
                "code" => 1007,
                "field" => "phone",
                "message" => "Phone required"
            ],
        ]
    ],
    [

        "index" => 7,
        "login" => null,
        "status" => "error",
        "errors" => [
            [
                "code" => 1007,
                "field" => "login",
                "message" => "Login required"
            ],
        ]
    ],

];


$typicalReportBuilder = new TypicalUserSyncReport(
    $userExtractor,
    $fs,
    $successReport,
    $errorReport,
    new CsvReportConverter(),
    new ConsoleProfiler()
);

$typicalReportBuilder([
    'prev' => $syncLog,
    'successReportFilename' => 'done.csv',
    'errorReportFilename' => 'error.csv'
]);