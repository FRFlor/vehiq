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

                <button @click="onJoinGameButtonClicked">Let me join it!</button>
            </div>
        </div>


        <!--The server informed the user is enrolled in a game that will begin soon-->
        <div v-if="gameStatus === 'Waiting for Game'">
            Waiting for game!

            <game-timer :start-seconds="secondsRemaining"
                        @time-expired="onGameStarting"></game-timer>
        </div>


        <!--The server informed the user is currently in the process of answering a question-->
        <div v-if="gameStatus === 'Asking Question'">
            <game-timer :start-seconds="secondsRemaining"
                        @time-expired="onQuestionTimeEnd"></game-timer>

            <question :question-data="questionData"
                      :read-only="isQuestionReadOnly"
                      @alternative-clicked="onAlternativeClickedOnQuestion"></question>
        </div>


        <!--TODO: Implement viewing answer poll-->
        <!--The server informed the user is currently in the process of viewing answer poll-->
        <div v-if="gameStatus === 'Viewing Answer Poll'">
            Viewing answer poll
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
            // Event Handlers
            //
            onGameStarting(){
                // The user was previously waiting for the game to start,
                // and now the game will finally begin!
                this.getGameStatus();
            },
            onQuestionTimeEnd(){
                // The timer for the current question has expired!
                this.getGameStatus();
            },
            onAlternativeClickedOnQuestion(answerStr){
                //The user is answering a question
              this.answerCurrentQuestion(answerStr);
            },
            onJoinGameButtonClicked(){
                // The user is not in a game yet, and is trying to join one!
                this.requestToJoinGame();
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
                        case 'Not in Game':
                            this.secondsRemaining = response.data.secondsRemaining;
                            // 'Not in game' has no further data
                            break;
                        case 'Waiting for Game': // Inform the user how long they have to wait
                            this.secondsRemaining = response.data.secondsRemaining;
                            break;
                        case 'Asking Question':
                            this.secondsRemaining = response.data.secondsRemaining; // How long they have to answer
                            this.questionData = response.data.currentQuestion;
                            this.isPlayerDisqualified = response.data.isDisqualified;
                            break;
                        case 'Viewing Answer Poll':
                            // Show answers in read only mode
                            break;
                        case 'Viewing Leaderboard':
                            // Show top 3 players
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