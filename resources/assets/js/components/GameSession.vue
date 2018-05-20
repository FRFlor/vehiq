<template>
    <div>
        <game-timer :start-seconds="timerSeconds"
                    v-show="!disqualified"
                    @time-expired="onTimeExpired"></game-timer>

        <question :question-data="questionData"
                  v-show="!disqualified"
                  @alternative-clicked="answerCurrentQuestion"></question>

        <p v-show="disqualified">Get out, loser!</p>
    </div>
</template>

<script>
    export default {
        props: ['userScore', 'userName', 'url', 'timePerQuestion'],
        data() {
            return {
                questionData: null,
                timerSeconds: 10,
                disqualified: false,
                showQuestion: true,
                userSecret: '',
            }
        },
        watch: {
            questionData: function () {
                this.$children[0].resetTimer();
            }
        },
        created() {

        },
        methods: {
            getUserSecret() {
                // Try to retrieve Secret Token for API Authentication
                axios.get('/oauth/clients')
                    .then(getAuthResponse => {
                        console.log("data Length = " + getAuthResponse.data.length);

                        // If no token is found in the database. Request creation of token
                        if (getAuthResponse.data.length === 0) {
                            axios.post('/oauth/clients', {
                                name: this.userName + Date.now(),
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
            onTimeExpired() {
                //this.showQuestion = false;
            },
            notifyTimeOut() {

            },
            retrieveQuestion() {

                if (this.userSecret === '') {
                    // If there's no user secret. Ignore the request.
                    console.log('User Secret is required to communicate with the API, request for question cannot be sent');
                    setTimeout(this.retrieveQuestion, 100);
                    return false;
                }

                console.log(`Reaching for: ${this.url}/api/game/getCurrentQuestion`);
                window.axios.get(`${this.url}/api/game/getCurrentQuestion?userSecretToken=${this.userSecret}`).then((response) => {

                    if (response.data.questionData === null) {
                        console.log("No more questions available");
                        return false;
                    }

                    this.questionData = response.data;
                    this.showQuestion = true;

                    console.log(`New Question retrieved! ${this.questionData.statement}`);
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
                axios.post(`${this.url}/api/game/answerQuestion`,{
                        userSecretToken: this.userSecret,
                        answerGiven: answerStr
                    }).then((response) => {

                    this.disqualified = !response.data.isAnswerRight;
                    console.log(`Answer-Question API replied with: ${response.data.isAnswerRight}`);
                    this.retrieveQuestion();
                });

            },
        },
        mounted() {
            this.getUserSecret();
            this.retrieveQuestion();
        }
    }
</script>

<style scoped>

</style>