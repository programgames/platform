<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Action;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PropertyAccess\PropertyPath;

use Oro\Bundle\WorkflowBundle\Model\Action\CallServiceMethod;
use Oro\Bundle\ActionBundle\Model\ActionContext;
use Oro\Bundle\WorkflowBundle\Tests\Unit\Action\Stub\TestService;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;

class CallServiceMethodTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|ContainerInterface */
    protected $container;

    /** @var \PHPUnit_Framework_MockObject_MockObject|EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var CallServiceMethod */
    protected $action;

    protected function setUp()
    {
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->action = new CallServiceMethod(new ContextAccessor(), $this->container);
        $this->action->setDispatcher($this->eventDispatcher);
    }

    protected function tearDown()
    {
        unset($this->action, $this->eventDispatcher, $this->container);
    }

    public function testInitialize()
    {
        $serviceName = 'test_service';
        $this->assertContainerCalled($serviceName, 1, 1);
        $options = [
            'service' => $serviceName,
            'method' => 'testMethod',
            'method_parameters' => ['param' => 'value'],
            'attribute' => 'test'
        ];
        $this->assertInstanceOf(
            'Oro\Bundle\WorkflowBundle\Model\Action\ActionInterface',
            $this->action->initialize($options)
        );
        $this->assertAttributeEquals($options, 'options', $this->action);
    }

    /**
     * @dataProvider initializeExceptionDataProvider
     *
     * @param array $inputData
     * @param string $exception
     * @param string $exceptionMessage
     * @param bool $hasService
     */
    public function testInitializeException(array $inputData, $exception, $exceptionMessage, $hasService = true)
    {
        $this->container->expects($this->any())
            ->method('has')
            ->willReturn($hasService);
        $this->container->expects($this->any())
            ->method('get')
            ->willReturn(new TestService());
        $this->setExpectedException($exception, $exceptionMessage);
        $this->action->initialize($inputData);
    }

    /**
     * @return array
     */
    public function initializeExceptionDataProvider()
    {
        return [
            [
                'inputData' => [],
                'expectedException' => 'Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException',
                'expectedExceptionMessage' => 'Service name parameter is required'
            ],
            [
                'inputData' => [
                    'service' => 'test_service'
                ],
                'expectedException' => 'Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException',
                'expectedExceptionMessage' => 'Undefined service with name "test_service"',
                'hasService' => false
            ],
            [
                'inputData' => [
                    'service' => 'test_service'
                ],
                'expectedException' => 'Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException',
                'expectedExceptionMessage' => 'Method name parameter is required'
            ],
            [
                'inputData' => [
                    'service' => 'test_service',
                    'method' => 'test_method'
                ],
                'expectedException' => 'Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException',
                'expectedExceptionMessage' => 'Could not found public method "test_method" in service "test_service"'
            ]
        ];
    }

    public function testExecuteMethod()
    {
        $this->assertContainerCalled('test_service');
        $context = new ActionContext(['param' => 'value']);
        $options = [
            'service' => 'test_service',
            'method' => 'testMethod',
            'method_parameters' => [new PropertyPath('param')],
            'attribute' => 'test'
        ];
        $this->action->initialize($options);
        $this->action->execute($context);
        $this->assertEquals(
            ['param' => 'value', 'test' => TestService::TEST_METHOD_RESULT . 'value'],
            $context->getValues()
        );
    }

    public function testExecuteWithoutAttribute()
    {
        $this->assertContainerCalled('test_service');
        $context = new ActionContext(['param' => 'value']);
        $options = [
            'service' => 'test_service',
            'method' => 'testMethod',
            'method_parameters' => ['test']
        ];
        $this->action->initialize($options);
        $this->action->execute($context);
        $this->assertEquals(['param' => 'value'], $context->getValues());
    }

    /**
     * @param string $serviceName
     * @param int $hasCalls
     * @param int $getCalls
     */
    protected function assertContainerCalled($serviceName, $hasCalls = 1, $getCalls = 2)
    {
        $this->container->expects($this->exactly($hasCalls))
            ->method('has')
            ->with($serviceName)
            ->willReturn(true);
        $this->container->expects($this->exactly($getCalls))
            ->method('get')
            ->with($serviceName)
            ->willReturn(new TestService());
    }
}
