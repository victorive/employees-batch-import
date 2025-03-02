<?php

namespace App\Dto;

use Carbon\Carbon;
use InvalidArgumentException;

class EmployeeDto
{
    public function __construct(
        public readonly int $empId,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly Carbon $dateOfBirth,
        public readonly Carbon $dateOfJoining,
        public readonly string $phoneNumber,
        public readonly ?string $namePrefix = null,
        public readonly ?string $middleInitial = null,
        public readonly ?string $gender = null,
        public readonly ?Carbon $timeOfBirth = null,
        public readonly ?float $ageInYears = null,
        public readonly ?float $ageInCompanyYears = null,
        public readonly ?string $placeName = null,
        public readonly ?string $county = null,
        public readonly ?string $city = null,
        public readonly ?string $zip = null,
        public readonly ?string $region = null,
        public readonly ?string $userName = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        self::validate($data);

        try {
            $dateOfBirth = Carbon::createFromFormat('m/d/Y', $data['Date of Birth']);
        } catch (\Exception $e) {
            throw new InvalidArgumentException("Invalid Date of Birth format: {$data['Date of Birth']}");
        }

        try {
            $dateOfJoining = Carbon::createFromFormat('m/d/Y', $data['Date of Joining']);
        } catch (\Exception $e) {
            throw new InvalidArgumentException("Invalid Date of Joining format: {$data['Date of Joining']}");
        }

        return new self(
            empId: (int) $data['Emp ID'],
            firstName: $data['First Name'],
            lastName: $data['Last Name'],
            email: $data['E Mail'],
            dateOfBirth: $dateOfBirth,
            dateOfJoining: $dateOfJoining,
            phoneNumber: $data['Phone No. '],
            namePrefix: $data['Name Prefix'] ?? null,
            middleInitial: $data['Middle Initial'] ?? null,
            gender: $data['Gender'] ?? null,
            timeOfBirth: isset($data['Time of Birth']) ?
            Carbon::createFromFormat('h:i:s A', $data['Time of Birth']) : null,
            ageInYears: isset($data['Age in Yrs.']) ? (float) $data['Age in Yrs.'] : null,
            ageInCompanyYears: isset($data['Age in Company (Years)']) ?
            (float) $data['Age in Company (Years)'] : null,
            placeName: $data['Place Name'] ?? null,
            county: $data['County'] ?? null,
            city: $data['City'] ?? null,
            zip: $data['Zip'] ?? null,
            region: $data['Region'] ?? null,
            userName: $data['User Name'] ?? null,
        );
    }

    public function toDatabaseArray(): array
    {
        return [
            'emp_id' => $this->empId,
            'name_prefix' => $this->namePrefix,
            'first_name' => $this->firstName,
            'middle_initial' => $this->middleInitial,
            'last_name' => $this->lastName,
            'gender' => $this->gender,
            'email' => $this->email,
            'date_of_birth' => $this->dateOfBirth,
            'time_of_birth' => $this->timeOfBirth,
            'age_in_yrs' => $this->ageInYears,
            'date_of_joining' => $this->dateOfJoining,
            'age_in_company_yrs' => $this->ageInCompanyYears,
            'phone_number' => $this->phoneNumber,
            'place_name' => $this->placeName,
            'county' => $this->county,
            'city' => $this->city,
            'zip' => $this->zip,
            'region' => $this->region,
            'user_name' => $this->userName,
        ];
    }

    private static function validate(array $data): void
    {
        $required = [
            'Emp ID',
            'First Name',
            'Last Name',
            'E Mail',
            'Date of Birth',
            'Date of Joining',
            'Phone No. '
        ];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new InvalidArgumentException("Missing required field: $field");
            }
        }

        if (!filter_var($data['E Mail'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email format: {$data['E Mail']}");
        }
    }
}
