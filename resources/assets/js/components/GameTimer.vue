<template>
    <div><p v-if="secondsCount > 0"> {{hours}}:{{minutes}}:{{seconds}} </p>
        <p v-if="secondsCount <= 0">Time's up</p>
    </div>

</template>

<script>
    export default {
        props: ['secondsCount'],
        data() {
            return {}
        },
        methods: {
            timerLoop() {

                if (this.secondsCount > 0) {
                    this.$emit("time-tick");
                }

                if (this.secondsCount <= 0) {
                    this.$emit("time-expired");
                }

                return setTimeout(this.timerLoop, 1000);

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
            this.timerLoop();
        },
        computed: {
            seconds() {
                return this.forceDoubleDigits((this.secondsCount) % 60);
            },
            minutes() {
                return this.forceDoubleDigits(Math.trunc((this.secondsCount) / 60) % 60);
            },
            hours() {
                return this.forceDoubleDigits(Math.trunc((this.secondsCount) / 60 / 24) % 24);
            },
        }
    }
</script>

