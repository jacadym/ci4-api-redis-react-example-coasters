<?php

namespace App\Controllers\API;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class Wagon extends BaseController
{
    use ResponseTrait;

    public function add(int $coasterId): ResponseInterface
    {
        if (!$this->request->is('post')) {
            return $this->failForbidden('Method Not Allowed');
        }
        if (!$this->request->is('json')) {
            return $this->failValidationErrors('Not Valid JSON');
        }
        try {
            $data = $this->request->getJSON(true);
        } catch (\Exception $e) {
            return $this->failValidationErrors($e->getMessage());
        }
        if (!$this->validateData($data, 'wagonAddRules')) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $validData = $this->validator->getValidated();

        return $this->respondCreated($validData);
    }

    public function delete(int $coasterId, int $wagonId): ResponseInterface
    {
        return $this->respondDeleted();
    }
}
