<?php
declare(strict_types=1);

namespace Ekvio\Integration\Invoker\Report;

/**
 * Interface ReportCollector
 * @package Ekvio\Integration\Invoker\Report
 */
interface ReportCollector
{
    /**
     * @param array $options
     * @return array
     */
    public function header(array $options = []): array;

    /**
     * @param array $options
     * @return array
     */
    public function content(array $options = []): array;
}