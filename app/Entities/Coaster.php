<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Coaster extends Entity
{
    protected $attributes = [
        'coaster_id' => null,
        'liczba_personelu' => null,
        'liczba_klientow' => null,
        'dl_trasy' => null,
        'godziny_od' => null,
        'godziny_do' => null,
    ];
}
