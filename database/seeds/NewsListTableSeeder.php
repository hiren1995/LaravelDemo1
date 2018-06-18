<?php

use Illuminate\Database\Seeder;

//seeder is basically used to create the entries for table newslist


class NewsListTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	factory(App\News::class,30)->create();

    	//here 30 is the number of news in news list
    }
}
