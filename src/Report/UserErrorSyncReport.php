<?php
declare(strict_types=1);

namespace Ekvio\Integration\Invoker\Report;

use RuntimeException;

/**
 * Class UserErrorSyncReport
 * @package Ekvio\Integration\Invoker\Report
 */
class UserErrorSyncReport implements Reporter
{
    private const ERROR_STATUS = 'error';
    private const FILED_LOGIN = 'login';
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
     * @param array $data
     * @param array $options
     * @return ReportCollector
     */
    public function build(array $data, array $options = []): ReportCollector
    {
        if(!isset($data['users']) || !is_array($data['users'])) {
            throw new RuntimeException('Parameter key "users" is not set or is not array');
        }

        if(!isset($data['syncLog']) || !is_array($data['syncLog'])) {
            throw new RuntimeException(sprintf('Parameter key "syncLog" is not set or is not array'));
        }

        $this->loadSyncErrorLog($data['syncLog']);

        $content = [];
        $loginField = $this->header->getHeaderByField(self::FILED_LOGIN);
        $headers = $this->header->headers();
        $reportErrors = $this->reportErrors->errors();
        foreach ($data['users'] as $index => $user) {

            $username = null;
            foreach ($headers as $header) {
                if($loginField === $header) {
                    $username = $user[$header];
                }
                $content[$index][] = $user[$header];
            }

            $errors = $username !== null ? $this->findErrorsByUsername($username) : $this->findErrorsByPosition($index);
            foreach ($reportErrors as $reportError) {
                $content[$index][] = in_array($reportError, $errors, true) ? 1 : 0;
            }
        }

        $header = array_merge($headers, $reportErrors);
        return ReportDataCollector::create($header, $content);
    }

    /**
     * @param string $username
     * @return array
     */
    private function findErrorsByUsername(string $username): array
    {
        return $this->convertErrors($this->syncErrorsLog[$username] ?? []);
    }

    /**
     * @param int $position
     * @return array
     */
    private function findErrorsByPosition(int $position): array
    {
        return $this->convertErrors($this->syncErrorsLog[$position] ?? []);
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
                $key = $log['login'] ?? $log['index'];
                if(!$key) {
                    continue;
                }

                $errors = $log['errors'] ?? null;
                if($errors) {
                    $this->syncErrorsLog[$key] = $errors;
                }
            }
        }
    }
}