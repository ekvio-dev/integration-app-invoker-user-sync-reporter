<?php
declare(strict_types=1);

namespace Ekvio\Integration\Invoker\Report;

use RuntimeException;

/**
 * Class UserSuccessSyncReport
 * @package Ekvio\Integration\Invoker\Report
 */
class UserSuccessSyncReport implements Reporter
{
    private const SUCCESS_STATUS = ['created', 'updated'];
    private const FIELD_STATUS = 'status';
    private const FIELD_LOGIN = 'login';
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
     * @param array $data
     * @param array $options
     * @return ReportCollector
     */
    public function build(array $data, array $options = []): ReportCollector
    {
        if(!isset($data['users'])) {
            throw new RuntimeException('Parameter key "users" is not set');
        }

        if(!isset($data['syncLog'])) {
            throw new RuntimeException(sprintf('Parameter key "syncLog" is not set'));
        }

        $this->loadSuccessLog($data['syncLog']);
        $headers = $this->header->headers();

        $content = [];
        foreach ($data['users'] as $index => $user) {

            $loginField = $this->header->getHeaderByField(self::FIELD_LOGIN);
            $login = $user[$loginField] ?? null;
            if($login && isset($this->syncLog[$login])) { //success user must present in log
                foreach ($headers as $header) {
                    $content[$index][] = $this->getValue($index, $user, $header);
                }
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
            if(in_array($log['status'], self::SUCCESS_STATUS, true)) {
                $this->syncLog[$log['login']] = $log['data'];
            }
        }
    }

    /**
     * @param int $index
     * @param array $user
     * @param string $header
     * @return string|null
     */
    private function getValue(int $index, array $user, string $header)
    {
        $status = $this->header->getHeaderByField(self::FIELD_STATUS);
        $login = $this->header->getHeaderByField(self::FIELD_LOGIN);

        if($header === $status) {
            $login = $user[$login] ?? null;
            return $this->syncLog[$login]['status'] ?? self::NOT_FOUND_FIELD_VALUE;
        }

        return $user[$header];
    }
}