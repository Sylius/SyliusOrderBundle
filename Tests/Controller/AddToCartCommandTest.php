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

namespace Tests\Sylius\Bundle\OrderBundle\Controller;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Bundle\OrderBundle\Controller\AddToCartCommand;
use Sylius\Bundle\OrderBundle\Controller\AddToCartCommandInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\Model\OrderItemInterface;

final class AddToCartCommandTest extends TestCase
{
    /** @var OrderInterface&MockObject */
    private MockObject $orderMock;

    /** @var OrderItemInterface&MockObject */
    private MockObject $orderItemMock;

    private AddToCartCommand $addToCartCommand;

    protected function setUp(): void
    {
        $this->orderMock = $this->createMock(OrderInterface::class);
        $this->orderItemMock = $this->createMock(OrderItemInterface::class);
        $this->addToCartCommand = new AddToCartCommand($this->orderMock, $this->orderItemMock);
    }

    public function testAddCartItemCommand(): void
    {
        $this->assertInstanceOf(AddToCartCommandInterface::class, $this->addToCartCommand);
    }

    public function testHasOrder(): void
    {
        $this->assertSame($this->orderMock, $this->addToCartCommand->getCart());
    }

    public function testHasOrderItem(): void
    {
        $this->assertSame($this->orderItemMock, $this->addToCartCommand->getCartItem());
    }
}
