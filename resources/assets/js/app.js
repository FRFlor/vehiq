
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('example-component', require('./components/ExampleComponent.vue'));

Vue.component('game-timer', require('./components/GameTimer.vue'));
Vue.component('question', require('./components/Question.vue'));
Vue.component('game-session', require('./components/GameSession.vue'));


Vue.component(
    'passport-clients',
    require('./components/passport/Clients.vue')
);

Vue.component(
    'passport-authorized-clients',
    require('./components/passport/AuthorizedClients.vue')
);

Vue.component(
    'passport-personal-access-tokens',
    require('./components/passport/PersonalAccessTokens.vue')
);
const app = new Vue({
    el: '#app',
    data:{
        gameState: 'waiting',
    },
    methods:{
        updateState(newState)
        {
            this.gameState = newState;
        },

        // If targetGameId == null, the time for the next game will be fetched
        secondsToGame()
        {
            return window.axios.get( `/api/game/secondsToGame?gameId=${gameId}`).then(function (response) {
                if (response.status === 200) {
                    return response.data.secondsToGame;
                }
            });
        },
    },
});
