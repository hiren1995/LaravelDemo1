<?php

use Faker\Generator as Faker;

$factory->define(Model::class, function (Faker $faker) {
    return [
        

    	'newstitle' => $faker->text(50);
    	'newsbody' => $faker->text(200);

    ];
});
