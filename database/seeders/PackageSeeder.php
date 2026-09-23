<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Package;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        Package::insert([
            [
                'package_name' => 'Beginner Package',
                'subtitle' => 'Ideal for quick appointments or a focused consultation.',
                'price' => 600,
                'image' => '/images/packages/beginner.jpg',
                'thumbnail' => '/images/packages/thumb-beginner.jpg',
                'description' =>'Ideal for quick appointments or a focused consultation.',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'package_name' => 'Intermediate Package',
                'subtitle' => 'Perfect for standard appointments or extended consultations.',
                'price' => 360,
                'image' => '/images/packages/advanced.jpg',
                'thumbnail' => '/images/packages/thumb-advanced.jpg',
                'description' =>'Perfect for standard appointments or extended consultations.',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'package_name' => 'Advanced Package',
                'subtitle' => 'Ideal for night driving, complex traffic scenarios',
                'price' => 240,
                'image' => '/images/packages/refresher.jpg',
                'thumbnail' => '/images/packages/thumb-refresher.jpg',
                'description' =>'',
                'status' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'package_name' => 'Test Preparation Package',
                'subtitle' => 'Great for Mock driving test, exam route practice & confidence building',
                'price' => 180,
                'image' => '/images/packages/refresher.jpg',
                'thumbnail' => '/images/packages/thumb-refresher.jpg',
                'description' =>'Ideal for night driving, complex traffic scenarios',
                'status' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'package_name' => 'Individual Lesson',
                'subtitle' => 'Customized to your specific needs, flexible scheduling',
                'price' => 225,
                'image' => '/images/packages/refresher.jpg',
                'thumbnail' => '/images/packages/thumb-refresher.jpg',
                'description' =>'Customized to your specific needs, flexible scheduling',
                'status' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'package_name' => 'Vehicle hiring for test',
                'subtitle' => 'Includes pick up and drop off to the student convenient location',
                'price' => 225,
                'image' => '/images/packages/refresher.jpg',
                'thumbnail' => '/images/packages/thumb-refresher.jpg',
                'description' =>'Includes pick up and drop off to the student convenient location',
                'status' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
