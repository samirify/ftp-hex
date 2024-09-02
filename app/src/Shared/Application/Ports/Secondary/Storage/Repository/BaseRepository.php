<?php
declare(strict_types=1);

namespace App\Shared\Application\Ports\Secondary\Storage\Repository;

use App\Shared\Domain\Model\BaseModel;

interface BaseRepository {
    /**
     * @param BaseModel $baseModel
     * @param array $data
     *
     * @return mixed
     */
    public function save(BaseModel $baseModel, array $data): mixed;

    /**
     * @param int $id
     *
     * @return mixed
     */
    public function getById(int $id): mixed;
}