<?php

namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Person;


class PersonType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
              ->add('firstname', TextType::class, ['attr' => ['placeholder' => 'Prénom', 'autocomplete' => 'off']])
              ->add('lastname', TextType::class, ['attr' => ['placeholder' => 'Nom', 'autocomplete' => 'off']])
              ->add('birthdate', DateType::class, [ 'label' => 'Date de naissance',
                                                                            'format' => 'dd MM yyyy',
                                                                            'widget' => 'choice',
                                                                            'years' => range(date('Y')-60, date('Y')-1),
                                                                        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
          $resolver->setDefaults(array(
              'data_class' => Person::class
          ));
    }
}
