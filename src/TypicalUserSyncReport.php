<?php
declare(strict_types=1);

namespace Ekvio\Integration\Invoker;

use Ekvio\Integration\Contracts\Invoker;
use Ekvio\Integration\Contracts\Profiler;
use Ekvio\Integration\Contracts\User\UserPipelineData;
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
    protected const NAME = 'User sync aggregate report';

    /**
     * @var FilesystemInterface
     */
    protected $fs;
    /**
     * @var Reporter
     */
    protected $successReport;
    /**
     * @var Reporter
     */
    protected $errorReport;
    /**
     * @var ReportConverter
     */
    protected $converter;
    /**
     * @var Profiler
     */
    protected $profiler;

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

        if(!$arguments['prev'] instanceof UserPipelineData) {
            throw new RuntimeException('Prev argument must be UserPipelineData type');
        }

        if(empty($arguments['parameters']['successReportFilename'])) {
            throw new RuntimeException('Success report filename not set');
        }

        if(empty($arguments['parameters']['errorReportFilename'])) {
            throw new RuntimeException('Error report filename not set');
        }

        $userSyncPipelineData = $arguments['prev'];
        $successReportFilename = $arguments['parameters']['successReportFilename'];
        $errorReportFilename = $arguments['parameters']['errorReportFilename'];

        $this->profiler->profile('Build success report...');
        $successReport = $this->successReport->build($userSyncPipelineData);
        $this->profiler->profile('Convert report...');
        $data = $this->converter->convert($successReport);
        $this->profiler->profile(sprintf('Write success report data to %s', $successReportFilename));
        $this->writeReportToFile($successReportFilename, $data);

        $this->profiler->profile('Build error report....');
        $errorReport = $this->errorReport->build($userSyncPipelineData);
        $this->profiler->profile('Convert error report');
        $data = $this->converter->convert($errorReport);
        $this->profiler->profile(sprintf('Write error report data to %s', $errorReportFilename));
        $this->writeReportToFile($errorReportFilename, $data);

        return $userSyncPipelineData;
    }

    /**
     * @param string $filename
     * @param string $data
     */
    protected function writeReportToFile(string $filename, string $data): void
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