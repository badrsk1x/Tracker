<?php

namespace ProjectBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\DateTime;
use Doctrine\ORM\EntityManager;

use ProjectBundle\Entity\Project ;

class ProjectController extends Controller
{
    /**
     * @Route("/")
     */

    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $projects = $em->getRepository(Project::class);
        if ( $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $projects = $projects->findAll();
        } else{
            $RAW_QUERY = 'SELECT distinct(project_id), b.id, b.name from `time_tracker` a left join project b on a.project_id=b.id where  
user_id='.$this->getUser()->getId()  ;


            $statement = $em->getConnection()->prepare($RAW_QUERY);
            $statement->execute();

            $projects = $statement->fetchAll();
        }


        return $this->render('ProjectBundle:Default:index.html.twig', ['projects' => $projects]);

    }

    /**
     * @Route("/{id}")
     * @Method("GET")
     * @param $id
     */
    public function getAction($id) {

        // if project id is not number show warning message
        if(!is_numeric($id)):
            $this->addFlash("warning", "This is a warning message : You have did sthg wrong");
            return $this->indexAction() ;
         endif ;


        $em = $this->getDoctrine()->getManager();
        $project = $em->getRepository(Project::class)
                   ->findOneBy(['id' => $id]) ;

        // if project id do not exist

        if($project==null):
            $this->addFlash("warning", "This is a warning message : This project do not exist");
            return $this->indexAction() ;
        endif ;

        $userManager = $this->get('fos_user.user_manager');

        $admin_view = null ;

        if ( !$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
         $admin_view = 'and a.user_id='.$this->getUser()->getId() ;
        }

        $RAW_QUERY = 'SELECT a.project_id , a.user_id, a.id , a.time as start , b.time as end , b.id FROM `time_tracker` a inner join 
`time_tracker` b on a.user_id=b.user_id where a.action=\'login\' and b.action=\'logout\'  and b.id=( select c.id from
 time_tracker c where c.user_id = a.user_id and c.id > a.id and c.action=\'logout\' order by c.id asc limit 1) and a
 .project_id='.$project->getId().' '.$admin_view  ;

        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();

        $result = $statement->fetchAll();



        $spent_time = 0 ;

        foreach($result as $key=>$res) {

            $user = $userManager->findUserBy(array('id' => $res['user_id'] ));

            $result[$key]['user_name'] = $user->getUsername() ;

            $d1= new \DateTime($res['start']);
            $d2= new \DateTime($res['end']);
            $spent_time += $d2->getTimestamp()-$d1->getTimestamp();

        }


        return $this->render('ProjectBundle:Default:project_view.html.twig',
            [
                'project' => $project,
                'results' => $result,
                'SpentTime'=> gmdate("H\h :i\m :s\s", $spent_time)
            ]);
    }

    public function peaktimepageAction() {

       $projects =  $this->getDoctrine()->getManager()->getRepository(Project::class)
            ->findAll();

        return $this->render('ProjectBundle:Default:peaktimehome.html.twig',
            [
                'projects' => $projects
            ]);

    }


    public function peaktimeAction(Request $request){

        $project = $request->get('project');
        $day     = $request->get('day');

        if($project==null or $day==null):
            $this->addFlash("warning", "This is a warning message : You did not choose a project or day");
            return $this->peaktimepageAction() ;
        endif ;

        $em = $this->getDoctrine()->getManager();

        $RAW_QUERY = "select MAX(start) as peak_start, MIN(end) as peak_end from (SELECT a.time as start , b.time as end , b.id FROM 
`time_tracker` a inner join 
`time_tracker` b on a.user_id=b.user_id where a.action='login' and b.action='logout' and b.id=( select c.id from 
time_tracker c where c.user_id = a.user_id and c.id > a.id and c.action='logout' order by c.id asc limit 1 ) and a
.project_id=$project ) peaktime where start BETWEEN '$day 00:00:00' AND '$day 23:59:59' "  ;

        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();

        $result = $statement->fetch();


        return $this->render('ProjectBundle:Default:peaktime.html.twig',
            [
                'day' => $day,
                'project' => $project,
                'results' => $result,
            ]) ;

    }

}
