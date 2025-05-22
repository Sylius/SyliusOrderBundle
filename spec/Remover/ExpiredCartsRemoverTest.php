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

namespace Tests\Sylius\Bundle\OrderBundle\Remover;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Sylius\Bundle\OrderBundle\Remover\ExpiredCartsRemover;
use Doctrine\Persistence\ObjectManager;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\Remover\ExpiredCartsRemoverInterface;
use Sylius\Component\Order\Repository\OrderRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class ExpiredCartsRemoverTest extends TestCase
{
    /**
     * @var OrderRepositoryInterface|MockObject
     */
    private MockObject $orderRepositoryMock;
    /**
     * @var ObjectManager|MockObject
     */
    private MockObject $orderManagerMock;
    /**
     * @var EventDispatcher|MockObject
     */
    private MockObject $eventDispatcherMock;
    private ExpiredCartsRemover $expiredCartsRemover;
    protected function setUp(): void
    {
        $this->orderRepositoryMock = $this->createMock(OrderRepositoryInterface::class);
        $this->orderManagerMock = $this->createMock(ObjectManager::class);
        $this->eventDispatcherMock = $this->createMock(EventDispatcher::class);
        $this->expiredCartsRemover = new ExpiredCartsRemover($this->orderRepositoryMock, $this->orderManagerMock, $this->eventDispatcherMock, '2 months');
    }

    public function testImplementsAnExpiredCartsRemoverInterface(): void
    {
        $this->assertInstanceOf(ExpiredCartsRemoverInterface::class, $this->expiredCartsRemover);
    }

    public function testRemovesACartWhichHasBeenUpdatedBeforeConfiguredDate(): void
    {
        /** @var OrderInterface|MockObject $firstCartMock */
        $firstCartMock = $this->createMock(OrderInterface::class);
        /** @var OrderInterface|MockObject $secondCartMock */
        $secondCartMock = $this->createMock(OrderInterface::class);
        $this->orderRepositoryMock->expects($this->once())->method('findCartsNotModifiedSince')->with($this->isType('\DateTimeInterface'), 100)->willReturn(
            [$firstCartMock, $secondCartMock],
            [],
        );
        $this->eventDispatcherMock->expects($this->once())->method('dispatch')
        ;
        $this->orderManagerMock->expects($this->once())->method('remove')->with($firstCartMock)->shouldBeCalledOnce();
        $this->orderManagerMock->expects($this->once())->method('remove')->with($secondCartMock)->shouldBeCalledOnce();
        $this->orderManagerMock->expects($this->once())->method('flush')->shouldBeCalledOnce();
        $this->orderManagerMock->expects($this->once())->method('clear')->shouldBeCalledOnce();
        $this->eventDispatcherMock->expects($this->once())->method('dispatch')
        ;
        $this->expiredCartsRemover->remove();
    }

    public function testRemovesCartsInBatches(): void
    {
        /** @var OrderInterface|MockObject $cartMock */
        $cartMock = $this->createMock(OrderInterface::class);
        $this->orderRepositoryMock->expects($this->once())->method('findCartsNotModifiedSince')->with($this->isType('\DateTimeInterface'), 100)
            ->willReturn(
                array_fill(0, 100, $cartMock),
                array_fill(0, 100, $cartMock),
                [],
            )
        ;
        $this->eventDispatcherMock->expects($this->once())->method('dispatch')
            ->shouldBeCalledTimes(2)
        ;
        $this->orderManagerMock->expects($this->once())->method('remove')->with($this->isInstanceOf(OrderInterface::class))->shouldBeCalledTimes(200);
        $this->orderManagerMock->expects($this->once())->method('flush')->shouldBeCalledTimes(2);
        $this->orderManagerMock->expects($this->once())->method('clear')->shouldBeCalledTimes(2);
        $this->eventDispatcherMock->expects($this->once())->method('dispatch')
            ->shouldBeCalledTimes(2)
        ;
        $this->expiredCartsRemover->remove();
    }
}
