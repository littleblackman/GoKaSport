<?php

namespace AppBundle\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class DateTournament extends Constraint
{
  public $message = 'La date du {{ string }} est passée, impossible de créer un tournoi.';
}
