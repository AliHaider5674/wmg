<?php
namespace Tests\Unit\Core\Models\Service\Event;

use App\Models\Service;
use App\Models\Validator\RegexRuleValidator;
use Tests\TestCase;
use App\Models\Service\Event\ServiceRuleValidator;
use App\Models\Service\Model\Shipment;
use App\Models\Service\Event\MetaDataExtractor;
use App\Models\Service\Model\Serialize;

/**
 * Test provider, for renewing token
 *
 * Class TokenProviderTest
 * @category WMG
 * @package  Tests\Unit\Mdc\Service
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class ServiceRuleValidatorTest extends TestCase
{
    /** @var ServiceRuleValidator */
    private $validator;
    private $sampleUSRequest;
    private $sampleEURequest;
    private $sampleAthenaRequest;
    private $serviceMock;

    public function setUp():void
    {
        parent::setUp();

        $extractorMock = $this->getMockBuilder(MetaDataExtractor::class)
            ->onlyMethods(['getMetaData'])
            ->getMock();
        $extractorMock->method('getMetaData')
            ->willReturnCallback(
                function (Serialize $model) {
                    return ['sales_channel' => $model->getSalesChannel()];
                }
            );

        $regexRuleValidator = new RegexRuleValidator();
        $this->validator = new ServiceRuleValidator($extractorMock, $regexRuleValidator);
        $this->serviceMock = $this->getMockBuilder(Service::class)
            ->onlyMethods(['getEventRules'])
            ->getMock();

        ##SAMPLE US
        /**
         * @todo Shipment class does not have the method getSalesChannel. Are we sure this is right?
         * this
         */
        $this->sampleUSRequest = $this->getMockBuilder(Shipment::class)
            ->addMethods(['getSalesChannel'])
            ->getMock();
        $this->sampleUSRequest->method('getSalesChannel')
            ->willReturn('M113US-BRUNO-MARS-02');

        ##SAMPLE EU
        $this->sampleEURequest = $this->getMockBuilder(Shipment::class)
            ->addMethods(['getSalesChannel'])
            ->getMock();

        $this->sampleEURequest->method('getSalesChannel')
            ->willReturn('M113EU-BRUNO-MARS');

        ##SAMPLE ATHENA
        $this->sampleAthenaRequest = $this->getMockBuilder(Shipment::class)
            ->addMethods(['getSalesChannel'])
            ->getMock();

        $this->sampleAthenaRequest->method('getSalesChannel')
            ->willReturn('SHINEDOWN_NA');
    }

    /**
     * @todo This test seems to work no matter the input. Might be worth looking into it.
     */
    public function testStandardEmpty()
    {
        $this->serviceMock->expects($this->atLeastOnce())
            ->method('getEventRules')
            ->willReturnOnConsecutiveCalls(
                [],
                null,
                [
                    'SOMETHING NOT EXIST' => '^M113US.*',
                ]
            );
        $this->assertTrue($this->validator->isPassed($this->serviceMock, $this->sampleUSRequest));
        $this->assertTrue($this->validator->isPassed($this->serviceMock, $this->sampleUSRequest));

        //Invalid Key False
        $this->assertTrue($this->validator->isPassed($this->serviceMock, $this->sampleUSRequest));
    }


    public function testStandardOneCondition()
    {
        //One Condition True then False
        $this->serviceMock->expects($this->exactly(2))
            ->method('getEventRules')
            ->willReturnOnConsecutiveCalls([
                'sales_channel' => '^M113US.*'
            ], [
                'sales_channel' => '^M113EU.*'
            ]);
        $this->assertTrue($this->validator->isPassed($this->serviceMock, $this->sampleUSRequest));
        $this->assertFalse($this->validator->isPassed($this->serviceMock, $this->sampleUSRequest));
    }

    public function testUSCases()
    {
        $this->serviceMock->method('getEventRules')
            ->willReturn([
                'sales_channel' => '^M113US.*',
            ]);
        $this->assertTrue($this->validator->isPassed($this->serviceMock, $this->sampleUSRequest));
        $this->assertFalse($this->validator->isPassed($this->serviceMock, $this->sampleEURequest));
        $this->assertFalse($this->validator->isPassed($this->serviceMock, $this->sampleAthenaRequest));
    }

    public function testEUCases()
    {
        $this->serviceMock->method('getEventRules')
            ->willReturn([
                'sales_channel' => '^M113EU.*',
            ]);
        $this->assertFalse($this->validator->isPassed($this->serviceMock, $this->sampleUSRequest));
        $this->assertTrue($this->validator->isPassed($this->serviceMock, $this->sampleEURequest));
        $this->assertFalse($this->validator->isPassed($this->serviceMock, $this->sampleAthenaRequest));
    }

    public function testAthenaCases()
    {
        $this->serviceMock->method('getEventRules')
            ->willReturn([
                'sales_channel' => '^(?!M113US|M113EU)',
            ]);
        $this->assertFalse($this->validator->isPassed($this->serviceMock, $this->sampleUSRequest));
        $this->assertFalse($this->validator->isPassed($this->serviceMock, $this->sampleEURequest));
        $this->assertTrue($this->validator->isPassed($this->serviceMock, $this->sampleAthenaRequest));
    }
}
