<?php

namespace App\Entities;

class CoasterDTO
{
    private $coaster_id;
    private $liczba_personelu;
    private $liczba_klientow;
    private $dl_trasy;
    private $godziny_od;
    private $godziny_do;

    /**
     * @var array<int, WagonDTO>
     */
    private array $wagons = [];

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    public static function create(array $data = []): CoasterDTO
    {
        return new self($data);
    }

    public function update(array $data = []): self
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }

        return $this;
    }

    public function getData(): array
    {
        return [
            'coaster_id' => $this->getCoasterId(),
            'liczba_personelu' => $this->getStaffCount(),
            'liczba_klientow' => $this->getCustomerCount(),
            'dl_trasy' => $this->getTrackLength(),
            'godziny_od' => $this->getStartTime(),
            'godziny_do' => $this->getEndTime(),
        ];
    }

    public function getCoasterId(): int
    {
        return (int) $this->coaster_id;
    }

    public function getStaffCount(): int
    {
        return (int) $this->liczba_personelu;
    }

    public function getCustomerCount(): int
    {
        return (int) $this->liczba_klientow;
    }

    public function getTrackLength(): int
    {
        return (int) $this->dl_trasy;
    }

    public function getStartTime(): string
    {
        return $this->godziny_od;
    }

    public function getEndTime(): string
    {
        return $this->godziny_do;
    }

    public function getWagon(string $wagonId): ?WagonDTO
    {
        return $this->wagons[$wagonId] ?? null;
    }

    public function getWagonsCount(): int
    {
        return count($this->wagons);
    }

    public function addWagon(WagonDTO $wagon): self
    {
        $wagonId = $wagon->getWagonId();
        if (!isset($this->wagons[$wagonId])) {
            $this->wagons[$wagonId] = $wagon;
        }

        return $this;
    }

    /**
     * @return array<string, WagonDTO>
     */
    public function getWagons(): array
    {
        return $this->wagons;
    }
}
