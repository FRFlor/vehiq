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

        foreach(range(0,9) as $i)
        {
            factory(Question::class)->create([
                'statement' => 'This is question '.($i+1),
                'rightAnswer' => 'Right Alternative of question '
            ]);
        }
    }
}
