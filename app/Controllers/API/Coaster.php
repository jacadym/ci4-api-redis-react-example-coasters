<?php

namespace App\Controllers\API;

use App\Controllers\BaseController;
use App\Libraries\ReactRedis;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class Coaster extends BaseController
{
    use ResponseTrait;

    /**
     * @var ReactRedis
     */
    private $redis;

    public function __construct()
    {
        $this->redis = service('reactredis');
    }

    public function index(): ResponseInterface
    {
        $collection = $this->redis->getCollection(ReactRedis::HASH_COASTER);

        return $this->respond(['status' => true, 'items' => $collection], 200);
    }

    public function get(int $coasterId): ResponseInterface
    {
        $key = ReactRedis::getKeyCoaster($coasterId);
        $data = $this->redis->getData(ReactRedis::HASH_COASTER, $key);

        return $this->respond(['status' => true, 'item' => $data], 200);
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

        $coasterId = $this->redis->getSequence('seq_coaster');
        $key = ReactRedis::getKeyCoaster($coasterId);
        $validData['coaster_id'] = $coasterId;
        $this->redis->createData(ReactRedis::HASH_COASTER, $key, $validData);

        return $this->respondCreated(['status' => true, 'id' => $coasterId]);
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

        $key = ReactRedis::getKeyCoaster($coasterId);
        $validData['coaster_id'] = $coasterId;
        $this->redis->updateData(ReactRedis::HASH_COASTER, $key, $validData);

        return $this->respondUpdated(['status' => true, 'id' => $coasterId]);
    }

    public function delete(int $coasterId): ResponseInterface
    {
        $key = ReactRedis::getKeyCoaster($coasterId);
        $this->redis->deleteData(ReactRedis::HASH_COASTER, $key);

        return $this->respondDeleted(['status' => true, 'id' => $coasterId]);
    }
}
