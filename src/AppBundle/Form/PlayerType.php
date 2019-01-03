<?php

namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Player;


class PlayerType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
              ->add('firstName', TextType::class, ['label' => 'Prénom'])
              ->add('lastName', TextType::class, ['label' => 'Nom'])
              ->add('position', TextType::class, ['label' => 'Poste']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
          $resolver->setDefaults(array(
              'data_class' => Player::class
          ));
    }
}
