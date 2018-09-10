<?php

use Illuminate\Database\Seeder;

class ColorTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('colors')->insert([
            ['id' => 1, 'name' => 'Blue', 'class_name' => 'bg-primary text-white', 'status' => 'A', ],
            ['id' => 2, 'name' => 'Gray', 'class_name' => 'bg-secondary text-white', 'status' => 'A', ],
            ['id' => 3, 'name' => 'Green', 'class_name' => 'bg-success text-white', 'status' => 'A', ],
            ['id' => 4, 'name' => 'Red', 'class_name' => 'bg-danger text-white', 'status' => 'A', ],
            ['id' => 5, 'name' => 'Orange', 'class_name' => 'bg-warning text-dark', 'status' => 'A', ],
            ['id' => 6, 'name' => 'Turquoise', 'class_name' => 'bg-info text-white', 'status' => 'A', ],
            ['id' => 7, 'name' => 'Dark', 'class_name' => 'bg-dark text-white', 'status' => 'A', ],
            ['id' => 8, 'name' => 'White', 'class_name' => 'bg-white text-dark', 'status' => 'A', ],
        ]);
    }
}
