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
    private const DELIMITER = ';';

    /**
     * @param ReportCollector $collector
     * @param array $options
     * @return mixed|string
     * @throws \League\Csv\CannotInsertRecord
     * @throws \League\Csv\Exception
     */
    public function convert(ReportCollector $collector, array $options = [])
    {
        $writer = self::createWriter($options);
        $writer->insertOne($collector->header());
        $writer->insertAll($collector->content());

        return $writer->getContent();
    }

    /**
     * @param array $options
     * @return Writer
     * @throws \League\Csv\Exception
     */
    private static function createWriter(array $options = []): Writer
    {
        $writer = Writer::createFromString();
        $writer->setDelimiter($options['delimiter'] ?? self::DELIMITER);

        return $writer;
    }
}