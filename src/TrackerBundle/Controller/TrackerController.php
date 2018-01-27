<?php
/**
 * Created by PhpStorm.
 * User: sk1x
 * Date: 20.01.18
 * Time: 16:18
 */

namespace TrackerBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

use TrackerBundle\Entity\Tracker;
use UserBundle\Entity\UserFile;
use TrackerBundle\Form\TrackerType;

class TrackerController extends Controller
{

    /**
     * Displays a form to import csv file.
     *
     */
    public function newAction(Request $request)
    {

        $userfile   = new UserFile();
        $form   = $this->createForm(TrackerType::class, $userfile);
        $form->handleRequest($request);

        if($form->isValid() && $form->isSubmitted()){
            /**
             * @var UploadedFile $file
             */

            $file = $userfile->getFile();

            $user = $this->getUser() ;
            $username = $user->getUsername();
            $filename = $username.'__'.md5(uniqid()).'.csv' ;

            $file->move($this->getParameter('files_directory'),
                $filename);



            $trackers = [];
            $row = 0;

            // Import  CSV
            if (($handle = fopen(__DIR__ . "/../../../web/uploads/".$filename, "r")) !== FALSE) {
                while (($data = fgetcsv($handle,
                        1000,
                        ",")) !== FALSE) {

                    $num = count($data);
                    $row++;
                    for ($c = 0; $c < $num; $c++) {
                        $trackers[$row] = array(
                            "Time" => $data[0],
                            "Action" => $data[1]
                        );
                    }
                }
                fclose($handle);
            }


            $em = $this->getDoctrine()->getManager() ;

            foreach($trackers as $tracker){

                $action = $tracker['Action'] ;

                $date = \DateTime::createFromFormat('Y-m-d H:i:s',$tracker['Time']);

                $user = $this->getUser();  // here we have our user

                $project = $em->getRepository(User::class)->findOneBy(array('id' => $user))->getProject();


                $tracker = new Tracker();
                $tracker->setUser($user);
                $tracker->setAction($action);
                $tracker->setTime($date);
                $tracker->setProject($project);
                $em->persist($tracker);



            }

            $em->flush();

            $request->getSession()
                ->getFlashBag()
                ->add('success', 'Your file was successfully loaded!')
            ;



        }
        return $this->render('TrackerBundle:Default:add_csv.html.twig', array(
            'form'   => $form->createView(),
        ));
    }


    /**
     * Creates a new Tracker entity.
     *
     */
    public function createAction(Request $request)
    {

    }

    /**
     * Loads a Tracker entity by its ID from database
     *
     * @param  integer $id
     * @return Tracker
     * @throws NotFoundException If there is not entity with this ID
     */
    private function findTrackerById ($id)
    {

    }

}