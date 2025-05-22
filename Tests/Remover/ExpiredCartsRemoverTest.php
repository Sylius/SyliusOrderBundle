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

use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Bundle\OrderBundle\Remover\ExpiredCartsRemover;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\Remover\ExpiredCartsRemoverInterface;
use Sylius\Component\Order\Repository\OrderRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class ExpiredCartsRemoverTest extends TestCase
{
    /** @var OrderRepositoryInterface&MockObject */
    private OrderRepositoryInterface $orderRepository;

    /** @var ObjectManager&MockObject */
    private ObjectManager $objectManager;

    /** @var EventDispatcher&MockObject */
    private EventDispatcher $eventDispatcher;

    private ExpiredCartsRemover $expiredCartsRemover;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->objectManager = $this->createMock(ObjectManager::class);
        $this->eventDispatcher = $this->createMock(EventDispatcher::class);
        $this->expiredCartsRemover = new ExpiredCartsRemover(
            $this->orderRepository,
            $this->objectManager,
            $this->eventDispatcher,
            '2 months',
        );
    }

    public function testImplementsAnExpiredCartsRemoverInterface(): void
    {
        self::assertInstanceOf(ExpiredCartsRemoverInterface::class, $this->expiredCartsRemover);
    }

    public function testRemovesACartWhichHasBeenUpdatedBeforeConfiguredDate(): void
    {
        $cart1 = $this->createMock(OrderInterface::class);
        $cart2 = $this->createMock(OrderInterface::class);

        $this->orderRepository->expects(self::once())
            ->method('findCartsNotModifiedSince')
            ->with($this->isInstanceOf('DateTimeInterface'), 100)
            ->willReturn([$cart1, $cart2]);

        $removedCarts = [];

        $this->objectManager->expects(self::exactly(2))
            ->method('remove')
            ->with($this->callback(function ($cart) use ($cart1, $cart2, &$removedCarts) {
                $removedCarts[] = $cart;

                return in_array($cart, [$cart1, $cart2], true);
            }));

        $this->objectManager->expects(self::once())->method('flush');
        $this->objectManager->expects(self::once())->method('clear');

        $this->expiredCartsRemover->remove();

        self::assertSame([$cart1, $cart2], $removedCarts);
    }

    public function testRemovesCartsInBatches(): void
    {
        /** @var OrderInterface&MockObject $cart */
        $cart = $this->createMock(OrderInterface::class);

        $this->orderRepository->expects(self::once())
            ->method('findCartsNotModifiedSince')
            ->with($this->isInstanceOf('DateTimeInterface'), 100)
            ->willReturn(
                array_fill(0, 100, $cart),
                array_fill(0, 100, $cart),
                [],
            )
        ;
        $this->eventDispatcher->expects(self::exactly(2))->method('dispatch');

        $this->objectManager->expects(self::exactly(200))
            ->method('remove')
            ->with($this->isInstanceOf(OrderInterface::class));

        $this->objectManager->expects(self::exactly(2))->method('flush');

        $this->objectManager->expects(self::exactly(2))->method('clear');

        $this->eventDispatcher->expects(self::exactly(2))->method('dispatch');

        $this->expiredCartsRemover->remove();
    }
}
