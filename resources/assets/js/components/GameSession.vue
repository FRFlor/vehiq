<template>
    <div>

        <!--No information has been obtained from the server yet-->
        <div v-if="isLoading">
            Loading...
        </div>


        <!--The server informed that the current user is not enrolled in any upcoming games-->
        <div v-if="isNotInGame">
            You are not part of any game... (Sorry!)
            <div v-if="secondsRemaining !== 0">
                But hey! There's a game coming soon!
                <game-timer :seconds-count="secondsRemaining"
                            @time-expired="getGameStatus" @time-tick="onTimeTick"></game-timer>

                <button @click="requestToJoinGame">Let me join it!</button>
            </div>
        </div>


        <!--The server informed the user is enrolled in a game that will begin soon-->
        <div v-if="isWaitingForGame">
            Waiting for game!

            <game-timer :seconds-count="secondsRemaining"
                        @time-expired="getGameStatus" @time-tick="onTimeTick"></game-timer>
        </div>


        <!--The server informed the user is currently playing the game-->
        <div v-if="isAskingQuestion || isViewingAnswerPolls">

            <div>{{playerName}}'s Score: {{playerScore}}</div>
            <game-timer :seconds-count="secondsRemaining"
                        @time-expired="getGameStatus" @time-tick="onTimeTick"></game-timer>


            <question v-if="isAskingQuestion"
                      :question-data="questionData"
                      :is-read-only="isQuestionReadOnly"
                      @alternative-clicked="answerCurrentQuestion"></question>

            <question v-if="isViewingAnswerPolls"
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

                questionData: null,
                questionStatistics: null,
                isQuestionReadOnly: false,


                isPlayerDisqualified: false,
                playerScore: 0,




                // Player related data
                // Question related data
                // Constants:
                statuses: {
                    LOADING: 'Loading',
                    NOT_IN_GAME: 'Not in Game',
                    WAITING_FOR_GAME: 'Waiting for Game',
                    ASKING_QUESTION: 'Asking Question',
                    VIEWING_ANSWER_POLL: 'Viewing Answer Poll',
                },
            }
        },
        watch: {
            questionData: function () {
                this.isQuestionReadOnly =
                    (this.isPlayerDisqualified || this.isViewingAnswerPolls);
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
                const INDEX_OF_RIGHT_ANSWER = 0;

                for(let i = 0; i < choicesStats.length; i++){
                    if(answerText === choicesStats[i].answerText){
                        return {
                            answerText: answerText,
                            count: choicesStats[i].count,
                            isRightChoice: (i === INDEX_OF_RIGHT_ANSWER),
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
                    choiceA: this.getStatsForAnswer(this.questionData.choices.choiceA, choicesStats),
                    choiceB: this.getStatsForAnswer(this.questionData.choices.choiceB, choicesStats),
                    choiceC: this.getStatsForAnswer(this.questionData.choices.choiceC, choicesStats),
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
                                this.getUserSecret();
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
                    return;
                }

                window.axios.get(`${this.url}/api/game/getStatus?userSecretToken=${this.userSecret}`).then((response) => {
                    this.gameStatus = response.data.status;

                    this.secondsRemaining = response.data.secondsRemaining;
                    switch (this.gameStatus) {
                        case this.statuses.NOT_IN_GAME: // If there's a upcoming game, tell player when it's coming
                            break;

                        case this.statuses.WAITING_FOR_GAME: // Inform the user how long they have to wait
                            break;

                        case this.statuses.ASKING_QUESTION:
                            this.questionData = response.data.currentQuestion;
                            this.questionStatistics = null;
                            break;

                        case this.statuses.VIEWING_ANSWER_POLL:
                            //this.questionStatistics = response.data.currentQuestion.statistics;
                            this.questionStatistics = this.parseQuestionStatistics(response.data.currentQuestion.statistics);
                            this.isPlayerDisqualified = response.data.player.isDisqualified;
                            this.playerScore = response.data.player.score;
                            break;

                        default:
                            Console.log(`Unhandled Game Status = ${response.data.status}`);
                            break;
                    }
                }).catch(()=>{
                    console.log(`Failed to get Game Status.`)
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
                    console.log(`New answer has been registered: (${answerStr}`);
                    this.isQuestionReadOnly = true;
                }).catch(()=>{
                    console.log(`Failed to register answer: (${answerStr}`);
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
                    console.log(`Game Joined!`);
                    this.getGameStatus();
                }).catch(()=>{
                    console.log(`Failed to Join the Game!`);
                });
            }
        },
        computed:{
            isLoading: function(){
                return this.gameStatus === this.statuses.LOADING;
            },
            isNotInGame: function (){
                return this.gameStatus === this.statuses.NOT_IN_GAME;
            },
            isWaitingForGame: function(){
                return this.gameStatus === this.statuses.WAITING_FOR_GAME;
            },
            isAskingQuestion: function(){
                return this.gameStatus === this.statuses.ASKING_QUESTION;
            },
            isViewingAnswerPolls: function(){
                return this.gameStatus === this.statuses.VIEWING_ANSWER_POLL;
            },
        },
        mounted() {
            this.getUserSecret();
            this.getGameStatus();
        }
    }
</script>

<style scoped>

</style>