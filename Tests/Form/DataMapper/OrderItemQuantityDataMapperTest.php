<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\Sylius\Bundle\OrderBundle\Form\DataMapper;

use ArrayIterator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Bundle\OrderBundle\Form\DataMapper\OrderItemQuantityDataMapper;
use Sylius\Component\Order\Model\OrderItemInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormInterface;

final class OrderItemQuantityDataMapperTest extends TestCase
{
    /** @var OrderItemQuantityModifierInterface&MockObject */
    private MockObject $orderItemQuantityModifierMock;

    /** @var DataMapperInterface&MockObject */
    private MockObject $propertyPathDataMapperMock;

    private OrderItemQuantityDataMapper $orderItemQuantityDataMapper;

    protected function setUp(): void
    {
        $this->orderItemQuantityModifierMock = $this->createMock(OrderItemQuantityModifierInterface::class);
        $this->propertyPathDataMapperMock = $this->createMock(DataMapperInterface::class);
        $this->orderItemQuantityDataMapper = new OrderItemQuantityDataMapper($this->orderItemQuantityModifierMock, $this->propertyPathDataMapperMock);
    }

    public function testImplementsADataMapperInterface(): void
    {
        $this->assertInstanceOf(DataMapperInterface::class, $this->orderItemQuantityDataMapper);
    }

    public function testUsesAPropertyPathDataMapperWhileMappingDataToForms(): void
    {
        /** @var FormInterface&MockObject $formMock */
        $formMock = $this->createMock(FormInterface::class);
        /** @var OrderItemInterface&MockObject $orderItemMock */
        $orderItemMock = $this->createMock(OrderItemInterface::class);
        $forms = new ArrayIterator([$formMock]);
        $this->propertyPathDataMapperMock->expects($this->once())->method('mapDataToForms')->with($orderItemMock, $forms);
        $this->orderItemQuantityDataMapper->mapDataToForms($orderItemMock, $forms);
    }
}
