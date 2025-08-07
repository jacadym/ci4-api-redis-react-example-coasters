<?php

namespace App\Controllers\API;

use App\Controllers\BaseController;
use App\Libraries\ReactRedis;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class Wagon extends BaseController
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
        $collection = $this->redis->getCollection(ReactRedis::HASH_WAGON);

        return $this->respond(['status' => true, 'items' => $collection], 200);
    }

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

        $wagonId = $this->redis->getSequence('seq_wagon');
        $key = ReactRedis::getKeyWagon($coasterId, $wagonId);
        $validData['coaster_id'] = $coasterId;
        $validData['wagon_id'] = $wagonId;
        $this->redis->createData(ReactRedis::HASH_WAGON, $key, $validData);

        return $this->respondCreated(['status' => true, 'id' => $wagonId]);
    }

    public function delete(int $coasterId, int $wagonId): ResponseInterface
    {
        $key = ReactRedis::getKeyWagon($coasterId, $wagonId);
        $this->redis->deleteData(ReactRedis::HASH_WAGON, $key);

        return $this->respondDeleted(['status' => true, 'id' => $wagonId]);
    }
}
