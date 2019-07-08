<?php

namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Player;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use AppBundle\Repository\SportPositionRepository;


class PlayerType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $sport = $_SESSION['sport'];
        unset($_SESSION['sport']);

        $builder
            ->add('position', EntityType::class, [
                                           'label' => 'Poste',
                                           'class' => 'AppBundle\Entity\SportPosition',
                                           'choice_label' => 'name',
                                           'query_builder' => function(SportPositionRepository $repository) use($sport) {
                                                                            return $repository->findPositionBySport($sport);
                                                                        },
                                       ]
                    );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
          $resolver->setDefaults(array(
              'data_class' => Player::class
          ));
    }
}
