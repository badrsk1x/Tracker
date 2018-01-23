<?php
/**
 * Created by PhpStorm.
 * User: sk1x
 * Date: 20.01.18
 * Time: 17:10
 */

namespace UserBundle\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

use Doctrine\ORM\EntityManager;
use TrackerBundle\Entity\Tracker ;
use ProjectBundle\Entity\Project ;
use UserBundle\Entity\User ;

class LogoutListener implements LogoutHandlerInterface {

    protected $em;

    // We need to inject this variables later.
    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function logout(Request $Request, Response $Response, TokenInterface $Token) {

        $roles = $Token->getRoles() ;
        // Tranform this list in array
        $rolesTab = array_map(function($role){
            return $role->getRole();
        }, $roles);

        // If is a not admin we do not log time exit
        if (!in_array('ROLE_ADMIN', $rolesTab, true)) {

            $user = $Token->getUser();
            $em = $this->em;

            $project = $this->em->getRepository("UserBundle:User")->findOneBy(array('id' => $user))->getProject();


            $tracker = new Tracker();
            $tracker->setUser($user);
            $tracker->setAction('logout');
            $tracker->setProject($project);
            $em->persist($tracker);

            $em->flush();
        }
    }
}