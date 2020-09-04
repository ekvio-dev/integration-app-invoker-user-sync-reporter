<?php
declare(strict_types=1);

namespace Ekvio\Integration\Invoker\Report;

use Ekvio\Integration\Contracts\User\UserPipelineData;

/**
 * Class UserErrorSyncReport
 * @package Ekvio\Integration\Invoker\Report
 */
class UserErrorSyncReport implements Reporter
{
    private const ERROR_STATUS = 'error';
    private const FIELD_SOURCE = 'source';
    /**
     * @var ReportHeader
     */
    private $header;
    /**
     * @var ReportError
     */
    private $reportErrors;
    /**
     * @var array
     */
    private $syncErrorsLog = [];


    /**
     * UserErrorSyncReport constructor.
     * @param ReportHeader $header
     * @param ReportError $reportErrors
     */
    public function __construct(ReportHeader $header, ReportError $reportErrors)
    {
        $this->header = $header;
        $this->reportErrors = $reportErrors;
    }

    /**
     * @param UserPipelineData $userPipelineData
     * @param array $options
     * @return ReportCollector
     */
    public function build(UserPipelineData $userPipelineData, array $options = []): ReportCollector
    {
        $this->loadSyncErrorLog($userPipelineData->logs());

        $headers = $this->header->headers();
        $reportErrors = $this->reportErrors->errors();

        $content = [];
        foreach ($this->syncErrorsLog as $index => $log) {

            $user = $userPipelineData->dataFromSource($log['index']);
            $sourceName = $userPipelineData->sourceName($log['index']);

            foreach ($headers as $header) {

                $source = $this->header->getHeaderByField(self::FIELD_SOURCE);
                if($header === $source) {
                    $content[$index][] = $sourceName;
                    continue;
                }

                $content[$index][] = $user[$header];
            }

            $errors = $this->convertErrors($log['errors']);
            foreach ($reportErrors as $reportError) {
                $content[$index][] = in_array($reportError, $errors, true) ? 1 : 0;
            }

        }

        return ReportDataCollector::create(array_merge($headers, $reportErrors), $content);
    }

    /**
     * @param array $errors
     * @return array
     */
    private function convertErrors(array $errors): array
    {
        $data = [];
        foreach ($errors as $error) {
            $data[] = $this->reportErrors->getError($error['field'], $error['message']);
        }

        return $data;
    }

    /**
     * @param array $logs
     */
    private function loadSyncErrorLog(array $logs): void
    {
        foreach ($logs as $log) {
            if($log['status'] === self::ERROR_STATUS) {
                $this->syncErrorsLog[] = $log;
            }
        }

        usort($this->syncErrorsLog, function ($a, $b) {
            return $a['index'] <=> $b['index'];
        });
    }
}