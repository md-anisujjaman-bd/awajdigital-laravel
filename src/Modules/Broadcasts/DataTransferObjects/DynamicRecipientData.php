<?php

declare(strict_types=1);

namespace MdAnisujjamanBd\AwajdigitalLaravel\Modules\Broadcasts\DataTransferObjects;

use MdAnisujjamanBd\AwajdigitalLaravel\Exceptions\ClientValidationException;
use MdAnisujjamanBd\AwajdigitalLaravel\Modules\Shared\PhoneNumber;

final readonly class DynamicRecipientData
{
    public PhoneNumber $phoneNumber;

    /**
     * @param  array<string, string>  $data
     */
    public function __construct(
        PhoneNumber|string $phoneNumber,
        public array $data,
    ) {
        $this->phoneNumber = $phoneNumber instanceof PhoneNumber ? $phoneNumber : PhoneNumber::from($phoneNumber);

        foreach ($data as $key => $val) {
            if (mb_strlen((string) $val) > 5000) {
                throw new ClientValidationException("Dynamic data value for key [{$key}] exceeds 5000 characters limit.");
            }
        }
    }

    /**
     * @return array{phone_number: string, data: array<string, string>}
     */
    public function toArray(): array
    {
        return [
            'phone_number' => $this->phoneNumber->toString(),
            'data' => $this->data,
        ];
    }
}
