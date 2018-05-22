<template>
    <div>

        <!--No information has been obtained from the server yet-->
        <div v-if="gameStatus === 'Loading'">
            Loading...
        </div>


        <!--The server informed that the current user is not enrolled in any upcoming games-->
        <div v-if="gameStatus === 'Not in Game'">
            You are not part of any game... (Sorry!)
            <div v-if="secondsRemaining !== 0">
                But hey! There's a game coming soon!
                <game-timer :seconds-count="secondsRemaining"
                            @time-expired="getGameStatus" @time-tick="onTimeTick"></game-timer>

                <button @click="requestToJoinGame">Let me join it!</button>
            </div>
        </div>


        <!--The server informed the user is enrolled in a game that will begin soon-->
        <div v-if="gameStatus === 'Waiting for Game'">
            Waiting for game!

            <game-timer :seconds-count="secondsRemaining"
                        @time-expired="getGameStatus" @time-tick="onTimeTick"></game-timer>
        </div>


        <!--The server informed the user is currently playing the game-->
        <div v-if="gameStatus === 'Asking Question' || gameStatus === 'Viewing Answer Poll'">

            <div>{{playerName}}'s Score: {{playerScore}}</div>
            <game-timer :seconds-count="secondsRemaining"
                        @time-expired="getGameStatus" @time-tick="onTimeTick"></game-timer>


            <question v-if="gameStatus === 'Asking Question'"
                      :question-data="questionData"
                      :is-read-only="isQuestionReadOnly"
                      @alternative-clicked="answerCurrentQuestion"></question>

            <question v-if="gameStatus === 'Viewing Answer Poll'"
                      :question-data="questionData"
                      :question-statistics="questionStatistics"
            ></question>
        </div>

    </div>
</template>

<script>
    export default {
        props: ['playerName', 'url'],
        data() {
            return {
                userSecret: '',

                gameStatus: 'Loading',
                secondsRemaining: 0,

                // Question related data
                questionData: null,
                questionStatistics: null,
                isQuestionReadOnly: false,


                // Player related data
                isPlayerDisqualified: false,
                playerScore: 0,
            }
        },
        watch: {
            questionData: function () {
                this.isQuestionReadOnly =
                    (this.isPlayerDisqualified || this.gameStatus === 'Viewing Answer Poll');
            }
        },
        methods: {
            onTimeTick(){
              this.secondsRemaining--;  //Keeping the GameSession up-to-date to the Timer
            },

            //
            // Support Methods
            //
            getStatsForAnswer(answerText, choicesStats) {
                for(let i = 0; i < choicesStats.length; i++){
                    if(answerText === choicesStats[i].answerText){
                        return {
                            answerText: answerText,
                            count: choicesStats[i].count,
                            isRightChoice: (i === 0), // The answers are stored in the database
                                                    // as (right, wrong1, wrong2) therefore
                                                    // the right alternative always comes first
                        };
                    }
                }

                return {
                    answerText: "N.A.",
                    count: 0,
                    isRightChoice: false,
                };
            },
            parseQuestionStatistics(choicesStats) {

                return {
                    choiceA: this.getStatsForAnswer(this.questionData.choices.choiceA,choicesStats),
                    choiceB: this.getStatsForAnswer(this.questionData.choices.choiceB,choicesStats),
                    choiceC: this.getStatsForAnswer(this.questionData.choices.choiceC,choicesStats),
                };
            },

            //
            // API calls
            //
            getUserSecret() {
                // Try to retrieve Secret Token for API Authentication
                axios.get('/oauth/clients')
                    .then(getAuthResponse => {

                        // If no token is found in the database. Request creation of token
                        if (getAuthResponse.data.length === 0) {
                            axios.post('/oauth/clients', {
                                name: this.playerName + Date.now(),
                                redirect: this.url,
                            }).then(() => {
                                // With token now created, re-attempt to retrieve said token
                                this.getUserSecret(); //TODO: Ask Dan if Recursion is frowned upon
                            });
                            return false;
                        };

                        // Secret successfully received, save it!
                        this.userSecret = getAuthResponse.data[0].secret;
                    });
            },
            getGameStatus() {
                if (this.userSecret === '') {
                    // If there's no user secret. Ignore the request.
                    console.log('User Secret is required to communicate with the API, request for status cannot be sent');
                    setTimeout(this.getGameStatus, 500);
                    return false;
                }

                window.axios.get(`${this.url}/api/game/getStatus?userSecretToken=${this.userSecret}`).then((response) => {
                    this.gameStatus = response.data.status;

                    // TODO: const the strings
                    this.secondsRemaining = response.data.secondsRemaining;
                    switch (this.gameStatus) {
                        case 'Not in Game': // If there's a upcoming game, tell player when it's coming
                            break;
                        case 'Waiting for Game': // Inform the user how long they have to wait
                            break;
                        case 'Asking Question':
                            this.questionData = response.data.currentQuestion;
                            this.questionStatistics = null;
                            break;
                        case 'Viewing Answer Poll':
                            //this.questionStatistics = response.data.currentQuestion.statistics;
                            this.questionStatistics = this.parseQuestionStatistics(response.data.currentQuestion.statistics);
                            this.isPlayerDisqualified = response.data.player.isDisqualified;
                            this.playerScore = response.data.player.score;
                            break;
                        default:
                            Console.log(`Unhandled gameStatue = ${response.data.status}`);
                            break;
                    }

                    return true;
                });

            },


            answerCurrentQuestion(answerStr) {
                console.log(`Answering with ${answerStr}`);

                if (this.userSecret === '') {
                    // If there's no user secret. Ignore the request.
                    console.log('User Secret is required to communicate with the API, answer request cannot be sent');
                    setTimeout(this.answerCurrentQuestion, 500);
                    return false;
                }
                this.showQuestion = false;
                axios.post(`${this.url}/api/game/answerQuestion`, {
                    userSecretToken: this.userSecret,
                    answerGiven: answerStr
                }).then((response) => {
                    this.isQuestionReadOnly = true;
                });

            },


            requestToJoinGame() {
                if (this.userSecret === '') {
                    // If there's no user secret. Ignore the request.
                    console.log('User Secret is required to communicate with the API, answer request cannot be sent');
                    setTimeout(this.requestToJoinGame, 500);
                    return false;
                }

                axios.post(`${this.url}/api/game/joinGame`, {
                    userSecretToken: this.userSecret,
                }).then(() => {
                    this.getGameStatus();
                });
            }
        },
        mounted() {
            this.getUserSecret();
            this.getGameStatus();
        }
    }
</script>

<style scoped>

</style>