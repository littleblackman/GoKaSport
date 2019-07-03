<?php

namespace AppBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('selectOption', [$this, 'getSelectedValue']),
        ];
    }

    public function getSelectedValue($value, $string)
    {
        if($value == $string) return "selected = 'selected' ";
    }
}
