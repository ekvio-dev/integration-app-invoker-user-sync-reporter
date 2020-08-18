<?php
declare(strict_types=1);

namespace Ekvio\Integration\Invoker;

use Ekvio\Integration\Contracts\Extractor;
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


    private $extractor;
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
        Extractor $extractor,
        FilesystemInterface  $fs,
        Reporter $successReport,
        Reporter $errorReport,
        ReportConverter $converter,
        Profiler $profiler
    ) {
        $this->extractor = $extractor;
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

        if(empty($arguments['parameters']['successReportFilename'])) {
            throw new RuntimeException('Success report filename not set');
        }

        if(empty($arguments['parameters']['errorReportFilename'])) {
            throw new RuntimeException('Error report filename not set');
        }

        $successReportFilename = $arguments['parameters']['successReportFilename'];
        $errorReportFilename = $arguments['parameters']['errorReportFilename'];

        $this->profiler->profile('Extract users...');
        $users = $this->extractor->extract();

        $this->profiler->profile('Build success report...');
        $successReport = $this->successReport->build([
            'users' => $users,
            'syncLog' => $arguments['prev']
        ]);
        $this->profiler->profile('Convert report...');
        $data = $this->converter->convert($successReport);
        $this->profiler->profile(sprintf('Write success report data to %s', $successReportFilename));
        $this->writeReportToFile($successReportFilename, $data);

        $this->profiler->profile('Build error report....');
        $errorReport = $this->errorReport->build([
            'users' => $users,
            'syncLog' => $arguments['prev']
        ]);
        $this->profiler->profile('Convert error report');
        $data = $this->converter->convert($errorReport);
        $this->profiler->profile(sprintf('Write error report data to %s', $errorReportFilename));
        $this->writeReportToFile($errorReportFilename, $data);
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