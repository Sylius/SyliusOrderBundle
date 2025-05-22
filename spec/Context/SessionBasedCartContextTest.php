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

namespace Tests\Sylius\Bundle\OrderBundle\Context;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Sylius\Bundle\OrderBundle\Context\SessionBasedCartContext;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Order\Context\CartNotFoundException;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\Repository\OrderRepositoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class SessionBasedCartContextTest extends TestCase
{
    /**
     * @var SessionInterface|MockObject
     */
    private MockObject $sessionMock;
    /**
     * @var OrderRepositoryInterface|MockObject
     */
    private MockObject $orderRepositoryMock;
    private SessionBasedCartContext $sessionBasedCartContext;
    protected function setUp(): void
    {
        $this->sessionMock = $this->createMock(SessionInterface::class);
        $this->orderRepositoryMock = $this->createMock(OrderRepositoryInterface::class);
        $this->sessionBasedCartContext = new SessionBasedCartContext($this->sessionMock, 'session_key_name', $this->orderRepositoryMock);
    }

    public function testImplementsACartContextInterface(): void
    {
        $this->assertInstanceOf(CartContextInterface::class, $this->sessionBasedCartContext);
    }

    public function testReturnsACartBasedOnIdStoredInSession(): void
    {
        /** @var OrderInterface|MockObject $cartMock */
        $cartMock = $this->createMock(OrderInterface::class);
        $this->sessionMock->expects($this->once())->method('has')->with('session_key_name')->willReturn(true);
        $this->sessionMock->expects($this->once())->method('get')->with('session_key_name')->willReturn(12345);
        $this->orderRepositoryMock->expects($this->once())->method('findCartById')->with(12345)->willReturn($cartMock);
        $this->assertSame($cartMock, $this->sessionBasedCartContext->getCart());
    }

    public function testThrowsACartNotFoundExceptionIfSessionKeyDoesNotExist(): void
    {
        $this->sessionMock->expects($this->once())->method('has')->with('session_key_name')->willReturn(false);
        $this->expectException(CartNotFoundException::class);
        $this->sessionBasedCartContext->getCart();
    }

    public function testThrowsACartNotFoundExceptionAndRemovesIdFromSessionWhenCartIsNotFound(): void
    {
        $this->sessionMock->expects($this->once())->method('has')->with('session_key_name')->willReturn(true);
        $this->sessionMock->expects($this->once())->method('get')->with('session_key_name')->willReturn(12345);
        $this->orderRepositoryMock->expects($this->once())->method('findCartById')->with(12345)->willReturn(null);
        $this->sessionMock->expects($this->once())->method('remove')->with('session_key_name')->willReturn(null);
        $this->expectException(CartNotFoundException::class);
        $this->sessionBasedCartContext->getCart();
    }
}
