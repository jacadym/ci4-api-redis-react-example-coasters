<?php

namespace App\Controllers\API;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class Coaster extends BaseController
{
    use ResponseTrait;

    public function index(): string
    {
        return 'List of coasters';
    }

    public function new(): ResponseInterface
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
        if (!$this->validateData($data, 'coasterNewRules')) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $validData = $this->validator->getValidated();

        return $this->respondCreated($validData);
    }

    public function update(int $coasterId): ResponseInterface
    {
        if (!$this->request->is('put')) {
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
        if (!$this->validateData($data, 'coasterUpdateRules')) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $validData = $this->validator->getValidated();

        return $this->respondUpdated($validData);
    }
}
