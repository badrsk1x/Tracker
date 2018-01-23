<?php
/**
 * Created by PhpStorm.
 * User: sk1x
 * Date: 23.01.18
 * Time: 18:40
 */

namespace TrackerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class TrackerType extends AbstractType {


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('file', FileType::class, array('label'=>'insert your CSV'))
                ->add('save',SubmitType::class);
    }

    public function getName()
    {
        return 'Upload_CSV';
    }

}