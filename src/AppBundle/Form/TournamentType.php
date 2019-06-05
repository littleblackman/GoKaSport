<?php

namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\CallbackTransformer;

use AppBundle\Entity\Tournament;


class TournamentType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
              ->add('name', TextType::class, ['label' => 'Nom'])
              ->add('description', TextareaType::class, ['required' => false])
              ->add('dateStart', DateType::class, [
                                                    'label' => 'Date du tournoi',
                                                    'format' => 'dd MM yyyy',
                                          ])
              ->add('address', TextType::class, ['label' => 'Adresse'])
              ->add('city', TextType::class, ['label' => 'Ville'] )
              ->add('postalCode', TextType::class, ['label' => 'Code Postal'])
              ->add('isOpen', ChoiceType::class, [
                                                      'label' => 'ouvert',
                                                      'choices' => ['non' => 0, 'oui' => 1]
                                                    ])
              ->add('isInit', ChoiceType::class, [
                                                      'label' => 'Equipes complète',
                                                      'choices' => ['non' => 0, 'oui' => 1]
                                                    ])
              ->add('isValided', ChoiceType::class, [
                                                      'label' => 'Groupes validés',
                                                      'choices' => ['non' => 0, 'oui' => 1]
                                                    ]);


        $builder->get('dateStart')->addModelTransformer(new CallbackTransformer(
              function ($value) {
                  if(!$value) {
                      return new \DateTime('today');
                  }
                  return $value;
              },
              function ($value) {
                  return $value;
              }
        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
          $resolver->setDefaults(array(
              'data_class' => Tournament::class
          ));
    }
}
