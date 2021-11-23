<?php
declare(strict_types=1);

namespace Unit;

use Ekvio\Integration\Invoker\Report\ReportError;
use PHPUnit\Framework\TestCase;

/**
 * Class ReportErrorTest
 * @package Unit
 */
class ReportErrorTest extends TestCase
{
    public function testGetDefaultReportErrors()
    {
        $error = new ReportError();
        $this->assertIsArray($error->errors());
    }

    public function testGetErrorByError()
    {
        $error = new ReportError();
        $this->assertEquals('PHONE_NUNIQ', $error->getError('phone', 'Value is not unique.'));
        $this->assertEquals('UNKNWN_ERR', $error->getError('unknown', 'Error not exist'));
    }

    public function testAddedErrorToReportError()
    {
        $error = new ReportError([
            'errorMap' => [
                'tabnumber_tabnumber_required' => 'TABNUMBER_NVALID'
            ]
        ]);
        $this->assertEquals('TABNUMBER_NVALID', $error->getError('tabnumber', 'Tabnumber required'));
        $this->assertEquals('UNKNWN_ERR', $error->getError('unknown', 'Error not exist'));
    }

    public function testUnknownErrorInHeader()
    {
        $error = new ReportError();
        $this->assertTrue(in_array('UNKNWN_ERR', $error->errors()));
    }
}