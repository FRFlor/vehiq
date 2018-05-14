<?php

namespace Tests\Unit;

use App\Question;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;


class QuestionModelTest extends TestCase
{
    public function testItProducesShuffledJsonWithoutLoss()
    {
        $testQuestion = factory(Question::class)->make();

        $questionAsShuffledJson = $testQuestion->getShuffledJson();

        $jsonElements = [
            $questionAsShuffledJson->statement,
            $questionAsShuffledJson->choiceA,
            $questionAsShuffledJson->choiceB,
            $questionAsShuffledJson->choiceC,
            $questionAsShuffledJson->choiceD,
            $questionAsShuffledJson->choiceE,
        ];

        $modelElements = [
            $testQuestion->statement,
            $testQuestion->rightAnswer,
            $testQuestion->wrongAnswer1,
            $testQuestion->wrongAnswer2,
            $testQuestion->wrongAnswer3,
            $testQuestion->wrongAnswer4,
        ];

        // Assert the shuffledJson has all the elements of the question
        $this->assertEquals(count($jsonElements), count(array_intersect($jsonElements, $modelElements)));

    }


    public function testItKnowsRightAnswerIsRight()
    {
        $testQuestion = factory(Question::class)->make();

        $rightAnswer = $testQuestion->rightAnswer;
        $wrongAnswer = $testQuestion->wrongAnswer2;

        $this->assertTrue($testQuestion->isAnswerRight($rightAnswer));

        $this->assertFalse($testQuestion->isAnswerRight($wrongAnswer));
    }

}
