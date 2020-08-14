<?php
declare(strict_types=1);

namespace Ekvio\Integration\Invoker;

use Ekvio\Integration\Contracts\Invoker;
use Ekvio\Integration\Contracts\Profiler;
use Ekvio\Integration\Invoker\Report\ReportConverter;
use Ekvio\Integration\Invoker\Report\Reporter;
use League\Flysystem\FilesystemInterface;
use RuntimeException;

/**
 * Class UserSyncAggregateReport
 * @package Ekvio\Integration\Invoker
 */
class TypicalUserSyncReport implements Invoker
{
    private const NAME = 'User sync aggregate report';

    /**
     * @var FilesystemInterface
     */
    private $fs;
    /**
     * @var Reporter
     */
    private $successReport;
    /**
     * @var Reporter
     */
    private $errorReport;
    /**
     * @var ReportConverter
     */
    private $converter;
    /**
     * @var Profiler
     */
    private $profiler;

    public function __construct(
        FilesystemInterface  $fs,
        Reporter $successReport,
        Reporter $errorReport,
        ReportConverter $converter,
        Profiler $profiler
    ) {
        $this->fs = $fs;
        $this->successReport = $successReport;
        $this->errorReport = $errorReport;
        $this->converter = $converter;
        $this->profiler = $profiler;
    }

    /**
     * @param array $arguments
     */
    public function __invoke(array $arguments = [])
    {
        if(!isset($arguments['prev'])) {
            throw new RuntimeException('No sync log in "prev" key');
        }

        if(empty($arguments['successReportFilename'])) {
            throw new RuntimeException('Success report filename not set');
        }

        if(empty($arguments['errorReportFilename'])) {
            throw new RuntimeException('Error report filename not set');
        }

        $usersFile = $arguments['users'];
        $users = [];

        $this->profiler->profile(sprintf('Check %s file existence...', $usersFile));
        if(!$this->fs->has($usersFile)) {
            if(($users = $this->fs->read($usersFile)) === false) {
                throw new RuntimeException(sprintf('Read error %s file', $usersFile));
            }
            $users = json_decode($users, true);
            $this->profiler->profile(sprintf('Get %s users from file', count($users)));
        }

        $this->profiler->profile('Build success report...');
        $successReport = $this->successReport->build([
            'users' => $users,
            'syncLog' => $arguments['prev']
        ]);
        $this->profiler->profile('Convert report...');
        $data = $this->converter->convert($successReport);
        $this->profiler->profile(sprintf('Write success report data to %s', $arguments['successReportFilename']));
        $this->writeReportToFile($arguments['successReportFilename'], $data);

        $this->profiler->profile('Build error report....');
        $errorReport = $this->errorReport->build([
            'users' => $users,
            'syncLog' => $arguments['prev']
        ]);
        $this->profiler->profile('Convert error report');
        $data = $this->converter->convert($errorReport);
        $this->profiler->profile(sprintf('Write error report data to %s', $arguments['errorReportFilename']));
        $this->writeReportToFile($arguments['errorReportFilename'], $data);
    }

    /**
     * @param string $filename
     * @param string $data
     */
    private function writeReportToFile(string $filename, string $data): void
    {
        if($this->fs->put($filename, $data) === false) {
            throw new RuntimeException(sprintf('Write error in %...', $filename));
        }
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return self::NAME;
    }
}