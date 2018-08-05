<?php

use Illuminate\Database\Seeder;
use App\OrderPackageBoxes;

class OrderPackageBoxesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      \DB::statement("SET FOREIGN_KEY_CHECKS=0");

        $packageBoxes = [
          [
            'id' => 1,
            'box_name' => 'DHL3',
            'box_size' => '33x31x9',
            'box_volumetric_weight' => '1.8'
          ],
          [
            'id' => 2,
            'box_name' => 'DHL4',
            'box_size' => '33x31x17',
            'box_volumetric_weight' => '3.4'
          ]
      ];

        foreach($packageBoxes as $key => $value)
        {
          OrderPackageBoxes::firstOrCreate([
            'id' => $value['id'],
            'box_name' => $value['box_name'],
            'box_size' => $value['box_size'],
            'box_volumetric_weight' => $value['box_volumetric_weight']
          ]);
        }

        \DB::statement("SET FOREIGN_KEY_CHECKS=1");
    }
}
