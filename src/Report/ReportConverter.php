<?php
declare(strict_types=1);

namespace Ekvio\Integration\Invoker\Report;

/**
 * Interface ReportConvertor
 * @package Ekvio\Integration\Invoker\Report
 */
interface ReportConverter
{
    /**
     * @param ReportCollector $collector
     * @return mixed
     */
    public function convert(ReportCollector $collector);
}