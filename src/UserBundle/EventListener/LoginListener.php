<?php
/**
 * Created by PhpStorm.
 * User: sk1x
 * Date: 20.01.18
 * Time: 17:10
 */

namespace UserBundle\EventListener;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;


use TrackerBundle\Entity\Tracker ;
use ProjectBundle\Entity\Project ;
use UserBundle\Entity\User ;


class LoginListener
{
    /** @var Router */
    protected $router;

    /** @var TokenStorage */
    protected $token;

    /** @var EventDispatcherInterface */
    protected $dispatcher;

    protected $em;

    /**
     * @param Router $router
     * @param TokenStorage $token
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(Router $router, TokenStorage $token, EventDispatcherInterface $dispatcher, EntityManager $entityManager)
    {
        $this->router = $router;
        $this->token = $token;
        $this->dispatcher = $dispatcher;
        $this->em = $entityManager ;
    }

    /**
     * @param InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $this->dispatcher->addListener(KernelEvents::RESPONSE,
            [$this, 'onKernelResponse']);
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {

        $roles = $this->token->getToken()->getRoles() ;
        // Tranform this list in array
        $rolesTab = array_map(function($role){
            return $role->getRole();
        }, $roles);

        // If is a not admin we do not log time entry
        if (!in_array('ROLE_ADMIN', $rolesTab, true)) {

            $user = $this->token->getToken()->getUser(); // here we have our user

            $project = $this->em->getRepository("UserBundle:User")->findOneBy(array('id' => $user))->getProject();


            $tracker = new Tracker();
            $tracker->setUser($user);
            $tracker->setAction('login');
            $tracker->setProject($project);
            $this->em->persist($tracker);

            $this->em->flush();
        }

    }
}