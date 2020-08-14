<?php
declare(strict_types=1);

namespace Ekvio\Integration\Invoker\Report;

/**
 * Interface Reporter
 * @package Ekvio\Integration\Invoker\Report
 */
interface Reporter
{
    /**
     * @param array $data
     * @param array $options
     * @return ReportCollector
     */
    public function build(array $data, array $options=[]): ReportCollector;
}