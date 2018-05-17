<?php

namespace Tests\Unit;

use App\Question;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;


class QuestionModelTest extends TestCase
{
    // Will the Question model properly shuffle the alternatives? (AKA: No data is lost)
    // OBS: There's no need to test if the array is in a different order, because staying in the same order is still
    // a possibility after shuffling
    public function testItShufflesWithoutLoss()
    {
        $testQuestion = factory(Question::class)->make();

        $shuffledAnswers = $testQuestion->shuffledAnswers;
        $shuffled = [
            $shuffledAnswers->choiceA,
            $shuffledAnswers->choiceB,
            $shuffledAnswers->choiceC
            ];

        $original = [
            $testQuestion->rightAnswer,
            $testQuestion->wrongAnswer1,
            $testQuestion->wrongAnswer2
        ];

        // Assert the shuffledJson has all the elements of the question
        $this->assertEquals(count($shuffled), count(array_intersect($shuffled, $original)));

    }


    // Will the Question model properly identify when an answer given is right or wrong?
    public function testItKnowsRightAnswerIsRight()
    {
        $testQuestion = factory(Question::class)->make();

        $rightAnswer = $testQuestion->rightAnswer;
        $wrongAnswer = $testQuestion->wrongAnswer2;

        $this->assertTrue($testQuestion->isAnswerRight($rightAnswer));

        $this->assertFalse($testQuestion->isAnswerRight($wrongAnswer));
    }


}
