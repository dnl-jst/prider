<?php

namespace AppBundle\Form;

use AppBundle\Entity\Group;
use AppBundle\Entity\KeyPair;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ServerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'Name'
            ])
            ->add('hostname', TextType::class, [
                'required' => true,
                'label' => 'Hostname or IP'
            ])
            ->add('sshUser', TextType::class, [
                'required' => true,
                'label' => 'SSH username'
            ])
            ->add('sshPassword', TextType::class, [
                'required' => true,
                'label' => 'SSH password'
            ])
            ->add('sshPort', NumberType::class, [
                'required' => true,
                'label' => 'SSH port'
            ])
            ->add('group', EntityType::class, [
                'class' => 'AppBundle:Group',
                'required' => true,
                'choice_label' => function (Group $group) {
                    return $group->getName();
                },
                'label' => 'Group',
            ])
            ->add('type', ChoiceType::class, [
                'required' => true,
                'choices' => [
                    'apt' => 'apt',
                    'yum' => 'yum'
                ],
                'label' => 'Used package manager'
            ])
            ->add('keyPair', EntityType::class, [
                'class' => 'AppBundle:KeyPair',
                'required' => false,
                'choice_label' => function (KeyPair $keyPair) {
                    return $keyPair->getName();
                },
                'label' => 'SSH key pair',
            ]);
    }
}
