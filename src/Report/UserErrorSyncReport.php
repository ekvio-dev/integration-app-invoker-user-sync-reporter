<?php
declare(strict_types=1);

namespace Ekvio\Integration\Invoker\Report;

use Ekvio\Integration\Contracts\User\UserPipelineData;
use RuntimeException;

/**
 * Class UserErrorSyncReport
 * @package Ekvio\Integration\Invoker\Report
 */
class UserErrorSyncReport implements Reporter
{
    private const ERROR_STATUS = 'error';
    private const FIELD_SOURCE = 'source';
    private const ERROR_VALUE = '1';
    private const NO_ERROR_VALUE = '0';
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

    private $groupErrors = [];

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
        $this->groupErrors = $this->reportErrors->errors(true);

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

                if(!array_key_exists($header, $user)) {
                    throw new RuntimeException(sprintf('For user from %s field %s not found.', $sourceName, $header));
                }

                $content[$index][] = $user[$header];
            }

            $errors = $this->convertErrors($log['errors']);
            $errorKeys = array_keys($errors);

            foreach ($reportErrors as $reportError) {
                if(!in_array($reportError, $errorKeys, true)) {
                    $content[$index][] = self::NO_ERROR_VALUE;
                    continue;
                }

                $content[$index][] = $this->render($reportError, $errors[$reportError]);
            }

        }

        return ReportDataCollector::create(array_merge($headers, $reportErrors), $content);
    }

    /**
     * @param string $errorKey
     * @param array $errors
     * @return string
     */
    private function render(string $errorKey, array $errors): string
    {
        if($errorKey === 'DUBLICAT') {
            return $errors[0]['extra'] ?? self::ERROR_VALUE;
        }

        if(in_array($errorKey, $this->groupErrors, true)) {
            return $this->renderGroupErrors($errors);
        }

        return self::ERROR_VALUE;
    }

    private function renderGroupErrors(array $errors): string
    {
        $data = '';
        foreach ($errors as $error) {
            [$message,] = explode('.', $error['message']);
            $data .= $message . '. ';
        }
        return trim($data, " ");
    }

    /**
     * @param array $errors
     * @return array
     */
    private function convertErrors(array $errors): array
    {
        $data = [];
        foreach ($errors as $error) {
            $key = $this->reportErrors->getError($error['field'], $error['message']);
            $data[$key][] = $error;
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