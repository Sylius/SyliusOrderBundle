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
use Doctrine\Common\Collections\Collection;
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
    /** @var EntityManagerInterface&MockObject */
    private MockObject $managerMock;

    private CartChangesResetter $cartChangesResetter;

    protected function setUp(): void
    {
        $this->managerMock = $this->createMock(EntityManagerInterface::class);
        $this->cartChangesResetter = new CartChangesResetter($this->managerMock);
    }

    public function testDoesNothingIfCartIsNotManaged(): void
    {
        /** @var OrderInterface&MockObject $cartMock */
        $cartMock = $this->createMock(OrderInterface::class);
        $this->managerMock->expects($this->once())->method('contains')->with($cartMock)->willReturn(false);
        $this->managerMock->expects($this->never())->method('refresh')->with($cartMock);
        $this->cartChangesResetter->resetChanges($cartMock);
    }

    public function testResetsChangesForCartItemsAndUnits(): void
    {
        /** @var UnitOfWork&MockObject $unitOfWorkMock */
        $unitOfWorkMock = $this->createMock(UnitOfWork::class);
        /** @var OrderInterface&MockObject $cartMock */
        $cartMock = $this->createMock(OrderInterface::class);
        /** @var OrderItemInterface&MockObject $itemMock */
        $itemMock = $this->createMock(OrderItemInterface::class);
        /** @var OrderItemUnitInterface&MockObject $unitNewMock */
        $unitNewMock = $this->createMock(OrderItemUnitInterface::class);
        /** @var OrderItemUnitInterface&MockObject $unitExistingMock */
        $unitExistingMock = $this->createMock(OrderItemUnitInterface::class);
        /** @var Collection&MockObject $itemsCollectionMock */
        $itemsCollectionMock = $this->createMock(Collection::class);
        $this->managerMock->expects($this->once())->method('contains')->with($cartMock)->willReturn(true);
        $this->managerMock->expects($this->once())->method('getUnitOfWork')->willReturn($unitOfWorkMock);
        $cartMock->expects($this->once())->method('getItems')->willReturn(new ArrayCollection([$itemMock]));
        $itemMock->expects($this->once())->method('getUnits')->willReturn(new ArrayCollection([$unitNewMock, $unitExistingMock]));
        $unitOfWorkMock->expects($this->exactly(2))->method('getEntityState')->willReturnMap([[$unitNewMock, UnitOfWork::STATE_NEW], [$unitExistingMock, UnitOfWork::STATE_MANAGED]]);
        $itemMock->expects($this->once())->method('removeUnit')->with($unitNewMock);
        $this->managerMock->expects($this->once())->method('refresh')->with($itemMock);
        $this->managerMock->expects($this->once())->method('refresh')->with($cartMock);
        $this->cartChangesResetter->resetChanges($cartMock);
    }
}
