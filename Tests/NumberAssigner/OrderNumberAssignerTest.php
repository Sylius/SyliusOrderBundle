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

namespace Tests\Sylius\Bundle\OrderBundle\NumberAssigner;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Bundle\OrderBundle\NumberAssigner\OrderNumberAssigner;
use Sylius\Bundle\OrderBundle\NumberAssigner\OrderNumberAssignerInterface;
use Sylius\Bundle\OrderBundle\NumberGenerator\OrderNumberGeneratorInterface;
use Sylius\Component\Order\Model\OrderInterface;

final class OrderNumberAssignerTest extends TestCase
{
    /** @var OrderNumberGeneratorInterface&MockObject */
    private MockObject $numberGeneratorMock;

    private OrderNumberAssigner $orderNumberAssigner;

    protected function setUp(): void
    {
        $this->numberGeneratorMock = $this->createMock(OrderNumberGeneratorInterface::class);
        $this->orderNumberAssigner = new OrderNumberAssigner($this->numberGeneratorMock);
    }

    public function testImplementsAnOrderNumberAssignerInterface(): void
    {
        $this->assertInstanceOf(OrderNumberAssignerInterface::class, $this->orderNumberAssigner);
    }

    public function testAssignsANumberToAnOrder(): void
    {
        /** @var OrderInterface&MockObject $orderMock */
        $orderMock = $this->createMock(OrderInterface::class);
        $orderMock->expects($this->once())->method('getNumber')->willReturn(null);
        $this->numberGeneratorMock->expects($this->once())->method('generate')->with($orderMock)->willReturn('00000007');
        $orderMock->expects($this->once())->method('setNumber')->with('00000007');
        $this->orderNumberAssigner->assignNumber($orderMock);
    }

    public function testDoesNotAssignANumberToAnOrderWithNumber(): void
    {
        /** @var OrderInterface&MockObject $orderMock */
        $orderMock = $this->createMock(OrderInterface::class);
        $orderMock->expects($this->once())->method('getNumber')->willReturn('00000007');
        $this->numberGeneratorMock->expects($this->never())->method('generate')->with($orderMock);
        $orderMock->expects($this->never())->method('setNumber');
        $this->orderNumberAssigner->assignNumber($orderMock);
    }
}
