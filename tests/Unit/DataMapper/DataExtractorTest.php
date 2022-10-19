<?php

namespace Tests\Unit\DataMapper;

use App\DataMapper\DataExtractor;
use App\DataMapper\Exceptions\InvalidMappingException;
use App\DataMapper\Map\VariableMap;
use App\DataMapper\Parsers\SyntaxParser;
use Illuminate\Support\Arr;
use Tests\TestCase;

/**
 * @class DataExtractorTest
 * Test data extractor
 */
class DataExtractorTest extends TestCase
{
    private DataExtractor $dataExtractor;
    private VariableMap $variableMap;

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function setUp(): void
    {
        parent::setUp();
        $arr = $this->app->make(Arr::class);
        $syntaxParser = $this->app->make(SyntaxParser::class);
        $this->dataExtractor = $this->app->makeWith(DataExtractor::class, ['arr' => $arr, 'parser' => $syntaxParser]);
        $this->variableMap = $this->app->make(VariableMap::class);
    }

    /**
     * @dataProvider srcDataProvider
     */
    public function testExtractSingle(array $srcData)
    {
        $map = [
            'customer_first_name' => 'customer.first_name',
            'customer_last_name' => 'customer.last_name',
            'not_exist' => 'customer.not_exist'
        ];
        $this->variableMap->setMap($map);
        $data = $this->dataExtractor->extract($srcData, $this->variableMap);
        $this->assertSame(
            [
                'customer_first_name' => 'Fname',
                'customer_last_name' => 'Lname',
                'not_exist' => null
            ],
            $data
        );
    }

    /**
     * @dataProvider srcDataProvider
     */
    public function testExtractCollection(array $srcData)
    {
        $map = [
            'customer_first_name' => 'customer.first_name',
            'order_id' => 'orders[].id',
            'order_amount' => 'orders[].amount',
            SyntaxParser::TYPE_KEY => 'collection'
        ];
        $this->variableMap->setMap($map);
        $data = $this->dataExtractor->extract($srcData, $this->variableMap);
        $this->assertSame(
            [
                [
                    'customer_first_name' => 'Fname',
                    'order_id' => 1,
                    'order_amount' => 100,
                ],
                [
                    'customer_first_name' => 'Fname',
                    'order_id' => 2,
                    'order_amount' => 200,
                ],
            ],
            $data
        );
    }

    /**
     * @dataProvider srcDataProvider
     */
    public function testMultipleKeyException($srcData)
    {
        $this->expectException(InvalidMappingException::class);
        $this->expectExceptionMessage('Unable to support multiple index.');
        $map = [
            'order_id' => 'orders[].id',
            'order_amount' => 'locations[].name',
            SyntaxParser::TYPE_KEY => 'collection'
        ];
        $this->variableMap->setMap($map);
        $this->dataExtractor->extract($srcData, $this->variableMap);
    }

    /**
     * @return array
     */
    public function srcDataProvider() : array
    {
        return [
            [
                [
                    'customer' => [
                        'first_name' => 'Fname',
                        'last_name' => 'Lname',
                    ],
                    'orders' => [
                        [
                            'id' => 1,
                            'amount' => 100
                        ],
                        [
                            'id' => 2,
                            'amount' => 200
                        ]
                    ]
                ],
            ]
        ];
    }
}
