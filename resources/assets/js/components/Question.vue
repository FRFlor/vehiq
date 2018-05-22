<template>
    <div v-if="questionData">

        <div class="jumbotron">
            <h1 class="display-3">Question {{questionData['questionNumber']}}</h1>
            <p class="lead">{{questionData['statement']}}</p>
            <hr class="my-2">

            <div v-if="!questionStatistics">
                <ul class="list-group">
                    <li class="list-unstyled" v-for="alternative in questionData['choices']">
                        <button v-if="isReadOnly" class="btn-sm btn-block m-1 readOnlyButton"
                                :class="{'readOnlySelectedButton': alternative === lastAnswer}">{{alternative}}</button>

                        <button v-else class="btn-sm btn-block m-1" @click="onAlternativeClicked(alternative)">{{alternative}}</button>
                    </li>
                </ul>
            </div>

            <div v-else>
                <ul class="list-group">
                    <li class="list-unstyled" v-for="answer in questionStatistics">
                        <span class="badge badge-pill badge-info">{{answer.count}}</span>

                        <button class="btn-sm m-1" :class="{
                       'selected m-2' : answer.answerText === lastAnswer,
                       'success' : answer.isRightChoice,
                       'fail' : !answer.isRightChoice}">{{answer.answerText}}</button>

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
                lastAnswer: ''
            }

        },
        methods:{
            onAlternativeClicked(alternative){
                console.log(`Alternative ${alternative} was clicked!`);
                this.lastAnswer  = alternative;
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
    .readOnlySelectedButton{
        background: #2b73bd;
        color: #f5f8fa;
    }
    .selected{
        font-weight: bolder;
    }
    .success{
        background: mediumseagreen;
    }
    .fail{
        background: indianred;
    }
</style>
