<?php

namespace AppBundle\Form;

use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'Name'
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'E-Mail Address'
            ])
            ->add('plainPassword', PasswordType::class, [
                'required' => false,
                'label' => 'Password'
            ])
            ->add('notifications', ChoiceType::class, [
                'required' => true,
                'label' => 'Notifications',
                'choices' => array_flip(User::NOTIFICATION_ARRAY),
            ])
        ;
    }
}
