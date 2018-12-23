<?php

namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Tournament;


class TournamentType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
              ->add('name', TextType::class)
              ->add('description', TextareaType::class)
              ->add('dateStart', DateType::class)
              ->add('address', TextType::class)
              ->add('city', TextType::class)
              ->add('postalCode', TextType::class)
              ->add('hasRound', IntegerType::class)
              ->add('nbTeams', IntegerType::class)
              ->add('isOpen', IntegerType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
          $resolver->setDefaults(array(
              'data_class' => Tournament::class
          ));
    }
}
