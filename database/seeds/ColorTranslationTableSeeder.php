<?php

use Illuminate\Database\Seeder;

class ColorTranslationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('colors_translation')->insert([
            ['id' => 1, 'color_id' => 1, 'name' => 'Blue', 'lang' => 'en', ],
            ['id' => 2, 'color_id' => 1, 'name' => 'Azul', 'lang' => 'es', ],
            ['id' => 3, 'color_id' => 2, 'name' => 'Gray','lang' => 'en', ],
            ['id' => 4, 'color_id' => 2, 'name' => 'Gris','lang' => 'es', ],
            ['id' => 5, 'color_id' => 3, 'name' => 'Green','lang' => 'en', ],
            ['id' => 6, 'color_id' => 3, 'name' => 'Verde', 'lang' => 'es',],
            ['id' => 7, 'color_id' => 4, 'name' => 'Red','lang' => 'en',  ],
            ['id' => 8, 'color_id' => 4, 'name' => 'Rojo','lang' => 'es',  ],
            ['id' => 9, 'color_id' => 5, 'name' => 'Orange','lang' => 'en',  ],
            ['id' => 10, 'color_id' => 5, 'name' => 'Naranja','lang' => 'es',  ],
            ['id' => 11, 'color_id' => 6, 'name' => 'Turquesa','lang' => 'en', ],
            ['id' => 12, 'color_id' => 6, 'name' => 'Turquoise','lang' => 'es', ],
            ['id' => 13, 'color_id' => 7, 'name' => 'Dark', 'lang' => 'en',],
            ['id' => 14, 'color_id' => 7, 'name' => 'Negro', 'lang' => 'es',],
            ['id' => 15, 'color_id' => 8, 'name' => 'White','lang' => 'en', ],
            ['id' => 16, 'color_id' => 8, 'name' => 'Blanco','lang' => 'es', ],
        ]);
    }
}
