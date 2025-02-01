<?php

namespace App\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AgeExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('age', [$this, 'calculateAge']),
        ];
    }

    public function calculateAge($birthDate): int
    {
        if (!$birthDate instanceof \DateTimeInterface) {
            return 0;
        }

        $now = new \DateTime();
        $interval = $birthDate->diff($now);
        
        return $interval->y;
    }
}
