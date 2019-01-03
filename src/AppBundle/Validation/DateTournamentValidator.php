<?php

namespace AppBundle\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class DateTournamentValidator extends ConstraintValidator
{

    public function validate($value, Constraint $constraint)
    {
        $today = new \DateTime('today');
        $interval = $today->diff($value);
        $v = $interval->format('%R%a');

        if($v <= 0) {
          $this->context->buildViolation($constraint->message)
               ->setParameter('{{ string }}', $value->format('d/m/Y'))
               ->addViolation();
        }
    }
}
