<template>
    <div v-if="questionData">

        <div class="jumbotron">
            <h1 class="display-3">Question {{questionData['questionNumber']}}</h1>
            <p class="lead">{{questionData['statement']}}</p>
            <hr class="my-2">

            <div v-if="!questionStatistics">
                <ul class="list-group">
                    <li class="list-unstyled" v-for="alternative in questionData['choices']">
                        <button v-if="isReadOnly" class="btn-sm btn-block m-1 readOnlyButton">{{alternative}}</button>
                        <button v-else class="btn-sm btn-block m-1" @click="onAlternativeClicked(alternative)">{{alternative}}</button>
                    </li>
                </ul>
            </div>

            <div v-else>
                <ul class="list-group">
                    <li class="list-unstyled" v-for="answer in questionStatistics">
                        <button class="btn readOnlyButton"></button>
                        <span class="badge-warning">{{answer.count}}</span> {{answer.answerText}}
                    </li>
                </ul>
            </div>
        </div>



    </div>
</template>

<script>
    export default {
        props:['questionData','questionStatistics','isReadOnly'],
        data(){
            return {
            }

        },
        methods:{
            onAlternativeClicked(alternative){
                console.log(`Alternative ${alternative} was clicked!`);
                this.$emit('alternative-clicked', alternative);
            },
            setReadOnly(newReadOnlyState){
                this.isReadOnly = newReadOnlyState;
            }
        },
        mounted(){
        },
    }
</script>

<style scoped>
    .readOnlyButton{
        background: #8c959d;
        color: #f5f8fa;
    }
    .statisticsButton{
        background: #8c959d;
        color: #e6eed8;
    }
</style>
