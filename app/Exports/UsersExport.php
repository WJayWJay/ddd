<?php

namespace App\Exports;

use App\Model\BasicInfo;
use App\User;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;


class UsersExport implements FromArray, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return User::all();
    }

    public function array(): array
    {
        // TODO: Implement array() method.

    }

    public function map($row): array
    {
        // TODO: Implement map() method.
    }

}
