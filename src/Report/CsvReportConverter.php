<?php
declare(strict_types=1);

namespace Ekvio\Integration\Invoker\Report;

use League\Csv\Writer;

/**
 * Class CsvReportConverter
 * @package Ekvio\Integration\Invoker\Report
 */
class CsvReportConverter implements ReportConverter
{
    /**
     * @param ReportCollector $collector
     * @return mixed|string
     * @throws \League\Csv\CannotInsertRecord
     */
    public function convert(ReportCollector $collector)
    {
        $writer = Writer::createFromString();
        $writer->insertOne($collector->header());
        $writer->insertAll($collector->content());

        return $writer->getContent();
    }
}