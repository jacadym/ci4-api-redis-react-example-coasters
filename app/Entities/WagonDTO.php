<?php

namespace App\Entities;

class WagonDTO
{
    private $wagon_id;
    private $coaster_id;
    private $ilosc_miejsc;
    private $predkosc_wagonu;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    public static function create(array $data = []): WagonDTO
    {
        return new self($data);
    }

    public function getWagonId(): int
    {
        return (int) $this->wagon_id;
    }

    public function getCoasterId(): int
    {
        return (int) $this->coaster_id;
    }

    public function getSeatCount(): int
    {
        return (int) $this->ilosc_miejsc;
    }

    public function getSpeed(): float
    {
        return (float) $this->predkosc_wagonu;
    }
}
