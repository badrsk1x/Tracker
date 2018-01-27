<?php
/**
 * Created by PhpStorm.
 * User: sk1x
 * Date: 28.01.18
 * Time: 1:34
 */
namespace ProjectBundle\Service;

use Doctrine\ORM\EntityManager;
use UserBundle\Entity\User ;

class SpentTime{

    protected $em;
    protected $userManager;

    public function __construct($em, $userManager) {
        $this->em = $em;
        $this->userManager = $userManager;
    }

    public function CountTime($result){
     $spent_time = 0 ;

        foreach($result as $key=>$res) {

            $user = $this->userManager->findUserBy(array('id' => $res['user_id'] ));

            $result[$key]['user_name'] = $user->getUsername() ;

            $d1= new \DateTime($res['start']);
            $d2= new \DateTime($res['end']);
            $spent_time += $d2->getTimestamp()-$d1->getTimestamp();

        }

        return $spent_time;

    }
}