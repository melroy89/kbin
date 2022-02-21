<?php declare(strict_types = 1);

namespace App\Twig\Runtime;

use App\Entity\Magazine;
use Symfony\Component\Security\Core\Security;
use Twig\Extension\RuntimeExtensionInterface;

class MagazineRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private Security $security,
    ) {
    }

    public function isSubscribed(Magazine $magazine): bool
    {
        if (!$this->security->getUser()) {
            return false;
        }

        return $magazine->isSubscribed($this->security->getUser());
    }

    public function isBlocked(Magazine $magazine): bool
    {
        if (!$this->security->getUser()) {
            return false;
        }

        return $this->security->getUser()->isBlockedMagazine($magazine);
    }
}
