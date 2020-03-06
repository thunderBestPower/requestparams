<?php

namespace Esc\Tests;

use Esc\RequestParams;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophet;
use Symfony\Component\HttpFoundation\Request;

class RequestParamsTest extends TestCase
{
    private $request;

    public function setUp(): void
    {
        parent::setUp();
        $prophet = new Prophet();
        $this->request = $prophet->prophesize(Request::class);
    }

    public function stringProvider(): array
    {
        return [
            [''],
            ['foo'],
            ['hello world']
        ];
    }

    /**
     * @dataProvider stringProvider
     * @param $returnValue
     */
    public function testRequestParamsFiltersReturnJsonException($returnValue): void
    {
        $this->request->get('filters', '{}')->willReturn($returnValue);
        $this->expectException(\JsonException::class);
        RequestParams::fromRequest($this->request->reveal());
    }

    public function filtersProvider(): array
    {
        return [
            [null],
            [false],
            [[]]
        ];
    }

    /**
     * @dataProvider filtersProvider
     * @param $returnValue
     */
    public function testRequestParamsFiltersReturnException($returnValue): void
    {
        $this->request->get('filters', '{}')->willReturn($returnValue);
        $this->expectException(\RuntimeException::class);
        RequestParams::fromRequest($this->request->reveal());
    }

    public function testRequestParamsFiltersResultBasedOnRequestFilters(): void
    {
        $expectedFilters = [
            'foo' => 'bar'
        ];
        $this->request->get('filters', '{}')->willReturn('{"foo": "bar"}');
        $actualRequestParams = RequestParams::fromRequest($this->request->reveal());
        $actualFilters = $actualRequestParams->get('filters');
        $this->assertEquals($expectedFilters, $actualFilters);
    }

    public function testIfRequestParamsSetEmptyArrayWhenSortByIsNull(): void
    {
        $this->request->get('filters', '{}')->willReturn('{"foo": "bar"}');
        $this->request->get('sortBy')->willReturn(null);
        $requestParams = RequestParams::fromRequest($this->request->reveal());
        $requestParamsValue = $requestParams->get('sortBy');
        $this->assertEquals([], $requestParamsValue);
    }

    public function testIfDescendingIsTrue(): void
    {
        $expectedValue = [
            'foo' => 'DESC',
        ];
        $this->request->get('filters', '{}')->willReturn('{"foo": "bar"}');
        $this->request->get('sortBy')->willReturn('foo');
        $this->request->get('descending')->willReturn(true);
        $requestParams = RequestParams::fromRequest($this->request->reveal());
        $requestParamsValue = $requestParams->get('sortBy');
        $this->assertEquals($expectedValue, $requestParamsValue);
    }

    public function testIfDescendingIsFalse(): void
    {
        $expectedValue = [
            'foo' => 'ASC',
        ];
        $this->request->get('filters', '{}')->willReturn('{"foo": "bar"}');
        $this->request->get('sortBy')->willReturn('foo');
        $this->request->get('descending')->willReturn(false);
        $requestParams = RequestParams::fromRequest($this->request->reveal());
        $requestParamsValue = $requestParams->get('sortBy');
        $this->assertEquals($expectedValue, $requestParamsValue);
    }

    public function testRowsPerPageAreEqualToLimitWhenRowsPerPageAreMoreThan0(): void
    {
        $this->request->get('filters', '{}')->willReturn('{"foo": "bar"}');
        $this->request->get(Argument::cetera())->willReturn(null);
        $this->request->get('rowsPerPage')->willReturn(3);
        $requestParams = RequestParams::fromRequest($this->request->reveal());
        $requestParamsValue = $requestParams->get('limit');
        $this->assertEquals(3, $requestParamsValue);
    }

    public function testLimitIsNullWhenRowsPerPageAre0(): void
    {
        $this->request->get('filters', '{}')->willReturn('{"foo": "bar"}');
        $this->request->get(Argument::cetera())->willReturn(null);
        $this->request->get('rowsPerPage')->willReturn(0);
        $requestParams = RequestParams::fromRequest($this->request->reveal());
        $requestParamsValue = $requestParams->get('limit');
        $this->assertEquals(null, $requestParamsValue);
    }

    public function testIfOffsetIsNot0(): void
    {
        $this->request->get('filters', '{}')->willReturn('{"foo": "bar"}');
        $this->request->get(Argument::cetera())->willReturn(null);
        $this->request->get('rowsPerPage')->willReturn(10);
        $this->request->get('page')->willReturn(2);
        $requestParams = RequestParams::fromRequest($this->request->reveal());
        $requestParamsValue = $requestParams->get('offset');
        $this->assertEquals(10, $requestParamsValue);
    }

    public function testIfOffsetIs0(): void
    {
        $this->request->get('filters', '{}')->willReturn('{"foo": "bar"}');
        $this->request->get(Argument::cetera())->willReturn(null);
        $this->request->get('rowsPerPage')->willReturn(10);
        $this->request->get('page')->willReturn(1);
        $requestParams = RequestParams::fromRequest($this->request->reveal());
        $requestParamsValue = $requestParams->get('offset');
        $this->assertEquals(0, $requestParamsValue);
    }
}
