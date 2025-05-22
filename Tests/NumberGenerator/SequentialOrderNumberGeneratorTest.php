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

namespace Tests\Sylius\Bundle\OrderBundle\NumberGenerator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Bundle\OrderBundle\NumberGenerator\OrderNumberGeneratorInterface;
use Sylius\Bundle\OrderBundle\NumberGenerator\SequentialOrderNumberGenerator;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\Model\OrderSequenceInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Resource\Factory\FactoryInterface;

final class SequentialOrderNumberGeneratorTest extends TestCase
{
    /** @var RepositoryInterface&MockObject */
    private MockObject $sequenceRepositoryMock;

    /** @var FactoryInterface&MockObject */
    private MockObject $sequenceFactoryMock;

    private SequentialOrderNumberGenerator $sequentialOrderNumberGenerator;

    protected function setUp(): void
    {
        $this->sequenceRepositoryMock = $this->createMock(RepositoryInterface::class);
        $this->sequenceFactoryMock = $this->createMock(FactoryInterface::class);
        $this->sequentialOrderNumberGenerator = new SequentialOrderNumberGenerator($this->sequenceRepositoryMock, $this->sequenceFactoryMock);
    }

    public function testImplementsAnOrderNumberGeneratorInterface(): void
    {
        $this->assertInstanceOf(OrderNumberGeneratorInterface::class, $this->sequentialOrderNumberGenerator);
    }

    public function testGeneratesAnOrderNumber(): void
    {
        /** @var OrderSequenceInterface&MockObject $sequenceMock */
        $sequenceMock = $this->createMock(OrderSequenceInterface::class);
        /** @var OrderInterface&MockObject $orderMock */
        $orderMock = $this->createMock(OrderInterface::class);
        $sequenceMock->expects($this->once())->method('getIndex')->willReturn(6);
        $this->sequenceRepositoryMock->expects($this->once())->method('findOneBy')->with([])->willReturn($sequenceMock);
        $sequenceMock->expects($this->once())->method('incrementIndex');
        $this->assertSame('000000007', $this->sequentialOrderNumberGenerator->generate($orderMock));
    }

    public function testGeneratesAnOrderNumberWhenSequenceIsNull(): void
    {
        /** @var OrderSequenceInterface&MockObject $sequenceMock */
        $sequenceMock = $this->createMock(OrderSequenceInterface::class);
        /** @var OrderInterface&MockObject $orderMock */
        $orderMock = $this->createMock(OrderInterface::class);
        $sequenceMock->expects($this->once())->method('getIndex')->willReturn(0);
        $this->sequenceRepositoryMock->expects($this->once())->method('findOneBy')->with([])->willReturn(null);
        $this->sequenceFactoryMock->expects($this->once())->method('createNew')->willReturn($sequenceMock);
        $this->sequenceRepositoryMock->expects($this->once())->method('add')->with($sequenceMock);
        $sequenceMock->expects($this->once())->method('incrementIndex');
        $this->assertSame('000000001', $this->sequentialOrderNumberGenerator->generate($orderMock));
    }
}
