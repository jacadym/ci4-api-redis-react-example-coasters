<?php

namespace App\Libraries;

use App\Entities\CoasterDTO;
use DateTime;

class CoasterValidator
{
    public const BREAKTIME = 5;    // Break time in minutes between trips
    public const COASTER_STAFF = 1; // Number of staff required for the coaster
    public const WAGON_STAFF = 2;   // Number of staff required per wagon

    private ?CoasterDTO $coasterDTO = null;

    private array $calculated = [
        'calculated' => false,
        'coaster_operating_time' => 0,
        'staff_required' => 0,
        'wagon_travel_time' => 0,
        'wagon_required' => 0,
        'transported_passengers' => 0,
        'more_wagons' => 0,
        'more_passengers' => 0,
        'more_staff' => 0,
    ];

    public function setCoaster(CoasterDTO $coasterDTO): void
    {
        $this->coasterDTO = $coasterDTO;
        $this->calculated['calculated'] = false;
        $this->calculate();
    }

    public function calculate(): void
    {
        if ($this->calculated['calculated']) {
            return;
        }

        $start = new DateTime($this->coasterDTO->getStartTime());
        $end = new DateTime($this->coasterDTO->getEndTime());
        $diff = $start->diff($end);
        $operatingTime = ($diff->h * 60) + $diff->i; // Total operating time in minutes
        $plannedPassengers = $this->coasterDTO->getCustomerCount();
        $plannedPassengers2 = $plannedPassengers * 2;

        $travelTime = 0;
        $transportedPassengers = 0;
        $workingWagons = 0;
        $moreWagons = 0;
        $morePassengers = 0;
        if ($this->coasterDTO->getWagonsCount() > 0) {
            // Calculate travel time based on the slowest wagon
            $minSpeed = min(array_map(fn($wagon): float => $wagon->getSpeed(), $this->coasterDTO->getWagons()));
            $travelTime = $minSpeed > 0 ? (int) ceil(($this->coasterDTO->getTrackLength() / $minSpeed) / 60) : 0; // Convert to minutes
            $workingTime = $operatingTime;
            $totalSeatCount = 0;
            foreach ($this->coasterDTO->getWagons() as $wagon) {
                $workingWagons += 1;
                // Calculate the number of trips for each wagon (up and down)
                $wagonTrips = (int) floor($workingTime / (($travelTime * 2) + self::BREAKTIME));
                $wagonSeatCount = $wagon->getSeatCount() * 2;
                $totalSeatCount += $wagonSeatCount; // total seats in all wagons
                $transportedPassengers += $wagonTrips * $wagonSeatCount; // up and down trips
                $workingTime -= self::BREAKTIME;
                if ($workingTime < ($travelTime * 2)) {
                    break; // No more time left for trips
                }
                if ($transportedPassengers >= $plannedPassengers2) {
                    break; // Enough passengers transported
                }
            }
            $averageSeats = (int) ($totalSeatCount / $workingWagons);
            while (
                ($workingTime > ($travelTime * 2))
                && (($transportedPassengers + $morePassengers) < $plannedPassengers)
            ) {
                // Calculate additional passengers that can be transported with more wagons
                $wagonTrips = (int) floor($workingTime / (($travelTime * 2) + self::BREAKTIME));
                $morePassengers += $wagonTrips * $averageSeats;
                $moreWagons += 1;
                $workingTime -= self::BREAKTIME; // Add break time for each additional wagon
            }
        }

        $this->calculated['coaster_operating_time'] = $operatingTime;
        $this->calculated['wagon_travel_time'] = $travelTime;
        $this->calculated['transported_passengers'] = $transportedPassengers;
        $this->calculated['more_wagons'] = $moreWagons;
        $this->calculated['more_passengers'] = $morePassengers;
        $this->calculated['more_staff'] = $moreWagons * self::WAGON_STAFF;
        $this->calculated['wagon_required'] = $workingWagons + $moreWagons;
        $this->calculated['staff_required'] = self::COASTER_STAFF + (self::WAGON_STAFF * $this->calculated['wagon_required']);

        $this->calculated['calculated'] = true;
    }

    public function getStatusString(): string
    {
        if (!$this->calculated['calculated']) {
            $this->calculate();
        }

        $problems = [];
        $status = 'Status: OK';

        $diffStaff = $this->coasterDTO->getStaffCount() - $this->getRequiredStaff();
        if ($diffStaff < 0) {
            $problems[] = sprintf('Brakuje %d pracownika(ów)', abs($diffStaff));
        } elseif ($diffStaff > 0) {
            $problems[] = sprintf('Nadmiarowo jest %d pracownik(ów)', $diffStaff);
        }

        $diffWagons = $this->coasterDTO->getWagonsCount() - $this->getRequiredWagons();
        if ($diffWagons < 0) {
            $problems[] = sprintf('Brak %d wagonu(ów)', abs($diffWagons));
        } elseif ($diffWagons > 0) {
            $problems[] = sprintf('Nadmiarowo %d wagon(y/ów)', $diffWagons);
        }

        if (($this->calculated['transported_passengers'] + $this->calculated['more_passengers']) < $this->coasterDTO->getCustomerCount()) {
            $problems[] = sprintf('Kolejka nie może obsłużyć takiej liczby klientów');
        }

        if (!empty($problems)) {
            $status = 'Problem: ' . implode(', ', $problems);
        }

        return $status;
    }

    public function getRequiredStaff(): int
    {
        if (!$this->calculated['calculated']) {
            $this->calculate();
        }

        return $this->calculated['staff_required'];
    }

    public function getRequiredWagons(): int
    {
        if (!$this->calculated['calculated']) {
            $this->calculate();
        }

        return $this->calculated['wagon_required'];
    }
}