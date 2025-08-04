<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Validation\StrictRules\CreditCardRules;
use CodeIgniter\Validation\StrictRules\FileRules;
use CodeIgniter\Validation\StrictRules\FormatRules;
use CodeIgniter\Validation\StrictRules\Rules;

class Validation extends BaseConfig
{
    // --------------------------------------------------------------------
    // Setup
    // --------------------------------------------------------------------

    /**
     * Stores the classes that contain the
     * rules that are available.
     *
     * @var list<string>
     */
    public array $ruleSets = [
        Rules::class,
        FormatRules::class,
        FileRules::class,
        CreditCardRules::class,
    ];

    /**
     * Specifies the views that are used to display the
     * errors.
     *
     * @var array<string, string>
     */
    public array $templates = [
        'list'   => 'CodeIgniter\Validation\Views\list',
        'single' => 'CodeIgniter\Validation\Views\single',
    ];

    // --------------------------------------------------------------------
    // Rules
    // --------------------------------------------------------------------
    public array $coasterNewRules = [
        'liczba_personelu' => 'integer|required',
        'liczba_klientow' => 'integer|required',
        'dl_trasy' => 'integer|required',
        'godziny_od' => 'required|regex_match[\A[012]?\d\:[0-5]\d\z]',
        'godziny_do' => 'required|regex_match[\A[012]?\d\:[0-5]\d\z]',
    ];
    public array $coasterUpdateRules = [
        'liczba_personelu' => 'integer|required',
        'liczba_klientow' => 'integer|required',
        'godziny_od' => 'required|regex_match[\A[012]?\d\:[0-5]\d\z]',
        'godziny_do' => 'required|regex_match[\A[012]?\d\:[0-5]\d\z]',
    ];
    public array $wagonAddRules = [
        'ilosc_miejsc' => 'integer|required',
        'predkosc_wagonu' => 'numeric|required',
    ];
}
