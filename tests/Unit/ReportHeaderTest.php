<?php
declare(strict_types=1);

namespace Ekvio\Integration\Invoker\Tests\Unit;

use Ekvio\Integration\Invoker\Report\ReportHeader;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Class ReportHeaderTest
 * @package Ekvio\Integration\Invoker\Tests\Unit
 */
class ReportHeaderTest extends TestCase
{
    private function headerMap(): array
    {
        return [
            'login' => 'USR_LOGIN',
            'first_name' => 'USR_FIRST_NAME',
            'last_name' => 'USR_LAST_NAME',
            'email' => 'USR_EMAIL',
            'phone' => 'USR_MOBILE',
            'chief_email' => 'MANAGER_EMAIL',
            'status' => 'USR_UDF_USER_FIRED',
            'groups.region' => 'REGION_NAME',
            'groups.city' => 'CITY_NAME',
            'groups.role' => 'ROLE',
            'groups.position' => 'POSITION_NAME',
            'groups.team' => 'TEAM_NAME',
            'groups.department' => 'DEPARTAMENT_NAME',
            'groups.assignment' => 'ASSIGNMENT_NAME',
        ];
    }

    public function testDefaultReportHeader()
    {
        $header = new ReportHeader();
        $this->assertEquals(array_values($this->headerMap()), $header->headers());
    }

    public function testMutateHeaderMap()
    {
        $header = new ReportHeader([
            'login' => 'Логин',
            'groups.assignment' => 'Назначение'
        ]);

        $headerMap = $this->headerMap();
        $headerMap['login'] = 'Логин';
        $headerMap['groups.assignment'] = 'Назначение';

        $this->assertEquals(array_values($headerMap), $header->headers());
    }

    public function testAddFormsToHeaders()
    {
        $header = (new ReportHeader())->addForms([
            '1' => 'OTCH',
            '100' => 'HIRE_DATE'
        ]);

        $headerMap = $this->headerMap();
        $headerMap['forms.1'] = 'OTCH';
        $headerMap['forms.100'] = 'HIRE_DATE';

        $this->assertEquals(array_values($headerMap), $header->headers());
    }

    public function testGetFieldByHeader()
    {
        $header = new ReportHeader(['login' => 'Логин']);
        $this->assertEquals('login', $header->getFieldByHeader('Логин'));

        $this->expectException(RuntimeException::class);
        $this->assertEquals('login', $header->getFieldByHeader('Логин2'));
    }
}