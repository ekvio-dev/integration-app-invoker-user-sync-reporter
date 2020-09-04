<?php
declare(strict_types=1);

namespace Ekvio\Integration\Invoker\Report;

use Ekvio\Integration\Contracts\User\UserPipelineData;
/**
 * Class UserSuccessSyncReport
 * @package Ekvio\Integration\Invoker\Report
 */
class UserSuccessSyncReport implements Reporter
{
    private const SUCCESS_STATUSES = ['created', 'updated', 'unchanged'];
    private const FIELD_STATUS = 'status';
    private const FIELD_SOURCE = 'source';
    private const NOT_FOUND_FIELD_VALUE = '-';

    /**
     * @var ReportHeader
     */
    private $header;
    /**
     * @var array
     */
    private $syncLog = [];

    /**
     * UserSuccessSyncReport constructor.
     * @param ReportHeader $header
     */
    public function __construct(ReportHeader $header)
    {
        $this->header = $header;
    }

    /**
     * @param UserPipelineData $userPipelineData
     * @param array $options
     * @return ReportCollector
     */
    public function build(UserPipelineData $userPipelineData, array $options = []): ReportCollector
    {
        $this->loadSuccessLog($userPipelineData->logs());
        $headers = $this->header->headers();

        $content = [];

        foreach ($this->syncLog as $index => $log) {
            $user = $userPipelineData->dataFromSource($log['index']);
            $sourceName = $userPipelineData->sourceName($log['index']);

            foreach ($headers as $header) {
                $content[$index][] = $this->getValue($header, $sourceName, $user, $log);
            }
        }

        return ReportDataCollector::create($headers, $content);
    }

    /**
     * @param array $logs
     * @return void
     */
    private function loadSuccessLog(array $logs): void
    {
        foreach ($logs as $log) {
            if(!empty($log['index']) && in_array($log['status'], self::SUCCESS_STATUSES, true)) {
                $this->syncLog[] = $log;
            }
        }

        usort($this->syncLog, function ($a, $b) {
            return $a['index'] <=> $b['index'];
        });
    }

    /**
     * @param string $header
     * @param string $sourceName
     * @param array $user
     * @param array $log
     * @return string|null
     */
    private function getValue(string $header, string $sourceName, array $user, array $log)
    {
        $source = $this->header->getHeaderByField(self::FIELD_SOURCE);
        if($header === $source) {
            return $sourceName;
        }

        $status = $this->header->getHeaderByField(self::FIELD_STATUS);

        if($header === $status) {
            return $log['data']['status'] ?? self::NOT_FOUND_FIELD_VALUE;
        }

        if(!array_key_exists($header, $user)) {
            return self::NOT_FOUND_FIELD_VALUE;
        }

        return $user[$header];
    }
}