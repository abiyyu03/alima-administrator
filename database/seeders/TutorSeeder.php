<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TutorSeeder extends Seeder
{
    public function run(): void
    {
        $tutorRole = Role::where('name', 'tutor')->first();

        $tutors = [
            [
                'name'      => 'Riza Fajriani',
                'dob'       => '1998-03-04',
                'telp'      => '089509546092',
                'domicille' => 'Bogor',
                'email'     => 'riza@alimaeducation.com',
                'password'  => 'riza12345',
            ],
            [
                'name'      => 'Santi Nugraha',
                'dob'       => '1999-03-20',
                'telp'      => '087820970132',
                'domicille' => 'Blok D2, No. 27, Griya Bukit Jaya 1',
                'email'     => 'santi@alimaeducation.com',
                'password'  => 'santi12345',
            ],
            [
                'name'      => 'Budiyana Abdul Muzaki',
                'dob'       => '2002-04-09',
                'telp'      => '083875353355',
                'domicille' => 'Karanggan Muda, RT 02/03',
                'email'     => 'budiyana@alimaeducation.com',
                'password'  => 'budiyana12345',
            ],
            [
                'name'      => 'Lathifah Diah Rahmawati',
                'dob'       => '2001-08-14',
                'telp'      => '085218195190',
                'domicille' => 'Bogor',
                'email'     => 'lathifah@alimaeducation.com',
                'password'  => 'lathifah12345',
            ],
            [
                'name'      => 'Elvira Putri Ningrum',
                'dob'       => '2003-01-23',
                'telp'      => '089636515386',
                'domicille' => 'Bogor',
                'email'     => 'elvira@alimaeducation.com',
                'password'  => 'elvira12345',
            ],
            [
                'name'      => 'Radita Yogi Oktaviani',
                'dob'       => '2002-10-28',
                'telp'      => '0895331275894',
                'domicille' => 'Cicadas, RT 02/RW 019, Gunung Putri',
                'email'     => 'radita@alimaeducation.com',
                'password'  => 'radita12345',
            ],
            [
                'name'      => 'Ikbal Septiadi',
                'dob'       => '1999-09-25',
                'telp'      => '085719705265',
                'domicille' => 'Kedep, RT 02/21, Tlajung Udik, Gunung Putri',
                'email'     => 'ikbal@alimaeducation.com',
                'password'  => 'ikbal12345',
            ],
            [
                'name'      => 'Fauzi Andrean',
                'dob'       => '1999-04-14',
                'telp'      => '081324690038',
                'domicille' => 'Tlajung Udik, Bogor',
                'email'     => 'fauzi@alimaeducation.com',
                'password'  => 'fauzi12345',
            ],
            [
                'name'      => 'Abiyyu Cakra',
                'dob'       => '2003-07-29',
                'telp'      => '085691875788',
                'domicille' => 'Bogor',
                'email'     => 'abiyyu@alimaeducation.com',
                'password'  => 'abiyyu12345',
            ],
        ];

        foreach ($tutors as $data) {
            $tutor = Tutor::firstOrCreate(
                ['name' => $data['name']],
                [
                    'dob'       => $data['dob'],
                    'telp'      => $data['telp'],
                    'domicille' => $data['domicille'],
                ]
            );

            User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'password' => Hash::make($data['password']),
                    'role_id'  => $tutorRole?->id,
                    'tutor_id' => $tutor->id,
                ]
            );
        }
    }
}
