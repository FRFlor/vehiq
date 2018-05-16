<?php

namespace Tests\Unit;

use App\Question;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;


class QuestionModelTest extends TestCase
{
    public function testItShufflesWithoutLoss()
    {
        $testQuestion = factory(Question::class)->make();

        $shuffled = [
            $testQuestion->shuffledAnswers->choiceA,
            $testQuestion->shuffledAnswers->choiceB,
            $testQuestion->shuffledAnswers->choiceC,
            $testQuestion->shuffledAnswers->choiceD,
            $testQuestion->shuffledAnswers->choiceE
            ];

        $original = [
            $testQuestion->rightAnswer,
            $testQuestion->wrongAnswer1,
            $testQuestion->wrongAnswer2,
            $testQuestion->wrongAnswer3,
            $testQuestion->wrongAnswer4,
        ];

        // Assert the shuffledJson has all the elements of the question
        $this->assertEquals(count($shuffled), count(array_intersect($shuffled, $original)));

    }


    public function testItKnowsRightAnswerIsRight()
    {
        $testQuestion = factory(Question::class)->make();

        $rightAnswer = $testQuestion->rightAnswer;
        $wrongAnswer = $testQuestion->wrongAnswer2;

        $this->assertTrue($testQuestion->isAnswerRight($rightAnswer));

        $this->assertFalse($testQuestion->isAnswerRight($wrongAnswer));
    }

    public function testApiRightAnswer()
    {
        $testQuestion = Question::find(1);

        // Test Api detects a wrong answer properly
        $getStr = '/api/question/testAnswer?id='.$testQuestion->id."&answer=".$testQuestion->wrongAnswer1;
        $response = $this->getJson($getStr);
        $response->assertJsonFragment(['isAnswerRight'=>false]);

        // Test Api detects a right answer properly
        $getStr = '/api/question/testAnswer?id='.$testQuestion->id."&answer=".$testQuestion->rightAnswer;
        $response = $this->getJson($getStr);
        $response->assertJsonFragment(['isAnswerRight'=>true]);


    }

}
