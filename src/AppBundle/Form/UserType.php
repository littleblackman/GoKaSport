<?php

namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\User;


class UserType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
              ->add('email', EmailType::class, ['attr' => ['placeholder' => 'Email', 'autocomplete' => 'email-'.rand(1,10000)]])
              ->add('username', TextType::class, ['attr' => ['placeholder' => 'Username', 'autocomplete' => 'new-username-'.rand(1,10000)]])
              ->add('password', PasswordType::class, ['attr' => ['placeholder' => 'Mot de passe', 'autocomplete' => 'new-password']])
              ->add('roles', ChoiceType::class, ['choices' => [
                                                                                                  'Coach'              => 'ROLE_COACH',
                                                                                                  'Organisateur.trice' => 'ROLE_MANAGER',
                                                                                                   'Arbitre'           => 'ROLE_REFEREE',
                                                                                                  'Joueur.se'          => 'ROLE_PLAYER'
                                                                                             ]
                                                                    ])

              ->add('person', PersonType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
          $resolver->setDefaults(array(
              'data_class' => User::class
          ));
    }
}
