<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Wagon extends Entity
{
    protected $attributes = [
        'wagon_id' => null,
        'coaster_id' => null,
        'ilosc_miejsc' => null,
        'predkosc_wagonu' => null,
    ];
}
