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

namespace Tests\Sylius\Bundle\OrderBundle\Resetter;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Bundle\OrderBundle\Resetter\CartChangesResetter;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\Model\OrderItemInterface;
use Sylius\Component\Order\Model\OrderItemUnitInterface;

final class CartChangesResetterTest extends TestCase
{
    private EntityManagerInterface&MockObject $managerMock;

    private CartChangesResetter $cartChangesResetter;

    private OrderInterface&MockObject $cart;

    protected function setUp(): void
    {
        parent::setUp();
        $this->managerMock = $this->createMock(EntityManagerInterface::class);
        $this->cartChangesResetter = new CartChangesResetter($this->managerMock);
        $this->cart = $this->createMock(OrderInterface::class);
    }

    public function testDoesNothingIfCartIsNotManaged(): void
    {
        $this->managerMock->expects(self::once())
            ->method('contains')
            ->with($this->cart)
            ->willReturn(false);

        $this->managerMock->expects(self::never())->method('refresh')->with($this->cart);

        $this->cartChangesResetter->resetChanges($this->cart);
    }

    public function testResetsChangesForCartItemsAndUnits(): void
    {
        $cart = $this->createMock(OrderInterface::class);
        $item = $this->createMock(OrderItemInterface::class);
        $unitNew = $this->createMock(OrderItemUnitInterface::class);
        $unitExisting = $this->createMock(OrderItemUnitInterface::class);
        $unitOfWork = $this->createMock(UnitOfWork::class);

        $this->managerMock
            ->method('contains')
            ->with($cart)
            ->willReturn(true);

        $this->managerMock
            ->method('getUnitOfWork')
            ->willReturn($unitOfWork);

        $cart
            ->method('getItems')
            ->willReturn(new ArrayCollection([$item]));

        $item
            ->method('getUnits')
            ->willReturn(new ArrayCollection([$unitNew, $unitExisting]));

        $unitOfWork
            ->method('getEntityState')
            ->willReturnMap([
                [$unitNew, UnitOfWork::STATE_NEW],
                [$unitExisting, UnitOfWork::STATE_MANAGED],
            ]);

        $item
            ->expects(self::once())
            ->method('removeUnit')
            ->with($unitNew);

        $this->managerMock
            ->expects(self::exactly(2))
            ->method('refresh')
            ->with($this->callback(function ($object) use ($item, $cart) {
                return $object === $item || $object === $cart;
            }));

        $this->cartChangesResetter->resetChanges($cart);
    }
}
