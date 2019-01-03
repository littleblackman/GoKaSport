<?php

namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Team;
use AppBundle\Form\PlayerType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;



class TeamType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
              ->add('name', TextType::class, ['label' => 'Nom'])
              ->add('description', TextareaType::class, ['required' => false])
              ->add('city', TextType::class, ['label' => 'Ville'] )
              ->add('postalCode', TextType::class, ['label' => 'Code Postal'])
              ->add('sportClass', TextType::class, ['label' => 'Catégorie'])
              ->add(
                    'players', CollectionType::class, array(
                                                              'entry_type' => PlayerType::class,
                                                              'entry_options' => array('label' => false),
                                                              'label' => false,
                                                              'required' => true,
                                                              'allow_add' => true,
                                                              'allow_delete' => true,
                                                              'prototype' => true,
                                                              'by_reference' => true    ,
                                                              'mapped' => true,
                                                            )
                    );



    }

    public function configureOptions(OptionsResolver $resolver)
    {
          $resolver->setDefaults(array(
              'data_class' => Team::class
          ));
    }
}
