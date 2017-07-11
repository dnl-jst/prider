<?php

namespace AppBundle\Form;

use AppBundle\Entity\Group;
use AppBundle\Entity\Topic;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{

    public function getName()
    {
        return 'UserType';
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
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'E-Mail-Adresse:'
            ])
            ->add('plainPassword', PasswordType::class, [
                'required' => false,
                'label' => 'Passwort:'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\User',
            'action' => '',
            'method' => 'POST'
        ]);
    }

}
