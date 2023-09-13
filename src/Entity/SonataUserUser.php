<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Sonata\UserBundle\Entity\BaseUser;
use Symfony\Component\Security\Core\User\UserInterface;
use function in_array;

#[ORM\Entity]
#[ORM\Table(name: 'user__user')]
class SonataUserUser extends BaseUser
{
    private const ROLE_ADMIN = 'ROLE_ADMIN';

    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue]
    protected $id;

    public function isEqualTo(UserInterface $user): bool
    {
        if (parent::isEqualTo($user) === false) {
            return false;
        }

        if (! $user instanceof self) {
            return false;
        }

        if ($user->isEnabled() === false) {
            return false;
        }

        if ($this->getRealRoles() !== $user->getRealRoles()) {
            return false;
        }

        return true;
    }

    public function __serialize(): array
    {
        $data = parent::__serialize();
        $data['roles'] = $this->roles;

        return $data;
    }

    public function __unserialize(array $data): void
    {
        parent::__unserialize($data);
        $this->roles = $data['roles'];
    }

    public function addRole(string $role): void
    {
        $role = strtoupper($role);
        if ($role === self::ROLE_ADMIN) {
            return;
        }

        parent::addRole($role);
    }

    public function getRoles(): array
    {
        $roles = parent::getRoles();
        $roles[] = self::ROLE_ADMIN;

        return array_values(array_unique($roles));
    }
}
