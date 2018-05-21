<template>
    <div>

        <!--No information has been obtained from the server yet-->
        <div v-if="gameStatus === 'Loading'">
            Loading...
        </div>


        <!--The server informed that the current user is not enrolled in any upcoming games-->
        <div v-if="gameStatus === 'Not in Game'">
            You are not part of any game... (Sorry!)
            <div v-if="secondsRemaining != 0">
                But hey! There's a game coming soon!
                <game-timer :start-seconds="secondsRemaining"></game-timer>

                <button @click="requestToJoinGame">Let me join it!</button>
            </div>
        </div>


        <!--The server informed the user is enrolled in a game that will begin soon-->
        <div v-if="gameStatus === 'Waiting for Game'">
            Waiting for game!

            <game-timer :start-seconds="secondsRemaining"
                        @time-expired="getGameStatus"></game-timer>
        </div>


        <!--The server informed the user is currently in the process of answering a question-->
        <div v-if="gameStatus === 'Asking Question'">
            <game-timer :start-seconds="secondsRemaining"
                        @time-expired="getGameStatus"></game-timer>

            <question :question-data="questionData"
                      :read-only="isQuestionReadOnly"
                      @alternative-clicked="answerCurrentQuestion"></question>
        </div>


        <!--The server informed the user is currently in the process of viewing answer poll-->
        <div v-if="gameStatus === 'Viewing Answer Poll'">
            <game-timer :start-seconds="secondsRemaining"
                        @time-expired="getGameStatus"></game-timer>

            <question :question-data="questionData"
                      :question-statistics="questionStatistics"
            ></question>
        </div>


    </div>
</template>

<script>
    export default {
        props: ['playerName','url'],
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
                this.$children[0].setTimerTo(this.secondsRemaining);

                this.isQuestionReadOnly =
                    (this.isPlayerDisqualified || this.gameStatus === 'Viewing Answer Poll');
            }
        },
        methods: {
            //
            // Support Methods
            //
            parseQuestionStatistics(choicesStats){

                let processedStatistics = [];

                // The answers in the backend are in order (right, wrong1, wrong2)
                // The answers shown to the user are in randomized order
                // The following loop applies the statistic data to the corresponding answer
                for (let i = 0; i < this.questionData['choices'].length; i++)
                {
                    let answerText = this.questionData['choices'][i];
                    for (let j=0; j < choicesStats.length; j++){
                        if (choicesStats[j] === answerText){
                            processedStatistics.push({
                                'text': answerText,
                                'count':choicesStats[j]['count']
                            });
                        }
                        break;
                    }
                    break;
                }

                return processedStatistics;
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


            getGameStatus(){
                if (this.userSecret === '') {
                    // If there's no user secret. Ignore the request.
                    console.log('User Secret is required to communicate with the API, request for status cannot be sent');
                    setTimeout(this.getGameStatus, 100);
                    return false;
                }

                window.axios.get(`${this.url}/api/game/getStatus?userSecretToken=${this.userSecret}`).then((response) => {
                    this.gameStatus = response.data.status;

                    switch(this.gameStatus){
                        case 'Not in Game': // If there's a upcoming game, tell player when it's coming
                            this.secondsRemaining = response.data.secondsRemaining;
                            break;
                        case 'Waiting for Game': // Inform the user how long they have to wait
                            this.secondsRemaining = response.data.secondsRemaining;
                            break;
                        case 'Asking Question':
                            this.secondsRemaining = response.data.secondsRemaining; // How long they have to answer
                            this.questionData = response.data.currentQuestion;
                            this.questionStatistics = null;
                            this.isPlayerDisqualified = response.data.player.isDisqualified;
                            this.playerScore = response.data.player.score;
                            this.$children[0].setTimerTo(this.secondsRemaining);
                            break;
                        case 'Viewing Answer Poll':
                            this.secondsRemaining = response.data.secondsRemaining;
                            this.isPlayerDisqualified = response.data.player.isDisqualified;
                            this.playerScore = response.data.player.score;
                            this.questionStatistics = response.data.currentQuestion.statistics;
                            this.$children[0].setTimerTo(this.secondsRemaining);
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
                    setTimeout(this.answerCurrentQuestion, 100);
                    return false;
                }
                this.showQuestion=false;
                axios.put(`${this.url}/api/game/answerQuestion`,{
                        userSecretToken: this.userSecret,
                        answerGiven: answerStr
                    }).then((response) => {
                });

            },


            requestToJoinGame() {
                if (this.userSecret === '') {
                    // If there's no user secret. Ignore the request.
                    console.log('User Secret is required to communicate with the API, answer request cannot be sent');
                    setTimeout(this.requestToJoinGame, 100);
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