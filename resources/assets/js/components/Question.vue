<template>
    <div v-if="questionData">
        <h1>Question {{questionData['questionNumber']}}:</h1>
        <h4>{{questionData['statement']}}</h4>
        <br/>
        <ul class="list-group">
            <li class="list-unstyled" v-for="alternative in questionData['choices']">
                <button class="btn-sm btn-block m-1" @click="onAlternativeClicked(alternative)">{{alternative}}</button>
            </li>
        </ul>
    </div>
</template>

<script>
    export default {
        props:['questionData','isReadOnly'],
        data(){
            return {
            }

        },
        methods:{
            onAlternativeClicked(alternative){
                console.log(`Alternative ${alternative} was clicked!`);
                this.$emit('alternative-clicked', alternative);
            }
        },
        mounted(){
        },
    }
</script>
