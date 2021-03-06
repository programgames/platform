<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Metadata\Annotation;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider constructorDataProvider
     *
     * @param array $data
     * @param string $expectedMode
     * @param string $expectedRouteName
     * @param string $expectedRouteView
     * @param string $expectedRouteCreate
     * @param array $expectedDefaultValues
     * @param array $expectedRoutes
     */
    public function testConstructor(
        array $data,
        $expectedMode,
        $expectedRouteName,
        $expectedRouteView,
        $expectedRouteCreate,
        array $expectedDefaultValues,
        array $expectedRoutes
    ) {
        $config = new Config($data);
        $this->assertEquals($expectedMode, $config->mode);
        $this->assertEquals($expectedRouteName, $config->routeName);
        $this->assertEquals($expectedRouteView, $config->routeView);
        $this->assertEquals($expectedRouteCreate, $config->routeCreate);
        $this->assertEquals($expectedDefaultValues, $config->defaultValues);
        $this->assertEquals($expectedRoutes, $config->routes);
    }

    public function testIncorrectMode()
    {
        $this->expectException(\Oro\Bundle\EntityConfigBundle\Exception\AnnotationException::class);
        $this->expectExceptionMessage('Annotation "Config" give invalid parameter "mode" : "some mode"');

        new Config(['mode' => 'some mode']);
    }

    public function testIncorrectDefaultValues()
    {
        $this->expectException(\Oro\Bundle\EntityConfigBundle\Exception\AnnotationException::class);
        $this->expectExceptionMessage(
            'Annotation "Config" parameter "defaultValues" expect "array" but "string" given'
        );

        new Config(['defaultValues' => 'some string']);
    }

    /**
     * @return array
     */
    public function constructorDataProvider()
    {
        return [
            [
                [],
                'default',
                '',
                '',
                '',
                [],
                []
            ],
            [
                ['mode' => 'readonly'],
                'readonly',
                '',
                '',
                '',
                [],
                []
            ],
            [
                ['value' => 'readonly'],
                'readonly',
                '',
                '',
                '',
                [],
                []
            ],
            [
                [
                    'mode'          => 'readonly',
                    'routeName'     => 'test_route_name',
                    'routeView'     => 'test_route_view',
                    'routeCreate'   => 'test_route_create',
                    'routeCustom'   => 'test_route_custom',
                    'defaultValues' => [
                        'test' => 'test_val'
                    ]
                ],
                'readonly',
                'test_route_name',
                'test_route_view',
                'test_route_create',
                [
                    'test' => 'test_val'
                ],
                [
                    'custom' => 'test_route_custom'
                ]
            ],
        ];
    }
}
