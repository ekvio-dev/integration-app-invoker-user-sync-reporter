<?php
declare(strict_types=1);

namespace Ekvio\Integration\Invoker\Report;

/**
 * Class ReportDataCollector
 * @package Ekvio\Integration\Invoker\Report
 */
class ReportDataCollector implements ReportCollector
{
    /**
     * @var array
     */
    private $header = [];
    /**
     * @var array
     */
    private $content = [];

    private function __construct(){}

    /**
     * @param array $header
     * @param array $content
     * @return static
     */
    public static function create(array $header, array $content): self
    {
        $self = new self();
        $self->header = $header;
        $self->content = $content;

        return $self;
    }

    /**
     * @param array $options
     * @return array
     */
    public function header(array $options = []): array
    {
        return $this->header;
    }

    /**
     * @param array $options
     * @return array
     */
    public function content(array $options = []): array
    {
        return $this->content;
    }
}