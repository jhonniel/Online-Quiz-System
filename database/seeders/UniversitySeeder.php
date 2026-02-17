<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\University;

class UniversitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $universities = [
            [
                'name' => 'Harvard University',
                'code' => 'HARVARD',
                'location' => 'Cambridge, MA, USA',
                'description' => 'A private research university in Cambridge, Massachusetts.',
            ],
            [
                'name' => 'Stanford University',
                'code' => 'STANFORD',
                'location' => 'Stanford, CA, USA',
                'description' => 'A private research university in Stanford, California.',
            ],
            [
                'name' => 'Massachusetts Institute of Technology',
                'code' => 'MIT',
                'location' => 'Cambridge, MA, USA',
                'description' => 'A private research university in Cambridge, Massachusetts.',
            ],
            [
                'name' => 'University of California, Berkeley',
                'code' => 'UCB',
                'location' => 'Berkeley, CA, USA',
                'description' => 'A public research university in Berkeley, California.',
            ],
            [
                'name' => 'University of Oxford',
                'code' => 'OXFORD',
                'location' => 'Oxford, England',
                'description' => 'A collegiate research university in Oxford, England.',
            ],
            [
                'name' => 'University of Cambridge',
                'code' => 'CAMBRIDGE',
                'location' => 'Cambridge, England',
                'description' => 'A collegiate research university in Cambridge, England.',
            ],
            [
                'name' => 'University of Toronto',
                'code' => 'UOT',
                'location' => 'Toronto, Canada',
                'description' => 'A public research university in Toronto, Canada.',
            ],
            [
                'name' => 'University of Sydney',
                'code' => 'USYD',
                'location' => 'Sydney, Australia',
                'description' => 'A public research university in Sydney, Australia.',
            ],
            [
                'name' => 'National University of Singapore',
                'code' => 'NUS',
                'location' => 'Singapore',
                'description' => 'A public research university in Singapore.',
            ],
            [
                'name' => 'University of Tokyo',
                'code' => 'UTOKYO',
                'location' => 'Tokyo, Japan',
                'description' => 'A public research university in Tokyo, Japan.',
            ],
            [
                'name' => 'Technical University of Munich',
                'code' => 'TUM',
                'location' => 'Munich, Germany',
                'description' => 'A public research university in Munich, Germany.',
            ],
            [
                'name' => 'École Polytechnique',
                'code' => 'EP',
                'location' => 'Palaiseau, France',
                'description' => 'A public research university in Palaiseau, France.',
            ],
            [
                'name' => 'University of Melbourne',
                'code' => 'UMELB',
                'location' => 'Melbourne, Australia',
                'description' => 'A public research university in Melbourne, Australia.',
            ],
            [
                'name' => 'Seoul National University',
                'code' => 'SNU',
                'location' => 'Seoul, South Korea',
                'description' => 'A public research university in Seoul, South Korea.',
            ],
            [
                'name' => 'Indian Institute of Technology',
                'code' => 'IIT',
                'location' => 'Multiple locations, India',
                'description' => 'A group of public research universities in India.',
            ],
        ];

        foreach ($universities as $university) {
            University::create($university);
        }
    }
}
