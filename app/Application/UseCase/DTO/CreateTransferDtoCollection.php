<?php declare(strict_types=1);

namespace App\Application\UseCase\DTO;

class CreateTransferDtoCollection extends \ArrayIterator {

    public function current(): CreateTransferDto {
        return parent::current();
    }

}
