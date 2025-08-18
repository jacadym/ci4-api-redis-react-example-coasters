<?php

namespace Tests\Unit\Libraries;

use App\Entities\CoasterDTO;
use App\Entities\WagonDTO;
use App\Libraries\CoasterValidator;
use CodeIgniter\Test\CIUnitTestCase;

class CoasterValidatorTest extends CIUnitTestCase
{
    public function testCalculateForOneWagon(): void
    {
        $validator = new CoasterValidator();
        $validator->setCoaster($this->createCoaster());

        $this->assertEquals(1, $validator->getRequiredWagons());
        $this->assertEquals(3, $validator->getRequiredStaff());
        $this->assertStringContainsString('OK', $validator->getStatusString());
    }

    private function createCoaster(): CoasterDTO
    {
        $coaster = CoasterDTO::create([
            'coaster_id' => 1,
            'liczba_personelu' => 3,
            'liczba_klientow' => 160,
            'dl_trasy' => 450,
            'godziny_od' => '12:00',
            'godziny_do' => '13:00',
        ]);
        $coaster->addWagon(WagonDTO::create([
            'wagon_id' => 1,
            'coaster_id' => 1,
            'ilosc_miejsc' => 20,
            'predkosc_wagonu' => 1.5,
        ]));

        return $coaster;
    }
}
