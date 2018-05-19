<template>
    <div><p v-if="remainingSeconds > 0"> {{hours}}:{{minutes}}:{{seconds}} </p>
        <p v-if="remainingSeconds <= 0">Time's up</p>
    </div>

</template>

<script>
    export default {
        props: ['startSeconds']
        ,
        data() {
            return {
                remainingSeconds: null,
            }
        },
        methods: {
            resetTimer(){
                this.remainingSeconds = this.startSeconds;
            },
            timerLoop() {
                if (this.remainingSeconds > 0) {
                    this.remainingSeconds--;
                }

                if (this.remainingSeconds <= 0) {
                    this.$emit("time-expired");
                }

                return setTimeout(this.timerLoop, 1000);

            },
            isRunning() {
                return (this.remainingSeconds > 0);
            },
            forceDoubleDigits(number) {
                // Numbers with 3 or more digits
                if (number > 99) {
                    return "99";
                }

                // Numbers with 1 digits
                if (number < 10) {
                    return "0" + number.toString();
                }

                // Numbers with 2 digits already
                return number.toString();
            },
        },
        mounted() {
            this.remainingSeconds = this.startSeconds;
            this.timerLoop();
        },
        computed: {
            seconds() {
                return this.forceDoubleDigits((this.remainingSeconds) % 60);
            },
            minutes() {
                return this.forceDoubleDigits(Math.trunc((this.remainingSeconds) / 60) % 60);
            },
            hours() {
                return this.forceDoubleDigits(Math.trunc((this.remainingSeconds) / 60 / 24) % 24);
            },
        }
    }
</script>

