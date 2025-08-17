<?php

namespace App\Entities;

class CoasterCollection
{
    private array $coasters = [];

    public static function create(): self
    {
        return new self();
    }

    public function get(): array
    {
        return $this->coasters;
    }

    public function add(CoasterDTO $coaster): self
    {
        $coasterId = $coaster->getCoasterId();
        if (!isset($this->coasters[$coasterId])) {
            $this->coasters[$coasterId] = $coaster;
        } else {
            // If the coaster already exists, we can merge the wagon
            foreach ($coaster->getWagons() as $wagon) {
                $this->coasters[$coasterId]->addWagon($wagon);
            }
            // and update the coaster data if needed
            $this->coasters[$coasterId]->update($coaster->getData());
        }

        return $this;
    }

    public function addWagon(WagonDTO $wagon): self
    {
        $coasterId = $wagon->getCoasterId();
        if (isset($this->coasters[$coasterId])) {
            $this->coasters[$coasterId]->addWagon($wagon);
        } else {
            // If the coaster does not exist, create a new one with the wagon
            $newCoaster = CoasterDTO::create([
                'coaster_id' => $coasterId,
                'liczba_personelu' => 0, // Default value, can be adjusted
                'liczba_klientow' => 0,  // Default value, can be adjusted
                'dl_trasy' => 0,         // Default value, can be adjusted
                'godziny_od' => '00:00', // Default value, can be adjusted
                'godziny_do' => '23:59', // Default value, can be adjusted
            ])->addWagon($wagon);
            $this->add($newCoaster);
        }

        return $this;
    }
}
