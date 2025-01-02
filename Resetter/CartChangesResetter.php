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

namespace Sylius\Bundle\OrderBundle\Resetter;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Sylius\Component\Order\Model\OrderInterface;

final class CartChangesResetter implements CartChangesResetterInterface
{
    public function __construct(private readonly EntityManagerInterface $manager)
    {
    }

    public function resetChanges(OrderInterface $cart): void
    {
        if (!$this->manager->contains($cart)) {
            return;
        }

        $uow = $this->manager->getUnitOfWork();

        foreach ($cart->getItems() as $item) {
            foreach ($item->getUnits() as $unit) {
                if ($uow->getEntityState($unit) === UnitOfWork::STATE_NEW) {
                    $item->removeUnit($unit);
                }
            }
            $this->manager->refresh($item);
        }
        $this->manager->refresh($cart);
    }
}
