<template>
    <div>
        <game-timer :start-seconds="timerSeconds"
                    v-show="!disqualified"
                    @time-expired="onTimeExpired"></game-timer>

        <question :question-data="questionData"
                  v-show="!disqualified"
                  @alternative-clicked="answerCurrentQuestion"></question>

        <p  v-show="disqualified">Get out, loser!</p>
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
            onTimeExpired() {
                //this.showQuestion = false;
            },
            notifyTimeOut() {

            },
            retrieveQuestion() {
                console.log(`Reaching for: ${this.url}/api/game/getCurrentQuestion`);
                window.axios.get(`${this.url}/api/game/getCurrentQuestion`).then((response) => {

                    if (response.status !== 200) {
                        console.log("Retrieve-Question API failed to respond.");
                        return false;
                    }

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
                window.axios.post(`${this.url}/game/answerQuestion`,
                    {
                        answerGiven: answerStr
                    }).then((response) => {
                    if (response.status !== 200) {
                        console.log("Answer-Question API failed to respond.");
                        return false;
                    }

                    this.disqualified = !response.data.isAnswerRight;
                    console.log(`Answer-Question API replied with: ${response.data.isAnswerRight}`);
                    this.retrieveQuestion();
                    return true;
                });
            },
        },
        mounted() {
            this.retrieveQuestion();
        }
    }
</script>

<style scoped>

</style>