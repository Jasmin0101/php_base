<?php

namespace App\Entity;

class Delta
{
    public function __construct(
        private readonly float $absolute,
        private readonly float $percentage
    ) {
    }

    public function getAbsolute(): float
    {
        return $this->absolute;
    }

    public function getPercentage(): float
    {
        return $this->percentage;
    }

    public function __toString(): string
    {
        return sprintf(
            '%+.2f  coin (%+.2f%%)',
            $this->absolute,
            $this->percentage
        );
    }
}
