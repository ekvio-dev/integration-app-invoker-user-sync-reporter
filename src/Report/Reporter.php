<?php
declare(strict_types=1);

namespace Ekvio\Integration\Invoker\Report;

use Ekvio\Integration\Contracts\User\UserPipelineData;

/**
 * Interface Reporter
 * @package Ekvio\Integration\Invoker\Report
 */
interface Reporter
{
    /**
     * @param UserPipelineData $userPipelineData
     * @param array $options
     * @return ReportCollector
     */
    public function build(UserPipelineData $userPipelineData, array $options=[]): ReportCollector;
}