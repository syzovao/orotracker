<?php

namespace Oro\Bundle\IssueBundle\Migrations\Data\ORM;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadRolesData;
use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;

class LoadUserData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    const DEFAULT_USER_USERNAME = 'user';
    const DEFAULT_USER_EMAIL = 'user@example.com';
    const DEFAULT_USER_FIRSTNAME = 'User';

    const DEFAULT_MANAGER_USERNAME = 'manager';
    const DEFAULT_MANAGER_EMAIL = 'manager@example.com';
    const DEFAULT_MANAGER_FIRSTNAME = 'Manager';

    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadRolesData',
            'Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->userManager = $container->get('oro_user.manager');
    }

    /**
     * Load default administrator
     *
     * @param ObjectManager $manager
     * @throws \RuntimeException
     */
    public function load(ObjectManager $manager)
    {
        $userRole = $manager->getRepository('OroUserBundle:Role')
            ->findOneBy(['role' => LoadRolesData::ROLE_USER]);
        if (!$userRole) {
            throw new \RuntimeException('User role should exist.');
        }
        if ($this->isUserWithRoleExist($manager, $userRole)) {
            return;
        }

        $businessUnit = $manager
            ->getRepository('OroOrganizationBundle:BusinessUnit')
            ->findOneBy(['name' => LoadOrganizationAndBusinessUnitData::MAIN_BUSINESS_UNIT]);
        $organization = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();

        $user = $this->userManager->createUser();
        $user
            ->setUsername(self::DEFAULT_USER_USERNAME)
            ->setFirstName(self::DEFAULT_USER_FIRSTNAME)
            ->setLastName(self::DEFAULT_USER_FIRSTNAME)
            ->setEmail(self::DEFAULT_USER_EMAIL)
            ->setEnabled(true)
            ->setOwner($businessUnit)
            ->setPlainPassword(md5(uniqid(mt_rand(), true)))
            ->addRole($userRole)
            ->addBusinessUnit($businessUnit)
            ->setOrganization($organization)
            ->addOrganization($organization);
        $this->userManager->updateUser($user);

        $managerRole = $manager->getRepository('OroUserBundle:Role')
            ->findOneBy(['role' => LoadRolesData::ROLE_MANAGER]);
        if (!$managerRole) {
            throw new \RuntimeException('Manager role should exist.');
        }
        if ($this->isUserWithRoleExist($manager, $managerRole)) {
            return;
        }

        $managerUser = $this->userManager->createUser();
        $managerUser
            ->setUsername(self::DEFAULT_MANAGER_USERNAME)
            ->setFirstName(self::DEFAULT_MANAGER_FIRSTNAME)
            ->setLastName(self::DEFAULT_MANAGER_FIRSTNAME)
            ->setEmail(self::DEFAULT_MANAGER_EMAIL)
            ->setEnabled(true)
            ->setOwner($businessUnit)
            ->setPlainPassword(md5(uniqid(mt_rand(), true)))
            ->addRole($managerRole)
            ->addBusinessUnit($businessUnit)
            ->setOrganization($organization)
            ->addOrganization($organization);
        $this->userManager->updateUser($managerUser);
    }

    /**
     * @param ObjectManager $manager
     * @param Role $role
     * @return bool
     */
    protected function isUserWithRoleExist(ObjectManager $manager, Role $role)
    {
        return null !== $manager->getRepository('OroUserBundle:Role')->getFirstMatchedUser($role);
    }
}
