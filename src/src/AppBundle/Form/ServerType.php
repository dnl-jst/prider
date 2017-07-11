<?php

namespace AppBundle\Form;

use AppBundle\Entity\Group;
use AppBundle\Entity\Topic;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServerType extends AbstractType
{

    public function getName()
    {
        return 'ServerType';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction($options['action'])
            ->setMethod($options['method'])
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'Name:'
            ])
            ->add('hostname', TextType::class, [
                'required' => true,
                'label' => 'Hostname oder IP:'
            ])
            ->add('sshUser', TextType::class, [
                'required' => true,
                'label' => 'SSH-Benutzername:'
            ])
            ->add('sshPassword', TextType::class, [
                'required' => true,
                'label' => 'SSH-Passwort:'
            ])
            ->add('sshPort', NumberType::class, [
                'required' => true,
                'label' => 'SSH-Port:'
            ])
            ->add('group', EntityType::class, [
                'class' => 'AppBundle:Group',
                'required' => true,
                'choice_label' => function (Group $group) {
                    return $group->getName();
                },
                'label' => 'Server-Gruppe:',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Server',
            'action' => '',
            'method' => 'POST'
        ]);
    }

}
