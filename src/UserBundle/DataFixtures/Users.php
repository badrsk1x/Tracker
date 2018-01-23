<?php
/**
 * Created by PhpStorm.
 * User: sk1x
 * Date: 22.01.18
 * Time: 12:51
 */

namespace UserBundle\DataFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


use ProjectBundle\Entity\Project ;
use UserBundle\Entity\User ;


class Users extends Fixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{

    /**
     * @var ContainerInterface
     */
    private $container;

    private $objectManager;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {

        // Get our userManager,
        $userManager = $this->container->get('fos_user.user_manager');


        // Create our user and set details

        $user = $userManager->createUser();
        $user->setUsername('admin');
        $user->setEmail('admin@domain.com');
        $user->setPlainPassword('123');
        $user->setEnabled(true);
        $user->setRoles(array('ROLE_ADMIN'));



        // Update the user
        $userManager->updateUser($user,
            true);


        for ($i = 0; $i < 10; $i++) {
            $user = $userManager->createUser();
            $user->setUsername('user' . $i);
            $user->setEmail('email' . $i . '@domain.com');
            $user->setPlainPassword('123');
            $user->setEnabled(true);
            $user->setRoles(array('ROLE_USER'));

            $user->setProject($this->getReference('project_ref'.mt_rand(1,4))) ;

            $userManager->updateUser($user);

        }
    }

        public  function getOrder(){
        return 2 ;
    }



}
