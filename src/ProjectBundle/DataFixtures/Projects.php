<?php
/**
 * Created by PhpStorm.
 * User: sk1x
 * Date: 22.01.18
 * Time: 12:44
 */

namespace ProjectBundle\DataFixtures;

use ProjectBundle\Entity\Project;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class Projects extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        // create 5 projects!
        for ($i = 0; $i < 5; $i++) {
            $project = new Project();
            $project->setName('project '.$i);
            $project->setActive($project::ACTIVE_PROJECT);

            $this->addReference('project_ref'.$i, $project) ;
            $manager->persist($project);
        }

        $manager->flush();

    }

    public  function getOrder(){
        return 1 ;
    }
}