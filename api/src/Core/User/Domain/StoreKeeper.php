<?php

declare(strict_types=1);

namespace MiniPay\Core\User\Domain;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class StoreKeeper extends User
{
    protected const USER_TYPE = 'storekeeper';
}
