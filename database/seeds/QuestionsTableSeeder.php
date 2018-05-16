<?php

use App\Question;
use Illuminate\Database\Seeder;

class QuestionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $numberOfQuestions = 10;

        foreach(range(1,$numberOfQuestions) as $i)
        {
            factory(Question::class)->create();
        }
    }
}
