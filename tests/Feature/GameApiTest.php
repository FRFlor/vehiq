<?php

namespace Tests\Feature;

use App\Game;
use App\User;
use App\Question;
use Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class GameApiTest extends TestCase
{
    // The player is not in any game yet
    // The api should know that and inform the player of an upcoming game
    public function testItInformsPlayerOfUpcomingGameWhenPlayerIsNotInAnyGame(){

        //
        // SET UP
        //
        // Create a game with 12 questions
        factory(Game::class)->create();
        $this->addMultipleQuestionsToLatestGame(12);

        // Create a fake user that is not in any game yet
        factory(User::class)->create();
        $this->addFakeSecretTokenForPlayer(1);

        // Retrieve the player's secret (to use the game api)
        $this->actingAs(User::find(1));
        $secret = $this->getJson('/oauth/clients')
            ->json()[0]['secret']; // Used to identify user

        // Set the game start time to 35 seconds from now
        Game::find(1)->update(['startTime' => Carbon::now()->addSeconds(35)]);


        //
        // EXECUTION
        //
        $response = $this->getJson("/api/game/getStatus?userSecretToken=$secret");


        //
        // ASSESSMENT
        //
        $response->assertJsonFragment([
            'status' => 'Not in Game',
        ]);
        $this->assertGreaterThan(30,$response->json(['secondsRemaining']));

    }


    // The player is not in any game and requests the API to put them into a game
    public function testItAllowsPlayerToJoinGame()
    {
        //
        // SET UP
        //
        // Create a game with 12 questions
        factory(Game::class)->create();
        $this->addMultipleQuestionsToLatestGame(12);

        // Create a fake user that is not in any game yet
        factory(User::class)->create();
        $this->addFakeSecretTokenForPlayer(1);

        // Retrieve the player's secret (to use the game api)
        $this->actingAs(User::find(1));
        $secret = $this->getJson('/oauth/clients')
            ->json()[0]['secret']; // Used to identify user

        // Set the game start time to 35 seconds from now
        Game::find(1)->update(['startTime' => Carbon::now()->addSeconds(35)]);


        //
        // EXECUTION
        //
        $response = $this->postJson("/api/game/joinGame?userSecretToken=$secret");


        //
        // ASSESSMENT
        //
        $response->assertStatus(Response::HTTP_OK);
    }


    // The player is already in a upcoming/ongoing game and requests
    // the API to join another game. The api should reject the request.
    public function testItPreventsPlayerFromJoiningMultipleGames()
    {
        //
        // SET UP
        //
        // Create a game with 12 questions
        factory(Game::class)->create();
        $this->addMultipleQuestionsToLatestGame(12);

        // Create a fake user that is already enrolled to the game
        factory(User::class)->create();
        $this->addFakeSecretTokenForPlayer(1);
        User::find(1)->joinGame();

        // Create a second game
        factory(Game::class)->create();
        $this->addMultipleQuestionsToLatestGame(6);

        // Retrieve the player's secret (to use the game api)
        $this->actingAs(User::find(1));
        $secret = $this->getJson('/oauth/clients')
            ->json()[0]['secret']; // Used to identify user

        // Set both games to start in the future
        Game::find(1)->update(['startTime' => Carbon::now()->addSeconds(35)]);
        Game::find(2)->update(['startTime' => Carbon::now()->addSeconds(55)]);


        //
        // EXECUTION
        //
        $response = $this->postJson("/api/game/joinGame?userSecretToken=$secret");


        //
        // ASSESSMENT
        //
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }


    // The player is currently enrolled in a game
    // The api should inform the player how long it will take until the game starts
    public function testItInformsPlayerOfUpcomingGameWhenPlayerIsWaitingForGameToStart(){


        //
        // SET UP
        //
        // Create a game with 12 questions
        factory(Game::class)->create();
        $this->addMultipleQuestionsToLatestGame(12);

        // Create a fake user that is in a game
        factory(User::class)->create();
        $this->addFakeSecretTokenForPlayer(1);
        User::find(1)->joinGame();

        // Retrieve the player's secret (to use the game api)
        $this->actingAs(User::find(1));
        $secret = $this->getJson('/oauth/clients')
            ->json()[0]['secret']; // Used to identify user

        // Set the game start time to 35 seconds from now
        Game::find(1)->update(['startTime' => Carbon::now()->addSeconds(35)]);


        //
        // EXECUTION
        //
        $response = $this->getJson("/api/game/getStatus?userSecretToken=$secret");


        //
        // ASSESSMENT
        //
        $response->assertJsonFragment([
            'status' => 'Waiting for Game',
        ]);
        $this->assertGreaterThan(30,$response->json(['secondsRemaining']));

    }



    // The player is enrolled in a game, and the game has already started!
    // Test if the api is asking the player a question.
    public function testItRecognizesWhenAGameStarts(){


        //
        // SET UP
        //
        // Create a game with 12 questions
        factory(Game::class)->create();
        $this->addMultipleQuestionsToLatestGame(12);

        // Create a user that is enrolled in the game
        factory(User::class)->create();
        $this->addFakeSecretTokenForPlayer(1);
        User::find(1)->joinGame();

        // Retrieve the player's secret (to use the game api)
        $this->actingAs(User::find(1));
        $secret = $this->getJson('/oauth/clients')
            ->json()[0]['secret']; // Used to identify user


        // The game has started 5 seconds ago
        Game::find(1)->update(['startTime' => Carbon::now()->subSeconds(5)]);


        //
        // EXECUTION
        //
        $response = $this->getJson("/api/game/getStatus?userSecretToken=$secret");


        //
        // ASSESSMENT
        //
        $response->assertSeeText("\"questionNumber\":1");
        $response->assertJsonFragment([
            'status' => 'Asking Question',
            'player' => [
                'isDisqualified' => false,
                'score' => 0,
            ]]);
    }



    // The game can end in two ways, either a player answers every
    // question available (completionist end of the game) or every
    // player gets disqualified (premature end of the game)
    //
    // This function tests if the game ends if all players have been disqualified
    // even when there are more questions available to ask
    public function testItEndsTheGameIfAllPlayersAreDisqualified()
    {


        //
        // SET UP
        //
        // Create a game with 12 questions
        factory(Game::class)->create([
            'secondsToAnswerQuestion' => 10,
            'secondsToReadQuestionStats' => 5]);
        $this->addMultipleQuestionsToLatestGame(12);

        // Create a user that is enrolled in the game
        factory(User::class)->create();
        $this->addFakeSecretTokenForPlayer(1);
        User::find(1)->joinGame();

        // Retrieve the player's secret (to use the game api)
        $this->actingAs(User::find(1));
        $secret = $this->getJson('/oauth/clients')
            ->json()[0]['secret']; // Used to identify user

        // Question 1 is in display
        $game = Game::find(1);
        $game->update(['startTime' => Carbon::now()->subSeconds(5)]);

        // Player Answers question 1 wrong (Causing them to be disqualified)
        User::find(1)->answerQuestion($game->currentQuestion->wrongAnswer1);

        // Enough time has passed for the second question to be on display
        $game->update(['startTime' => Carbon::now()->subSeconds(20)]);


        //
        // EXECUTION
        //
        $response = $this->getJson("/api/game/getStatus?userSecretToken=$secret");


        //
        // ASSESSMENT
        //

        // There should be no question 2, since all players have been disqualified.
        // The game should have ended prematurely
        $response->assertJsonFragment([
            'status' => 'Not in Game'
        ]);
    }


    // When all players get disqualified the game ends, however it is important to keep the
    // complete execution of the game loop. That is:
    // - Ask a question (All players get it wrong in this test)
    // - Wait for question time to expire
    // - Show question statistics
    // - Wait for question statistics time to expire
    // - End game  (since all players have been disqualified)
    public function testItKeepsTheGameLoopCompleteEvenWhenAllPlayersHaveBeenDisqualified(){


        //
        // SET UP
        //
        // Create a game with 12 questions
        factory(Game::class)->create([
            'secondsToAnswerQuestion' => 10,
            'secondsToReadQuestionStats' => 5]);
        $this->addMultipleQuestionsToLatestGame(12);

        // Create a user that is enrolled in the game
        factory(User::class)->create();
        $this->addFakeSecretTokenForPlayer(1);
        User::find(1)->joinGame();

        // Retrieve the player's secret (to use the game api)
        $this->actingAs(User::find(1));
        $secret = $this->getJson('/oauth/clients')
            ->json()[0]['secret']; // Used to identify user

        // Question 1 is in display
        $game = Game::find(1);
        $game->update(['startTime' => Carbon::now()->subSeconds(5)]);
        // Player Answers it wrong (Causing them to be disqualified)
        User::find(1)->answerQuestion($game->currentQuestion->wrongAnswer1);

        //
        // EXECUTION + ASSESSMENT
        //

        // The game should still be holding the question on the screen after
        // the player answers it wrong
        $response = $this->getJson("/api/game/getStatus?userSecretToken=$secret");
        $response->assertJsonFragment([
            'status' => 'Asking Question'
        ]);

        // The game should still show the answer statistics for the player after the
        // asking question period
        $game->update(['startTime' => Carbon::now()->subSeconds(12)]);
        $response = $this->getJson("/api/game/getStatus?userSecretToken=$secret");
        $response->assertJsonFragment([
            'status' => 'Viewing Answer Poll'
        ]);

        // Only after the question statistics have been show the game will end because
        // all the players playing have been disqualified
        $game->update(['startTime' => Carbon::now()->subSeconds(20)]);
        $response = $this->getJson("/api/game/getStatus?userSecretToken=$secret");
        $response->assertJsonFragment([
            'status' => 'Not in Game'
        ]);
    }


    // The game can end in two ways, either a player answers every
    // question available (completionist end of the game) or every
    // player gets disqualified (premature end of the game)
    //
    // This function tests if the game ends after all questions have been asked
    public function testItEndsTheGameIfAllQuestionsHaveBeenAsked()
    {


        //
        // SET UP
        //
        // Create a game with 12 questions
        factory(Game::class)->create([
            'secondsToAnswerQuestion' => 10,
            'secondsToReadQuestionStats' => 5]);
        $this->addMultipleQuestionsToLatestGame(2); // Game has only 2 questions

        // Create a user that is enrolled in the game
        factory(User::class)->create();
        $this->addFakeSecretTokenForPlayer(1);
        User::find(1)->joinGame();

        // Retrieve the player's secret (to use the game api)
        $this->actingAs(User::find(1));
        $secret = $this->getJson('/oauth/clients')
            ->json()[0]['secret']; // Used to identify user


        // Question 1 is in display
        $game = Game::find(1);
        $game->update(['startTime' => Carbon::now()->subSeconds(5)]);
        // Player Answers it right
        User::find(1)->answerQuestion($game->currentQuestion->rightAnswer);

        // Question 2 is in display
        $game->update(['startTime' => Carbon::now()->subSeconds(20)]);
        // Player Answers it right
        User::find(1)->answerQuestion($game->currentQuestion->rightAnswer);

        // Game ends
        $game->update(['startTime' => Carbon::now()->subSeconds(50)]);


        //
        // EXECUTION
        //
        $response = $this->getJson("/api/game/getStatus?userSecretToken=$secret");


        //
        // ASSESSMENT
        //
        $response->assertJsonFragment([
            'status' => 'Not in Game'
        ]);
    }



    // If the player does not answer a question within the time limit,
    // they are disqualified
    public function testItDisqualifiesAPlayerForTimeOut()
    {


        //
        // SET UP
        //
        // Create a game with 12 questions
        factory(Game::class)->create([
            'secondsToAnswerQuestion' => 10,
            'secondsToReadQuestionStats' => 5]);
        $this->addMultipleQuestionsToLatestGame(12);

        // Create a user that is enrolled in the game
        factory(User::class)->create();
        $this->addFakeSecretTokenForPlayer(1);
        User::find(1)->joinGame();

        // Retrieve the player's secret (to use the game api)
        $this->actingAs(User::find(1));
        $secret = $this->getJson('/oauth/clients')
            ->json()[0]['secret']; // Used to identify user

        // Question 1 is in display
        $game = Game::find(1);
        $game->update(['startTime' => Carbon::now()->subSeconds(5)]);

        // Player fails to answer the question within this time frame

        // Enough time has passed for the second question to be on display
        $game->update(['startTime' => Carbon::now()->subSeconds(20)]);


        //
        // EXECUTION
        //
        $response = $this->getJson("/api/game/getStatus?userSecretToken=$secret");


        //
        // ASSESSMENT
        //

        // There should be no question 2, since all players have been disqualified.
        // The game should have ended prematurely
        $response->assertJsonFragment([
            'status' => 'Not in Game'
        ]);
    }

    // A player that is disqualified is free to watch the game that is in-progress
    function testItAllowsADisqualifiedPlayerToWatchTheGame(){


        //
        // SET UP
        //
        // Create a game with 12 questions
        factory(Game::class)->create([
            'secondsToAnswerQuestion' => 10,
            'secondsToReadQuestionStats' => 5]);
        $this->addMultipleQuestionsToLatestGame(12);

        // Create two users that are enrolled in the game
        factory(User::class)->create();
        $this->addFakeSecretTokenForPlayer(1);
        $disqualifiedPlayer = User::find(1);
        $disqualifiedPlayer->joinGame();

        factory(User::class)->create();
        $this->addFakeSecretTokenForPlayer(2);
        $activePlayer = User::find(2);
        $activePlayer->joinGame();


        // Retrieve the players' secrets (to use the game api)
        $this->actingAs($disqualifiedPlayer);
        $disqualifiedPlayerSecret = $this->getJson('/oauth/clients')
            ->json()[0]['secret']; // Used to identify user

        $this->actingAs($activePlayer);
        $activePlayerSecret = $this->getJson('/oauth/clients')
            ->json()[0]['secret']; // Used to identify user


        // Disqualify one of the players, but keep the other one qualified
        $game = Game::find(1);
        $game->update(['startTime' => Carbon::now()->subSeconds(5)]);
        // Player 1 Answers it wrong (Causing them to be disqualified)
        $disqualifiedPlayer->answerQuestion($game->currentQuestion->wrongAnswer1);
        // Player 2 Answers it correctly
        $activePlayer->answerQuestion($game->currentQuestion->rightAnswer);



        //
        // EXECUTION
        //

        // Enough time has elapsed for the second question to be on display
        $game->update(['startTime' => Carbon::now()->subSeconds(20)]);

        // Both players request the game status to the API
        $this->actingAs($activePlayer);
        $replyToActivePlayer = $this->getJson("/api/game/getStatus?userSecretToken=$activePlayerSecret");

        $this->actingAs($disqualifiedPlayer);
        $replyToDisqualifiedPlayer = $this->getJson("/api/game/getStatus?userSecretToken=$disqualifiedPlayerSecret");

        //
        // ASSESSMENT
        //

        // The game should still be displaying the question information to both players, but it should
        // know that the player that got question 1 wrong is now disqualified
        $replyToActivePlayer->assertJsonFragment([
            'status' => 'Asking Question',
            'isDisqualified' => false,
            'questionNumber' => 2]);

        $replyToDisqualifiedPlayer->assertJsonFragment([
            'status' => 'Asking Question',
            'isDisqualified' => true,
            'questionNumber' => 2]);

    }


    // Even though disqualified players are able to watch the game, they should
    // not be able to answer questions.
    function testItIgnoreRequestsToAnswerQuestionFromDisqualifiedPlayers(){


        //
        // SET UP
        //
        // Create a game with 12 questions
        factory(Game::class)->create([
            'secondsToAnswerQuestion' => 10,
            'secondsToReadQuestionStats' => 5]);
        $this->addMultipleQuestionsToLatestGame(12);

        // Create two users that are enrolled in the game
        factory(User::class)->create();
        $this->addFakeSecretTokenForPlayer(1);
        $disqualifiedPlayer = User::find(1);
        $disqualifiedPlayer->joinGame();

        factory(User::class)->create();
        $this->addFakeSecretTokenForPlayer(2);
        $activePlayer = User::find(2);
        $activePlayer->joinGame();


        // Retrieve the players' secrets (to use the game api)
        $this->actingAs($disqualifiedPlayer);
        $disqualifiedPlayerSecret = $this->getJson('/oauth/clients')
            ->json()[0]['secret']; // Used to identify user

        $this->actingAs($activePlayer);
        $activePlayerSecret = $this->getJson('/oauth/clients')
            ->json()[0]['secret']; // Used to identify user


        // Disqualify one of the players, but keep the other one qualified
        $game = Game::find(1);
        $game->update(['startTime' => Carbon::now()->subSeconds(5)]);
        // Player 1 Answers it wrong (Causing them to be disqualified)
        $disqualifiedPlayer->answerQuestion($game->currentQuestion->wrongAnswer1);
        // Player 2 Answers it correctly
        $activePlayer->answerQuestion($game->currentQuestion->rightAnswer);



        //
        // EXECUTION
        //

        // Enough time has elapsed for the second question to be on display
        $game->update(['startTime' => Carbon::now()->subSeconds(20)]);

        // Both players request the API to register their answers for question 2
        $this->actingAs($activePlayer);
        $replyToActivePlayer = $this->postJson("/api/game/answerQuestion",[
            'userSecretToken' => $activePlayerSecret,
            'answerGiven' => $game->currentQuestion->rightAnswer
        ]);

        $this->actingAs($disqualifiedPlayer);
        $replyToDisqualifiedPlayer = $this->postJson("/api/game/answerQuestion",[
            'userSecretToken' => $disqualifiedPlayerSecret,
            'answerGiven' => $game->currentQuestion->rightAnswer
        ]);


        //
        // ASSESSMENT
        //

        // The API should not store the answer given by the disqualified player and
        // store the answer given by the qualified player
        $replyToActivePlayer->assertStatus(Response::HTTP_OK);
        $replyToDisqualifiedPlayer->assertStatus(Response::HTTP_BAD_REQUEST);


        // The active player should have 2 answers stored in the database, while the
        // disqualified player should only have 1 answer stored
        $this->assertEquals(2,$activePlayer->questions()->count());
        $this->assertEquals(1,$disqualifiedPlayer->questions()->count());
    }



    // A player may only answer a given question once, the api needs to enforce this rule
    function testItIgnoresSubsequentRequestsToAnswerQuestion(){


        //
        // SET UP
        //
        // Create a game with 12 questions
        factory(Game::class)->create([
            'secondsToAnswerQuestion' => 10,
            'secondsToReadQuestionStats' => 5]);
        $this->addMultipleQuestionsToLatestGame(12);

        // Create a user that are enrolled in the game
        factory(User::class)->create();
        $this->addFakeSecretTokenForPlayer(1);
        $activePlayer = User::find(1);
        $activePlayer->joinGame();


        // Retrieve the player's secret (to use the game api)
        $this->actingAs($activePlayer);
        $activePlayerSecret = $this->getJson('/oauth/clients')
            ->json()[0]['secret']; // Used to identify user


        // Save an initial answer for the question
        $game = Game::find(1);
        $game->update(['startTime' => Carbon::now()->subSeconds(5)]);
        $activePlayer->answerQuestion($game->currentQuestion->rightAnswer);


        //
        // EXECUTION
        //

        // The player requests the api to again answer the same question
        $response = $this->postJson("/api/game/answerQuestion",[
            'userSecretToken' => $activePlayerSecret,
            'answerGiven' => $game->currentQuestion->rightAnswer
        ]);


        //
        // ASSESSMENT
        //

        // The API should not store the newly given answer, because each player should
        // only be allowed to answer a question once.
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }



    public function testItKeepsTrackOfThePlayerScore(){


        //
        // SET UP
        //
        // Create a game with 12 questions
        factory(Game::class)->create([
            'secondsToAnswerQuestion' => 10,
            'secondsToReadQuestionStats' => 5]);
        $this->addMultipleQuestionsToLatestGame(12);

        // Create a user that is enrolled in the game
        factory(User::class)->create();
        $this->addFakeSecretTokenForPlayer(1);
        User::find(1)->joinGame();

        // Retrieve the player's secret (to use the game api)
        $this->actingAs(User::find(1));
        $secret = $this->getJson('/oauth/clients')
            ->json()[0]['secret']; // Used to identify user

        $game = Game::find(1);

        //
        // EXECUTION + ASSESSMENT
        //
        // The player starts the game with 0 points
        $response = $this->getJson("/api/game/getStatus?userSecretToken=$secret");

        // The player answers the first question correctly
        $game->update(['startTime' => Carbon::now()->subSeconds(5)]);
        User::find(1)->answerQuestion($game->currentQuestion->rightAnswer);
        $response = $this->getJson("/api/game/getStatus?userSecretToken=$secret");
        $response->assertJsonFragment([
            'score' => 1
        ]);

        // The player answers the second question correctly
        $game->update(['startTime' => Carbon::now()->subSeconds(20)]);
        User::find(1)->answerQuestion($game->currentQuestion->rightAnswer);
        $response = $this->getJson("/api/game/getStatus?userSecretToken=$secret");
        $response->assertJsonFragment([
            'score' => 2
        ]);


        // The player answers the third question wrong (Score shouldn't change)
        $game->update(['startTime' => Carbon::now()->subSeconds(35)]);
        User::find(1)->answerQuestion($game->currentQuestion->wrongAnswer1);
        $response = $this->getJson("/api/game/getStatus?userSecretToken=$secret");
        $response->assertJsonFragment([
            'score' => 2
        ]);
    }




    // =================================================
    //                  Support Methods
    // =================================================
    public function addMultipleQuestionsToLatestGame($questionCount){
        for($i = 0; $i < $questionCount; $i++){
            factory(Question::class)->create();
        }
    }

    public function addFakeSecretTokenForPlayer($playerId){
        DB::table('oauth_clients')->insert([
            'user_id' => $playerId,
            'name' => 'Testing',
            'secret' => 'testSecret'.$playerId,
            'redirect' => 'http://vehiq.test/',
            'personal_access_client' => 0,
            'password_client' => 0,
            'revoked' => 0,
        ]);
    }
}
